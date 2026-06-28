<?php

include ("../../../inc/includes.php");

Session::checkRight("config", UPDATE);

$plugin_config = new \GlpiPlugin\Openid\Config();

if (isset($_POST["update"])) {
    $config = new Config();
    $config->setConfigurationValues('plugin_openid', [
        'client_id'     => $_POST['client_id'] ?? '',
        'client_secret' => $_POST['client_secret'] ?? '',
        'provider_url'  => $_POST['provider_url'] ?? '',
        'user_field'    => $_POST['user_field'] ?? 'email'
    ]);
    
    Html::back();
}

Html::header(\GlpiPlugin\Openid\Config::getTypeName(1));
$plugin_config->showForm(1);
Html::footer();
