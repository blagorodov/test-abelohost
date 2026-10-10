<?php

use Smarty\Smarty;

require dirname(__DIR__) . '/vendor/autoload.php';

$smarty = new Smarty();
$smarty->setTemplateDir(dirname(__DIR__) . '/templates');
$smarty->setCompileDir(dirname(__DIR__) . '/templates_c');
$smarty->setCacheDir(dirname(__DIR__) . '/cache');
$smarty->display('index.tpl');
