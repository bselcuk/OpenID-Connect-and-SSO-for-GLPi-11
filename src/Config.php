<?php
namespace GlpiPlugin\Openid;

class Config extends \CommonGLPI {
    public static $rightname = 'config';

    public static function getTypeName($nb = 0) {
        return 'OpenID SSO';
    }

    public static function getMenuContent() {
        return [
            'title' => self::getTypeName(),
            'page'  => '/plugins/openid/front/config.php',
            'icon'  => 'ti ti-shield-lock',
            'links' => [
                'search' => '/plugins/openid/front/provider.php',
                'add'    => '/plugins/openid/front/provider.form.php'
            ]
        ];
    }
}
