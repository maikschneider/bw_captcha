import { test, expect } from '@playwright/test';

test.describe('Captcha Appearance', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/captcha-form');
  });

  test('captcha image is visible', async ({ page }) => {
    await expect(page.locator('.captcha img')).toBeVisible();
  });

  test('captcha image has correct attributes', async ({ page }) => {
    await expect(page.locator('.captcha img[aria-live="polite"]')).toBeVisible();
    await expect(page.locator('.captcha img[loading="lazy"]')).toBeVisible();
  });

  test('captcha image loads from middleware', async ({ page }) => {
    const src = await page.locator('.captcha img').getAttribute('src');
    expect(src).toContain('type=3413');
  });

  test('captcha input field is visible', async ({ page }) => {
    await expect(page.locator('input[id="captcha_test-1-captcha-1"]')).toBeVisible();
  });

  test('refresh button is visible', async ({ page }) => {
    await expect(page.locator('a.captcha__reload')).toBeVisible();
    await expect(page.locator('a.captcha__reload svg')).toBeVisible();
  });

  test('refresh button has correct attributes', async ({ page }) => {
    await expect(page.locator('a.captcha__reload[role="button"]')).toBeVisible();
    const dataUrl = await page.locator('a.captcha__reload').getAttribute('data-url');
    expect(dataUrl).toContain('type=3413');
  });
});
