<?php

function plugin_openid_install() {
    global $DB;
    $config = new Config();
    // Default values
    $default_config = [
        'client_id'     => '',
        'client_secret' => '',
        'provider_url'  => '',
        'user_field'    => 'email'
    ];
    
    $config->setConfigurationValues('plugin_openid', $default_config);
    
    if (!$DB->tableExists('glpi_plugin_openid_providers')) {
        $query = "CREATE TABLE `glpi_plugin_openid_providers` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `provider_url` VARCHAR(255) DEFAULT NULL,
            `client_id` VARCHAR(255) DEFAULT NULL,
            `client_secret` VARCHAR(255) DEFAULT NULL,
            `icon` VARCHAR(255) DEFAULT 'ti ti-brand-openid',
            `scopes` VARCHAR(255) DEFAULT 'openid email profile',
            `match_openid_claim` VARCHAR(255) DEFAULT 'email',
            `match_glpi_field` VARCHAR(255) DEFAULT 'email',
            `sync_field_mapping` TEXT DEFAULT NULL,
            `auto_provision` TINYINT(1) DEFAULT 0,
            `is_active` TINYINT(1) DEFAULT 1,
            `date_mod` TIMESTAMP NULL DEFAULT NULL,
            `date_creation` TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $DB->doQuery($query);
    }
    
    return true;
}

function plugin_openid_uninstall() {
    global $DB;
    // Delete plugin configuration from glpi_configs
    $DB->delete('glpi_configs', ['context' => 'plugin_openid']);
    
    if ($DB->tableExists('glpi_plugin_openid_providers')) {
        $DB->doQuery("DROP TABLE `glpi_plugin_openid_providers`");
    }
    
    return true;
}

function plugin_openid_display_login() {
    global $CFG_GLPI, $DB;
    if (!$DB->tableExists('glpi_plugin_openid_providers')) return;
    $iterator = $DB->request(['FROM' => 'glpi_plugin_openid_providers', 'WHERE' => ['is_active' => 1]]);
    foreach ($iterator as $provider) {
        $icon = !empty($provider['icon']) ? $provider['icon'] : 'ti ti-brand-openid';
        $url = $CFG_GLPI['url_base'] . '/plugins/openid/login?provider_id=' . $provider['id'];
        echo '<div style="margin-top: 10px; text-align: center;">';
        echo '<a href="' . $url . '" class="btn btn-primary" style="width: 100%; max-width: 300px;">';
        echo '<i class="' . $icon . '"></i> ' . $provider['name'] . ' ile Giriş Yap</a></div>';
    }
}
