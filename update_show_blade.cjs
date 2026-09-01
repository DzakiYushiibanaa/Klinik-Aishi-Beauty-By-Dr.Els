const fs = require('fs');
const file = 'resources/views/payment/show.blade.php';
const content = fs.readFileSync(file, 'utf8');
const lines = content.split(/\r?\n/);

const replacement = fs.readFileSync('replacement.txt', 'utf8');

// The block to replace is from index 2 to index 149 (inclusive).
// This is exactly lines 3 to 150.
lines.splice(2, 148, replacement);

fs.writeFileSync(file, lines.join('\n'));
console.log('show.blade.php updated successfully!');