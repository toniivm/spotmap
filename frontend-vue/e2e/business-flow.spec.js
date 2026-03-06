import path from 'node:path';
import { expect, test } from '@playwright/test';

const E2E_USER_EMAIL = process.env.E2E_USER_EMAIL || '';
const E2E_USER_PASSWORD = process.env.E2E_USER_PASSWORD || '';
const E2E_MOD_EMAIL = process.env.E2E_MOD_EMAIL || '';
const E2E_MOD_PASSWORD = process.env.E2E_MOD_PASSWORD || '';
const REQUIRE_E2E_BUSINESS = String(process.env.REQUIRE_E2E_BUSINESS || '').toLowerCase() === 'true';

const hasBusinessCreds = Boolean(
  E2E_USER_EMAIL
  && E2E_USER_PASSWORD
  && E2E_MOD_EMAIL
  && E2E_MOD_PASSWORD,
);

async function openLogin(page) {
  await page.getByRole('button', { name: 'Iniciar sesión' }).click();
  await expect(page.getByRole('dialog')).toBeVisible();
}

async function login(page, email, password) {
  await openLogin(page);
  await page.locator('#login-email').fill(email);
  await page.locator('#login-password').fill(password);
  await page.getByRole('button', { name: 'Entrar' }).click();
  await expect(page.getByRole('button', { name: 'Cerrar sesión' })).toBeVisible({ timeout: 25000 });
}

async function logout(page) {
  const logoutButton = page.getByRole('button', { name: 'Cerrar sesión' });
  if (await logoutButton.isVisible().catch(() => false)) {
    await logoutButton.click();
    await expect(page.getByRole('button', { name: 'Iniciar sesión' })).toBeVisible({ timeout: 15000 });
  }
}

async function createPendingSpot(page, title) {
  await page.getByRole('button', { name: /Añadir Spot/i }).click();
  await expect(page.getByRole('dialog', { name: 'Crear spot' })).toBeVisible();

  await page.locator('#spot-title').fill(title);
  await page.locator('#spot-description').fill('Spot de negocio E2E para moderacion y notificaciones');
  await page.locator('#spot-category').fill('e2e-business');
  await page.locator('#spot-tags').fill('e2e,business,moderation');
  await page.locator('#spot-lat').fill('40.4168');
  await page.locator('#spot-lng').fill('-3.7038');

  const filePath = path.resolve(__dirname, '../../icon-192x192.png');
  await page.locator('#spot-image-1').setInputFiles(filePath);

  await page.getByRole('button', { name: 'Crear Spot' }).click();

  // Normal user flow should enter moderation queue.
  await expect(page.locator('.toast-msg')).toContainText(/revisad|moderaci[oó]n|pendient/i, { timeout: 30000 });
  await expect(page.getByRole('dialog', { name: 'Crear spot' })).not.toBeVisible({ timeout: 15000 });
}

async function moderateSpotAsModerator(page, spotTitle, action = 'approve') {
  const moderationSummary = page.getByRole('button', { name: /Moderaci[oó]n/i });
  await expect(moderationSummary).toBeVisible({ timeout: 20000 });
  await moderationSummary.click();

  const row = page.locator('.mod-item', { hasText: spotTitle }).first();
  await expect(row).toBeVisible({ timeout: 30000 });
  const actionLabel = action === 'reject' ? 'Rechazar' : 'Aprobar';
  await row.getByRole('button', { name: actionLabel }).click();

  // After action the item should disappear from pending list (stale/duplicate guard).
  await expect(page.locator('.mod-item', { hasText: spotTitle })).toHaveCount(0, { timeout: 30000 });

  // Ensure stale row does not keep actionable buttons after moderation refresh cycle.
  await moderationSummary.click();
  await expect(page.locator('.mod-item', { hasText: spotTitle })).toHaveCount(0, { timeout: 15000 });
}

async function openModerationAndFindRow(page, spotTitle) {
  const moderationSummary = page.getByRole('button', { name: /Moderaci[oó]n/i });
  await expect(moderationSummary).toBeVisible({ timeout: 20000 });
  await moderationSummary.click();

  const row = page.locator('.mod-item', { hasText: spotTitle }).first();
  await expect(row).toBeVisible({ timeout: 30000 });
  return row;
}

async function assertModerationNotification(page, spotTitle, expectedStatus = 'approved') {
  const bellButton = page.locator('.notif-btn').first();
  await expect(bellButton).toBeVisible({ timeout: 20000 });
  await bellButton.click();

  // Notification payload includes the spot title.
  await expect(page.locator('.notif-item', { hasText: spotTitle }).first()).toBeVisible({ timeout: 30000 });

  const moderationMsg = page.locator('.notif-item', {
    hasText: expectedStatus === 'rejected'
      ? /rechazad|rechazado|no aprobad/i
      : /aprobad|visible para todos/i,
  }).first();
  await expect(moderationMsg).toBeVisible({ timeout: 30000 });
}

test.describe('SpotMap business flow', () => {
  test.beforeAll(() => {
    if (REQUIRE_E2E_BUSINESS && !hasBusinessCreds) {
      throw new Error('Faltan credenciales E2E_USER_* y E2E_MOD_* para un entorno donde business-flow es obligatorio.');
    }
  });

  test.skip(
    !REQUIRE_E2E_BUSINESS && !hasBusinessCreds,
    'Define E2E_USER_* y E2E_MOD_* para ejecutar el flujo de negocio completo.',
  );

  test('user creates pending spot, moderator approves, user receives notification', async ({ page }) => {
    const spotTitle = `E2E Business Spot ${Date.now()}`;

    await page.goto('/');

    await login(page, E2E_USER_EMAIL, E2E_USER_PASSWORD);
    await createPendingSpot(page, spotTitle);
    await logout(page);

    await login(page, E2E_MOD_EMAIL, E2E_MOD_PASSWORD);
    await moderateSpotAsModerator(page, spotTitle, 'approve');
    await logout(page);

    await login(page, E2E_USER_EMAIL, E2E_USER_PASSWORD);
    await assertModerationNotification(page, spotTitle, 'approved');
  });

  test('user creates pending spot, moderator rejects, user receives rejection notification', async ({ page }) => {
    const spotTitle = `E2E Business Reject Spot ${Date.now()}`;

    await page.goto('/');

    await login(page, E2E_USER_EMAIL, E2E_USER_PASSWORD);
    await createPendingSpot(page, spotTitle);
    await logout(page);

    await login(page, E2E_MOD_EMAIL, E2E_MOD_PASSWORD);
    await moderateSpotAsModerator(page, spotTitle, 'reject');
    await logout(page);

    await login(page, E2E_USER_EMAIL, E2E_USER_PASSWORD);
    await assertModerationNotification(page, spotTitle, 'rejected');
  });

  test('stale duplicate moderation action shows conflict feedback', async ({ browser, page }) => {
    const spotTitle = `E2E Business Stale Spot ${Date.now()}`;
    const secondModeratorPage = await browser.newPage();

    try {
      await page.goto('/');
      await login(page, E2E_USER_EMAIL, E2E_USER_PASSWORD);
      await createPendingSpot(page, spotTitle);
      await logout(page);

      await login(page, E2E_MOD_EMAIL, E2E_MOD_PASSWORD);
      await openModerationAndFindRow(page, spotTitle);

      await secondModeratorPage.goto('/');
      await login(secondModeratorPage, E2E_MOD_EMAIL, E2E_MOD_PASSWORD);
      const staleRow = await openModerationAndFindRow(secondModeratorPage, spotTitle);

      await moderateSpotAsModerator(page, spotTitle, 'approve');
      await staleRow.getByRole('button', { name: 'Rechazar' }).click();

      await expect(secondModeratorPage.locator('.mod-state--error')).toContainText(/no longer pending moderation|ya no est[aá] pendiente/i, { timeout: 20000 });
    } finally {
      await secondModeratorPage.close();
    }
  });
});
