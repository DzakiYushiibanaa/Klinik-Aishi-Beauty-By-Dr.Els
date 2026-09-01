const fs = require('fs');

const file = 'app/Filament/Resources/Orders/Tables/OrdersTable.php';
const content = fs.readFileSync(file, 'utf8');
const lines = content.split(/\r?\n/);

const HasOrderColumns = "<?php\n\nnamespace App\\Filament\\Resources\\Orders\\Tables\\Concerns;\n\nuse App\\Models\\Order;\nuse App\\Support\\PaymentMethodFormatter;\nuse Filament\\Tables\\Columns\\TextColumn;\nuse Illuminate\\Database\\Eloquent\\Builder;\nuse Illuminate\\Support\\Carbon;\n\ntrait HasOrderColumns\n{\n" + lines.slice(68, 184).join('\n') + "\n}\n";

const HasOrderFilters = "<?php\n\nnamespace App\\Filament\\Resources\\Orders\\Tables\\Concerns;\n\nuse Filament\\Tables\\Filters\\SelectFilter;\n\ntrait HasOrderFilters\n{\n" + lines.slice(184, 217).join('\n') + "\n}\n";

const HasOrderRowActions = "<?php\n\nnamespace App\\Filament\\Resources\\Orders\\Tables\\Concerns;\n\nuse App\\Models\\Order;\nuse App\\Notifications\\OrderShippingUpdated;\nuse App\\Services\\FonnteService;\nuse Filament\\Actions\\Action;\nuse Filament\\Forms\\Components\\Placeholder;\nuse Filament\\Forms\\Components\\Textarea;\nuse Filament\\Forms\\Components\\TextInput;\nuse Filament\\Notifications\\Notification;\nuse Filament\\Support\\Enums\\Alignment;\nuse Illuminate\\Support\\HtmlString;\n\ntrait HasOrderRowActions\n{\n" + lines.slice(217, 265).join('\n') + "\n" + lines.slice(739, 1031).join('\n') + "\n}\n";

const HasOrderPaymentAction = "<?php\n\nnamespace App\\Filament\\Resources\\Orders\\Tables\\Concerns;\n\nuse App\\Models\\Order;\nuse App\\Services\\Members\\ConsultationMembershipService;\nuse Filament\\Actions\\Action;\nuse Filament\\Forms\\Components\\Placeholder;\nuse Filament\\Forms\\Components\\Select;\nuse Filament\\Forms\\Components\\TextInput;\nuse Filament\\Notifications\\Notification;\nuse Filament\\Schemas\\Components\\Grid;\nuse Filament\\Schemas\\Components\\Section;\nuse Filament\\Schemas\\Components\\Utilities\\Get;\nuse Filament\\Schemas\\Components\\Utilities\\Set;\nuse Filament\\Support\\Enums\\Alignment;\nuse Illuminate\\Support\\Carbon;\nuse Illuminate\\Support\\HtmlString;\n\ntrait HasOrderPaymentAction\n{\n" + lines.slice(265, 684).join('\n') + "\n}\n";

const HasOrderHelpers = "<?php\n\nnamespace App\\Filament\\Resources\\Orders\\Tables\\Concerns;\n\nuse App\\Models\\Order;\nuse App\\Notifications\\OrderShippingUpdated;\nuse Illuminate\\Support\\HtmlString;\nuse Filament\\Schemas\\Components\\Utilities\\Get;\n\ntrait HasOrderHelpers\n{\n" + lines.slice(684, 739).join('\n') + "\n" + lines.slice(1031, 1115).join('\n') + "\n}\n";

fs.mkdirSync('app/Filament/Resources/Orders/Tables/Concerns', { recursive: true });
fs.writeFileSync('app/Filament/Resources/Orders/Tables/Concerns/HasOrderColumns.php', HasOrderColumns);
fs.writeFileSync('app/Filament/Resources/Orders/Tables/Concerns/HasOrderFilters.php', HasOrderFilters);
fs.writeFileSync('app/Filament/Resources/Orders/Tables/Concerns/HasOrderRowActions.php', HasOrderRowActions);
fs.writeFileSync('app/Filament/Resources/Orders/Tables/Concerns/HasOrderPaymentAction.php', HasOrderPaymentAction);
fs.writeFileSync('app/Filament/Resources/Orders/Tables/Concerns/HasOrderHelpers.php', HasOrderHelpers);

let newLines = [...lines.slice(0, 68), '}'];

const traitImports = "use App\\Filament\\Resources\\Orders\\Tables\\Concerns\\HasOrderColumns;\nuse App\\Filament\\Resources\\Orders\\Tables\\Concerns\\HasOrderFilters;\nuse App\\Filament\\Resources\\Orders\\Tables\\Concerns\\HasOrderRowActions;\nuse App\\Filament\\Resources\\Orders\\Tables\\Concerns\\HasOrderPaymentAction;\nuse App\\Filament\\Resources\\Orders\\Tables\\Concerns\\HasOrderHelpers;";

const classDefIndex = newLines.findIndex(l => l.startsWith('class OrdersTable'));
newLines.splice(classDefIndex, 0, traitImports, '');
const classBodyStart = newLines.findIndex(l => l.startsWith('class OrdersTable')) + 2; 
newLines.splice(classBodyStart, 0, '    use HasOrderColumns;', '    use HasOrderFilters;', '    use HasOrderRowActions;', '    use HasOrderPaymentAction;', '    use HasOrderHelpers;\n');

fs.writeFileSync(file, newLines.join('\n'));
console.log('Extraction completed successfully!');