import { test, expect } from '@playwright/test';

test.describe('Backend Form Editor', () => {
  test('captcha element exists in form editor', async ({ page }) => {
    // Login to TYPO3 backend
    await page.goto('/typo3');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="p_field"]', 'Passw0rd!');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(3000);

    // Navigate to the Form module
    await page.click('a[data-modulemenu-identifier="web_FormFormbuilder"]');
    await page.waitForTimeout(2000);

    // Switch to content frame
    const contentFrame = page.frameLocator('[name="list_frame"]');

    // Open the test form
    await contentFrame.locator('a', { hasText: 'Captcha Test Form' }).click();
    await expect(contentFrame.locator('.formeditor-module')).toBeVisible({ timeout: 30000 });

    // Verify the Captcha element is present in the editor at its tree path,
    // and renders with the extension's own icon (proves the element type registered).
    const captchaElement = contentFrame.locator(
      '.formeditor-element[data-element-identifier-path="captcha_test/page-1/captcha-1"]'
    );
    await expect(captchaElement).toBeVisible();
    // v13 renders one captcha icon, v14 renders more than one inside the element — assert at least one.
    await expect(captchaElement.locator('.icon-t3-form-captcha-element').first()).toBeVisible();
  });
});
