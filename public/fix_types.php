<?php
$files = [];
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Filament/Resources'));
foreach ($dir as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), 'Resource.php')) {
        $files[] = $file->getPathname();
    }
}
$dir2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Filament/Pages'));
foreach ($dir2 as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
        $files[] = $file->getPathname();
    }
}

foreach ($files as $f) {
    $c = file_get_contents($f);
    if (preg_match('/protected static \?string \$navigationIcon =/', $c)) {
        $c = preg_replace('/protected static \?string \$navigationIcon =/', 'protected static string|\BackedEnum|null $navigationIcon =', $c);
        file_put_contents($f, $c);
        echo "Fixed property type in $f\n";
    }
}
