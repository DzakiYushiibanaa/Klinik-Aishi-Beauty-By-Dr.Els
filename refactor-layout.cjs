const fs = require('fs');
const path = require('path');

const basePath = 'resources/views/layouts';
const partialsDir = path.join(basePath, '_partials/guest');
if (!fs.existsSync(partialsDir)) fs.mkdirSync(partialsDir, { recursive: true });

const mainFile = path.join(basePath, 'guest.blade.php');
let content = fs.readFileSync(mainFile, 'utf8');

// 1. Header
const headerStart = '<header x-data="{ open: false }" class="sticky top-0 z-50 w-full bg-white shadow-sm transition-all">';
const headerEndStr = '</header>';
const headerEnd = content.indexOf(headerEndStr) + headerEndStr.length;
const headerContent = content.substring(content.indexOf(headerStart), headerEnd);
fs.writeFileSync(path.join(partialsDir, '_header.blade.php'), headerContent);

// 2. Footer
const footerStart = '<footer class="bg-[#1C1A18] text-[#9CA3AF] py-16">';
const footerEndStr = '</footer>';
const footerEnd = content.indexOf(footerEndStr) + footerEndStr.length;
const footerContent = content.substring(content.indexOf(footerStart), footerEnd);
fs.writeFileSync(path.join(partialsDir, '_footer.blade.php'), footerContent);

// 3. WA Widget
const waStart = '<div id="wa-widget" x-data="{ open: false }" class="fixed bottom-20 lg:bottom-6 right-6 z-[2147483647]">';
// Find the closing div right before <x-toast />
const toastIndex = content.indexOf('<x-toast />');
// We find the last </div> before <x-toast />
const beforeToast = content.substring(content.indexOf(waStart), toastIndex);
const waContent = beforeToast.trim();
fs.writeFileSync(path.join(partialsDir, '_wa_widget.blade.php'), waContent);

// 4. Update Main File
let newContent = content.replace(headerContent, "@include('layouts._partials.guest._header')");
newContent = newContent.replace(footerContent, "@include('layouts._partials.guest._footer')");

// Replace WA Widget exactly
newContent = newContent.replace(
    content.substring(content.indexOf(waStart), toastIndex),
    "@include('layouts._partials.guest._wa_widget')\n\n    "
);

// We need to make sure the replacement didn't mess up. 
// Just write it out.
fs.writeFileSync(mainFile, newContent);

console.log("Blade layout refactoring complete");
