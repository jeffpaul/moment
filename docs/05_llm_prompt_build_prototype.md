# LLM Prompt: Build the Project Moment WordPress Prototype

Use this prompt with a coding LLM or agent such as Claude Code, Cursor, OpenClaw, or another local/cloud coding setup.

---

You are helping build a private prototype WordPress plugin called **Project Moment**.

## Product Summary

Project Moment is a phone-first Personal Site Publisher Mode for WordPress.

It lets a logged-in user publish text, images, videos, audio/podcast episodes, or mixed media to their own WordPress site as easily as they publish to social networks.

The site remains the source of truth. Social networks are optional distribution channels, not the canonical home for the content.

Moment should route different Moment types to sensible default social destinations, such as notes to Bluesky, images to Instagram, video to YouTube, and audio/podcast Moments to an audio destination where configured, while letting the user override those choices before publishing.

Moment should also demonstrate conversation backflow: social replies/comments can come back into WordPress, appear on the original Moment with source labels, and show up in a Moment notifications screen.

This prototype is for private testing and demos. It does not need to be production-ready, but it should be secure enough for local/private use and architected in a way that can evolve.

## Core Product Principle

Do not build mobile wp-admin.

Build a simple mobile publishing surface.

Admin mode is for managing a site.

Editor mode is for crafting richer long-form content.

Personal Site Publisher Mode is for quickly sharing what is happening now from a personal site. It is designed first for individuals, while still supporting brands that publish from WordPress to a single brand identity on social platforms.

## Plugin Name

Use:

- Public plugin name: `Moment`
- Public project/proposal name: `Project Moment`
- WordPress.org plugin slug: `moment`
- Plugin directory: `moment`
- Main plugin file: `moment.php`
- Text domain: `moment`
- REST namespace: `/wp-json/moment/v1/`
- Block namespace: `moment`
- PHP class prefix or namespace: `Moment_` or `Moment\`
- Action/filter prefix: `moment_`

Do not use `project-moment` or `project_moment` for code-facing identifiers, package names, REST namespaces, block names, text domains, plugin directories, action hooks, or function/class prefixes. `Project Moment` is the public concept name; `moment` is the technical slug.

## Technical Goal

Create a WordPress plugin that provides:

1. A mobile-first route at `/moment`.
2. A `New Moment` publishing flow.
3. Camera roll file selection through a mobile-friendly file input.
4. Text/caption input.
5. Optional AI Assist UI.
6. Standard WordPress post creation.
7. Standard WordPress media uploads.
8. Basic Moment views for timeline, images, videos, audio/podcast episodes, and notes.
9. Default syndication routing by Moment type, with publish-time overrides.
10. Mocked social connector registry for outbound publishing destinations.
11. External post reference tracking for mocked social destinations.
12. Mocked conversation backflow for replies/comments from social destinations.
13. A `/moment/notifications` screen for Moment-created content.
14. Home-screen install instructions and best-effort PWA app-shell support.
15. Shortcode or block-based presentation outputs.
16. Clean README with setup, demo, home-screen, PWA, routing, and notifications instructions.

## Target Environment

Assume:

- WordPress 7.0+ where available.
- PHP 8.1+ preferred.
- Modern browser.
- Local/private WordPress installation.

However, the prototype should gracefully run without WordPress 7.0 AI infrastructure by using mock AI Assist suggestions.

## Important WordPress 7.0 AI Assumption

WordPress 7.0+ includes provider-agnostic AI infrastructure through the AI Client and Connectors API.

Moment should integrate with that infrastructure only when available.

Do not hardcode a dependency on OpenAI, Anthropic, Google, or any specific provider.

Do not store AI provider API keys in Moment.

Do not bundle a provider connector.

Instead:

- Create an internal AI adapter/service class.
- Feature-detect whether AI Client/Connectors infrastructure exists.
- If available and configured, use it.
- If unavailable, return deterministic mock suggestions suitable for demos.
- Never block publishing when AI is unavailable.

Because APIs may still evolve, inspect the installed WordPress version and available classes/functions before wiring the real integration. Keep the integration isolated so it can be updated later.

## Required User Flow

A logged-in user should be able to:

1. Visit `/moment`.
2. See a clean phone-first interface.
3. Tap `New Moment`.
4. Choose one or more images, videos, audio files, or text-only content from the phone.
5. Preview selected media.
6. Add optional caption or text.
7. Optionally tap `AI Assist`.
8. Accept, edit, or ignore AI suggestions.
9. Tap `Publish`.
10. See a success screen with links to:
    - View on site.
    - Create another Moment.
    - View timeline.

## Content Requirements

Use standard WordPress posts as the canonical Moment object.

Do not create a custom post type unless there is a compelling technical reason. If you think a custom post type is needed, stop and explain why before implementing it.

Do not model a Moment as a Media Library attachment alone. Media attachments are used for files, but the Moment itself should be the `post` that wraps the media, text, comments, permalink, syndication tracking, and notifications.

When publishing a single-image Moment:

- Upload the image to the standard WordPress Media Library.
- Create a standard `post` post type.
- Attach the uploaded image to the post.
- Set the uploaded image as the featured image where practical.
- Set `post_status` to `publish` for users with `publish_posts`.
- If the user lacks `publish_posts` but has `edit_posts`, create a draft.
- Generate a sensible title from the caption or timestamp.
- Render the image and optional caption in `post_content` using normal block markup where practical.
- Mark the post as a Moment using metadata.
- Apply default destination routing based on the Moment type, such as image Moment to Instagram, while keeping the WordPress post canonical.

For text, video, audio/podcast, gallery, and mixed-media Moments, use the same pattern: a standard `post` plus normal Media Library attachments where media exists.

Suggested post fields:

- `post_type` = `post`
- `post_title` = generated from caption or timestamp
- `post_content` = block markup for the Moment
- `post_excerpt` = optional caption summary
- `featured_image` = primary media attachment where practical

Suggested metadata:

- `_moment_is_moment` = `1`
- `_moment_primary_type` = `image`, `video`, `audio`, `podcast`, `note`, `gallery`, or `mixed`
- `_moment_media_ids` = JSON or serialized array of attachment IDs
- `_moment_syndication_targets` = JSON or serialized array
- `_moment_default_destinations` = JSON or serialized array of defaults applied before overrides
- `_moment_syndication_status` = `not_attempted`, `mocked`, `queued`, `published`, or `failed`
- `_moment_external_posts` = JSON or serialized array of external post references
- `_moment_comment_backflow_enabled` = `1` or `0`
- `_moment_ai_assist_used` = `1` or `0`

Preferred content markup:

- Use paragraph blocks for text.
- Use `core/image` for single images.
- Use `core/gallery` for galleries if practical.
- Use `core/video` for videos.
- Use `core/audio` for audio or podcast-style Moments.
- For mixed media, use straightforward block markup in the order selected.

## Presentation Requirements

Provide ways to display Moment content on the front end.

Implement at least one of these approaches:

### Preferred

Dynamic blocks:

- `moment/timeline`
- `moment/images`
- `moment/videos`
- `moment/audio`
- `moment/notes`

### Acceptable for prototype

Shortcodes:

- `[moment_timeline]`
- `[moment_images]`
- `[moment_videos]`
- `[moment_audio]`
- `[moment_notes]`

If dynamic blocks are too time-consuming, implement shortcodes first and structure the code so dynamic blocks can be added later.

Each view should query standard posts where `_moment_is_moment = 1`.

Filtering:

- Timeline: all Moments.
- Images: image, gallery, or mixed Moments with image media.
- Videos: video or mixed Moments with video media.
- Audio: audio/podcast or mixed Moments with audio media.
- Notes: note Moments or Moments with no media.

## Routes

Implement `/moment` as the mobile app shell.

For presentation routes, choose the simplest reliable approach:

- Create pages automatically on activation with shortcodes, or
- Add rewrite routes and templates, or
- Provide blocks/shortcodes and document page creation.

Suggested views:

- `/timeline`
- `/images`
- `/videos`
- `/audio`
- `/notes`

For prototype speed, automatically creating pages on activation is acceptable if done carefully and documented.

## UI Requirements

The UI should feel like a simple mobile publishing app, not WordPress admin.

Use plain CSS or a lightweight build setup.

Required screens:

1. Moment Home
   - New Moment button.
   - Recent Moments or drafts.
   - Links to Timeline, Images, Videos, Audio, Notes.

2. Create Moment
   - Media picker.
   - Preview selected media.
   - Caption/text field.
   - AI Assist button.
   - Publish button.

3. AI Assist Sheet
   - Suggested caption.
   - Suggested alt text.
   - Suggested tags.
   - Accept/edit/ignore controls.
   - Clear note if mock suggestions are being used.

4. Publish Screen
   - Destination: Your Site, required and always enabled.
   - Optional syndication toggles for Bluesky, Mastodon, Threads, Instagram, TikTok, YouTube, and X.
   - Defaults preselected based on Moment type.
   - Clear mocked/not connected labels for prototype destinations.
   - Publish Now button.

5. Success Screen
   - Published confirmation.
   - View on Site.
   - Mocked syndication status.
   - Create Another.

6. Notifications
   - On-site comments on Moment-created posts.
   - Imported social replies/comments attached to Moment-created posts.
   - Source labels such as `Reply from Bluesky` or `Comment from Instagram`.
   - No comments from normal non-Moment posts by default.

Design constraints:

- Mobile-first.
- Large tap targets.
- Minimal navigation.
- No admin menus.
- No plugin/user/settings distractions in the publishing flow.
- Accessible labels and keyboard-friendly controls.

## Social-First Onboarding Posture

Do not build a full hosted onboarding system in the prototype, but preserve the product direction.

Moment should be compatible with a future flow where a social-first creator can:

1. Create or claim a site address.
2. Pick a simple personal profile style.
3. Connect optional social destinations through WordPress Connector plugins or compatible social plugins.
4. Skip or enable AI Assist.
5. Land directly in `/moment`.
6. Publish the first Moment quickly.

The prototype should make this feel plausible by keeping the first screen focused on publishing rather than configuration.

## Home Screen and PWA Requirements

Moment should be launchable from a phone home screen for demos.

### README instructions

The plugin README must include a section titled `Using Moment Like a Phone App` with instructions for:

- Opening `/moment` on a phone.
- Adding the Moment URL to the iOS/iPadOS home screen using Safari → Share → Add to Home Screen.
- Adding the Moment URL to the Android home screen using Chrome → Add to Home screen or Install app.
- Explaining whether the current prototype launches as a browser shortcut or a true PWA standalone experience.

Use this example URL in the README and tell users to replace it with their own site URL:

```text
https://example.com/moment
```

### Best-effort PWA support

Implement a lightweight PWA shell if practical within the prototype.

Recommended implementation:

- Add a web app manifest.
- App name: `Moment`.
- Short name: `Moment`.
- Start URL: `/moment`.
- Scope: `/moment`.
- Display mode: `standalone`.
- Include basic app icons.
- Include theme and background colors.
- Register a service worker only for Moment routes.
- Cache only static app-shell assets.
- Do not cache authenticated REST responses, nonces, wp-admin pages, or private media URLs.

Do not implement push notifications, background sync, or aggressive offline behavior in the first prototype.

If full PWA support cannot be completed quickly, implement the manifest first and document the remaining work clearly in the README.

## REST API Requirements

Create a namespace such as `/wp-json/moment/v1/`.

Suggested endpoints:

- `POST /moments`
  - Creates a Moment.
  - Accepts multipart form data.
  - Requires nonce and logged-in user.

- `POST /ai/suggestions`
  - Returns optional AI Assist suggestions.
  - Requires nonce and logged-in user.
  - Uses real AI provider if available, otherwise mock suggestions.

- `GET /moments`
  - Returns recent Moment summaries for the app shell.

- `POST /moments/{id}/sync-responses`
  - Creates mocked imported social replies/comments for a Moment.
  - Requires nonce and logged-in user.

- `GET /notifications`
  - Returns on-site comments and imported social responses for Moment-created posts only.

All endpoints must check capabilities and nonces.

## Security Requirements

Implement:

- `current_user_can()` checks.
- REST nonces.
- Upload MIME validation.
- Text sanitization.
- Escaped output.
- No unauthenticated publishing.
- No raw user input in HTML.
- No external API calls unless explicitly configured through WordPress AI infrastructure.

## AI Assist Details

Create a class such as `Moment_AI_Assist`.

Methods:

- `is_available()`
- `get_provider_label()`
- `suggest_caption( $context )`
- `suggest_alt_text( $attachment_id, $context )`
- `suggest_tags( $context )`
- `get_suggestions( $context )`

Fallback mock behavior:

- Caption: return a gentle, editable caption based on user text or file count.
- Alt text: return simple alt text based on file type and filename.
- Tags: return safe generic tags like `moment`, `photo`, `video`, `notes`, depending on content.

Important:

- Clearly mark mock suggestions in developer comments and optionally in the UI.
- Keep real provider integration isolated.
- Never make AI required for publishing.

## Syndication Routing and Connector Stub

Do not implement real syndication yet.

Add UI toggles for future targets:

- Bluesky
- Mastodon
- Threads
- X/Twitter
- Instagram
- TikTok
- YouTube

For now:

- Detect Moment type as `text`, `image`, `video`, `gallery`, or `mixed`.
- Preselect default destinations based on Moment type.
- Allow publish-time overrides.
- Store selected targets in post meta.
- Store mocked external post references for selected targets.
- Show destinations as `mocked`, `planned`, or `not connected`.
- Add an action hook after publishing, for example:

```php
do_action( 'moment_published', $post_id, $moment_data );
```

This allows future syndication plugins or integrations to hook in. Prefer future integrations that use WordPress Connector plugins or compatible existing social publishing plugins so Moment does not own every social network API directly.

## Conversation Backflow and Notifications

Build a small but visible backflow layer.

### External post tracking

When a Moment is published with mocked syndication targets, store external references as post meta, for example:

```php
_moment_external_posts = array(
    'bluesky' => array(
        'external_id' => 'mock-bsky-123',
        'external_url' => 'https://bsky.app/profile/example.com/post/mock-bsky-123',
        'label' => 'Bluesky',
    ),
);
```

### Mocked reply/comment import

Create a prototype-only action to import sample responses for the selected Moment.

Examples:

- `Reply from Bluesky`: "Love this."
- `Comment from Instagram`: "Great shot."
- `Comment from YouTube`: "This looks fun."

Use WordPress comments with comment meta where practical:

```php
_moment_comment_source
_moment_comment_source_label
_moment_comment_external_id
_moment_comment_external_url
_moment_comment_external_author
_moment_comment_imported_at
```

Imported comments should display on the individual Moment post alongside normal WordPress comments.

### Notifications screen

Add a `/moment/notifications` view.

It should show:

- New on-site comments on Moment-created posts.
- Imported replies/comments from social networks for Moment-created posts.
- Source labels such as `Reply from Bluesky` and `Comment from Instagram`.
- A link to the Moment post.
- A link to the source network comment/reply when available.

Default exclusion rule:

- Do not show comments from normal posts that were not created as Moments.
- This can be controlled by checking `_moment_is_moment` on the post.

### Connector posture

Add comments/interfaces showing future real integrations may use:

- Social network APIs directly.
- WordPress Connector plugins that connect directly to social platforms.
- Existing WordPress social publishing plugins.
- Native Moment connector plugins.
- Hosted integrations managed by a host.

For the first prototype, do not integrate real comment APIs. Mock the data but make the architecture obvious.

## Default Syndication Routing

Build a small but visible routing layer.

### Moment type detection

Classify a Moment as one of:

- `note`
- `image`
- `video`
- `audio`
- `podcast`
- `gallery`
- `mixed`

Use the primary selected content item as the default type when needed. For the first prototype, mixed media can use the primary content type and show an override.

### Default destination settings

Add a lightweight settings/config structure for default destinations by type.

Demo defaults:

```php
$note_defaults = array( 'bluesky' );
$image_defaults = array( 'instagram' );
$video_defaults = array( 'youtube' );
$audio_defaults = array(); // podcast/audio destination when configured
$gallery_defaults = array( 'instagram' );
$mixed_defaults = array(); // ask user or derive from primary type
```

The UI can be a simple admin settings page, JSON config, or prototype-only settings panel.

### Publish screen behavior

On the Publish screen:

- Always show `Your Site` as required and enabled.
- Show optional social destinations below it.
- Preselect destinations based on Moment type defaults.
- Allow users to toggle destinations before publishing.
- Clearly label disconnected or mocked destinations.

### Metadata

Store the routing decisions on the created post:

```php
_moment_type
_moment_primary_media_type
_moment_selected_destinations
_moment_syndication_status
```

### Connector abstraction

Create a minimal interface or service layer such as:

```php
interface Moment_Syndication_Connector {
    public function get_id(): string;
    public function get_label(): string;
    public function supports_moment_type( string $type ): bool;
    public function is_connected(): bool;
    public function publish( int $post_id, array $payload ): array;
}
```

For the prototype, implement mocked connectors for:

- Bluesky
- Mastodon
- Instagram
- YouTube
- TikTok
- Threads
- X

Do not build real API publishing unless specifically requested. The first goal is to prove the workflow and product concept, not to complete every network integration.

### Integration posture

Add comments and hooks indicating that future connectors may be implemented by:

- WordPress Connector plugins that connect directly to social platforms.
- Existing WordPress social publishing plugins.
- Native Moment connector plugins.
- Hosted provider integrations.
- Direct platform APIs where appropriate.

Avoid hard dependencies on any existing plugin in the first prototype.

## Suggested File Structure

Use a clean plugin structure. Example:

```text
moment/
  moment.php
  README.md
  includes/
    class-plugin.php
    class-routes.php
    class-rest-controller.php
    class-publisher.php
    class-ai-assist.php
    class-renderer.php
    class-blocks.php
  assets/
    app.css
    app.js
  blocks/
    timeline/
      block.json
      render.php
    images/
      block.json
      render.php
    videos/
      block.json
      render.php
    notes/
      block.json
      render.php
```

If using `@wordpress/scripts`, set up `package.json` and build tooling. If that slows the prototype too much, use vanilla JavaScript first and document the tradeoff.

## README Requirements

Create a README that includes:

- What Project Moment is and why the plugin uses `moment` as its code slug.
- How to install the plugin locally.
- How to access `/moment`.
- How to add `/moment` to a phone home screen on iOS and Android.
- Whether PWA support is implemented, partial, or planned.
- How to create a Moment.
- How to add Moment views to pages.
- How AI Assist behaves.
- What is mocked versus real.
- Known limitations.
- Privacy/trust FAQ.
- End-to-end test instructions.
- Next steps.

## Demo Script to Support

The final prototype should support this private demo:

1. "This is not mobile wp-admin. This is Personal Site Publisher Mode for WordPress."
2. Open `/moment` on a phone-sized viewport.
3. Show that `/moment` can be launched from a phone home screen or explain the Add to Home Screen path.
4. Tap `New Moment`.
5. Select an image.
6. Add a caption.
7. Tap `AI Assist` and show optional suggestions.
8. Publish.
9. View the published post.
10. Open the timeline.
11. Open the images view.
12. Explain that the post is standard WordPress content and remains portable.

## Acceptance Criteria

The work is complete when:

- The plugin activates without fatal errors.
- `/moment` loads for logged-in users.
- The README includes iOS and Android Add to Home Screen instructions.
- The prototype includes at least a web app manifest or clearly documents PWA support as a next step.
- Unauthenticated users are redirected to login.
- A user can publish at least one image Moment.
- A user can publish a text-only Moment.
- The post appears as a standard WordPress post.
- Uploaded media appears in the standard media library.
- Moment timeline displays the published Moment.
- Image view displays image Moments.
- Notes view displays text-only Moments.
- AI Assist can return suggestions without requiring a real AI provider.
- The code is documented enough for another developer to continue.
- The README explains what is prototype, what is mocked, and what is ready for iteration.
- The prototype includes or documents end-to-end tests for publishing, routing, notifications, and portability.

## Constraints

- Prioritize working prototype over perfect architecture.
- Keep publishing friction extremely low.
- Do not add site management features to the Moment UI.
- Do not build plugin marketplace, user management, analytics dashboard, or theme-building features into the Moment UI.
- Do not make AI required.
- Do not lock content into a proprietary structure.
- Do not build real social syndication in the first pass.
- Do not build real social comment/reply ingestion in the first pass. Mock the backflow behavior.
- Keep the product story visible in the UX.

## Final Output

Return:

1. A working plugin directory.
2. Setup instructions.
3. A brief summary of key decisions.
4. Known limitations.
5. Recommended next development steps.

---

Strategic reminder:

WordPress does not need to become a social network. It needs to become the best place for social-shaped content to begin.
