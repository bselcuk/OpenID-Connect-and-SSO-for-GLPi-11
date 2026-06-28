<?php
include ("../../../inc/includes.php");

\Session::checkRight("config", READ);

\Html::header(__('OpenID Providers', 'openid'), $_SERVER['PHP_SELF'], "config", "openid");
\Search::show('GlpiPlugin\Openid\Provider');
\Html::footer();
