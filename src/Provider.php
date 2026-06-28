<?php
namespace GlpiPlugin\Openid;

class Provider extends \CommonDBTM {
    public static $rightname = 'config';

    public static function canCreate(): bool { return \Config::canUpdate(); }
    public static function canView(): bool { return \Session::haveRight('config', READ); }
    public static function canUpdate(): bool { return \Config::canUpdate(); }
    public static function canDelete(): bool { return \Config::canUpdate(); }
    public static function canPurge(): bool { return \Config::canUpdate(); }

    static function getTypeName($nb = 0) {
        return __('OpenID Provider', 'openid');
    }

    public static function getMenuContent() {
        return [
            'title' => self::getTypeName(2),
            'page'  => '/plugins/openid/front/provider.php',
            'icon'  => 'ti ti-brand-openid',
            'links' => [
                'search' => '/plugins/openid/front/provider.php',
                'add'    => '/plugins/openid/front/provider.form.php'
            ]
        ];
    }

    function rawSearchOptions() {
        $tab = [];

        $tab[] = [
            'id'                 => 'common',
            'name'               => __('Characteristics')
        ];

        $tab[] = [
            'id'                 => '1',
            'table'              => $this->getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => 'is_active',
            'name'               => __('Active'),
            'datatype'           => 'bool'
        ];

        $tab[] = [
            'id'                 => '3',
            'table'              => $this->getTable(),
            'field'              => 'provider_url',
            'name'               => __('Provider URL'),
            'datatype'           => 'string'
        ];

        $tab[] = [
            'id'                 => '4',
            'table'              => $this->getTable(),
            'field'              => 'client_id',
            'name'               => __('Client ID'),
            'datatype'           => 'string'
        ];

        $tab[] = [
            'id'                 => '5',
            'table'              => $this->getTable(),
            'field'              => 'scopes',
            'name'               => __('Scopes'),
            'datatype'           => 'string'
        ];
        
        $tab[] = [
            'id'                 => '6',
            'table'              => $this->getTable(),
            'field'              => 'match_openid_claim',
            'name'               => __('Match OpenID Claim', 'openid'),
            'datatype'           => 'string'
        ];

        $tab[] = [
            'id'                 => '7',
            'table'              => $this->getTable(),
            'field'              => 'match_glpi_field',
            'name'               => __('Match GLPI Field', 'openid'),
            'datatype'           => 'string'
        ];

        $tab[] = [
            'id'                 => '8',
            'table'              => $this->getTable(),
            'field'              => 'auto_provision',
            'name'               => __('Auto-Provision', 'openid'),
            'datatype'           => 'bool'
        ];

        return $tab;
    }

    public function showForm($ID, array $options = []) {
        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>Name:<br><small class='text-muted'>Sağlayıcının görünen adı (Örn: Keycloak, Google, Azure AD).</small></td>";
        echo "<td><input type='text' name='name' value='" . htmlentities($this->fields['name'] ?? '') . "' class='form-control'></td>";
        echo "<td>Active:<br><small class='text-muted'>Bu sağlayıcı giriş ekranında aktif mi?</small></td>";
        echo "<td>";
        \Dropdown::showYesNo('is_active', $this->fields['is_active'] ?? 1);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Provider URL:<br><small class='text-muted'>OpenID sağlayıcısının kök adresi (Örn: https://accounts.google.com veya http://IP:8080/realms/glpi).</small></td>";
        echo "<td><input type='text' name='provider_url' value='" . htmlentities($this->fields['provider_url'] ?? '') . "' class='form-control'></td>";
        echo "<td>Icon Class:<br><small class='text-muted'>Buton ikonu (Örn: ti ti-key, ti ti-brand-google).</small></td>";
        echo "<td><input type='text' name='icon' value='" . htmlentities($this->fields['icon'] ?? 'ti ti-brand-openid') . "' class='form-control'></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Client ID:<br><small class='text-muted'>Sağlayıcıdan alınan benzersiz istemci kimliği.</small></td>";
        echo "<td><input type='text' name='client_id' value='" . htmlentities($this->fields['client_id'] ?? '') . "' class='form-control'></td>";
        echo "<td>Client Secret:<br><small class='text-muted'>Sağlayıcıdan alınan gizli anahtar (Sadece yetkili sunucular arası iletişimde kullanılır).</small></td>";
        echo "<td><input type='password' name='client_secret' value='" . htmlentities($this->fields['client_secret'] ?? '') . "' class='form-control'></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Scopes:<br><small class='text-muted'>İzin kapsamları. Standart OIDC için genellikle 'openid email profile' kullanılır.</small></td>";
        echo "<td><input type='text' name='scopes' value='" . htmlentities($this->fields['scopes'] ?? 'openid email profile') . "' class='form-control'></td>";
        echo "<td>Auto-Provision:<br><small class='text-muted'>Kullanıcı GLPi'de yoksa otomatik oluşturulsun mu?</small></td>";
        echo "<td>";
        \Dropdown::showYesNo('auto_provision', $this->fields['auto_provision'] ?? 0);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Match OpenID Claim:<br><small class='text-muted'>Token'dan dönecek benzersiz kimlik değeri (Örn: email, preferred_username, sub).</small></td>";
        echo "<td><input type='text' name='match_openid_claim' value='" . htmlentities($this->fields['match_openid_claim'] ?? 'email') . "' class='form-control'></td>";
        echo "<td>Match GLPI Field:<br><small class='text-muted'>Yukarıdaki claim değerinin GLPi veritabanında hangi alanla eşleştirileceği.</small></td>";
        echo "<td>";
        \Dropdown::showFromArray('match_glpi_field', ['email' => 'Email', 'name' => 'Username'], ['value' => $this->fields['match_glpi_field'] ?? 'email']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>"; 
        echo "<td>Sync Field Mapping (JSON):<br> <small class='text-muted'> Profil bilgilerini eşleştirmek için JSON formatı kullanın.<br> <b>Format:</b> {\"OpenID_Tarafı\": \"GLPI_Tarafı\"}<br><br> <b>Kullanabileceğiniz GLPi Sütun İsimleri:</b><br> - <b>name</b> (Kullanıcı Adı)<br> - <b>realname</b> (Soyad)<br> - <b>firstname</b> (Ad)<br> - <b>phone</b> (Telefon)<br> - <b>phone2</b> (Telefon 2)<br> - <b>mobile</b> (Cep Telefonu)<br> - <b>registration_number</b> (İdari numara)<br> - <b>comment</b> (Notlar)<br> - <b>timezone</b> (Saat dilimi)<br> - <b>is_active</b> (Etkin - 1 veya 0)<br> - <b>begin_date</b> (Geçerlilik Başlangıcı YYYY-MM-DD)<br> - <b>end_date</b> (Geçerlilik Bitişi YYYY-MM-DD)<br><br> <i>* Not: Fotoğraf fiziksel bir dosya olduğu için; Konum, Kategori ve Başlık gibi alanlar ise metin yerine ID numarası (locations_id vb.) gerektirdiği için salt metin eşleştirmesiyle (JSON) doğrudan senkronize edilemez. E-posta adresleri ise kimlik eşleşmesi sırasında sistem tarafından otomatik yönetilir.</i> </small></td>"; 
        echo "<td colspan='3'><textarea name='sync_field_mapping' class='form-control' rows='14' style='width: 100%;'>" . htmlentities($this->fields['sync_field_mapping'] ?? '') . "</textarea></td>"; 
        echo "</tr>";

        $this->showFormButtons($options);
        return true;
    }
}
