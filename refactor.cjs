const fs = require('fs');
const path = require('path');

const basePath = 'app/Filament/Resources/Appointments';
const formFile = path.join(basePath, 'Schemas/AppointmentForm.php');
const listFile = path.join(basePath, 'Pages/ListAppointments.php');

const supportDir = path.join(basePath, 'Support');
const sectionsDir = path.join(basePath, 'Schemas/Sections');

if (!fs.existsSync(supportDir)) fs.mkdirSync(supportDir, { recursive: true });
if (!fs.existsSync(sectionsDir)) fs.mkdirSync(sectionsDir, { recursive: true });

const formContent = fs.readFileSync(formFile, 'utf8');

// 1. AppointmentItemSync
const syncLogic = formContent.substring(
    formContent.indexOf('/**\n     * Transient store for the consultation_id'),
    formContent.indexOf('    public static function configure(Schema $schema): Schema')
).trim();

const syncClass = `<?php

namespace App\\Filament\\Resources\\Appointments\\Support;

use App\\Models\\Appointment;
use App\\Models\\Treatment;

class AppointmentItemSync
{
    ${syncLogic}
}
`;
fs.writeFileSync(path.join(supportDir, 'AppointmentItemSync.php'), syncClass);

// 2. AppointmentHandlerResolver
let handlerLogic = formContent.substring(
    formContent.indexOf('private static function resolveAllowedHandlerTypes(?int $treatmentId): array'),
    formContent.indexOf('private static function dateOptions(Get $get): array')
).trim();
handlerLogic = handlerLogic.replace(/private static function/g, 'public static function');

const handlerClass = `<?php

namespace App\\Filament\\Resources\\Appointments\\Support;

use App\\Models\\Treatment;
use App\\Models\\User;
use Illuminate\\Database\\Eloquent\\Builder;

class AppointmentHandlerResolver
{
    ${handlerLogic}
}
`;
fs.writeFileSync(path.join(supportDir, 'AppointmentHandlerResolver.php'), handlerClass);

// 3. AppointmentSlotResolver
let slotLogic = formContent.substring(
    formContent.indexOf('private static function dateOptions(Get $get): array'),
    formContent.lastIndexOf('}')
).trim();
slotLogic = slotLogic.replace(/private static function/g, 'public static function');

const slotClass = `<?php

namespace App\\Filament\\Resources\\Appointments\\Support;

use App\\Models\\Appointment;
use App\\Models\\JadwalPraktik;
use Carbon\\Carbon;
use Filament\\Schemas\\Components\\Utilities\\Get;

class AppointmentSlotResolver
{
    ${slotLogic}
}
`;
fs.writeFileSync(path.join(supportDir, 'AppointmentSlotResolver.php'), slotClass);

// 4. Sections
const extractSection = (startMarker, endMarker, className, imports, replaces) => {
    let sectionContent = '';
    if (endMarker) {
        sectionContent = formContent.substring(formContent.indexOf(startMarker), formContent.indexOf(endMarker));
    } else {
        sectionContent = formContent.substring(formContent.indexOf(startMarker), formContent.indexOf('])\n            ->columns(1);'));
    }
    
    // Trim trailing commas and spaces at the very end of sectionContent to ensure clean return Section::make...
    sectionContent = sectionContent.trim().replace(/,$/, '');
    
    for (const rep of replaces) {
        sectionContent = sectionContent.split(rep.from).join(rep.to);
    }
    
    // Handle the ensureSelectedTerapisEligible bug
    if (className === 'RincianLayananSection') {
        const target = 'self::ensureSelectedTerapisEligible($get, $set);';
        const replaceWith = `// TODO: method ensureSelectedTerapisEligible tidak ditemukan definisinya
                                // di seluruh codebase (per pengecekan saat refactor tanggal 9 Juli 2026).
                                // Kemungkinan leftover code atau bug lama. Perlu investigasi terpisah oleh
                                // pemilik project — TIDAK diperbaiki dalam refactor ini agar behavior
                                // tetap identik dengan sebelum refactor.
                                \\App\\Filament\\Resources\\Appointments\\Schemas\\AppointmentForm::ensureSelectedTerapisEligible($get, $set);`;
        sectionContent = sectionContent.split(target).join(replaceWith);
    }

    const classContent = `<?php

namespace App\\Filament\\Resources\\Appointments\\Schemas\\Sections;

use Filament\\Schemas\\Components\\Section;
${imports.join('\n')}

class ${className}
{
    public static function make(): Section
    {
        return ${sectionContent};
    }
}
`;
    fs.writeFileSync(path.join(sectionsDir, className + '.php'), classContent);
};

// 4a
extractSection(
    "// ═══════════════════════════════════════════\n                // Section 1: Identitas Pasien",
    "// ═══════════════════════════════════════════\n                // Section 2: Layanan & Petugas Utama",
    "IdentitasPasienSection",
    [
        "use Filament\\Forms\\Components\\Select;",
        "use Filament\\Forms\\Components\\TextInput;",
        "use Illuminate\\Database\\Eloquent\\Builder;",
        "use Filament\\Facades\\Filament;"
    ],
    []
);

// 4b
extractSection(
    "// ═══════════════════════════════════════════\n                // Section 2: Layanan & Petugas Utama",
    "// ═══════════════════════════════════════════\n                // Section 3: Rincian Layanan",
    "LayananPetugasSection",
    [
        "use Filament\\Forms\\Components\\Select;",
        "use Filament\\Forms\\Components\\ToggleButtons;",
        "use Filament\\Schemas\\Components\\Utilities\\Get;",
        "use Illuminate\\Database\\Eloquent\\Builder;"
    ],
    []
);

// 4c
extractSection(
    "// ═══════════════════════════════════════════\n                // Section 3: Rincian Layanan",
    "// ═══════════════════════════════════════════\n                // Section 4: Pilih Jadwal",
    "RincianLayananSection",
    [
        "use App\\Models\\Appointment;",
        "use App\\Models\\Consultation;",
        "use Filament\\Facades\\Filament;",
        "use Filament\\Forms\\Components\\Hidden;",
        "use Filament\\Forms\\Components\\Repeater;",
        "use Filament\\Forms\\Components\\Select;",
        "use Filament\\Forms\\Components\\TextInput;",
        "use Filament\\Schemas\\Components\\Grid;",
        "use Filament\\Schemas\\Components\\Utilities\\Get;",
        "use App\\Filament\\Resources\\Appointments\\Support\\AppointmentHandlerResolver;"
    ],
    [
        {from: 'self::defaultHandlerIdForTreatment', to: 'AppointmentHandlerResolver::defaultHandlerIdForTreatment'},
        {from: 'self::resolveSelectedHandlerType', to: 'AppointmentHandlerResolver::resolveSelectedHandlerType'},
        {from: 'self::handlerOptionsForTreatment', to: 'AppointmentHandlerResolver::handlerOptionsForTreatment'},
        {from: 'self::isDoctorOnlyTreatment', to: 'AppointmentHandlerResolver::isDoctorOnlyTreatment'},
        {from: 'self::handlerHelperText', to: 'AppointmentHandlerResolver::handlerHelperText'}
    ]
);

// 4d
extractSection(
    "// ═══════════════════════════════════════════\n                // Section 4: Pilih Jadwal",
    "// ═══════════════════════════════════════════\n                // Section 3: Status dan Catatan", // Original file had a typo Section 3
    "PilihJadwalSection",
    [
        "use Filament\\Forms\\Components\\DateTimePicker;",
        "use Filament\\Forms\\Components\\Placeholder;",
        "use Filament\\Forms\\Components\\ToggleButtons;",
        "use Filament\\Schemas\\Components\\Utilities\\Get;",
        "use Illuminate\\Support\\HtmlString;",
        "use App\\Filament\\Resources\\Appointments\\Support\\AppointmentSlotResolver;"
    ],
    [
        {from: 'self::dateOptions', to: 'AppointmentSlotResolver::dateOptions'},
        {from: 'self::slotOptions', to: 'AppointmentSlotResolver::slotOptions'},
        {from: 'self::slotDisabled', to: 'AppointmentSlotResolver::slotDisabled'},
        {from: 'self::slotStart', to: 'AppointmentSlotResolver::slotStart'}
    ]
);

// 4e
extractSection(
    "// ═══════════════════════════════════════════\n                // Section 3: Status dan Catatan",
    "// ═══════════════════════════════════════════\n                // Section 4: Lanjut ke Treatment (Konsultasi only)",
    "CatatanSection",
    [
        "use Filament\\Facades\\Filament;",
        "use Filament\\Forms\\Components\\Hidden;",
        "use Filament\\Forms\\Components\\Select;",
        "use Filament\\Forms\\Components\\Textarea;"
    ],
    []
);

// 4f
extractSection(
    "// ═══════════════════════════════════════════\n                // Section 4: Lanjut ke Treatment (Konsultasi only)",
    null,
    "LanjutTreatmentSection",
    [
        "use App\\Models\\Appointment;",
        "use App\\Models\\Treatment;",
        "use Filament\\Facades\\Filament;",
        "use Filament\\Forms\\Components\\Hidden;",
        "use Filament\\Forms\\Components\\Placeholder;",
        "use Filament\\Forms\\Components\\Repeater;",
        "use Filament\\Forms\\Components\\Select;",
        "use Filament\\Forms\\Components\\TextInput;",
        "use Filament\\Forms\\Components\\Toggle;",
        "use Filament\\Schemas\\Components\\Grid;",
        "use Filament\\Schemas\\Components\\Utilities\\Get;",
        "use Illuminate\\Support\\HtmlString;",
        "use App\\Filament\\Resources\\Appointments\\Support\\AppointmentHandlerResolver;",
        "use App\\Filament\\Resources\\Appointments\\Support\\AppointmentItemSync;"
    ],
    [
        {from: 'self::defaultHandlerIdForTreatment', to: 'AppointmentHandlerResolver::defaultHandlerIdForTreatment'},
        {from: 'self::resolveSelectedHandlerType', to: 'AppointmentHandlerResolver::resolveSelectedHandlerType'},
        {from: 'self::handlerOptionsForTreatment', to: 'AppointmentHandlerResolver::handlerOptionsForTreatment'},
        {from: 'self::isDoctorOnlyTreatment', to: 'AppointmentHandlerResolver::isDoctorOnlyTreatment'},
        {from: 'self::handlerHelperText', to: 'AppointmentHandlerResolver::handlerHelperText'},
        {from: 'self::resolveItemPrice', to: 'AppointmentItemSync::resolveItemPrice'}
    ]
);

// 5. Update external caller
let listContent = fs.readFileSync(listFile, 'utf8');
if (!listContent.includes('use App\\Filament\\Resources\\Appointments\\Support\\AppointmentItemSync;')) {
    listContent = listContent.replace(
        'use Filament\\Resources\\Pages\\ListRecords;',
        'use Filament\\Resources\\Pages\\ListRecords;\nuse App\\Filament\\Resources\\Appointments\\Support\\AppointmentItemSync;'
    );
}
listContent = listContent.replace(/AppointmentForm::captureAndStripConsultationId/g, 'AppointmentItemSync::captureAndStripConsultationId');
listContent = listContent.replace(/AppointmentForm::captureAndStripFollowupItems/g, 'AppointmentItemSync::captureAndStripFollowupItems');
listContent = listContent.replace(/AppointmentForm::syncConsultationItem/g, 'AppointmentItemSync::syncConsultationItem');
listContent = listContent.replace(/AppointmentForm::consumeConsultationId/g, 'AppointmentItemSync::consumeConsultationId');
listContent = listContent.replace(/AppointmentForm::syncFollowupItems/g, 'AppointmentItemSync::syncFollowupItems');
fs.writeFileSync(listFile, listContent);

// 6. Rewrite AppointmentForm
const newFormContent = `<?php

namespace App\\Filament\\Resources\\Appointments\\Schemas;

use App\\Filament\\Resources\\Appointments\\Schemas\\Sections\\IdentitasPasienSection;
use App\\Filament\\Resources\\Appointments\\Schemas\\Sections\\LayananPetugasSection;
use App\\Filament\\Resources\\Appointments\\Schemas\\Sections\\RincianLayananSection;
use App\\Filament\\Resources\\Appointments\\Schemas\\Sections\\PilihJadwalSection;
use App\\Filament\\Resources\\Appointments\\Schemas\\Sections\\CatatanSection;
use App\\Filament\\Resources\\Appointments\\Schemas\\Sections\\LanjutTreatmentSection;
use Filament\\Schemas\\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                IdentitasPasienSection::make(),
                LayananPetugasSection::make(),
                RincianLayananSection::make(),
                PilihJadwalSection::make(),
                CatatanSection::make(),
                LanjutTreatmentSection::make(),
            ])
            ->columns(1);
    }
}
`;
fs.writeFileSync(formFile, newFormContent);

console.log("Refactoring complete");