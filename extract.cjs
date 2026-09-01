const fs = require('fs');
const content = fs.readFileSync('resources/views/checkout/index.blade.php', 'utf8');
const lines = content.split(/\r?\n/);

// Extract parts
const header = lines.slice(5, 37).join('\n');
const notice = lines.slice(53, 298).join('\n');
const address = lines.slice(313, 356).join('\n');
const courier = lines.slice(357, 457).join('\n');
const summary = lines.slice(475, 579).join('\n');
const styles = '@once\n' + lines.slice(584, 1577).join('\n') + '\n@endonce';
const script = lines.slice(1579, 1705).join('\n');

// Write without BOM
fs.writeFileSync('resources/views/checkout/_partials/_checkout_header.blade.php', header);
fs.writeFileSync('resources/views/checkout/_partials/_incomplete_notice.blade.php', notice);
fs.writeFileSync('resources/views/checkout/_partials/_checkout_address.blade.php', address);
fs.writeFileSync('resources/views/checkout/_partials/_checkout_courier.blade.php', courier);
fs.writeFileSync('resources/views/checkout/_partials/_checkout_summary.blade.php', summary);
fs.writeFileSync('resources/views/checkout/_partials/_checkout_styles.blade.php', styles);
fs.writeFileSync('resources/views/checkout/_partials/_checkout_script.blade.php', script);

console.log('All partials extracted successfully!');