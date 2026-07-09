# Project Moment Artifact Package

Project Moment is a phone-first **Personal Site Publisher Mode** for WordPress.

It is designed to help people publish text, images, video, audio, and mixed media to their own WordPress site as easily as they publish to social networks, while keeping the site as the source of truth.

This package includes the current set of working artifacts for private demos, product conversations, and prototype development.

## How to use this package

Recommended path:

1. Read this README.
2. Review `02_one_page_product_brief.md` for the product shape.
3. Review `04_prototype_mvp_spec.md` for the build scope.
4. Use `05_llm_prompt_build_prototype.md` with a coding agent to generate the first local plugin prototype.
5. Build using the WordPress plugin slug `moment`.
6. Add the `/moment` route to your phone home screen using the instructions in `10_home_screen_and_pwa_instructions.md`.
7. Run the end-to-end test scenarios in `13_success_metrics_and_e2e_tests.md`.
8. Use `14_private_demo_script.md` for private walkthroughs.
9. Use `15_hosted_moment_concept.md` when discussing a future host-supported path.

## Naming conventions

Use **Project Moment** for the public product concept, blog post, and external proposal language.

Use `moment` for all code-facing references. This includes:

- WordPress.org plugin slug: `moment`
- Plugin directory: `moment`
- Main plugin file: `moment.php`
- Text domain: `moment`
- REST namespace: `/wp-json/moment/v1/`
- Block namespace: `moment/*`
- Shortcodes: `[moment_timeline]`, `[moment_images]`, `[moment_videos]`, `[moment_notes]`, `[moment_audio]`
- Action/filter prefix: `moment_`
- PHP class prefix or namespace: `Moment_` or `Moment\`

Avoid `project-moment` and `project_moment` in prototype code, plugin scaffolding, package names inside the plugin, REST namespaces, block names, text domains, action hooks, function names, and class prefixes.

## Included artifacts

1. `01_blog_post_project_moment.md`
   - Publishable blog post draft for public validation.

2. `02_one_page_product_brief.md`
   - Product brief for builders, contributors, sponsors, hosts, and product leaders.

3. `03_private_demo_storyline.md`
   - A short narrative for demo conversations.

4. `04_prototype_mvp_spec.md`
   - MVP scope, user flows, implementation assumptions, non-goals, and end-to-end test direction.

5. `05_llm_prompt_build_prototype.md`
   - Detailed prompt for a coding LLM/agent to build a private WordPress prototype.

6. `06_visual_mockup_brief.md`
   - Direction for cleaner mobile-first mockups and front-end presentation patterns.

7. `07_visual_mockup_prompts.md`
   - Prompts for generating or iterating visual mockups.

8. `08_decisions_and_open_questions.md`
   - Current decisions, constraints, and unresolved questions.

9. `09_default_syndication_routing.md`
   - Product model for default publishing destinations by Moment type and social connection strategy.

10. `10_home_screen_and_pwa_instructions.md`
   - Instructions and implementation notes for adding `/moment` to a phone home screen and making the prototype PWA-ready.

11. `11_conversation_backflow_notifications.md`
   - Product model for importing replies/comments from syndicated social posts and showing them in Moment notifications.

12. `12_content_model_technical_path.md`
   - Technical path for representing Moments as standard WordPress posts with attached media and metadata.

13. `13_success_metrics_and_e2e_tests.md`
   - Candidate success metrics and test-driven end-to-end scenarios for the first prototype.

14. `14_private_demo_script.md`
   - Repeatable private demo script for walking others through the concept.

15. `15_hosted_moment_concept.md`
   - Optional hosted Moment concept for discussions with WordPress.com, hosts, or sponsors.

16. `assets/moment-reference-mockup-board.png`
   - Existing reference mockup board showing the broader product direction.

## Strategic line

WordPress does not need to become a social network. It needs to become the best place for social-shaped content to begin.

## Product model

Moment has five connected layers:

1. **Personal Site Publisher Mode**
   - A simple mobile-first experience for creating Moments without exposing wp-admin.
   - The initial target is personal sites representing an individual, while still allowing brand or organization use cases where content maps to a single social identity.

2. **Portable WordPress content**
   - Moments are standard WordPress `post` objects with attached media wherever possible.
   - The selected image, video, audio, or gallery media lives in the standard Media Library and is attached to the Moment post.
   - For an image Moment, the primary image should also be set as the featured image where practical.
   - Moment-specific metadata is used only to support routing, filtering, app views, notifications, AI Assist, and external social references.
   - A Moment should not be modeled as a media attachment alone because it needs a permalink, comments, feeds, syndication tracking, notifications, and template/query support.
   - Avoid a custom post type for the prototype unless a hard technical constraint appears; portability is more important than a perfectly isolated data model.

3. **Presentation**
   - Timeline, images, videos, audio/podcast, notes, profile, and homepage sections powered by the same underlying posts.
   - Blocks, patterns, and templates should ideally integrate with the latest default WordPress theme so the front-end experience feels native to WordPress rather than bolted on.

4. **Distribution**
   - The WordPress site is always the canonical destination.
   - Optional social destinations are preselected based on Moment type and can be changed before publishing.
   - Social platform integrations should preferably route through WordPress Connector plugins or existing social publishing plugins, rather than Moment owning every network API directly.

5. **Conversation backflow**
   - Moment tracks external social posts created from a Moment.
   - Connected networks or adapters can import replies/comments back into WordPress.
   - Imported responses and on-site comments appear together on the Moment and inside Moment notifications.
   - Moment notifications are scoped to Moment-created content by default.

## Prototype target

Build a WordPress plugin prototype that provides:

- A mobile-first publishing route, such as `/moment`.
- README instructions for adding `/moment` to a phone home screen as an app-like launcher.
- Best-effort PWA support so `/moment` can feel app-like when launched from the home screen.
- A seamless, graceful, simple onboarding-friendly flow for social-first creators.
- A flow to create a Moment from the phone camera roll or text input.
- Standard WordPress post and media storage, where each Moment is a `post` with attached media rather than a standalone media item.
- Optional AI Assist via WordPress 7.0 AI Client and Connectors, with graceful fallback.
- Basic feed presentation through blocks, shortcodes, templates, or latest-default-theme-friendly patterns.
- Default syndication routing by Moment type, such as notes to Bluesky, images to Instagram, video to YouTube, and audio/podcast Moments to a podcast/audio feed or selected audio destination.
- Publish-time destination toggles so users can override defaults before publishing.
- External post reference tracking for mocked social destinations.
- Conversation backflow that can import mocked replies/comments from connected social posts and attach them to the original Moment.
- A Moment notifications screen focused on Moment-created content by default.
- An outbound and inbound social adapter layer that can integrate with WordPress Connector plugins, existing WordPress social plugins, or native Moment connector plugins.
- No custom post type unless absolutely necessary; the prototype should default to `post` plus Moment metadata.
- No dependency on a specific AI vendor.
- No content lock-in.

## Candidate success metrics

These are starting metrics to refine before treating them as final:

- First publish completion rate: percentage of new users who publish their first Moment during the initial session.
- Time to first Moment: time from opening `/moment` to a published Moment.
- Home-screen adoption: percentage of testers who add Moment to their phone home screen.
- Repeat publishing: percentage of testers who publish three or more Moments in a week.
- Routing comprehension: percentage of testers who understand that WordPress is canonical and social networks are optional destinations.
- Notification comprehension: percentage of testers who understand imported social replies/comments are shown inside Moment.
- Portability confidence: percentage of testers who understand Moments remain standard WordPress content.
- Builder interest: number of people who want to contribute to mobile UX, blocks/patterns, connectors, AI Assist, or prototype engineering.
- Host interest: number of host/platform conversations that result in interest in a low-cost Moment-style onboarding or publishing tier.

## Moment types and default syndication routing

Moment should support default syndication destinations by Moment type. For example:

| Moment type | Primary content | Example default destination | WordPress model |
| --- | --- | --- | --- |
| Note | Text | Bluesky | `post` |
| Image | Single image | Instagram | `post` + media attachment |
| Gallery | Multiple images | Instagram | `post` + media attachments |
| Video | Video | YouTube | `post` + media attachment or embed |
| Audio / Podcast | Audio-only episode | Podcast/audio feed or selected audio destination | `post` + media attachment or embed |
| Mixed media | Text plus media | Ask each time or use primary content type | `post` + attachments |

Podcast Moments are included as a concept because some creators publish audio-first content. Video podcast handling can be worked out later as part of the broader audio/video model.

Users should be able to override defaults at publish time. The prototype does not need to complete real outbound publishing, but it should model the settings, routing logic, UI state, and stored metadata so the demo clearly shows how routing works.

## Conversation backflow and notifications

Moment should support a two-way publishing loop where technically possible:

1. Publish the Moment to the user's WordPress site.
2. Optionally syndicate the Moment to selected social networks.
3. Track the external social posts created from that Moment.
4. Pull replies/comments back into WordPress when supported by connected networks.
5. Show those responses alongside on-site comments on the original Moment.
6. Surface those responses inside a Moment notifications screen.

Imported responses should be clearly labeled, such as:

- `Reply from Bluesky`
- `Comment from Instagram`
- `Comment from YouTube`
- `On-site comment`

By default, the Moment notifications screen should only show activity for Moment-created content, not comments on normal posts created through wp-admin or Gutenberg. That keeps the phone-first Moment experience focused and avoids pulling users into full-site management unexpectedly.

## Privacy and trust FAQ

### Does Moment own my content?

No. The WordPress site is the canonical home. Moment should store content as standard WordPress posts and media wherever possible.

### Does Moment require publishing to social networks?

No. Social destinations are optional. The user's site is always the required destination.

### Does Moment require AI?

No. AI Assist is optional and should never block publishing. It should connect through WordPress AI infrastructure and provider/connector plugins where available.

### Does Moment store AI provider API keys?

No. Moment should not store provider API keys. AI provider configuration should live in the appropriate WordPress AI provider or connector plugin.

### Can users control comment/reply backflow?

Yes. Conversation backflow should be network-aware and optional. Users should be able to disable imported replies/comments per network or globally.

### Will normal WordPress post comments appear in Moment notifications?

Not by default. Moment notifications should focus on Moment-created content. A future setting could opt in comments from other posts, but the default should keep the Moment experience focused.

### Can brands use Moment?

Yes, but the primary design target is a personal site representing an individual. Brands or organizations can use the same model when they publish to a single brand identity across social platforms.

## Home screen and PWA demo path

Moment should be usable from a phone home screen for private demos. At minimum, the prototype README should explain how to add the `/moment` URL to iOS and Android home screens so it behaves like a lightweight phone app launcher, even if it opens in the default browser.

Best case, the prototype should include Progressive Web App support with a manifest, icons, standalone display mode, and conservative service worker behavior for the Moment app shell. PWA support should be scoped to Moment routes and should not cache authenticated REST responses, nonces, wp-admin pages, or private media URLs.

## AI Assist reference context

The optional AI Assist layer should align with WordPress 7.0+ AI infrastructure:

- AI Client: provider-agnostic PHP API for AI calls.
- Connectors API: provider discovery and connector management.
- Provider plugins: separate connector/provider plugins rather than Moment bundling a specific AI service.

Useful reference sources:

- https://make.wordpress.org/core/2026/03/18/introducing-the-connectors-api-in-wordpress-7-0/
- https://make.wordpress.org/core/2026/03/24/introducing-the-ai-client-in-wordpress-7-0/
- https://make.wordpress.org/core/2026/05/14/wordpress-7-0-field-guide/
- https://make.wordpress.org/ai/2026/03/25/call-for-testing-community-ai-connector-plugins/
