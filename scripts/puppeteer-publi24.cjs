const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');

puppeteer.use(StealthPlugin());

(async () => {
    const url = process.argv[2];
    if (!url) {
        console.error("Missing URL argument.");
        process.exit(1);
    }

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();

    // Set common headers to look more human
    await page.setUserAgent(
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36'
    );
    await page.setViewport({ width: 1280, height: 800 });

    // Fake navigator.webdriver
    await page.evaluateOnNewDocument(() => {
        Object.defineProperty(navigator, 'webdriver', {
            get: () => false,
        });
    });

    try {
        const response = await page.goto(url, {
            waitUntil: 'domcontentloaded',
            timeout: 60000,
        });

        if (!response || !response.ok()) {
            console.error(`Failed to load page, status: ${response ? response.status() : 'no response'}`);
            await browser.close();
            process.exit(1);
        }

        // Basic anti-bot delay
        function delay(time) {
            return new Promise(resolve => setTimeout(resolve, time));
        }

        await delay(3000);

        await page.mouse.move(100, 200);
        await page.mouse.move(200, 300);

        await page.evaluate(() => {
            window.scrollBy(0, 300);
        });

        await delay(3000);

        const html = await page.content();

        console.log(`Fetched URL: ${page.url()}`);
        console.log(html);

    } catch (err) {
        console.error('Error fetching page:', err);
    }

    await browser.close();
})();

