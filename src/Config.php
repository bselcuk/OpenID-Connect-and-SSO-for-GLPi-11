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
            'icon'  => 'ti ti-shield-lock' // Yapılandırma menüsünde görünecek ikon
        ];
    }
}
