const fs = require('fs');

const file = 'app/Filament/Resources/Appointments/Tables/AppointmentsTable.php';
const content = fs.readFileSync(file, 'utf8');
const lines = content.split(/\r?\n/);

// Create traits
const rescheduleContent = "<?php\n\nnamespace App\\Filament\\Resources\\Appointments\\Tables\\Concerns;\n\nuse App\\Models\\Appointment;\nuse App\\Models\\JadwalPraktik;\nuse App\\Models\\User;\nuse App\\Notifications\\AppointmentStatusChanged;\nuse App\\Services\\FonnteService;\nuse Filament\\Schemas\\Components\\Utilities\\Get;\nuse Illuminate\\Support\\HtmlString;\n\ntrait HasReschedule\n{\n" + lines.slice(699, 926).join('\n') + "\n}\n";

const staffContent = "<?php\n\nnamespace App\\Filament\\Resources\\Appointments\\Tables\\Concerns;\n\nuse App\\Models\\Appointment;\nuse App\\Models\\User;\nuse App\\Notifications\\AppointmentStatusChanged;\nuse Filament\\Facades\\Filament;\nuse Filament\\Forms\\Components\\Hidden;\nuse Filament\\Forms\\Components\\Placeholder;\nuse Filament\\Forms\\Components\\Repeater;\nuse Filament\\Forms\\Components\\Select;\nuse Filament\\Schemas\\Components\\Grid;\nuse Filament\\Schemas\\Components\\Utilities\\Get;\nuse Illuminate\\Database\\Eloquent\\Builder;\nuse Illuminate\\Support\\HtmlString;\n\ntrait HasStaffAssignment\n{\n" + lines.slice(926, 1206).join('\n') + "\n}\n";

fs.mkdirSync('app/Filament/Resources/Appointments/Tables/Concerns', { recursive: true });
fs.writeFileSync('app/Filament/Resources/Appointments/Tables/Concerns/HasReschedule.php', rescheduleContent);
fs.writeFileSync('app/Filament/Resources/Appointments/Tables/Concerns/HasStaffAssignment.php', staffContent);

// Modify AppointmentsTable
// Keep lines up to 698, then append '}'
let newLines = [...lines.slice(0, 699), '}'];

// Add imports for traits
const traitImports = "use App\\Filament\\Resources\\Appointments\\Tables\\Concerns\\HasReschedule;\nuse App\\Filament\\Resources\\Appointments\\Tables\\Concerns\\HasStaffAssignment;";

// Find where to insert use statements (around line 20-30, let's insert it right above 'class AppointmentsTable')
const classDefIndex = newLines.findIndex(l => l.startsWith('class AppointmentsTable'));

newLines.splice(classDefIndex, 0, traitImports, '');
const classBodyStart = newLines.findIndex(l => l.startsWith('class AppointmentsTable')) + 2; // the '{' line
newLines.splice(classBodyStart, 0, '    use HasReschedule;', '    use HasStaffAssignment;\n');

fs.writeFileSync(file, newLines.join('\n'));
console.log('Extraction completed successfully!');