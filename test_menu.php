<?php
include(__DIR__ . "/../../inc/includes.php");
$menu = Html::generateMenuSession();
echo isset($menu['config']['content']['glpiplugin\openid\provider']) ? "YES_PROVIDER\n" : "NO_PROVIDER\n";
echo isset($menu['config']['content']['openid']) ? "YES_OPENID\n" : "NO_OPENID\n";
