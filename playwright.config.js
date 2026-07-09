// Playwright config for Moment E2E tests.
// NOT RUN YET: requires `npm i -D @playwright/test && npx playwright install chromium`.
// Set WP_BASE_URL / WP_ADMIN_USER / WP_ADMIN_PASS for your local site.
import { defineConfig, devices } from '@playwright/test';

export default defineConfig( {
	testDir: './tests/e2e',
	fullyParallel: false,
	retries: 0,
	use: {
		baseURL: process.env.WP_BASE_URL || 'http://wp70.local',
		trace: 'on-first-retry',
		// Scenario 1 requires a phone-sized viewport.
		...devices[ 'iPhone 13' ],
	},
	projects: [ { name: 'mobile-chromium', use: { ...devices[ 'iPhone 13' ] } } ],
} );
