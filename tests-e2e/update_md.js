const fs = require('fs');

const filePath = 'c:\\laragon\\www\\klinik_aishibeauty\\docs\\skenario_pasien_lengkap.md';
let content = fs.readFileSync(filePath, 'utf8');

// The new register block
const registerBlock = `## 3.1 Skenario Register
Klik ikon profil pada navigation bar, lalu klik link "Daftar" pada halaman login. Halaman register akan menampilkan form pendaftaran akun baru dengan field: Nama Lengkap, Email, Password, dan Konfirmasi Kata Sandi. Terdapat juga tombol "Buat Akun" dan opsi "Lanjut sebagai Tamu".
![Skenario Register](./images/01_register_page.png)

`;

// First, increment all existing "## 3.x" to "## 3.(x+1)"
content = content.replace(/## 3\.(\d+)/g, (match, p1) => {
    return `## 3.${parseInt(p1, 10) + 1}`;
});

// Now insert the registerBlock right after the intro text.
// Intro text is: Dokumen ini memuat skenario fungsionalitas utama yang dapat dilakukan oleh pasien pada sistem Klinik Aishi Beauty.\n\n
content = content.replace(
    'Klinik Aishi Beauty.\n\n',
    'Klinik Aishi Beauty.\n\n' + registerBlock
);

fs.writeFileSync(filePath, content);
console.log('Successfully updated markdown.');
