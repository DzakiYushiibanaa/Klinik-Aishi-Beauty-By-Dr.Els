<?php
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
];

foreach ($mappings as $filename => $config) {
    $path = "app/Filament/Resources/{$filename}";
    if (!file_exists($path)) {
        echo "Missing: $path\n";
        continue;
    }
    
    $content = file_get_contents($path);
    $group = $config['group'];
    $icon = $config['icon'];
    $sort = $config['sort'];

    // Avoid inserting if already present
    if (strpos($content, 'function getNavigationGroup(') !== false) continue;

    $methods_to_append = "
    public static function getNavigationIcon(): string
    {
        return '{$icon}';
    }

    public static function getNavigationGroup(): ?string
    {
        return '{$group}';
    }

    public static function getNavigationSort(): ?int
    {
        return {$sort};
    }
";
    
    // Find the last closing brace of the class
    $content = preg_replace('/}[ \t\r\n]*$/', $methods_to_append . "}\n", $content);
    file_put_contents($path, $content);
    echo "Added methods to $path\n";
}
