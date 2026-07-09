# Moment

Personal Site Publisher Mode for WordPress.

**Requires at least:** 7.0 · **Tested up to:** 7.0 ·
**Requires PHP:** 8.1 · **Stable tag:** 0.1.0 · **License:** GPLv2 or later

Moment is a phone-first publishing experience for WordPress. A logged-in
user visits `/moment`, picks media from the camera roll, adds a caption,
and publishes a standard WordPress post — the site stays the canonical
source of truth.

**Status:** prototype. App shell, REST API, and home-screen/PWA support
are in place; see "Using Moment Like a Phone App" below.

## Colors

The Moment brand palette is a range of purples:

| Token | Value | Use |
|---|---|---|
| Primary purple | `#7A00DF` | Primary actions, accents, brand marks |
| Deep purple | `#5300BE` | Pressed/hover states, emphasis, dark surfaces |
| Light purple | `#D7A7FF` | Tints, highlights, chips, subtle backgrounds |
| Transparent purple | `rgba(122, 0, 223, 0.12)` | Washes, focus rings, selected states |

> The prototype app shell currently ships a neutral near-black palette in
> `assets/app.css` (see the `--moment-*` custom properties); these purples are
> the documented brand direction to migrate toward.

## AI-assisted development

This plugin was generated with [Claude Code](https://claude.com/claude-code)
working from the Project Moment specification documents, with human guidance,
review, and testing throughout — every build phase was gated on verification
against a live WordPress site, and the test suites (PHPUnit, WP-CLI smoke,
browser E2E) exist to keep that review honest. Treat it as an AI-generated,
human-directed prototype.

## Requirements

- WordPress 7.0+ (the bundled AI Client powers optional AI Assist; publishing never requires a configured AI provider)
- PHP 8.1+

## Quick start

```bash
wp plugin activate moment
```

Then visit `/moment` on a phone-sized viewport while logged in.

## FAQ

### How do I connect Bluesky for bi-directional publishing?

Moment's built-in connectors are mocked demos. The companion plugin
**Moment Connector for Bluesky** (`moment-connector-bluesky/`, a separate
plugin in this repo) replaces the Bluesky mock with the real thing —
Moments publish to Bluesky, and replies flow back into WordPress as
comments on the original Moment. Credentials are managed through the
WordPress 7.0 **Connectors API**, so it feels core-native:

1. **Activate both plugins** — `moment` and `moment-connector-bluesky`.
2. **Create a Bluesky app password**: Bluesky → Settings → Privacy and
   Security → [App Passwords](https://bsky.app/settings/app-passwords) →
   Add App Password. Never use your main account password.
3. **Enter the app password in WordPress**: wp-admin → Settings →
   **Connectors** → Bluesky. (Core masks the stored key in REST
   responses; you can also supply it via a `BLUESKY_APP_PASSWORD`
   environment variable or PHP constant instead, which takes precedence
   over the database value.)
4. **Enter your handle**: wp-admin → Settings → **General** → "Bluesky
   Handle" (e.g. `you.bsky.social`).
5. **Publish**: in `/moment`, the Bluesky row on the publish screen now
   shows **Connected**. Note and mixed Moments publish for real — the
   caption plus a link back to your post, and the Bluesky post URL is
   stored on the Moment (`_moment_external_posts`).
6. **Pull replies back**: syncing responses (the
   `POST /moment/v1/moments/{id}/sync-responses` endpoint the app uses)
   fetches actual replies from your Bluesky thread and imports them as
   WordPress comments labeled "Reply from Bluesky", deduplicated by
   reply ID — safe to sync repeatedly. They appear on the post and in
   `/moment/notifications`.

If the connector is not configured (or a Bluesky call fails), publishing
never blocks — the connector degrades to the same mocked behavior as the
built-in demo connector.

### How do I connect Mastodon?

Same model as Bluesky, via the companion plugin **Moment Connector for
Mastodon** (`moment-connector-mastodon/`):

1. **Activate both plugins** — `moment` and `moment-connector-mastodon`.
2. **Create an access token on your instance**: Preferences →
   Development → **New application** (any name; `read` and `write`
   scopes) → copy the access token.
3. **Enter the token in WordPress**: wp-admin → Settings →
   **Connectors** → Mastodon. (Also accepted via a
   `MASTODON_ACCESS_TOKEN` environment variable or PHP constant.)
4. **Enter your instance**: wp-admin → Settings → **General** →
   "Mastodon Instance" (e.g. `https://mastodon.social`).
5. **Publish and sync** exactly as with Bluesky: note, image, and mixed
   Moments post for real (caption + link), and syncing responses imports
   direct replies to your status as comments labeled "Reply from
   Mastodon", deduplicated per reply.

The same never-blocks rule applies: unconfigured or failing, it falls
back to mocked demo behavior.

## Using Moment Like a Phone App

Moment is designed to sit on your phone's home screen like a native app.
The demo URL pattern is always:

```
https://[yoursite]/moment
```

For example: `https://example.com/moment` (log in first, or you will be
redirected to the WordPress login screen and then back to Moment).

### iOS (Safari)

1. Open `https://[yoursite]/moment` in Safari.
2. Tap the **Share** button.
3. Tap **Add to Home Screen**.
4. Confirm the name "Moment" and tap **Add**.

### Android (Chrome)

1. Open `https://[yoursite]/moment` in Chrome.
2. Tap the **⋮** menu.
3. Tap **Add to Home Screen** (or **Install App** when Chrome offers it).

### What to expect (prototype)

- The home-screen icon launches Moment as a browser shortcut. Full
  standalone display (`display: standalone` in the manifest, no browser
  chrome) requires **HTTPS**.
- **Local demo note:** the local dev site (`http://wp70.local`) is
  HTTP-only, so iOS will open the shortcut in regular Safari and Chrome
  will not offer "Install App". That is expected for local demos — on
  any HTTPS site the same URL installs as a standalone app.
- A conservative service worker (`assets/moment-sw.js`, cache
  `moment-v1`, scope limited to the plugin `assets/` directory) caches
  only the app's static `app.css` and `app.js`. It never caches REST
  responses, nonces, HTML, media, or anything under `/wp-admin/`. There
  is no offline publishing mode.

### Icons

The manifest references `assets/icon.svg` (the source of truth) plus
`assets/icon-192.png` and `assets/icon-512.png`, all checked in. If you
edit the SVG, regenerate the PNGs, for example:

```bash
# From the plugin root, with librsvg installed:
rsvg-convert -w 192 -h 192 assets/icon.svg > assets/icon-192.png
rsvg-convert -w 512 -h 512 assets/icon.svg > assets/icon-512.png
```
