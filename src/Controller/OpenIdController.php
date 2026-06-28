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

        $_SESSION['openid_provider_id'] = $provider_id;
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
        $provider_id = $_SESSION['openid_provider_id'] ?? 0;
        $provider = new \GlpiPlugin\Openid\Provider();

        if (empty($code) || !$provider->getFromDB($provider_id)) {
            return new RedirectResponse($CFG_GLPI['url_base'] . '/index.php?error=1');
        }

        $provider_url = rtrim($provider->fields['provider_url'], '/');
        $redirect_uri = $CFG_GLPI['url_base'] . '/plugins/openid/callback';

        $ch = curl_init($provider_url . '/protocol/openid-connect/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $provider->fields['client_id'],
            'client_secret' => $provider->fields['client_secret'],
            'redirect_uri' => $redirect_uri
        ]));
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!isset($data['id_token'])) {
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
                    $input['_useremails'] = [$claim_value];
                }
                $users_id = $user->add($input);
                if (!$users_id) {
                     \Session::addMessageAfterRedirect("Kullanici otomatik olusturulamadi.", false, ERROR);
                     return new RedirectResponse($CFG_GLPI['url_base'] . '/index.php');
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
                    if (isset($payload[$oid_key]) && $user->fields[$g_field] != $payload[$oid_key]) {
                        $update_input[$g_field] = $payload[$oid_key];
                        $needs_update = true;
                    }
                }
                if ($needs_update) {
                    $user->update($update_input);
                }
            }
        }

        $auth = new \Auth();
        $auth->user = clone $user;
        $auth->auth_succeded = true;
        $auth->extauth = 1;
        \Session::init($auth);

        $redirect_url = $CFG_GLPI['root_doc'] . '/front/central.php';
        if (\Session::getCurrentInterface() === 'helpdesk') {
            $redirect_url = $CFG_GLPI['root_doc'] . '/Helpdesk';
        }
        return new RedirectResponse($redirect_url);
    }

    #[Route('/logout', name: 'openid_logout')]
    #[SecurityStrategy(Firewall::STRATEGY_NO_CHECK)]
    public function logout() {
        global $CFG_GLPI;
        $provider_id = $_SESSION['openid_provider_id'] ?? 0;

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
