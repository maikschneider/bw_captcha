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
    await expect(contentFrame.locator('.formeditor')).toBeVisible({ timeout: 30000 });

    // Verify the Captcha element is visible
    await expect(contentFrame.getByText('captcha-1')).toBeVisible();
  });
});
