import { test, expect } from '@playwright/test';

test.describe('Backend Form Editor', () => {
  test('captcha element exists in form editor', async ({ page }) => {
    // Login to TYPO3 backend
    await page.goto('/typo3');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="p_field"]', 'Password1!');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(3000);

    // Navigate to the Form module
    await page.click('a[data-modulemenu-identifier="web_FormFormbuilder"]');
    await page.waitForTimeout(2000);

    // Switch to content frame
    const contentFrame = page.frameLocator('[name="list_frame"]');

    // Create a new form
    await contentFrame.locator('button.btn', { hasText: 'Create new form' }).click();
    await page.waitForTimeout(1000);

    // Fill in form name and proceed through wizard
    const modal = page.locator('.modal');
    await expect(modal).toBeVisible({ timeout: 10000 });
    await modal.locator('input[id="simpleInput"]').fill('Test Form');
    await modal.locator('button', { hasText: 'Next' }).click();
    await page.waitForTimeout(1000);
    await modal.locator('button', { hasText: 'Next' }).click();
    await page.waitForTimeout(2000);

    // Switch to content frame and wait for form editor
    await expect(contentFrame.locator('.form-editor')).toBeVisible({ timeout: 30000 });

    // Open the "new element" panel
    await contentFrame.locator('.t3-form-btn-new-element').click();
    await page.waitForTimeout(1000);

    // Verify the Captcha element is listed
    await expect(contentFrame.getByText('Captcha')).toBeVisible();
  });
});
