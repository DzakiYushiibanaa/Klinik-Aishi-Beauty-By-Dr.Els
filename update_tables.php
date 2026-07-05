<?php
$dir = new RecursiveDirectoryIterator("app/Filament/Resources");
$iterator = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($iterator, "/Table\.php$/", RecursiveRegexIterator::MATCH);

foreach($files as $file) {
    $path = $file->getPathname();
    $content = file_get_contents($path);
    if (strpos($content, "->rowIndex()") === false && preg_match("/->columns\(\s*\[/", $content)) {
        $content = preg_replace("/->columns\(\s*\[/", "->columns([\n                \Filament\Tables\Columns\TextColumn::make(\"No\")->rowIndex(),", $content);
        file_put_contents($path, $content);
        echo "Updated $path\n";
    }
}

