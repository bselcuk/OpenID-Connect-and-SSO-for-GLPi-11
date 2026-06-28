<?php

define('PLUGIN_OPENID_VERSION', '1.0.0');

function plugin_version_openid() {
    return [
        'name'           => 'OpenID Login',
        'version'        => PLUGIN_OPENID_VERSION,
        'author'         => 'macsoft',
        'license'        => 'GPLv3+',
        'homepage'       => 'https://gitloi.macsoft.com/itsmloi/openid',
        'requirements'   => [
            'glpi' => [
                'min' => '11.0.0'
            ],
            'php' => [
                'ext-curl' => true
            ]
        ]
    ];
}

function plugin_openid_check_prerequisites() {
    if (version_compare(GLPI_VERSION, '11.0.0', '<')) {
        echo "This plugin requires GLPI >= 11.0.0";
        return false;
    }
    if (!extension_loaded('curl')) {
        echo "This plugin requires PHP cURL extension";
        return false;
    }
    return true;
}

function plugin_init_openid() {
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['openid'] = true;
    $PLUGIN_HOOKS['config_page']['openid'] = 'front/config.php';
    
    $PLUGIN_HOOKS['display_login']['openid'] = 'plugin_openid_display_login';
    
    // Register public page for callback
    $PLUGIN_HOOKS['public_pages']['openid'] = ['^/login', '^/callback'];
}

function plugin_openid_boot() {
    if (class_exists(\Glpi\Http\SessionManager::class)) {
        \Glpi\Http\SessionManager::registerPluginNoAuthPath('openid', '@^/?(login|callback)@');
    }
}
