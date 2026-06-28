<?php
include ("../../../inc/includes.php");

\Session::checkRight("config", READ);

$provider = new \GlpiPlugin\Openid\Provider();

if (isset($_POST["add"])) {
    $provider->check(-1, CREATE, $_POST);
    $provider->add($_POST);
    \Html::back();
} else if (isset($_POST["update"])) {
    $provider->check($_POST["id"], UPDATE);
    $provider->update($_POST);
    \Html::back();
} else if (isset($_POST["purge"])) {
    $provider->check($_POST["id"], PURGE);
    $provider->delete($_POST, 1);
    $provider->redirectToList();
} else {
    \Html::header(__('OpenID Providers', 'openid'), $_SERVER['PHP_SELF'], "config", "openid");
    $provider->display($_GET);
    \Html::footer();
}
