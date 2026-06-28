<?php
namespace GlpiPlugin\Openid;

class Provider extends \CommonDBTM {
    static $rightname = 'config';

    public static function canCreate(): bool { return (bool) \Session::haveRight('config', UPDATE); }
    public static function canView(): bool { return (bool) \Session::haveRight('config', READ); }
    public static function canUpdate(): bool { return (bool) \Session::haveRight('config', UPDATE); }
    public static function canDelete(): bool { return (bool) \Session::haveRight('config', UPDATE); }
    public static function canPurge(): bool { return (bool) \Session::haveRight('config', UPDATE); }

    static function getTypeName($nb = 0) {
        return __('OpenID Provider', 'openid');
    }

    static function getMenuContent() {
        global $CFG_GLPI;
        return [
            'title' => self::getTypeName(2),
            'page'  => '/plugins/openid/front/provider.php',
            'icon'  => 'ti ti-brand-openid'
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
        echo "<td>Name:</td>";
        echo "<td><input type='text' name='name' value='" . htmlentities($this->fields['name'] ?? '') . "' class='form-control'></td>";
        echo "<td>Active:</td>";
        echo "<td>";
        \Dropdown::showYesNo('is_active', $this->fields['is_active'] ?? 1);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Provider URL:</td>";
        echo "<td><input type='text' name='provider_url' value='" . htmlentities($this->fields['provider_url'] ?? '') . "' class='form-control'></td>";
        echo "<td>Icon Class:</td>";
        echo "<td><input type='text' name='icon' value='" . htmlentities($this->fields['icon'] ?? 'ti ti-brand-openid') . "' class='form-control'></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Client ID:</td>";
        echo "<td><input type='text' name='client_id' value='" . htmlentities($this->fields['client_id'] ?? '') . "' class='form-control'></td>";
        echo "<td>Client Secret:</td>";
        echo "<td><input type='password' name='client_secret' value='" . htmlentities($this->fields['client_secret'] ?? '') . "' class='form-control'></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Scopes:</td>";
        echo "<td><input type='text' name='scopes' value='" . htmlentities($this->fields['scopes'] ?? 'openid email profile') . "' class='form-control'></td>";
        echo "<td>Auto-Provision:</td>";
        echo "<td>";
        \Dropdown::showYesNo('auto_provision', $this->fields['auto_provision'] ?? 0);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Match OpenID Claim:</td>";
        echo "<td><input type='text' name='match_openid_claim' value='" . htmlentities($this->fields['match_openid_claim'] ?? 'email') . "' class='form-control'></td>";
        echo "<td>Match GLPI Field:</td>";
        echo "<td>";
        \Dropdown::showFromArray('match_glpi_field', ['email' => 'Email', 'name' => 'Username'], ['value' => $this->fields['match_glpi_field'] ?? 'email']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Sync Field Mapping (JSON):</td>";
        echo "<td colspan='3'><textarea name='sync_field_mapping' class='form-control' rows='4' style='width: 100%;'>" . htmlentities($this->fields['sync_field_mapping'] ?? '') . "</textarea></td>";
        echo "</tr>";
        
        $this->showFormButtons($options);
        return true;
    }
}
