const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

(async () => {
    const docsImagesPath = path.resolve(__dirname, '../docs/images');
    if (!fs.existsSync(docsImagesPath)) {
        fs.mkdirSync(docsImagesPath, { recursive: true });
    }

    console.log('Launching browser...');
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        ignoreHTTPSErrors: true
    });
    
    const page = await context.newPage();
    const baseUrl = 'https://dev.aishibeauty.my.id';

    try {
        // 1. Skenario Login
        console.log('Navigating to login page...');
        await page.goto(`${baseUrl}/login`);
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: path.join(docsImagesPath, '01_login_page.png') });

        // Do Login
        console.log('Logging in...');
        await page.fill('input[type="email"]', 'pasien@test.com');
        await page.fill('input[type="password"]', 'password123');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }),
            page.click('button[type="submit"]')
        ]);

        // 2. Dashboard
        console.log('Taking screenshot: Dashboard...');
        await page.waitForTimeout(1000);
        await page.screenshot({ path: path.join(docsImagesPath, '02_dashboard_page.png') });

        // 3. Edit Profil
        console.log('Navigating to profile edit...');
        await page.goto(`${baseUrl}/profile`);
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: path.join(docsImagesPath, '03_profile_page.png') });

        // 4. Katalog Treatment
        console.log('Navigating to treatment catalog...');
        await page.goto(`${baseUrl}/treatment`);
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: path.join(docsImagesPath, '04_treatment_page.png') });

        // 5. Booking Janji Temu (Multi-page)
        console.log('Navigating to booking...');
        await page.goto(`${baseUrl}/appointment/buat`);
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        await page.screenshot({ path: path.join(docsImagesPath, '05_a_booking_select_treatment.png') });
        
        try {
            console.log('Selecting treatment...');
            await page.locator('text=Pilih').first().click();
            await page.waitForTimeout(2000);
            await page.screenshot({ path: path.join(docsImagesPath, '05_b_booking_select_datetime.png') });
        } catch (e) {
            console.log('Could not click Pilih for booking:', e.message);
        }

        // 6. Katalog Shop (Multi-page)
        console.log('Navigating to shop...');
        await page.goto(`${baseUrl}/shop`);
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        await page.screenshot({ path: path.join(docsImagesPath, '06_a_shop_catalog.png') });

        try {
            console.log('Adding to cart...');
            await page.locator('text=Keranjang').first().click();
            await page.waitForTimeout(2000);
            await page.screenshot({ path: path.join(docsImagesPath, '06_b_shop_cart.png') });
        } catch (e) {
            console.log('Could not interact with cart:', e.message);
        }

        // 7. Pra-Konsultasi
        console.log('Navigating to pra-konsultasi...');
        await page.goto(`${baseUrl}/pra-konsul/buat`);
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: path.join(docsImagesPath, '07_prakonsul_page.png') });

        // 8. Forum (Multi-page)
        console.log('Navigating to forum...');
        await page.goto(`${baseUrl}/forum`);
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: path.join(docsImagesPath, '08_a_forum_list.png') });

        try {
            console.log('Clicking new topic...');
            await page.locator('text=Pertanyaan').first().click();
            await page.waitForTimeout(2000);
            await page.screenshot({ path: path.join(docsImagesPath, '08_b_forum_new_topic.png') });
        } catch (e) {
            console.log('Could not click new topic:', e.message);
        }

        console.log('Flow completed successfully.');
    } catch (error) {
        console.error('Error during flow:', error);
    } finally {
        await browser.close();
    }
})();
