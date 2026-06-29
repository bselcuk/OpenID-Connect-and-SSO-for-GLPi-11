<?php
include ("../../../inc/includes.php");

\Session::checkRight("config", READ);

\Html::header(
    \GlpiPlugin\Openid\Provider::getTypeName(2),
    $_SERVER['PHP_SELF'],
    "config",
    \GlpiPlugin\Openid\Config::class,
    \GlpiPlugin\Openid\Provider::class
);
\Search::show('GlpiPlugin\Openid\Provider');
\Html::footer();
