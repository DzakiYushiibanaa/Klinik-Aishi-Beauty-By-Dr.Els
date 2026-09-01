import os
import re

files = [
    r'app\Console\Commands\SendAppointmentReminders.php',
    r'app\Filament\Resources\Appointments\Schemas\Sections\CatatanSection.php',
    r'app\Filament\Resources\Appointments\Support\AppointmentSlotResolver.php',
    r'app\Http\Controllers\AppointmentController.php',
    r'app\Http\Controllers\DashboardController.php',
    r'app\Http\Controllers\PaymentController.php',
    r'app\Services\AdminDashboardService.php',
    r'app\Services\GlobalSearchService.php',
    r'resources\views\appointment\index.blade.php',
    r'resources\views\filament\appointments\detail-slideover.blade.php',
    r'resources\views\filament\pages\_partials\_dash_schedule.blade.php',
    r'resources\views\filament\pages\_partials\_dash_styles.blade.php',
    r'app\Filament\Resources\Appointments\Schemas\MulaiLayananForm.php'
]

for file_path in files:
    if not os.path.exists(file_path):
        continue
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Pattern 1: ['diajukan', 'disetujui'] -> ['disetujui']
    content = re.sub(r"'diajukan',\s*", "", content)
    # Pattern 2: ['menunggu_pembayaran', 'diajukan'] -> ['menunggu_pembayaran']
    content = re.sub(r",\s*'diajukan'", "", content)
    # Pattern 3: 'diajukan' => ['Diajukan', ...],
    content = re.sub(r"\s*'diajukan'\s*=>\s*\[[^\]]+\]\s*,", "", content)
    # Pattern 4: 'diajukan' => 'Diajukan',
    content = re.sub(r"\s*'diajukan'\s*=>\s*'Diajukan'\s*,", "", content)
    # Pattern 5: CSS .dash-status-diajukan { ... }
    content = re.sub(r"\s*\.dash-status-diajukan\s*\{[^}]+\}", "", content)

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

print('Replaced in files')
