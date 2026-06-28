<?php
include ("../../../inc/includes.php");
\Session::checkRight("config", UPDATE);
\Html::header("OpenID", $_SERVER['PHP_SELF'], "config", "plugins");

if (isset($_POST["update"])) {
    \Config::setConfigurationValues('plugin_openid', ['mix_mode' => $_POST['mix_mode']]);
    \Html::redirect($_SERVER['PHP_SELF']);
}
$config = \Config::getConfigurationValues('plugin_openid');
$mix_mode = isset($config['mix_mode']) ? $config['mix_mode'] : 1;

echo "<div class='center' style='margin-top:20px;'>";
echo "<a href='provider.php' class='btn btn-secondary mb-4'><i class='ti ti-brand-openid'></i> OpenID Sağlayıcılarını (Providers) Yönet</a>";
echo "<br><br>";

echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
echo "<table class='tab_cadre_fixe'>";
echo "<tr><th colspan='2'>OpenID Genel Ayarları</th></tr>";
echo "<tr class='tab_bg_1'>";
echo "<td>Mix Mod (GLPi Girişi + OpenID birlikte):<br><small class='text-muted'>Hayır seçilirse, GLPi yerel giriş formu gizlenir ve sadece OpenID butonları gösterilir. Acil durumlarda yerel giriş için URL sonuna '?noAUTO=1' ekleyebilirsiniz.</small></td>";
echo "<td>";
\Dropdown::showYesNo('mix_mode', $mix_mode);
echo "</td></tr>";
echo "<tr class='tab_bg_2'><td colspan='2' class='center'>";
echo "<input type='hidden' name='_glpi_csrf_token' value='".\Session::getNewCSRFToken()."'>";
echo "<input type='submit' name='update' value='Kaydet' class='btn btn-primary'>";
echo "</td></tr>";
echo "</table>";
\Html::closeForm();
echo "</div>";
\Html::footer();
