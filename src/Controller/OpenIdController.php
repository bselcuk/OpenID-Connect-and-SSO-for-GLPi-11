<?php

namespace GlpiPlugin\Openid\Controller;

use Glpi\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use User;
use Auth;
use Session;
use Html;

class OpenIdController extends AbstractController {

    #[Route('/login', name: 'plugin_openid_login', methods: ['GET'])]
    public function login() {
        global $CFG_GLPI;
        // Simülasyon: Sağlayıcıya gitmiş ve geri dönmüş gibi callback sayfasına yönlendiriyoruz
        Html::redirect($CFG_GLPI['root_doc'] . '/plugins/openid/callback');
    }

    #[Route('/callback', name: 'plugin_openid_callback', methods: ['GET'])]
    public function callback() {
        global $CFG_GLPI;
        
        // Simülasyon: OpenID sağlayıcısından dönen kullanıcı emaili
        $email_from_openid = 'admin@example.com'; 
        
        // 1. Find the User by email
        $user = new User();
        
        // Simülasyon için: ID=2 (Genellikle 'glpi' super-admin kullanıcısıdır) veya email ile bulma simülasyonu
        // Gerçek kodda şöyle olacaktı: $user->getFromDBByCrit(['email' => $email_from_openid]);
        // Şimdilik oturumun başarıyla açılabilmesi için ID=2 kullanıcısını yüklüyoruz.
        $user->getFromDB(2); 
        
        // 2. Instantiate Auth
        $auth = new Auth();
        
        // 3. Assign user and set flags
        $auth->user = $user;
        $auth->auth_succeded = true;
        $auth->extauth = 1;
        
        // 4. Call Session::init
        Session::init($auth);
        
        // 5. Redirect to GLPi central page
        Html::redirect($CFG_GLPI['root_doc'] . '/front/central.php');
    }
}
