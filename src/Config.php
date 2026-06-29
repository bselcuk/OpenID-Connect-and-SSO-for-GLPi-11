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
            'options' => [
                \GlpiPlugin\Openid\Provider::class => [
                    'title' => \GlpiPlugin\Openid\Provider::getTypeName(2),
                    'page'  => \GlpiPlugin\Openid\Provider::getSearchURL(false),
                    'icon'  => 'ti ti-brand-openid',
                    'links' => [
                        'search' => \GlpiPlugin\Openid\Provider::getSearchURL(false),
                        'add'    => \GlpiPlugin\Openid\Provider::getFormURL(false)
                    ]
                ]
            ]
        ];
    }
}
