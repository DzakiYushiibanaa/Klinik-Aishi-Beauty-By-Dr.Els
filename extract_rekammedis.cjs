const fs = require('fs');

const file = 'app/Filament/Resources/RekamMedis/Tables/RekamMedisTable.php';
const content = fs.readFileSync(file, 'utf8');
const lines = content.split(/\r?\n/);

const HasRekamMedisColumns = "<?php\n\nnamespace App\\Filament\\Resources\\RekamMedis\\Tables\\Concerns;\n\nuse App\\Models\\RekamMedis;\nuse Filament\\Tables\\Columns\\TextColumn;\nuse Illuminate\\Database\\Eloquent\\Builder;\nuse Illuminate\\Support\\Carbon;\nuse Illuminate\\Support\\Str;\n\ntrait HasRekamMedisColumns\n{\n" + lines.slice(204, 510).join('\n') + "\n" + lines.slice(620, 665).join('\n') + "\n}\n";

const HasRekamMedisFilters = "<?php\n\nnamespace App\\Filament\\Resources\\RekamMedis\\Tables\\Concerns;\n\nuse Filament\\Tables\\Filters\\SelectFilter;\nuse Illuminate\\Database\\Eloquent\\Builder;\n\ntrait HasRekamMedisFilters\n{\n" + lines.slice(510, 532).join('\n') + "\n}\n";

const editActionStr = lines.slice(52, 192).join('\n');
const HasRekamMedisActions = "<?php\n\nnamespace App\\Filament\\Resources\\RekamMedis\\Tables\\Concerns;\n\nuse App\\Models\\RekamMedis;\nuse Filament\\Actions\\Action;\nuse Filament\\Actions\\EditAction;\nuse Filament\\Forms\\Components\\FileUpload;\nuse Filament\\Forms\\Components\\Repeater;\nuse Filament\\Forms\\Components\\Select;\nuse Filament\\Forms\\Components\\Textarea;\nuse Filament\\Forms\\Components\\TextInput;\nuse Filament\\Schemas\\Components\\Grid;\nuse Filament\\Schemas\\Components\\Section;\nuse Filament\\Schemas\\Components\\Utilities\\Get;\nuse Filament\\Support\\Enums\\Alignment;\nuse Illuminate\\Support\\Facades\\Storage;\n\ntrait HasRekamMedisActions\n{\n" + lines.slice(532, 620).join('\n') + "\n    private static function actionEditRekamMedis(): \\Filament\\Actions\\EditAction\n    {\n        return " + editActionStr.trim() + ";\n    }\n}\n";

const HasRekamMedisEditHelpers = "<?php\n\nnamespace App\\Filament\\Resources\\RekamMedis\\Tables\\Concerns;\n\nuse App\\Models\\Product;\nuse App\\Models\\RekamMedis;\nuse App\\Models\\Treatment;\n\ntrait HasRekamMedisEditHelpers\n{\n" + lines.slice(665, 813).join('\n') + "\n}\n";

fs.mkdirSync('app/Filament/Resources/RekamMedis/Tables/Concerns', { recursive: true });
fs.writeFileSync('app/Filament/Resources/RekamMedis/Tables/Concerns/HasRekamMedisColumns.php', HasRekamMedisColumns);
fs.writeFileSync('app/Filament/Resources/RekamMedis/Tables/Concerns/HasRekamMedisFilters.php', HasRekamMedisFilters);
fs.writeFileSync('app/Filament/Resources/RekamMedis/Tables/Concerns/HasRekamMedisActions.php', HasRekamMedisActions);
fs.writeFileSync('app/Filament/Resources/RekamMedis/Tables/Concerns/HasRekamMedisEditHelpers.php', HasRekamMedisEditHelpers);

// Reconstruct RekamMedisTable.php
const mainClassImports = [
    'namespace App\\Filament\\Resources\\RekamMedis\\Tables;',
    '',
    'use Filament\\Actions\\BulkActionGroup;',
    'use Filament\\Actions\\DeleteBulkAction;',
    'use Filament\\Tables\\Table;',
    'use App\\Filament\\Resources\\RekamMedis\\Tables\\Concerns\\HasRekamMedisColumns;',
    'use App\\Filament\\Resources\\RekamMedis\\Tables\\Concerns\\HasRekamMedisFilters;',
    'use App\\Filament\\Resources\\RekamMedis\\Tables\\Concerns\\HasRekamMedisActions;',
    'use App\\Filament\\Resources\\RekamMedis\\Tables\\Concerns\\HasRekamMedisEditHelpers;',
    '',
    'class RekamMedisTable',
    '{',
    '    use HasRekamMedisColumns;',
    '    use HasRekamMedisFilters;',
    '    use HasRekamMedisActions;',
    '    use HasRekamMedisEditHelpers;',
].join('\n');

// configure method from line 32 to 199, replacing EditAction with self::actionEditRekamMedis(),
let configureLines = lines.slice(31, 199);
// Find the EditAction block inside configureLines and replace it
const startEditAction = configureLines.findIndex(l => l.includes('EditAction::make()'));
const endEditAction = configureLines.findIndex((l, idx) => idx > startEditAction && l.includes('}),'));
configureLines.splice(startEditAction, endEditAction - startEditAction + 1, '                self::actionEditRekamMedis(),');

const finalContent = "<?php\n\n" + mainClassImports + "\n\n" + configureLines.join('\n') + "\n}\n";

fs.writeFileSync(file, finalContent);
console.log('Extraction completed successfully!');