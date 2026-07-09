/**
 * Moment E2E browser tests — scaffold (scenarios 1-3, 7 from doc 13).
 *
 * STATUS: SCAFFOLDED, NOT RUN. Playwright is not installed in this repo.
 * To run:
 *   npm i -D @playwright/test
 *   npx playwright install chromium
 *   WP_ADMIN_USER=... WP_ADMIN_PASS=... npx playwright test
 *
 * Selectors marked TODO must be verified against the live app shell markup
 * before enabling; tests are `test.fixme()` until then.
 */
import { test, expect } from '@playwright/test';

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.WP_ADMIN_PASS || 'password';

// Auth helper — logs in through wp-login.php and keeps the session cookie.
async function loginAs( page, username, password ) {
	await page.goto( '/wp-login.php' );
	await page.fill( '#user_login', username );
	await page.fill( '#user_pass', password );
	await page.click( '#wp-submit' );
	await page.waitForURL( '**/wp-admin/**' );
}

// Scenario 1 (unauthenticated half): /moment redirects to login.
test( 'unauthenticated /moment redirects to login', async ( { page } ) => {
	await page.goto( '/moment' );
	await expect( page ).toHaveURL( /wp-login/ );
} );

// Scenario 1: focused Moment home, no wp-admin chrome.
test( 'authenticated user sees Moment Home without wp-admin chrome', async ( { page } ) => {
	await loginAs( page, ADMIN_USER, ADMIN_PASS );
	await page.goto( '/moment' );
	await expect( page ).toHaveTitle( 'Moment' );
	await expect( page.getByText( 'New Moment' ) ).toBeVisible(); // TODO: verify label/selector.
	await expect( page.locator( '#wpadminbar' ) ).toHaveCount( 0 );
	await expect( page.locator( '#adminmenu' ) ).toHaveCount( 0 );
} );

// Scenario 3 + 4: create a text Moment; Bluesky preselected for notes.
test.fixme( 'can create a text Moment with Bluesky preselected', async ( { page } ) => {
	await loginAs( page, ADMIN_USER, ADMIN_PASS );
	await page.goto( '/moment' );
	await page.getByText( 'New Moment' ).click();
	await page.fill( 'textarea', 'E2E test note' ); // TODO: verify composer selector.
	await page.getByText( 'Next' ).click(); // TODO: verify flow steps.
	await expect( page.locator( '[data-connector="bluesky"]' ) ).toBeChecked(); // TODO: verify attribute.
	await page.getByText( 'Publish' ).click();
	await expect( page.getByText( /Published/ ) ).toBeVisible();
} );

// Scenario 2: image Moment publish flow (upload + caption + publish).
test.fixme( 'can create an image Moment', async ( { page } ) => {
	await loginAs( page, ADMIN_USER, ADMIN_PASS );
	await page.goto( '/moment' );
	await page.getByText( 'New Moment' ).click();
	await page.setInputFiles( 'input[type="file"]', 'tests/e2e/fixtures/test-image.png' ); // TODO: add fixture.
	await page.fill( 'textarea', 'E2E image caption' );
	await page.getByText( 'Publish' ).click();
	await expect( page.getByText( /Published/ ) ).toBeVisible();
} );

// Scenario 7: notifications screen shows imported social replies.
test.fixme( 'notifications show imported social replies with source labels', async ( { page } ) => {
	await loginAs( page, ADMIN_USER, ADMIN_PASS );
	await page.goto( '/moment/notifications' );
	await expect( page.getByText( /Reply from Bluesky|Comment from Instagram/ ) ).toBeVisible();
} );
