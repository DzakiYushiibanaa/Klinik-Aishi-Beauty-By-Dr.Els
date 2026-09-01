const fs = require('fs');
const content = fs.readFileSync('resources/views/checkout/payment.blade.php', 'utf8');
const lines = content.split(/\r?\n/);

const orderDetail = lines.slice(24, 74).join('\n');
const shippingAddress = lines.slice(75, 92).join('\n');
const scriptBlock = lines.slice(116, 152).join('\n');

fs.writeFileSync('resources/views/checkout/_partials/_payment_order_detail.blade.php', orderDetail);
fs.writeFileSync('resources/views/checkout/_partials/_payment_shipping_address.blade.php', shippingAddress);
fs.writeFileSync('resources/views/checkout/_partials/_payment_script.blade.php', scriptBlock);
console.log('Payment partials extracted!');