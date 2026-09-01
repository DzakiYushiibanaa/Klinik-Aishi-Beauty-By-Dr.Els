const fs = require('fs');
const file = 'resources/views/dashboard/orders.blade.php';
const content = fs.readFileSync(file, 'utf8');
const lines = content.split(/\r?\n/);

const replacement = fs.readFileSync('replacement.txt', 'utf8');

lines.splice(1, 119, replacement);

fs.writeFileSync(file, lines.join('\n'));
console.log('orders.blade.php updated successfully!');