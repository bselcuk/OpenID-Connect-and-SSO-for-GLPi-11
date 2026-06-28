<?php

function plugin_openid_install() {
    $config = new Config();
    // Default values
    $default_config = [
        'client_id'     => '',
        'client_secret' => '',
        'provider_url'  => '',
        'user_field'    => 'email'
    ];
    
    $config->setConfigurationValues('plugin_openid', $default_config);
    
    return true;
}

function plugin_openid_uninstall() {
    global $DB;
    // Delete plugin configuration from glpi_configs
    $DB->delete('glpi_configs', ['context' => 'plugin_openid']);
    
    return true;
}

function plugin_openid_display_login() {
    global $CFG_GLPI;
    
    $plugin_url = $CFG_GLPI['root_doc'] . '/plugins/openid';
    
    // Echo HTML button on login screen
    echo '<div style="margin-top: 20px; text-align: center;">';
    echo '<a href="' . $plugin_url . '/front/login.php" class="btn btn-primary">';
    echo '<i class="fas fa-sign-in-alt"></i> Login with OpenID';
    echo '</a>';
    echo '</div>';
}
