const fs = require('fs');
const file = 'resources/views/pdf/finance-report.blade.php';
const content = fs.readFileSync(file, 'utf8');
const lines = content.split(/\r?\n/);

const replacement = fs.readFileSync('replacement.txt', 'utf8');

lines.splice(84, 122, replacement);

fs.writeFileSync(file, lines.join('\n'));
console.log('finance-report.blade.php updated successfully!');