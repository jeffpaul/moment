# Home Screen and PWA Instructions for Prototype README

Moment should be demoable from a phone as if it were a lightweight app, even before a native mobile app exists.

The prototype README should include a section titled **Using Moment Like a Phone App**.

## Baseline Requirement

At minimum, the prototype should document how to add the Moment URL to a phone home screen.

Example URL:

```text
https://example.com/moment
```

For local demos, replace `example.com` with the local development URL.

## iPhone / iPad Home Screen Shortcut

1. Open Safari.
2. Visit the Moment route, for example `https://example.com/moment`.
3. Tap the Share button.
4. Choose **Add to Home Screen**.
5. Name it `Moment`.
6. Tap **Add**.
7. Launch Moment from the new home screen icon.

This may open as a browser shortcut unless PWA support is implemented and available for the current environment. That is acceptable for the first prototype as long as the publishing flow is optimized for phone usage.

## Android Home Screen Shortcut

1. Open Chrome.
2. Visit the Moment route, for example `https://example.com/moment`.
3. Tap the menu button.
4. Choose **Add to Home screen** or **Install app** when available.
5. Name it `Moment`.
6. Tap **Add** or **Install**.
7. Launch Moment from the new home screen icon.

## Best-Case PWA Experience

The prototype should attempt to provide a Progressive Web App experience where practical.

Recommended PWA requirements:

- Web app manifest.
- App name: `Moment`.
- Short name: `Moment`.
- Start URL: `/moment`.
- Scope: `/moment`.
- Display mode: `standalone`.
- App icons in standard sizes.
- Theme color and background color.
- Service worker registration for the Moment app shell.
- Minimal offline fallback for the app shell.

## WordPress Implementation Notes

The prototype can implement PWA support by:

- Enqueuing a manifest link only on Moment routes.
- Registering a service worker only for the Moment experience.
- Keeping service worker caching conservative.
- Avoiding caching authenticated REST responses, nonces, wp-admin pages, or private media URLs.
- Avoiding push notifications in the first prototype.
- Avoiding background sync unless specifically added later.

The goal is not to build a full app platform.

The goal is to make `/moment` feel launchable, focused, and app-like from a phone home screen.

## README Acceptance Criteria

The prototype README should clearly explain:

- The Moment route URL.
- How to open it on a phone.
- How to add it to the iOS home screen.
- How to add it to the Android home screen.
- Whether PWA support is implemented or only planned.
- Known limitations of the current home-screen/PWA behavior.
