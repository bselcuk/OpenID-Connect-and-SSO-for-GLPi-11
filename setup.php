<?php

define('PLUGIN_OPENID_VERSION', '1.3.7');

function plugin_init_openid() {
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['openid'] = true;
    
    // Eklentiler listesindeki çark ikonunun gideceği adres
    $PLUGIN_HOOKS['config_page']['openid'] = 'front/config.php';
    
    // GLPi "Yapılandırma (Setup)" menüsüne ikonlu menü öğesi ekleme
    $PLUGIN_HOOKS['menu_toadd']['openid'] = ['config' => 'GlpiPlugin\Openid\Config'];

    // Login ve Logout Kancaları
    $PLUGIN_HOOKS['display_login']['openid'] = 'plugin_openid_display_login';
    $PLUGIN_HOOKS['post_init']['openid'] = 'plugin_openid_post_init';

    // Sınıfların GLPi çekirdeğine tanıtılması (Menü ve Hooklar için zorunlu)
    Plugin::registerClass('GlpiPlugin\Openid\Provider');
    Plugin::registerClass('GlpiPlugin\Openid\Config');
}

function plugin_version_openid() {
    return [
        'name'           => 'OpenID Connect & SSO',
        'version'        => PLUGIN_OPENID_VERSION,
        'author'         => 'B.Selçuk ÖKSÜZ - MacSoft',
        'license'        => 'GPLv3+',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => '11.0'
            ]
        ]
    ];
}

function plugin_openid_check_prerequisites() {
    if (version_compare(GLPI_VERSION, '11.0', '<')) {
        echo "This plugin requires GLPI >= 11.0";
        return false;
    }
    return true;
}

function plugin_openid_check_config() {
    return true;
}
