<?php

use Smarty\Smarty;

function render(string $template, int $status = 200, array $vars = []): void
{
    http_response_code($status);
    $root = dirname(__DIR__);
    $smarty = new Smarty();
    $smarty->setTemplateDir($root . '/templates');
    $smarty->setCompileDir($root . '/templates_c');
    $smarty->setCacheDir($root . '/cache');
    foreach ($vars as $key => $value) {
        $smarty->assign($key, $value);
    }
    $smarty->display($template);
}
