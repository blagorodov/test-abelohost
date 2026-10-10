<?php

use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

return Dotenv::createArrayBacked(__DIR__)->load();
