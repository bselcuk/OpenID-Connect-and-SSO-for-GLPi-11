<?php
include ("../../../inc/includes.php");
Session::checkRight("config", UPDATE);

global $CFG_GLPI;
$form_url = $CFG_GLPI['root_doc'] . '/plugins/openid/front/config.php';

Html::header("OpenID SSO", $form_url, "config", "openid_config");

if (isset($_POST["update"])) {
    Config::setConfigurationValues('plugin_openid', ['mix_mode' => $_POST['mix_mode']]);
    Html::redirect($form_url);
}

$config = Config::getConfigurationValues('plugin_openid');
$mix_mode = isset($config['mix_mode']) ? $config['mix_mode'] : 1;

$plugin_info = Plugin::getInfo('openid');

echo "<div class='center' style='margin-top:20px;'>";

// Eklenti Bilgi (Branding) Kartı
echo "<div class='card mb-4' style='max-width: 800px; margin: 0 auto; text-align: left; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 8px;'>";
echo "<div class='card-header text-white' style='background-color: #2e3a46; border-radius: 8px 8px 0 0;'><i class='ti ti-info-circle'></i> Eklenti Bilgileri</div>";
echo "<div class='card-body' style='background-color: #f8f9fa;'>";
echo "<h3 style='margin-top: 0; color: #333;'>" . htmlentities($plugin_info['name']) . "</h3>";
echo "<p class='text-muted'>Bu eklenti, GLPi sisteminize modern ve güvenli bir Single Sign-On (SSO) altyapısı sunar. Çoklu OpenID sağlayıcılarını yönetebilir, profil eşleştirmeleri yapabilir ve Mix Mod ile kurumsal giriş kısıtlamaları uygulayabilirsiniz.</p>";
echo "<div style='display: flex; gap: 20px; flex-wrap: wrap; margin-top: 15px;'>";
echo "<div style='flex: 1; min-width: 150px;'><b>Versiyon:</b> <span class='badge bg-info'>" . htmlentities($plugin_info['version']) . "</span></div>";
echo "<div style='flex: 1; min-width: 150px;'><b>Geliştirici:</b> " . htmlentities(explode(' - ', $plugin_info['author'])[0]) . "</div>";
echo "<div style='flex: 1; min-width: 150px;'><b>Firma:</b> <strong>" . htmlentities(explode(' - ', $plugin_info['author'])[1]) . "</strong></div>";
echo "</div>";
echo "</div></div>";

// Aksiyon Butonları
echo "<div style='margin-bottom: 25px;'>";
echo "<a href='provider.php' class='btn btn-secondary' style='margin-right:10px;'><i class='ti ti-list'></i> Sağlayıcı (Provider) Listesi</a>";
echo "<a href='provider.form.php' class='btn btn-success'><i class='ti ti-plus'></i> Yeni Sağlayıcı Ekle</a>";
echo "</div>";

// Genel Ayarlar Formu
echo "<form action='" . $form_url . "' method='post' style='max-width: 800px; margin: 0 auto;'>";
echo "<table class='tab_cadre_fixe'>";
echo "<tr><th colspan='2'><i class='ti ti-settings'></i> OpenID Genel Ayarları ve Güvenlik</th></tr>";
echo "<tr class='tab_bg_1'>";
echo "<td><b>Mix Mod (GLPi Yerel Girişi + OpenID Birlikte):</b><br><small class='text-muted'>'Hayır' seçilirse, standart GLPi kullanıcı adı ve şifre kutuları tamamen gizlenir. Sadece eklediğiniz SSO sağlayıcılarının giriş butonları ekranda kalır.<br><br><i>Olası acil durumlarda yerel girişi açmak için URL'nin sonuna <b>?local_login=1</b> parametresini ekleyebilirsiniz.</i></small></td>";
echo "<td class='center' style='width: 20%;'>";
Dropdown::showYesNo('mix_mode', $mix_mode);
echo "</td></tr>";
echo "<tr class='tab_bg_2'><td colspan='2' class='center'>";
echo "<input type='hidden' name='_glpi_csrf_token' value='".Session::getNewCSRFToken()."'>";
echo "<button type='submit' name='update' class='btn btn-primary'><i class='ti ti-device-floppy'></i> Ayarları Kaydet</button>";
echo "</td></tr>";
echo "</table>";
Html::closeForm();
echo "</div>";
Html::footer();
