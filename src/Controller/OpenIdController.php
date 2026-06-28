<?php

namespace GlpiPlugin\Openid\Controller;

use Glpi\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Glpi\Security\Attribute\SecurityStrategy;
use Glpi\Http\Firewall;
use User;
use Auth;
use Session;
use Html;

class OpenIdController extends AbstractController {

    #[Route('/login', name: 'openid_login')]
    #[SecurityStrategy(Firewall::STRATEGY_NO_CHECK)]
    public function login() {
        global $CFG_GLPI;
        
        $config = new \Config();
        $settings = $config->getConfigurationValues('plugin_openid');
        
        $provider_url = $settings['provider_url'] ?? '';
        $client_id = $settings['client_id'] ?? '';
        
        $redirect_uri = $CFG_GLPI['url_base'] . '/plugins/openid/callback';
        
        $params = [
            'response_type' => 'code',
            'client_id'     => $client_id,
            'scope'         => 'openid email profile',
            'redirect_uri'  => $redirect_uri
        ];
        
        $url = rtrim($provider_url, '/') . '/protocol/openid-connect/auth?' . http_build_query($params);
        
        return new \Symfony\Component\HttpFoundation\RedirectResponse($url);
    }

    #[Route('/callback', name: 'openid_callback')]
    #[SecurityStrategy(Firewall::STRATEGY_NO_CHECK)]
    public function callback() {
        global $CFG_GLPI, $DB;
        
        $code = $_GET['code'] ?? null;
        if (!$code) {
            return new \Symfony\Component\HttpFoundation\RedirectResponse($CFG_GLPI['root_doc'] . '/index.php');
        }
        
        $config = new \Config();
        $settings = $config->getConfigurationValues('plugin_openid');
        
        $provider_url = $settings['provider_url'] ?? '';
        $client_id = $settings['client_id'] ?? '';
        $client_secret = $settings['client_secret'] ?? '';
        
        $redirect_uri = $CFG_GLPI['url_base'] . '/plugins/openid/callback';
        $token_endpoint = rtrim($provider_url, '/') . '/protocol/openid-connect/token';
        
        $post_data = http_build_query([
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => $redirect_uri
        ]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $token_endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if (!$response) {
            return new \Symfony\Component\HttpFoundation\RedirectResponse($CFG_GLPI['root_doc'] . '/index.php?error=1');
        }
        
        $data = json_decode($response, true);
        if (!isset($data['id_token'])) {
            return new \Symfony\Component\HttpFoundation\RedirectResponse($CFG_GLPI['root_doc'] . '/index.php?error=1');
        }
        
        $parts = explode('.', $data['id_token']);
        if (count($parts) < 2) {
            return new \Symfony\Component\HttpFoundation\RedirectResponse($CFG_GLPI['root_doc'] . '/index.php?error=1');
        }
        
        $payload = json_decode(base64_decode($parts[1]), true);
        $email = $payload['email'] ?? '';
        
        if (empty($email)) {
            return new \Symfony\Component\HttpFoundation\RedirectResponse($CFG_GLPI['root_doc'] . '/index.php?error=1');
        }
        
        $iterator = $DB->request([
            'FROM'  => 'glpi_useremails',
            'WHERE' => ['email' => $email]
        ]);
        
        if (count($iterator) > 0) {
            $row = $iterator->current();
            $users_id = $row['users_id'];
            
            $user = new \User();
            if ($user->getFromDB($users_id)) {
                $auth = new \Auth();
                $auth->user = clone $user;
                $auth->auth_succeded = true;
                $auth->extauth = 1;
                
                \Session::init($auth);
                $redirect_url = $CFG_GLPI['root_doc'] . '/front/central.php';
                if (\Session::getCurrentInterface() === 'helpdesk') {
                    $redirect_url = $CFG_GLPI['root_doc'] . '/Helpdesk';
                }
                return new \Symfony\Component\HttpFoundation\RedirectResponse($redirect_url);
            }
        }
        
        // Eşleşme olmazsa yetkisiz uyarısı için
        return new \Symfony\Component\HttpFoundation\RedirectResponse($CFG_GLPI['root_doc'] . '/index.php?error=1');
    }
}
