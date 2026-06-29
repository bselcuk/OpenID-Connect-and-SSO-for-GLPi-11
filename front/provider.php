<?php
include ("../../../inc/includes.php");

\Session::checkRight("config", READ);

\Html::header(__('OpenID Providers', 'openid'), $_SERVER['PHP_SELF'], "config", \GlpiPlugin\Openid\Provider::class);
\Search::show('GlpiPlugin\Openid\Provider');
\Html::footer();
