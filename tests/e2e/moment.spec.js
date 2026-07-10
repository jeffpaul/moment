/**
 * Moment E2E browser tests (doc 13 scenarios 1-4, 7 + wp-admin visibility).
 *
 * Run:
 *   npx playwright install chromium   # once
 *   WP_BASE_URL=http://wp70.local WP_ADMIN_USER=... WP_ADMIN_PASS=... npx playwright test
 *
 * Needs a live WordPress with pretty permalinks, an administrator account,
 * and the moment + moment-connector-bluesky plugins active. Tests create
 * posts titled "E2E ..." and do not delete them — use a scratch site or
 * clean up afterwards.
 *
 * CONNECTED BLUESKY REQUIRED: the publish screen only offers connected
 * networks, so the target site needs Bluesky "connected" with the stubbed
 * API (the CI workflow does all of this):
 *   1. copy tests/e2e/fixtures/moment-e2e-bluesky-stub.php into wp-content/mu-plugins/
 *   2. wp option update moment_bluesky_handle e2e.bsky.social
 *   3. wp option update connectors_social_bluesky_app_password e2e-fake
 *
 * FRESH USER REQUIRED per run: destination memory persists per user, so
 * the image test's "nothing preselected" baseline needs an account with
 * no publish history (recreate the test user between local runs).
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

// Scenarios 3 + 4: note Moment with the connected network (Bluesky)
// preselected by the model default; unconnected networks are not offered.
test('note Moment: connected Bluesky preselected, publishes, appears in notes view', async ({ page }) => {
	const caption = `E2E note ${RUN_ID}`;

	await loginAs(page);
	await page.goto('/moment');
	await page.locator('[data-action="new-moment"]').click();

	await page.fill('#moment-caption', caption);
	await page.locator('[data-action="next"]').click();

	// Bluesky is connected (stubbed) → offered with a Connected chip and
	// preselected for notes. Unconnected networks are not offered at all.
	await expect(page.locator('[data-connector="bluesky"]')).toBeChecked();
	await expect(page.getByText('Connected').first()).toBeVisible();
	await expect(page.locator('[data-connector="instagram"]')).toHaveCount(0);
	await expect(page.locator('[data-connector="youtube"]')).toHaveCount(0);
	await expect(page.locator('[data-connector="mastodon"]')).toHaveCount(0);

	await page.locator('[data-action="publish"]').click();
	await expect(page.getByText('Published to your site')).toBeVisible();
	await expect(page.getByText('Bluesky')).toBeVisible(); // syndication row

	await page.goto('/notes/');
	await expect(page.getByText(caption)).toBeVisible();
});

// Scenario 2 + destination memory: image Moment via the file picker; no
// networks preselected initially (image default Instagram is not
// connected), the user's explicit choice is remembered for the next
// image Moment.
test('image Moment: picker works, choice of networks is remembered per type', async ({ page }) => {
	const caption = `E2E image ${RUN_ID}`;

	await loginAs(page);
	await page.goto('/moment');
	await page.locator('[data-action="new-moment"]').click();

	await page.setInputFiles('#moment-file-input', 'tests/e2e/fixtures/test-image.png');
	await expect(page.locator('[data-type-badge]')).toHaveText(/image/i);
	await page.fill('#moment-caption', caption);
	await page.locator('[data-action="next"]').click();

	// Image default is Instagram — not connected, so nothing preselected;
	// Bluesky is offered (text networks take any type) but off.
	await expect(page.locator('[data-connector="bluesky"]')).not.toBeChecked();

	// Explicitly choose Bluesky for this image Moment.
	await page.locator('[data-connector="bluesky"]').click({ force: true });
	await expect(page.locator('[data-connector="bluesky"]')).toBeChecked();

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

	// Destination memory: the next image Moment preselects Bluesky.
	await page.goto('/moment');
	await page.locator('[data-action="new-moment"]').click();
	await page.setInputFiles('#moment-file-input', 'tests/e2e/fixtures/test-image.png');
	await page.fill('#moment-caption', `E2E image memory ${RUN_ID}`);
	await page.locator('[data-action="next"]').click();
	await expect(page.locator('[data-connector="bluesky"]')).toBeChecked();
});

// Scenario 7: real (stubbed) backflow replies appear in notifications.
test('notifications show imported social replies with source labels', async ({ page }) => {
	const caption = `E2E backflow ${RUN_ID}`;

	await loginAs(page);

	// Publish a note Moment to connected (stubbed) Bluesky through the UI.
	await page.goto('/moment');
	await page.locator('[data-action="new-moment"]').click();
	await page.fill('#moment-caption', caption);
	await page.locator('[data-action="next"]').click();
	await expect(page.locator('[data-connector="bluesky"]')).toBeChecked();
	await page.locator('[data-action="publish"]').click();
	await expect(page.getByText('Published to your site')).toBeVisible();

	// Trigger the sync from the app context (uses the page's own nonce +
	// REST config, same as a future in-UI sync control would).
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
	await expect(page.getByText('Love this one!').first()).toBeVisible();
});
