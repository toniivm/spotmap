import { expect, test } from '@playwright/test';

test.describe('Modal accessibility', () => {
  test('traps keyboard focus and restores trigger focus on close', async ({ page }) => {
    await page.goto('/');

    const loginTrigger = page.getByRole('button', { name: 'Iniciar sesión' });
    await expect(loginTrigger).toBeVisible();

    await loginTrigger.focus();
    await expect(loginTrigger).toBeFocused();
    await page.keyboard.press('Enter');

    const modal = page.getByRole('dialog', { name: 'Iniciar sesión' });
    await expect(modal).toBeVisible();

    await expect(page.locator('#login-email')).toBeFocused();

    for (let step = 0; step < 10; step += 1) {
      await page.keyboard.press('Tab');
      const focusInsideModal = await page.evaluate(() => {
        const modalElement = document.querySelector('[role="dialog"]');
        return Boolean(modalElement && modalElement.contains(document.activeElement));
      });
      expect(focusInsideModal).toBe(true);
    }

    await page.keyboard.press('Escape');
    await expect(modal).not.toBeVisible();
    await expect(loginTrigger).toBeFocused();
  });
});
