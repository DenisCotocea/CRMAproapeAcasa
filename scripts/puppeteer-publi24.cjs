// puppeteer-publi24.cjs
const puppeteer = require('puppeteer');

(async () => {
    const url = process.argv[2];
    if (!url) {
        console.error("Missing URL argument.");
        process.exit(1);
    }

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox']
    });
    const page = await browser.newPage();

    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36');
    await page.setViewport({ width: 1280, height: 800 });

    try {
        const response = await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });

        if (!response || !response.ok()) {
            console.error(`Failed to load page, status: ${response ? response.status() : 'no response'}`);
            await browser.close();
            process.exit(1);
        }

        function delay(time) {
            return new Promise(resolve => setTimeout(resolve, time));
        }

        // Extra wait to allow JS to finish rendering
        await delay;

        const html = await page.content();

        // Optional: Log the current page url after navigation
        console.log(`Fetched URL: ${page.url()}`);

        console.log(html);

    } catch (err) {
        console.error('Error fetching page:', err);
    }

    await browser.close();
})();
