<?php

namespace GlpiPlugin\Openid;

use CommonGLPI;
use Html;
use Session;

class Config extends CommonGLPI {

    static function getTypeName($nb = 0) {
        return __('OpenID Configuration', 'openid');
    }

    public static function canCreate(): bool {
        return Session::haveRight('config', UPDATE);
    }

    public static function canView(): bool {
        return Session::haveRight('config', READ);
    }

    public function showForm($id, array $options = []) {
        global $CFG_GLPI;
        
        $config = new \Config();
        $values = $config->getConfigurationValues('plugin_openid');
        
        $tpl_data = [
            'client_id'     => $values['client_id'] ?? '',
            'client_secret' => $values['client_secret'] ?? '',
            'provider_url'  => $values['provider_url'] ?? '',
            'user_field'    => $values['user_field'] ?? 'email',
            'action'        => $CFG_GLPI['root_doc'] . '/plugins/openid/front/config.php'
        ];
        
        // Output template
        \Glpi\Application\View\TemplateRenderer::getInstance()->display('@openid/config.html.twig', $tpl_data);
        
        return true;
    }
}
