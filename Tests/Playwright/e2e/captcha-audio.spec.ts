import { test, expect } from '@playwright/test';

test.describe('Captcha Audio', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/captcha-form');
    await page.waitForSelector('.captcha img', { timeout: 10000 });
  });

  test('audio button is visible', async ({ page }) => {
    await expect(page.locator('a.captcha__audio')).toBeVisible();
    await expect(page.locator('a.captcha__audio svg')).toBeVisible();
  });

  test('audio button has correct attributes', async ({ page }) => {
    await expect(page.locator('a.captcha__audio[role="button"]')).toBeVisible();
    const dataUrl = await page.locator('a.captcha__audio').getAttribute('data-url');
    expect(dataUrl).toContain('type=3414');
  });

  test('audio button has sound and mute icons', async ({ page }) => {
    await expect(page.locator('a.captcha__audio .captcha__audio__sound')).toBeVisible();
    await expect(page.locator('a.captcha__audio .captcha__audio__mute')).toBeVisible();
  });

  test('clicking audio button triggers playback', async ({ page }) => {
    await page.click('a.captcha__audio');
    await page.waitForTimeout(1000);

    await expect(page.locator('.captcha.captcha--playing')).toBeVisible();
  });

  test('audio button click sends fetch request', async ({ page }) => {
    await page.evaluate(() => {
      (window as any).__audioFetchCalled = false;
      const originalFetch = window.fetch;
      window.fetch = function (url: any, options: any) {
        if (url && url.toString().includes('type=3414') && options && options.method === 'POST') {
          (window as any).__audioFetchCalled = true;
        }
        return originalFetch.apply(this, arguments as any);
      } as typeof fetch;
    });

    await page.click('a.captcha__audio');
    await page.waitForTimeout(3000);

    const fetchCalled = await page.evaluate(() => (window as any).__audioFetchCalled);
    expect(fetchCalled).toBe(true);
  });
});
