import { expect, test } from '@playwright/test';

test.describe('SpotMap recovery flow', () => {
  test('opens reset mode from recovery hash, validates confirm, and updates password', async ({ page }) => {
    let updatePasswordCalls = 0;
    const jwtLikeAccessToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjQ3NjczNjAwMDAsInN1YiI6ImUyZS11c2VyLWlkIiwicm9sZSI6InVzZXIifQ.signature';
    const refreshToken = 'e2e-refresh-token';

    await page.route('**/auth/v1/token**', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          access_token: jwtLikeAccessToken,
          refresh_token: refreshToken,
          expires_in: 3600,
          token_type: 'bearer',
          user: {
            id: 'e2e-user-id',
            email: 'recovery-e2e@spotmap.local',
          },
        }),
      });
    });

    await page.route('**/auth/v1/user**', async (route) => {
      const method = route.request().method();
      if (method === 'PUT') {
        updatePasswordCalls += 1;
      }

      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          id: 'e2e-user-id',
          email: 'recovery-e2e@spotmap.local',
          user_metadata: {
            full_name: 'Recovery E2E',
          },
          app_metadata: {},
        }),
      });
    });

    await page.route('**/rest/v1/profiles**', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ role: 'user' }),
      });
    });

    await page.goto(`/#type=recovery&access_token=${encodeURIComponent(jwtLikeAccessToken)}&refresh_token=${encodeURIComponent(refreshToken)}`);

    await expect(page.getByRole('dialog', { name: 'Actualizar contrasena' })).toBeVisible();
    await expect(page.locator('#recovery-password')).toBeVisible();
    await expect(page.locator('#recovery-password-confirm')).toBeVisible();

    await page.locator('#recovery-password').fill('nueva123');
    await page.locator('#recovery-password-confirm').fill('distinta123');
    await page.getByRole('button', { name: 'Guardar nueva contrasena' }).click();

    await expect(page.getByText('Las contrasenas no coinciden')).toBeVisible();
    await expect.poll(() => updatePasswordCalls).toBe(0);

    await page.locator('#recovery-password-confirm').fill('nueva123');
    await page.getByRole('button', { name: 'Guardar nueva contrasena' }).click();

    await expect(page.locator('.toast-msg')).toContainText('Contrasena actualizada', { timeout: 10000 });
    await expect(page.getByRole('dialog', { name: 'Iniciar sesión' })).toBeVisible();
    await expect(page.locator('#login-password')).toBeVisible();
    await expect.poll(() => updatePasswordCalls).toBe(1);

    await expect.poll(async () => page.evaluate(() => window.location.hash)).toBe('');
  });
});
