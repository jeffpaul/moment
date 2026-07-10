# Project Moment: Prototype MVP Spec

## Prototype Goal

Build a private WordPress plugin prototype that demonstrates the core Project Moment experience:

A logged-in user can open a mobile-first publishing route, select content from their phone camera roll, add text, publish a standard WordPress post, and view it in feed-style Moment views.

The prototype should be suitable for personal testing and private demos.

It does not need to be production-ready.


## Code Naming Requirements

Publicly, the product concept can be described as **Project Moment**.

Technically, the prototype should use `moment` everywhere code-facing:

- WordPress.org plugin slug: `moment`
- Plugin directory: `moment`
- Main plugin file: `moment.php`
- Text domain: `moment`
- REST namespace: `/wp-json/moment/v1/`
- Block namespace: `moment/*`
- Action/filter prefix: `moment_`
- PHP class prefix or namespace: `Moment_` or `Moment\`

Do not scaffold the plugin as `project-moment` and do not use `project_moment` in hooks or function names.

## Non-Goals

The prototype should not attempt to build:

- A full native mobile app.
- A replacement for wp-admin.
- A replacement for Gutenberg.
- A new social network.
- A theme builder.
- A plugin marketplace.
- A user-management dashboard.
- Advanced media editing.
- Complex onboarding.
- Multi-user workflows beyond normal WordPress permissions.
- Analytics dashboards.
- Real syndication to external platforms.
- Real conversation backflow from every social network.
- Real AI provider integration as a requirement.
- A custom post type unless absolutely necessary.
- A fully polished settings system.
- Production-grade media editing.

Moment wins by refusing complexity.

## Core User Flow

1. User logs into WordPress.
2. User visits `/moment` on a phone.
3. User taps `New Moment`.
4. User selects one or more files from the camera roll.
5. User adds optional text or caption.
6. User optionally requests AI Assist suggestions.
7. User taps `Publish`.
8. WordPress creates a standard post and uploads media to the media library.
9. User sees confirmation and can view the Moment on the site.

## MVP Scope

### Must Have

- Plugin activation.
- Mobile-first front-end route, such as `/moment`.
- README instructions for adding `/moment` to a phone home screen.
- Best-effort PWA manifest and app-like launch behavior.
- Logged-in user publishing flow.
- File picker for images, videos, audio, or mixed media.
- Caption/text field.
- Publish button.
- Standard WordPress post creation.
- Standard WordPress media upload.
- Post metadata to identify content as a Moment.
- Default outbound routing by Moment type.
- Publish-time destination toggles with defaults preselected.
- Mocked syndication connector registry.
- Mocked conversation backflow for social replies/comments.
- Moment notifications screen for Moment-created content.
- Basic timeline view.
- Basic images view.
- Basic videos view.
- Basic audio/podcast view, even if initially demo-only.
- Basic notes view.
- Graceful AI Assist fallback when no AI provider is configured.

### Should Have

- End-to-end test scenarios for the core publishing and notification flows.
- Dynamic blocks for Moment views.
- Shortcode fallbacks for the same views.
- Route helpers for `/images`, `/videos`, `/audio`, `/notes`, and `/timeline`.
- Optional syndication toggles that store intent but do not actually publish externally.
- Comment/reply adapter interfaces for future real integrations.
- Source labels for imported responses, such as `Reply from Bluesky` or `Comment from Instagram`.
- Settings/config for default destinations by Moment type.
- Basic responsive design optimized for phone usage.
- PWA-ready metadata for the Moment app shell, where practical.

### Could Have

- Offline app-shell fallback.
- Native-style install prompt handling where supported by the browser.
- Draft support.
- Basic profile page.
- Demo seed data.
- Simple theme or block pattern examples designed to work with the latest default WordPress theme.

## Data Model

Use standard WordPress posts and media.

For the prototype, a Moment should be a standard `post` with attached media, not a media attachment by itself and not a custom post type by default.

Recommended single-image Moment flow:

1. User selects an image from the phone camera roll.
2. Upload the image to the standard WordPress Media Library.
3. Create a standard `post` post type.
4. Attach the image to the post.
5. Set the image as the featured image where practical.
6. Render the image and optional caption in `post_content` using normal block markup.
7. Mark the post as a Moment with metadata.
8. Apply default syndication routing, such as image Moment to Instagram, while keeping WordPress canonical.

Do not model the photo itself as the Moment. The Moment is the WordPress post. The photo is the primary media inside that post.

Suggested post fields:

- `post_type` = `post`
- `post_status` = `publish` or `draft` based on user capability
- `post_title` = generated from caption or timestamp
- `post_content` = normal block markup containing media and optional text
- `post_excerpt` = optional caption summary
- `featured_image` = primary media attachment for image/video poster where practical
- comments enabled where site settings allow

Recommended metadata:

- `_moment_is_moment` = `1`
- `_moment_primary_type` = `image`, `video`, `audio`, `podcast`, `note`, `gallery`, or `mixed`
- `_moment_media_ids` = array of media attachment IDs
- `_moment_syndication_targets` = array of selected external targets
- `_moment_default_destinations` = array of defaults applied before publish-time overrides
- `_moment_syndication_status` = `not_attempted`, `mocked`, `queued`, `published`, or `failed`
- `_moment_external_posts` = array of external post references created for this Moment
- `_moment_comment_backflow_enabled` = boolean
- `_moment_ai_assist_used` = boolean

Avoid a custom post type for the prototype. A custom post type may be considered later only if there is a clear technical need that outweighs portability, feed compatibility, normal comments, and standard theme/template support.

Use standard post content with block markup where practical:

- `core/image`
- `core/gallery`
- `core/video`
- paragraph blocks for text



## Conversation Backflow and Notifications

The prototype should demonstrate conversation backflow with mocked data.

Goal: when a Moment is syndicated outward, Moment can track the external post and later import replies/comments back into WordPress so the conversation remains visible around the original Moment.

Prototype behavior:

- Store external post references for each mocked syndication target.
- Provide a mocked sync action that creates sample imported responses for a Moment.
- Represent imported responses using WordPress-native comments where practical, with comment metadata to preserve source details.
- Keep on-site comments and imported social replies/comments associated with the same Moment post.
- Prefix or label imported responses clearly in display, such as `Reply from Bluesky` or `Comment from Instagram`.
- Include a source link back to the social network reply/comment when available.
- Add a `/moment/notifications` view inside the Moment app shell.
- Show new on-site comments and imported social responses for Moment-created content in that notifications view.
- Exclude normal non-Moment post comments from Moment notifications by default.

Suggested imported comment metadata:

- `_moment_comment_source` = `site`, `bluesky`, `mastodon`, `instagram`, `youtube`, etc.
- `_moment_comment_source_label` = `Reply from Bluesky`, `Comment from Instagram`, etc.
- `_moment_comment_external_id` = external reply/comment ID.
- `_moment_comment_external_url` = stable URL to the source reply/comment when available.
- `_moment_comment_external_author` = display name/handle from the source network.
- `_moment_comment_external_created_at` = source timestamp.
- `_moment_comment_imported_at` = WordPress import timestamp.

If WordPress comments are not a good fit for a specific imported item, the prototype can use a lightweight custom storage table or option for demos, but the preferred direction is to keep responses attached to the standard post/comment model wherever possible.

The prototype should not attempt production-grade polling, webhooks, moderation, deduplication, or bi-directional reply posting. It only needs to prove the product behavior.

## Default Syndication Routing

The prototype should model default outbound destinations by Moment type.

Initial defaults for demo purposes:

- Text Moment → Bluesky
- Image Moment → Instagram
- Video Moment → YouTube
- Audio / podcast Moment → podcast/audio destination where configured
- Gallery Moment → Instagram
- Mixed media Moment → primary content type, with manual override

Requirements:

- Add a simple settings/config area for default destinations by type.
- Detect the Moment type based on selected content.
- Preselect outbound destinations on the publish screen.
- Allow the user to toggle destinations before publishing.
- Store selected destinations as post meta.
- Do not require real outbound publishing for the first prototype.
- Expose hooks/interfaces so actual connectors can be added later.

The user's WordPress site is always the required destination. Social destinations are optional.

## Social Connection Strategy

Moment should use an adapter layer for outbound publishing.

The prototype should include a connector registry abstraction with mocked destinations for Bluesky, Mastodon, Instagram, YouTube, TikTok, Threads, and X.

Future implementations can map those destinations to:

- Existing WordPress social publishing plugins.
- Native Moment connector plugins.
- Platform APIs where appropriate.
- Hosted provider integrations where a host chooses to manage the connection layer.
- Comment/reply ingestion adapters for bringing social responses back into WordPress.

Moment should not hard-code direct dependencies on any one social plugin or network API in the core publishing flow.

## AI Assist

AI Assist should be optional.

Prototype behavior:

- Detect whether WordPress 7.0+ AI Client and Connectors infrastructure appears available.
- If available, attempt to use the configured provider through the WordPress AI Client.
- If unavailable or unconfigured, use deterministic placeholder suggestions for demos.
- Never block publishing if AI is unavailable.

Potential suggestions:

- Caption rewrite.
- Alt text draft.
- Tags.
- Title suggestion.

Moment should not store provider API keys.

Moment should not require a specific AI connector.

## Security and Permissions

- Require logged-in users.
- Require `edit_posts` at minimum.
- Require `publish_posts` for immediate publishing, or create drafts when unavailable.
- Use nonces for REST requests.
- Sanitize all text fields.
- Validate uploaded file MIME types.
- Escape all output.
- Do not expose unauthenticated publishing endpoints.

## Suggested Routes

- `/moment` — mobile Moment app shell.
- `/moment/new` — create flow, if separate from app shell.
- `/timeline` — mixed Moment timeline.
- `/images` — image Moments.
- `/videos` — video Moments.
- `/audio` — audio or podcast Moments.
- `/notes` — text Moments.

For the prototype, these can be implemented via rewrite rules, page templates, shortcodes, or dynamic blocks.

## Social-First Onboarding Direction

The prototype does not need to fully provision a new hosted site, but the product direction should be clear: a social-first creator should be able to move from interest to first publish with minimal setup.

Ideal onboarding behavior for future host or platform integrations:

1. Choose or confirm a site address.
2. Pick a simple personal profile style.
3. Optionally connect social destinations.
4. Optionally enable AI Assist through a WordPress AI provider/connector.
5. Land directly in `/moment`.
6. Publish the first Moment.

The prototype should not overbuild this onboarding. It should simply avoid decisions that would make this path harder later.

## Home Screen and PWA Requirements

The prototype should be demoable from a phone home screen.

At minimum, the prototype README must include instructions for adding the Moment URL to a phone home screen:

- iOS/iPadOS via Safari → Share → Add to Home Screen.
- Android via Chrome → Add to Home screen or Install app.

The default demo URL should be documented as:

```text
https://example.com/moment
```

For local demos, users should replace `example.com` with their local WordPress URL.

Best-case prototype behavior should include a Progressive Web App shell:

- Web app manifest.
- App name and short name: `Moment`.
- Start URL: `/moment`.
- Scope: `/moment`.
- Display mode: `standalone`.
- App icons.
- Theme and background colors.
- Conservative service worker for the Moment app shell only.

Do not overbuild offline behavior. Do not cache authenticated REST responses, nonces, wp-admin pages, or private media URLs.

PWA support is intended to make the demo feel app-like, not to replace future native app exploration.

## End-to-End Test-Driven Development

Use the scenarios in `13_success_metrics_and_e2e_tests.md` as the starting point for test-driven prototype work.

The first implementation should prioritize tests for:

1. Loading `/moment` for a logged-in user.
2. Creating an image Moment as a standard `post` with attached media.
3. Creating a text-only Moment.
4. Applying default destination routing by Moment type.
5. Allowing publish-time routing overrides.
6. Returning AI Assist fallback suggestions without a provider.
7. Mocking social comment/reply backflow.
8. Showing Moment-created activity in `/moment/notifications`.
9. Excluding normal non-Moment post comments from Moment notifications by default.
10. Preserving content as standard posts/media when Moment is deactivated.

## Demo Acceptance Criteria

A private demo should be able to show:

1. Visit `/moment` on a phone-sized viewport.
2. Add `/moment` to a phone home screen or demonstrate the documented home-screen flow.
3. Launch Moment from the home screen or directly visit `/moment`.
4. Create a new Moment from a camera-roll image.
5. Add a caption.
6. Optionally click AI Assist and accept/edit/ignore a suggestion.
7. See destination defaults preselected based on Moment type.
8. Override selected outbound destinations if desired.
9. Publish.
10. See the Moment as a standard WordPress post.
11. See selected destinations stored as post metadata, with syndication mocked or marked as not attempted.
12. See the Moment appear in the timeline and images view.
13. See a mocked social reply/comment imported back and labeled with source context.
14. See on-site comments and imported social responses in `/moment/notifications`.
15. Confirm normal non-Moment post comments are not shown in Moment notifications by default.
16. Deactivate the plugin and confirm the post and media still exist as normal WordPress content.

## Prototype Success

The prototype succeeds if a viewer understands this in under one minute:

WordPress can be as fast as a social app for everyday publishing, while keeping the user's own site as the source of truth.
