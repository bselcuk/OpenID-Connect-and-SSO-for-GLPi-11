<?php
namespace GlpiPlugin\Openid;

class Provider extends \CommonDBTM {
    static $rightname = 'config';

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

    function showForm($ID, array $options = []) {
        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . "</td>";
        echo "<td>";
        \Html::autocompletionTextField($this, "name");
        echo "</td>";
        
        echo "<td>" . __('Active') . "</td>";
        echo "<td>";
        \Dropdown::showYesNo("is_active", $this->fields["is_active"]);
        echo "</td>";
        echo "</tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Provider URL', 'openid') . "</td>";
        echo "<td>";
        \Html::autocompletionTextField($this, "provider_url");
        echo "</td>";
        
        echo "<td>" . __('Client ID', 'openid') . "</td>";
        echo "<td>";
        \Html::autocompletionTextField($this, "client_id");
        echo "</td>";
        echo "</tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Client Secret', 'openid') . "</td>";
        echo "<td>";
        echo "<input type='password' name='client_secret' value='" . \Html::cleanInputText($this->fields["client_secret"]) . "' size='30'>";
        echo "</td>";
        
        echo "<td>" . __('Icon', 'openid') . "</td>";
        echo "<td>";
        \Html::autocompletionTextField($this, "icon", ['value' => empty($this->fields["icon"]) ? 'ti ti-brand-openid' : $this->fields["icon"]]);
        echo "</td>";
        echo "</tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Scopes', 'openid') . "</td>";
        echo "<td>";
        \Html::autocompletionTextField($this, "scopes", ['value' => empty($this->fields["scopes"]) ? 'openid email profile' : $this->fields["scopes"]]);
        echo "</td>";
        
        echo "<td>" . __('Match OpenID Claim', 'openid') . "</td>";
        echo "<td>";
        \Html::autocompletionTextField($this, "match_openid_claim", ['value' => empty($this->fields["match_openid_claim"]) ? 'email' : $this->fields["match_openid_claim"]]);
        echo "</td>";
        echo "</tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Match GLPI Field', 'openid') . "</td>";
        echo "<td>";
        \Dropdown::showFromArray("match_glpi_field", [
            'email' => __('Email'),
            'name'  => __('Username')
        ], ['value' => $this->fields["match_glpi_field"]]);
        echo "</td>";
        
        echo "<td>" . __('Auto-Provision', 'openid') . "</td>";
        echo "<td>";
        \Dropdown::showYesNo("auto_provision", $this->fields["auto_provision"]);
        echo "</td>";
        echo "</tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Sync Field Mapping (JSON)', 'openid') . "</td>";
        echo "<td colspan='3'>";
        echo "<textarea name='sync_field_mapping' cols='80' rows='4'>" . \Html::cleanInputText($this->fields["sync_field_mapping"]) . "</textarea>";
        echo "</td>";
        echo "</tr>\n";

        $this->showFormButtons($options);
        return true;
    }
}
