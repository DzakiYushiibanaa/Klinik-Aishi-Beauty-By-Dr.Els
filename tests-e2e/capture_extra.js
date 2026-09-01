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
        console.log('Navigating to login page...');
        await page.goto(`${baseUrl}/login`);
        await page.waitForLoadState('networkidle');

        console.log('Logging in...');
        await page.fill('input[type="email"]', 'pasien@test.com');
        await page.fill('input[type="password"]', 'password123');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }),
            page.click('button[type="submit"]')
        ]);

        // 4b. Treatment Detail
        console.log('Navigating to treatment catalog...');
        await page.goto(`${baseUrl}/treatment`);
        await page.waitForLoadState('networkidle');
        
        try {
            console.log('Clicking first treatment detail...');
            // In aishibeauty, treatments usually have a "Lihat Detail" or just click the card
            // Let's try to click the first link that goes to /treatment/...
            const detailLink = await page.$('a[href^="' + baseUrl + '/treatment/"]');
            if (detailLink) {
                await Promise.all([
                    page.waitForNavigation({ waitUntil: 'networkidle' }),
                    detailLink.click()
                ]);
                await page.waitForTimeout(1000);
                await page.screenshot({ path: path.join(docsImagesPath, '04_b_treatment_detail.png') });
            } else {
                console.log('Detail link not found on treatment page.');
            }
        } catch (e) {
            console.log('Could not get treatment detail:', e.message);
        }

        // 9. Booking Konsultasi
        console.log('Navigating to booking consultation...');
        await page.goto(`${baseUrl}/appointment/konsultasi`);
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: path.join(docsImagesPath, '09_a_booking_konsul_type.png') });

        try {
            console.log('Selecting Konsultasi Awal...');
            await page.locator('text=Konsultasi Awal').first().click();
            await page.waitForTimeout(1000);
            await page.locator('text=Selanjutnya').first().click();
            await page.waitForTimeout(2000);
            await page.screenshot({ path: path.join(docsImagesPath, '09_b_booking_konsul_datetime.png') });
        } catch (e) {
            console.log('Could not interact with consultation wizard:', e.message);
        }

        console.log('Extra capture completed successfully.');
    } catch (error) {
        console.error('Error during flow:', error);
    } finally {
        await browser.close();
    }
})();
