import { expect, test } from '@playwright/test';

test.describe('SpotMap smoke', () => {
  test('home render and login modal opens', async ({ page }) => {
    await page.goto('/');

    await expect(page.getByText('SpotMap')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Iniciar sesión' })).toBeVisible();

    await page.getByRole('button', { name: 'Iniciar sesión' }).click();
    await expect(page.getByRole('dialog', { name: 'Iniciar sesión' })).toBeVisible();

    await expect(page.locator('#login-email')).toBeVisible();
    await expect(page.locator('#login-password')).toBeVisible();
  });
});
