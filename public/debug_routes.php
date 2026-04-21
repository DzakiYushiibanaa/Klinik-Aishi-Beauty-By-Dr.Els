<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (app('router')->getRoutes() as $r) {
    if (strpos($r->getName(), 'admin') !== false && strpos($r->getName(), 'profile') !== false) {
        echo $r->getName() . PHP_EOL;
    }
}
