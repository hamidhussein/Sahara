/**
 * UI audit for the public site (plan 0.3 "Every screen must pass").
 *
 *   node scripts/ui-audit.mjs [baseUrl] [outDir]
 *
 * Uses the installed Google Chrome through playwright-core (no browser
 * download). For each page at 360 px and 1440 px it records: axe-core
 * accessibility violations (incl. colour contrast), horizontal scrolling,
 * tap targets under 44 px, text under 14 px, images without alt, console
 * errors and failed requests, and saves a full-page screenshot. It also
 * drives the mobile menu and the Donate steps.
 */
import { chromium } from 'playwright-core';
import { readFileSync, mkdirSync, writeFileSync } from 'node:fs';
import { createRequire } from 'node:module';
import path from 'node:path';

const require = createRequire(import.meta.url);
const axeSource = readFileSync(require.resolve('axe-core/axe.min.js'), 'utf8');

const base = (process.argv[2] || 'http://127.0.0.1:8000').replace(/\/$/, '');
const outDir = process.argv[3] || 'storage/ui-audit';
mkdirSync(outDir, { recursive: true });

const pages = [
    '/', '/programs', '/programs/clean-water', '/projects', '/projects/miachar-water-plant',
    '/news', '/donate', '/donate/zakat-calculator', '/about', '/contact',
];
const viewports = { phone: { width: 360, height: 800 }, desktop: { width: 1440, height: 900 } };

const browser = await chromium.launch({ channel: 'chrome', headless: true });
const report = [];

for (const [vpName, viewport] of Object.entries(viewports)) {
    const context = await browser.newContext({ viewport, reducedMotion: 'reduce' });

    for (const url of pages) {
        const page = await context.newPage();
        const consoleErrors = [];
        const failed = [];
        page.on('console', (m) => m.type() === 'error' && consoleErrors.push(m.text()));
        page.on('pageerror', (e) => consoleErrors.push(String(e)));
        page.on('response', (r) => r.status() >= 400 && failed.push(`${r.status()} ${r.url()}`));

        await page.goto(base + url, { waitUntil: 'networkidle' });

        // Scroll through the page so lazy-loaded photos load before measuring and screenshotting.
        await page.evaluate(async () => {
            for (let y = 0; y < document.body.scrollHeight; y += window.innerHeight) {
                window.scrollTo(0, y);
                await new Promise((r) => setTimeout(r, 120));
            }
            window.scrollTo(0, 0);
        });
        await page.waitForLoadState('networkidle');

        const checks = await page.evaluate(() => {
            const visible = (el) => {
                const r = el.getBoundingClientRect();
                const s = getComputedStyle(el);
                return r.width > 0 && r.height > 0 && s.visibility !== 'hidden' && s.display !== 'none';
            };
            const label = (el) => (el.innerText || el.getAttribute('aria-label') || el.tagName).trim().replace(/\s+/g, ' ').slice(0, 40);

            const overflow = document.documentElement.scrollWidth - window.innerWidth;

            // Interactive targets smaller than 44x44 (inline links inside sentences are exempt).
            const smallTargets = [...document.querySelectorAll('a, button, input, select, [role=button]')]
                .filter(visible)
                .filter((el) => !(el.tagName === 'A' && el.closest('p, li, dd, address') && getComputedStyle(el).display === 'inline'))
                .filter((el) => el.type !== 'hidden' && el.type !== 'checkbox' && el.type !== 'radio')
                .map((el) => ({ el: label(el), ...(({ width, height }) => ({ w: Math.round(width), h: Math.round(height) }))(el.getBoundingClientRect()) }))
                .filter((t) => t.h < 44 || t.w < 44);

            const tinyText = [...document.querySelectorAll('main *, footer *')]
                .filter((el) => el.childNodes.length && [...el.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim()))
                .filter(visible)
                .filter((el) => parseFloat(getComputedStyle(el).fontSize) < 14)
                .map((el) => `${label(el)} (${getComputedStyle(el).fontSize})`);

            const imagesNoAlt = [...document.images].filter((i) => !i.hasAttribute('alt')).map((i) => i.src);
            const brokenImages = [...document.images].filter((i) => i.complete && i.naturalWidth === 0).map((i) => i.src);

            return { overflow, smallTargets, tinyText: [...new Set(tinyText)], imagesNoAlt, brokenImages };
        });

        await page.addScriptTag({ content: axeSource });
        const axe = await page.evaluate(async () => {
            const r = await window.axe.run(document, { resultTypes: ['violations'] });
            return r.violations.map((v) => ({ id: v.id, impact: v.impact, help: v.help, count: v.nodes.length, sample: v.nodes.slice(0, 3).map((n) => n.target.join(' ')) }));
        });

        const file = path.join(outDir, `${vpName}${url === '/' ? '-home' : url.replace(/\//g, '-')}.png`);
        await page.screenshot({ path: file, fullPage: true });

        report.push({ viewport: vpName, url, ...checks, axe, consoleErrors, failed, screenshot: file });
        await page.close();
    }

    await context.close();
}

// Interactions on a phone.
const phone = await browser.newContext({ viewport: viewports.phone, reducedMotion: 'reduce' });
const interactions = {};
{
    const page = await phone.newPage();
    await page.goto(base + '/', { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: 'Open menu' }).click();
    interactions.mobileMenuOpens = await page.locator('#mobile-menu').waitFor({ state: 'visible', timeout: 3000 }).then(() => true, () => false);
    await page.getByRole('button', { name: 'Programs' }).click();
    interactions.programsSubmenuLinks = await page.locator('#mobile-menu a[href*="/programs/"]').count();
    await page.screenshot({ path: path.join(outDir, 'phone-menu-open.png') });

    await page.goto(base + '/donate', { waitUntil: 'networkidle' });
    interactions.typeTilesOnFirstScreen = await page.locator('[aria-pressed]').count();
    await page.getByRole('button', { name: /^Zakat/ }).click();
    await page.waitForSelector('text=Choose a cause');
    await page.getByRole('button', { name: 'Ramadan Package' }).click();
    await page.getByRole('button', { name: 'Continue' }).click();
    await page.waitForSelector('text=Choose an amount');
    await page.getByRole('button', { name: 'Rs 5,000' }).click();
    await page.getByLabel('Full name').fill('Test Donor');
    await page.getByLabel('Mobile number').fill('0300 1234567');
    await page.getByRole('button', { name: 'Continue' }).click();
    await page.waitForSelector('text=Choose how to pay');
    interactions.donateReachesPayment = true;
    interactions.summaryAmount = (await page.locator('aside dd').first().innerText()).trim();
    interactions.donateButton = (await page.getByRole('button', { name: /^Donate Rs/ }).innerText()).trim();
    await page.screenshot({ path: path.join(outDir, 'phone-donate-step4.png'), fullPage: true });
}
await phone.close();
await browser.close();

writeFileSync(path.join(outDir, 'report.json'), JSON.stringify({ base, report, interactions }, null, 2));

// Short summary on the console.
for (const r of report) {
    const issues = [];
    if (r.overflow > 0) issues.push(`scrolls sideways by ${r.overflow}px`);
    if (r.axe.length) issues.push(`axe: ${r.axe.map((a) => `${a.id}×${a.count}`).join(', ')}`);
    if (r.smallTargets.length) issues.push(`${r.smallTargets.length} small targets`);
    if (r.tinyText.length) issues.push(`${r.tinyText.length} tiny text`);
    if (r.imagesNoAlt.length) issues.push(`${r.imagesNoAlt.length} img without alt`);
    if (r.brokenImages.length) issues.push(`${r.brokenImages.length} broken img`);
    if (r.consoleErrors.length) issues.push(`${r.consoleErrors.length} console errors`);
    if (r.failed.length) issues.push(`${r.failed.length} failed requests`);
    console.log(`${r.viewport.padEnd(8)} ${r.url.padEnd(32)} ${issues.join(' | ') || 'ok'}`);
}
console.log('interactions', JSON.stringify(interactions));
