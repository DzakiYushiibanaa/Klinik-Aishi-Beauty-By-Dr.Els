<?php
require 'vendor/autoload.php';

$mappings = [
    // Resources
    'Appointments\AppointmentResource.php' => ['group' => 'Layanan Klinik', 'icon' => 'heroicon-o-calendar-days', 'sort' => 1],
    'Treatments\TreatmentResource.php'   => ['group' => 'Layanan Klinik', 'icon' => 'heroicon-o-sparkles', 'sort' => 2],
    'TreatmentCategories\TreatmentCategoryResource.php' => ['group' => 'Layanan Klinik', 'icon' => 'heroicon-o-rectangle-group', 'sort' => 3],
    'RekamMedis\RekamMedisResource.php'   => ['group' => 'Layanan Klinik', 'icon' => 'heroicon-o-document-text', 'sort' => 4],
    
    'PatientForms\PatientFormResource.php' => ['group' => 'Pra Konsultasi', 'icon' => 'heroicon-o-clipboard-document-list', 'sort' => 1],
    
    'ForumQuestions\ForumQuestionResource.php' => ['group' => 'Forum', 'icon' => 'heroicon-o-chat-bubble-left-ellipsis', 'sort' => 1],
    'ForumAnswers\ForumAnswerResource.php'   => ['group' => 'Forum', 'icon' => 'heroicon-o-chat-bubble-oval-left-ellipsis', 'sort' => 2],
    
    'Orders\OrderResource.php'       => ['group' => 'Transaksi', 'icon' => 'heroicon-o-shopping-cart', 'sort' => 1],
    
    'Products\ProductResource.php'     => ['group' => 'Katalog', 'icon' => 'heroicon-o-shopping-bag', 'sort' => 1],
    'ProductCategories\ProductCategoryResource.php' => ['group' => 'Katalog', 'icon' => 'heroicon-o-tag', 'sort' => 2],
    'Bundles\BundleResource.php'      => ['group' => 'Katalog', 'icon' => 'heroicon-o-gift', 'sort' => 3],
    'Promotions\PromotionResource.php'   => ['group' => 'Katalog', 'icon' => 'heroicon-o-ticket', 'sort' => 4],

    'Dokters\DokterResource.php'      => ['group' => 'Master Data', 'icon' => 'heroicon-o-user-circle', 'sort' => 1],
    'Terapis\TerapisResource.php'      => ['group' => 'Master Data', 'icon' => 'heroicon-o-user', 'sort' => 2],
    'Pasiens\PasienResource.php'      => ['group' => 'Master Data', 'icon' => 'heroicon-o-users', 'sort' => 3],
    'JadwalPraktiks\JadwalPraktikResource.php' => ['group' => 'Master Data', 'icon' => 'heroicon-o-clock', 'sort' => 4],

    // Pages
    '../Pages/FinancialReport.php'      => ['group' => 'Laporan', 'icon' => 'heroicon-o-chart-bar-square', 'sort' => 1],
    '../Pages/PerformanceReport.php'    => ['group' => 'Laporan', 'icon' => 'heroicon-o-presentation-chart-line', 'sort' => 2],
];

function updateFile($path, $config) {
    if (!file_exists($path)) {
        echo "Not found: $path\n";
        return;
    }
    
    $content = file_get_contents($path);
    
    $group = $config['group'];
    $icon = $config['icon'];
    $sort = $config['sort'];

    // Overwrite navigation element if exists, else insert
    if (preg_match('/protected static \?string \$navigationGroup = .*/', $content)) {
        $content = preg_replace('/protected static \?string \$navigationGroup = .*/', "protected static ?string \$navigationGroup = '{$group}';", $content);
    } else {
        $content = preg_replace('/(class [a-zA-Z0-9_]+ extends [a-zA-Z0-9_]+(\s+implements\s+[a-zA-Z0-9_,\s]+)?\s*\{)/', "$1\n    protected static ?string \$navigationGroup = '{$group}';", $content);
    }

    if (preg_match('/protected static \?string \$navigationIcon = .*/', $content)) {
        $content = preg_replace('/protected static \?string \$navigationIcon = .*/', "protected static ?string \$navigationIcon = '{$icon}';", $content);
    } else {
        $content = preg_replace('/(class [a-zA-Z0-9_]+ extends [a-zA-Z0-9_]+(\s+implements\s+[a-zA-Z0-9_,\s]+)?\s*\{)/', "$1\n    protected static ?string \$navigationIcon = '{$icon}';", $content);
    }

    if (preg_match('/protected static \?int \$navigationSort = .*/', $content)) {
        $content = preg_replace('/protected static \?int \$navigationSort = .*/', "protected static ?int \$navigationSort = {$sort};", $content);
    } else {
        $content = preg_replace('/(class [a-zA-Z0-9_]+ extends [a-zA-Z0-9_]+(\s+implements\s+[a-zA-Z0-9_,\s]+)?\s*\{)/', "$1\n    protected static ?int \$navigationSort = {$sort};", $content);
    }
    
    file_put_contents($path, $content);
    echo "Updated: $path\n";
}

foreach ($mappings as $filename => $config) {
    $path = "app/Filament/Resources/{$filename}";
    updateFile($path, $config);
}
