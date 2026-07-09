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

## Requirements

- WordPress 7.0+ (the bundled AI Client powers optional AI Assist; publishing never requires a configured AI provider)
- PHP 8.1+

## Quick start

```bash
wp plugin activate moment
```

Then visit `/moment` on a phone-sized viewport while logged in.

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
