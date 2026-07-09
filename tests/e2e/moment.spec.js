/**
 * Moment E2E browser tests (doc 13 scenarios 1-4, 7 + wp-admin visibility).
 *
 * Run:
 *   npx playwright install chromium   # once
 *   WP_BASE_URL=http://wp70.local WP_ADMIN_USER=... WP_ADMIN_PASS=... npx playwright test
 *
 * Needs a live WordPress with the moment plugin active, pretty permalinks,
 * and an administrator account. Tests create posts titled "E2E ..." and do
 * not delete them — use a scratch site or clean up afterwards.
 *
 * DEMO MODE REQUIRED: the publish screen hides unconnected networks by
 * default, and these tests assert against the mocked connectors. Enable
 * demo mode on the target site first (the CI workflow does this):
 *   add_filter( 'moment_show_unconnected_connectors', '__return_true' );
 */
import { test, expect } from '@playwright/test';

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.WP_ADMIN_PASS || 'password';

// Unique-ish per run so title assertions never match older test posts.
const RUN_ID = `${Date.now()}`.slice(-6);

// Logs in through wp-login.php; the session cookie persists on the context.
async function loginAs(page) {
	await page.goto('/wp-login.php');
	await page.fill('#user_login', ADMIN_USER);
	await page.fill('#user_pass', ADMIN_PASS);
	await page.click('#wp-submit');
	await page.waitForURL('**/wp-admin/**');
}

// Scenario 1 (unauthenticated half): /moment redirects to login.
test('unauthenticated /moment redirects to login', async ({ page }) => {
	await page.goto('/moment');
	await expect(page).toHaveURL(/wp-login/);
});

// Scenario 1: focused Moment home, no wp-admin chrome.
test('authenticated user sees Moment Home without wp-admin chrome', async ({ page }) => {
	await loginAs(page);
	await page.goto('/moment');
	await expect(page).toHaveTitle('Moment');
	await expect(page.locator('[data-action="new-moment"]')).toBeVisible();
	await expect(page.locator('#wpadminbar')).toHaveCount(0);
	await expect(page.locator('#adminmenu')).toHaveCount(0);
});

// Scenarios 3 + 4: text Moment with Bluesky preselected, published from the UI.
test('note Moment: Bluesky preselected, publishes, appears in notes view', async ({ page }) => {
	const caption = `E2E note ${RUN_ID}`;

	await loginAs(page);
	await page.goto('/moment');
	await page.locator('[data-action="new-moment"]').click();

	await page.fill('#moment-caption', caption);
	await page.locator('[data-action="next"]').click();

	// Type-based default: note → Bluesky on; Your Site locked on.
	await expect(page.locator('[data-connector="bluesky"]')).toBeChecked();
	await expect(page.locator('[data-connector="instagram"]')).not.toBeChecked();

	// A note can't become an Instagram post or a YouTube/TikTok video —
	// those toggles are visible but disabled, with a reason chip.
	await expect(page.locator('[data-connector="instagram"]')).toBeDisabled();
	await expect(page.locator('[data-connector="youtube"]')).toBeDisabled();
	await expect(page.locator('[data-connector="tiktok"]')).toBeDisabled();
	await expect(page.getByText('Needs an image')).toBeVisible();
	await expect(page.getByText('Needs video').first()).toBeVisible();

	await page.locator('[data-action="publish"]').click();
	await expect(page.getByText('Published to your site')).toBeVisible();
	await expect(page.getByText('Bluesky')).toBeVisible(); // mocked syndication row

	await page.goto('/notes/');
	await expect(page.getByText(caption)).toBeVisible();
});

// Scenario 2: image Moment — file picker accepts an image; Instagram default;
// the post lands in wp-admin as a standard post and in timeline + images views.
test('image Moment: picker accepts image, publishes, visible in wp-admin and views', async ({ page }) => {
	const caption = `E2E image ${RUN_ID}`;

	await loginAs(page);
	await page.goto('/moment');
	await page.locator('[data-action="new-moment"]').click();

	await page.setInputFiles('#moment-file-input', 'tests/e2e/fixtures/test-image.png');
	await expect(page.locator('[data-type-badge]')).toHaveText(/image/i);
	await page.fill('#moment-caption', caption);
	await page.locator('[data-action="next"]').click();

	await expect(page.locator('[data-connector="instagram"]')).toBeChecked();
	await page.locator('[data-action="publish"]').click();
	await expect(page.getByText('Published to your site')).toBeVisible();

	// Standard post visible in wp-admin.
	await page.goto('/wp-admin/edit.php');
	await expect(page.locator('.row-title').filter({ hasText: caption }).first()).toBeVisible();

	// Timeline and images views show it.
	await page.goto('/timeline/');
	await expect(page.getByText(caption)).toBeVisible();
	await page.goto('/images/');
	await expect(page.getByText(caption)).toBeVisible();
});

// Scenario 7: mocked backflow replies appear in the notifications screen.
test('notifications show imported social replies with source labels', async ({ page }) => {
	const caption = `E2E backflow ${RUN_ID}`;

	await loginAs(page);

	// Publish a note Moment (Bluesky default) through the UI.
	await page.goto('/moment');
	await page.locator('[data-action="new-moment"]').click();
	await page.fill('#moment-caption', caption);
	await page.locator('[data-action="next"]').click();
	await page.locator('[data-action="publish"]').click();
	await expect(page.getByText('Published to your site')).toBeVisible();

	// Trigger the mocked sync from the app context (uses the page's own
	// nonce + REST config, same as a future in-UI sync control would).
	const imported = await page.evaluate(async () => {
		const config = window.momentApp;
		const listRes = await fetch(`${config.restUrl}moments?per_page=1`, {
			headers: { 'X-WP-Nonce': config.nonce },
			credentials: 'same-origin',
		});
		const [latest] = await listRes.json();
		const syncRes = await fetch(`${config.restUrl}moments/${latest.id}/sync-responses`, {
			method: 'POST',
			headers: { 'X-WP-Nonce': config.nonce, 'Content-Type': 'application/json' },
			credentials: 'same-origin',
			body: JSON.stringify({ networks: ['bluesky'] }),
		});
		const sync = await syncRes.json();
		return sync.imported_count;
	});
	expect(imported).toBeGreaterThan(0);

	await page.goto('/moment/notifications');
	await expect(page.getByText('Reply from Bluesky').first()).toBeVisible();
});
