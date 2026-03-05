import path from 'node:path';
import { expect, test } from '@playwright/test';

const E2E_USER_EMAIL = process.env.E2E_USER_EMAIL || '';
const E2E_USER_PASSWORD = process.env.E2E_USER_PASSWORD || '';
const E2E_USER_NAME = process.env.E2E_USER_NAME || 'E2E User';

const hasAuthCreds = Boolean(E2E_USER_EMAIL && E2E_USER_PASSWORD);

async function openLogin(page) {
  await page.getByRole('button', { name: 'Iniciar sesión' }).click();
  await expect(page.getByRole('dialog')).toBeVisible();
}

async function performLogin(page) {
  await page.locator('#login-email').fill(E2E_USER_EMAIL);
  await page.locator('#login-password').fill(E2E_USER_PASSWORD);
  await page.getByRole('button', { name: 'Entrar' }).click();
  await expect(page.getByRole('button', { name: 'Cerrar sesión' })).toBeVisible({ timeout: 20000 });
}

async function performRegister(page) {
  await page.getByRole('button', { name: 'No tengo cuenta, quiero registrarme' }).click();
  await page.locator('#register-name').fill(E2E_USER_NAME);
  await page.locator('#login-email').fill(E2E_USER_EMAIL);
  await page.locator('#login-password').fill(E2E_USER_PASSWORD);
  await page.getByRole('button', { name: 'Crear cuenta' }).click();
  await expect(page.getByRole('button', { name: 'Cerrar sesión' })).toBeVisible({ timeout: 25000 });
}

test.describe('SpotMap authenticated flow', () => {
  test.skip(!hasAuthCreds, 'Define E2E_USER_EMAIL y E2E_USER_PASSWORD para ejecutar este flujo.');

  test('login/register then create spot', async ({ page }) => {
    await page.goto('/');
    await openLogin(page);

    await performLogin(page);

    if (await page.getByText(/No se pudo iniciar sesión/i).isVisible().catch(() => false)) {
      await performRegister(page);
    }

    await expect(page.getByRole('button', { name: /Añadir Spot/i })).toBeVisible();
    await page.getByRole('button', { name: /Añadir Spot/i }).click();

    await expect(page.getByRole('dialog', { name: 'Crear spot' })).toBeVisible();

    const uniqueTitle = `E2E Spot ${Date.now()}`;
    await page.locator('#spot-title').fill(uniqueTitle);
    await page.locator('#spot-description').fill('Spot creado por prueba E2E autenticada');
    await page.locator('#spot-category').fill('e2e');
    await page.locator('#spot-tags').fill('e2e,automation');
    await page.locator('#spot-lat').fill('40.4168');
    await page.locator('#spot-lng').fill('-3.7038');

    const filePath = path.resolve(__dirname, '../../icon-192x192.png');
    await page.locator('#spot-image-1').setInputFiles(filePath);

    await page.getByRole('button', { name: 'Crear Spot' }).click();

    await expect(page.locator('.toast-msg')).toContainText(/spot creado|cread/i, { timeout: 30000 });
    await expect(page.getByRole('dialog', { name: 'Crear spot' })).not.toBeVisible();

    await expect(page.locator('.spot-item').first()).toBeVisible({ timeout: 20000 });
  });
});
