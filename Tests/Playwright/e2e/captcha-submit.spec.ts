import { test, expect } from '@playwright/test';

test.describe('Captcha Submit', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/captcha-form');
    await page.waitForSelector('.captcha img', { timeout: 10000 });
  });

  test('wrong captcha input shows error', async ({ page }) => {
    await page.fill('input[id*="captcha"]', 'WRONG');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);

    await expect(page.getByText('not correct')).toBeVisible();
  });

  test('empty captcha input shows error', async ({ page }) => {
    await page.evaluate(() => {
      const input = document.querySelector('input[id*="captcha"]');
      if (input) input.removeAttribute('required');
    });
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);

    await expect(page.getByText('not correct')).toBeVisible();
  });
});
