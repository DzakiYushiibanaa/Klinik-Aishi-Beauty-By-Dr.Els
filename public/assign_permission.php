<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (\Spatie\Permission\Models\Role::all() as $r) {
    echo $r->name . "\n";
}
