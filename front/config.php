<?php
include ("../../../inc/includes.php");
\Session::checkRight("config", UPDATE);

global $CFG_GLPI;
$form_url = $CFG_GLPI['root_doc'] . '/plugins/openid/front/config.php';

\Html::header("OpenID", $form_url, "config", "plugins");

if (isset($_POST["update"])) {
    \Config::setConfigurationValues('plugin_openid', ['mix_mode' => $_POST['mix_mode']]);
    \Html::redirect($form_url);
}
$config = \Config::getConfigurationValues('plugin_openid');
$mix_mode = isset($config['mix_mode']) ? $config['mix_mode'] : 1;

echo "<div class='center' style='margin-top:20px;'>";
echo "<a href='provider.php' class='btn btn-secondary mb-4' style='margin-right:10px;'><i class='ti ti-list'></i> Sağlayıcı Listesi</a>";
echo "<a href='provider.form.php' class='btn btn-success mb-4'><i class='ti ti-plus'></i> Yeni Sağlayıcı Ekle</a>";
echo "<br><br>";

echo "<form action='" . $form_url . "' method='post'>";
echo "<table class='tab_cadre_fixe'>";
echo "<tr><th colspan='2'>OpenID Genel Ayarları</th></tr>";
echo "<tr class='tab_bg_1'>";
echo "<td>Mix Mod (GLPi Girişi + OpenID birlikte):<br><small class='text-muted'>Hayır seçilirse, GLPi yerel giriş formu gizlenir ve sadece OpenID butonları gösterilir. Acil durumlarda yerel giriş için URL sonuna '?local_login=1' ekleyebilirsiniz.</small></td>";
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
