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
    if (strpos($c, 'string|BackedEnum|null $navigationIcon') !== false) {
        $c = preg_replace('/[ \t]*protected static string\|BackedEnum\|null \$navigationIcon = .*?;[\r\n]*/', '', $c);
        file_put_contents($f, $c);
        echo "Fixed property in $f\n";
    }
}
