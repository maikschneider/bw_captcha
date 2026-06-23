import { test, expect } from '@playwright/test';

test.describe('Captcha Reload', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/captcha-form');
  });

  test('reload button changes captcha image', async ({ page }) => {
    await page.waitForSelector('.captcha img', { timeout: 10000 });

    const originalSrc = await page.locator('.captcha img').getAttribute('src');

    await page.click('a.captcha__reload');
    await page.waitForTimeout(2000);

    const newSrc = await page.locator('.captcha img').getAttribute('src');
    expect(newSrc).not.toBe(originalSrc);
  });

  test('reload button adds spin animation', async ({ page }) => {
    await page.waitForSelector('.captcha img', { timeout: 10000 });

    await page.click('a.captcha__reload');

    await expect(page.locator('.captcha.captcha--spin')).toBeVisible();
  });
});
