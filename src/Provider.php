<?php
namespace GlpiPlugin\Openid;

class Provider extends \CommonDBTM {
    public static $rightname = 'config';

    public static function canCreate(): bool { return \Config::canUpdate(); }
    public static function canView(): bool { return \Config::canUpdate(); }
    public static function canUpdate(): bool { return \Config::canUpdate(); }
    public static function canDelete(): bool { return \Config::canUpdate(); }
    public static function canPurge(): bool { return \Config::canUpdate(); }

    static function getTypeName($nb = 0) {
        return __('OpenID Provider', 'openid');
    }

    public static function getSectorizedDetails(): array {
        return ['config', self::class];
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
            'id'                 => '9',
            'table'              => $this->getTable(),
            'field'              => 'logout_url',
            'name'               => __('Logout URL'),
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
        echo "<input type='hidden' name='_glpi_csrf_token' value='" . \Session::getNewCSRFToken() . "' />";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Name:<br><small class='text-muted'>Sağlayıcının görünen adı (Örn: Keycloak, Google, Azure AD).</small></td>";
        echo "<td><input type='text' name='name' value='" . htmlentities($this->fields['name'] ?? '') . "' class='form-control'></td>";
        echo "<td>Active:<br><small class='text-muted'>Bu sağlayıcı giriş ekranında aktif mi?</small></td>";
        echo "<td>";
        \Dropdown::showYesNo('is_active', $this->fields['is_active'] ?? 1);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Provider URL:<br><small class='text-muted'>OpenID kök adresi (Sonunda '/' olmadan).<br>(Örn: http://192.168.X.X:8080/realms/glpi)</small></td>";
        echo "<td><input type='text' name='provider_url' value='" . htmlentities($this->fields['provider_url'] ?? '') . "' class='form-control'></td>";
        echo "<td>Logout URL (End Session):<br><small class='text-muted'>SSO Oturumunu kapatma adresi.<br>(Örn: http://.../protocol/openid-connect/logout)</small></td>";
        echo "<td><input type='text' name='logout_url' value='" . htmlentities($this->fields['logout_url'] ?? '') . "' class='form-control'></td>";
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

        $default_json = "{\n  \"email\": \"email\",\n  \"given_name\": \"firstname\",\n  \"family_name\": \"realname\"\n}";
        $current_mapping = !empty($this->fields['sync_field_mapping']) ? $this->fields['sync_field_mapping'] : $default_json;

        echo "<tr class='tab_bg_1'>"; 
        echo "<td>Sync Field Mapping (JSON):<br><br><small class='text-muted'>Profil bilgilerini eşleştirmek için JSON formatı kullanın.<br><b>Format:</b> {\"OpenID_Tarafı\": \"GLPI_Tarafı\"}<br><br><b>Kullanılabilir GLPi Alanları:</b><br>name, realname, firstname, phone, phone2, mobile, registration_number, comment, timezone, is_active, begin_date, end_date</small></td>"; 
        echo "<td colspan='3'><textarea name='sync_field_mapping' class='form-control' rows='8' style='width: 100%; font-family: monospace;'>" . htmlentities($current_mapping) . "</textarea></td>"; 
        echo "</tr>";

        $this->showFormButtons($options);
        return true;
    }
}
