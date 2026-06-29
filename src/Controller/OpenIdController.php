<?php
namespace GlpiPlugin\Openid\Controller;

use Glpi\Http\SessionManager;
use Glpi\Security\Attribute\SecurityStrategy;
use Glpi\Http\Firewall;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class OpenIdController extends \Glpi\Controller\AbstractController {

    #[Route('/login', name: 'openid_login')]
    #[SecurityStrategy(Firewall::STRATEGY_NO_CHECK)]
    public function login() {
        global $CFG_GLPI;
        $provider_id = $_GET['provider_id'] ?? 0;
        $provider = new \GlpiPlugin\Openid\Provider();

        if (!$provider->getFromDB($provider_id) || !$provider->fields['is_active']) {
            \Session::addMessageAfterRedirect('Gecersiz veya pasif OpenID saglayicisi.', false, ERROR);
            return new RedirectResponse($CFG_GLPI['url_base'] . '/index.php');
        }

        $_SESSION['glpi_plugins']['openid_provider_id'] = $provider_id;
        $provider_url = rtrim($provider->fields['provider_url'], '/');
        $redirect_uri = $CFG_GLPI['url_base'] . '/plugins/openid/callback';
        
        $url = $provider_url . '/protocol/openid-connect/auth?response_type=code&client_id=' . urlencode($provider->fields['client_id']) . '&scope=' . urlencode($provider->fields['scopes']) . '&redirect_uri=' . urlencode($redirect_uri);

        return new RedirectResponse($url);
    }

    #[Route('/callback', name: 'openid_callback')]
    #[SecurityStrategy(Firewall::STRATEGY_NO_CHECK)]
    public function callback() {
        global $CFG_GLPI, $DB;
        $code = $_GET['code'] ?? '';
        $provider_id = $_SESSION['glpi_plugins']['openid_provider_id'] ?? 0;
        $provider = new \GlpiPlugin\Openid\Provider();

        if (empty($code) || !$provider->getFromDB($provider_id)) {
            return new RedirectResponse($CFG_GLPI['url_base'] . '/index.php?error=1');
        }

        $provider_url = rtrim($provider->fields['provider_url'], '/');
        $redirect_uri = $CFG_GLPI['url_base'] . '/plugins/openid/callback';

        $ch = curl_init($provider_url . '/protocol/openid-connect/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $provider->fields['client_id'],
            'client_secret' => $provider->fields['client_secret'],
            'redirect_uri' => $redirect_uri
        ]));
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        \Toolbox::logInFile('openid', "Token Request HTTP Code: $http_code\n");
        if ($response === false) {
            \Toolbox::logInFile('openid', "cURL Error: $curl_error\n");
        } else {
            \Toolbox::logInFile('openid', "Token Response: $response\n");
        }

        $data = json_decode($response, true);
        if (!isset($data['id_token'])) {
            \Toolbox::logInFile('openid', "id_token is missing! Redirecting to error=1\n");
            return new RedirectResponse($CFG_GLPI['url_base'] . '/index.php?error=1');
        }

        $parts = explode('.', $data['id_token']);
        $payload = json_decode(base64_decode($parts[1]), true);

        $claim_key = !empty($provider->fields['match_openid_claim']) ? $provider->fields['match_openid_claim'] : 'email';
        $glpi_field = !empty($provider->fields['match_glpi_field']) ? $provider->fields['match_glpi_field'] : 'email';
        $claim_value = $payload[$claim_key] ?? '';

        if (empty($claim_value)) {
             \Session::addMessageAfterRedirect("Token icinde '$claim_key' degeri bulunamadi.", false, ERROR);
             return new RedirectResponse($CFG_GLPI['url_base'] . '/index.php');
        }

        $users_id = 0;
        if ($glpi_field === 'email') {
            $iterator = $DB->request(['SELECT' => 'users_id', 'FROM' => 'glpi_useremails', 'WHERE' => ['email' => $claim_value]]);
            if (count($iterator)) {
                $users_id = $iterator->current()['users_id'];
            }
        } else {
            $iterator = $DB->request(['SELECT' => 'id', 'FROM' => 'glpi_users', 'WHERE' => [$glpi_field => $claim_value]]);
            if (count($iterator)) {
                $users_id = $iterator->current()['id'];
            }
        }

        $user = new \User();
        if ($users_id > 0) {
            $user->getFromDB($users_id);
        } else {
            if ($provider->fields['auto_provision']) {
                $input = [
                    'name' => $payload['preferred_username'] ?? $claim_value,
                    'is_active' => 1
                ];
                if ($glpi_field === 'email') {
                    // In GLPi 10+, emails are managed via UserEmail class, not User::add() input.
                    // We will add it right after user creation.
                }
                $users_id = $user->add($input);
                if (!$users_id) {
                     \Session::addMessageAfterRedirect("Kullanici otomatik olusturulamadi.", false, ERROR);
                     return new RedirectResponse($CFG_GLPI['url_base'] . '/index.php');
                }
                
                // Add the email via UserEmail
                if ($glpi_field === 'email' && !empty($claim_value)) {
                    $userEmail = new \UserEmail();
                    $userEmail->add([
                        'users_id'   => $users_id,
                        'email'      => $claim_value,
                        'is_default' => 1,
                        'is_dynamic' => 1
                    ]);
                }
                
                $user->getFromDB($users_id);
            } else {
                \Session::addMessageAfterRedirect("Yetkisiz erisim. Kullanici kaydi bulunamadi.", false, ERROR);
                return new RedirectResponse($CFG_GLPI['url_base'] . '/index.php');
            }
        }

        if (!empty($provider->fields['sync_field_mapping'])) {
            $mapping = json_decode($provider->fields['sync_field_mapping'], true);
            if (is_array($mapping)) {
                $update_input = ['id' => $users_id];
                $needs_update = false;
                foreach ($mapping as $oid_key => $g_field) {
                    if (!isset($payload[$oid_key])) continue;
                    
                    if ($g_field === 'email') {
                        $new_email = $payload[$oid_key];
                        $email_iterator = $DB->request(['SELECT' => 'id', 'FROM' => 'glpi_useremails', 'WHERE' => ['users_id' => $users_id, 'email' => $new_email]]);
                        if (count($email_iterator) == 0) {
                            $userEmail = new \UserEmail();
                            $userEmail->add([
                                'users_id'   => $users_id,
                                'email'      => $new_email,
                                'is_default' => 1,
                                'is_dynamic' => 1
                            ]);
                        }
                        continue;
                    }
                    
                    if (array_key_exists($g_field, $user->fields) && $user->fields[$g_field] != $payload[$oid_key]) {
                        $update_input[$g_field] = $payload[$oid_key];
                        $needs_update = true;
                    }
                }
                if ($needs_update) {
                    $user->update($update_input);
                    $user->getFromDB($users_id); // Refresh user object after update
                }
            }
        }

        // Ensure user has at least one profile to prevent error=1 (Access Denied)
        $profile_iterator = $DB->request(['SELECT' => 'id', 'FROM' => 'glpi_profiles_users', 'WHERE' => ['users_id' => $users_id]]);
        if (count($profile_iterator) == 0) {
            $profUser = new \Profile_User();
            $profUser->add([
                'users_id'    => $users_id,
                'profiles_id' => 1, // 1 is usually Self-Service
                'entities_id' => 0, // Root entity
                'is_dynamic'  => 1
            ]);
        }

        $auth = new \Auth();
        $auth->user = clone $user;
        $auth->auth_succeded = true;
        $auth->extauth = 1;
        \Session::init($auth);

        return new RedirectResponse($CFG_GLPI['url_base'] . '/index.php');
    }

    #[Route('/logout', name: 'openid_logout')]
    #[SecurityStrategy(Firewall::STRATEGY_NO_CHECK)]
    public function logout() {
        global $CFG_GLPI;
        $provider_id = $_SESSION['glpi_plugins']['openid_provider_id'] ?? 0;

        if (class_exists(\Session::class)) {
            \Session::cleanOnLogout(); // GLPi 11.0 native logout cleaner
        }

        // noAUTO veya local_login parametresi YOK. Doğrudan index.php'ye gönderiyoruz.
        // Böylece tekil sağlayıcı varsa tekrar SSO'ya (bu kez Keycloak'tan çıkış yapıldığı için KC login ekranına) düşer.
        $redirect_uri = $CFG_GLPI['url_base'] . '/index.php';

        if ($provider_id > 0) {
            $provider = new \GlpiPlugin\Openid\Provider();
            if ($provider->getFromDB($provider_id) && !empty($provider->fields['logout_url'])) {
                $logout_url = trim($provider->fields['logout_url']);
                $client_id = $provider->fields['client_id'];
                $sep = strpos($logout_url, '?') !== false ? '&' : '?';
                $final_url = $logout_url . $sep . 'client_id=' . urlencode($client_id) . '&post_logout_redirect_uri=' . urlencode($redirect_uri);
                return new RedirectResponse($final_url);
            }
        }
        return new RedirectResponse($redirect_uri);
    }
}
