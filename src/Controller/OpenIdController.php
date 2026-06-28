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
        // Simülasyon: Sağlayıcıya gitmiş ve geri dönmüş gibi callback sayfasına yönlendiriyoruz
        return new \Symfony\Component\HttpFoundation\RedirectResponse($CFG_GLPI['root_doc'] . '/plugins/openid/callback');
    }

    #[Route('/callback', name: 'openid_callback')]
    #[SecurityStrategy(Firewall::STRATEGY_NO_CHECK)]
    public function callback() {
        global $CFG_GLPI;
        
        // Simülasyon: OpenID sağlayıcısından dönen kullanıcı emaili
        $email_from_openid = 'admin@example.com'; 
        
        // 1. Find the User by email
        $user = new \User();
        
        // Simülasyon için: ID=2 (Genellikle 'glpi' super-admin kullanıcısıdır)
        $user->getFromDB(2); 
        
        // 2. Instantiate Auth
        $auth = new \Auth();
        
        // 3. Assign user and set flags
        $auth->user = $user;
        $auth->auth_succeded = true;
        $auth->extauth = 1;
        
        // 4. Call Session::init
        \Session::init($auth);
        
        // 5. Redirect to GLPi central page
        return new \Symfony\Component\HttpFoundation\RedirectResponse($CFG_GLPI['root_doc'] . '/front/central.php');
    }
}
