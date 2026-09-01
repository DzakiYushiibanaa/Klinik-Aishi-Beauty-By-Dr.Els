const fs = require('fs');
const file = 'resources/views/pdf/doctor-performance-report.blade.php';
const content = fs.readFileSync(file, 'utf8');
const lines = content.split(/\r?\n/);

const replacement = fs.readFileSync('replacement.txt', 'utf8');

// The lines 85 to 233 are at indices 84 to 232, total 149 lines to replace
lines.splice(84, 149, replacement);

fs.writeFileSync(file, lines.join('\n'));
console.log('doctor-performance-report.blade.php updated successfully!');