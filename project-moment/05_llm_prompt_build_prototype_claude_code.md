# LLM Prompt: Build the Project Moment WordPress Prototype
## Optimized for Claude Code + Fable-Class Models with Multi-Agent Orchestration

> **Usage**: Run from your local WordPress environment root with Claude Code.
> Feed the full artifact package into context before starting (see Context Loading below).
> This prompt replaces `05_llm_prompt_build_prototype.md` for Claude Code sessions.

---

## How to Start This Session

```bash
cd /path/to/your/local/wordpress
claude --model claude-fable-5  # or current Fable-class model flag
```

Then paste this prompt. Claude Code will read it, load context, generate `CLAUDE.md`,
and begin Phase 0. Do not proceed past each phase gate until the verification passes.

---

## Context Loading

You have a 1M token context window. Load all of these before writing any code:

```
Read into context:
- project-moment/00_README.md
- project-moment/02_one_page_product_brief.md
- project-moment/04_prototype_mvp_spec.md
- project-moment/08_decisions_and_open_questions.md
- project-moment/09_default_syndication_routing.md
- project-moment/11_conversation_backflow_notifications.md
- project-moment/12_content_model_technical_path.md
- project-moment/13_success_metrics_and_e2e_tests.md
```

Do not summarize these. Hold them in full context throughout the session.

---

## Your Role

You are Claude Code, working autonomously on a local WordPress installation.
You have full bash access. You will read files, write code, run WP-CLI commands,
execute tests, and coordinate sub-agents for parallel workstreams.

You are helping build a private prototype WordPress plugin called **Project Moment**.

**Do not ask for permission between phases unless a verification gate fails.**
Work autonomously through each phase. Report blockers, not updates.

---

## THINKING CHECKPOINT: Before Any Code

> Engage extended reasoning here. Do not skip this.

Think through the following before writing a single file:

1. **Content model decision**: Standard `post` vs. custom post type. Consider query performance, REST exposure, feed compatibility, portability when deactivated, and the demo deactivation acceptance test. The spec says to default to `post` — confirm this is right for the prototype, or stop and explain why it isn't.

2. **WP 7.0 AI Client availability**: The environment may not have WP 7.0. Confirm whether `WP_AI_Client` or `WP_Connectors_API` classes are available via `wp eval`. If not, the mock path applies from session one.

3. **Route strategy**: `/moment` can be implemented three ways — rewrite rules + template, page auto-created on activation, or a custom endpoint. Pick the most reliable approach for a local/private prototype and commit to it.

4. **Phase ordering**: The phases below are sequential by dependency, not by estimated effort. Do not reorder them. A failing phase blocks the next.

---

## Phase 0 — Environment and CLAUDE.md

**What to do:**

1. Run environment checks:
```bash
php --version
wp --version
wp core version
wp eval "echo PHP_VERSION;"
wp eval "echo defined('WP_AI_CLIENT_VERSION') ? WP_AI_CLIENT_VERSION : 'not available';"
wp option get siteurl
```

2. Generate `CLAUDE.md` at the WordPress root with the following structure:

```markdown
# Project Moment — CLAUDE.md

## Plugin identity
- Public name: Moment
- Concept name: Project Moment
- Plugin slug: moment
- Plugin dir: moment/
- Main file: moment/moment.php
- Text domain: moment
- REST namespace: /wp-json/moment/v1/
- Block namespace: moment/*
- PHP class prefix: Moment_
- Action/filter prefix: moment_
- DO NOT use: project-moment, project_moment anywhere code-facing

## Environment
- WP version: [fill from check]
- PHP version: [fill from check]
- WP 7.0 AI Client available: [yes/no]
- Site URL: [fill from check]
- Plugin path: [wp eval "echo WP_PLUGIN_DIR;"]/moment/

## Build commands
- Activate: wp plugin activate moment
- Deactivate: wp plugin deactivate moment
- Tests (PHP): cd moment && composer test
- Tests (E2E): cd moment && npx playwright test
- Lint: cd moment && composer phpcs

## Phase status
- [ ] Phase 0: Environment + CLAUDE.md
- [ ] Phase 1: Plugin scaffold + activation
- [ ] Phase 2: REST API + publisher
- [ ] Phase 3: Frontend app shell
- [ ] Phase 4: AI Assist adapter
- [ ] Phase 5: Syndication routing + connectors
- [ ] Phase 6: Conversation backflow + notifications
- [ ] Phase 7: Blocks and shortcodes
- [ ] Phase 8: PWA + home screen
- [ ] Phase 9: E2E tests

## Key decisions (fill in as made)
- Route strategy: [fill]
- Content model: standard post
- AI fallback: mock until WP 7.0 AI Client confirmed
- Block vs shortcode: [fill]

## Conventions
- Never store AI provider keys in plugin
- Never create a custom post type unless blocked by a hard constraint
- Never use project-moment as a code identifier
- Escaped output everywhere
- WP nonces on all REST endpoints
```

**Phase 0 gate:** CLAUDE.md exists and environment vars are filled in.

---

## Phase 1 — Plugin Scaffold + Activation

**Delegate to sub-agent: `wp-php-core`**

Spawn a sub-agent with the following briefing. The sub-agent has access to Read, Write, Edit, and Bash (WP-CLI, phpcs).

```
Sub-agent task: scaffold the Moment WordPress plugin.

Plugin identity from CLAUDE.md applies. Do not deviate.

Create this structure:
  moment/
    moment.php              # Plugin header + bootstrap
    README.md               # Placeholder, detailed later
    includes/
      class-plugin.php      # Singleton loader
      class-routes.php      # /moment rewrite + template routing
      class-rest-controller.php
      class-publisher.php
      class-ai-assist.php
      class-blocks.php
      class-renderer.php
      class-syndication-registry.php
      class-notifications.php
    assets/
      app.css
      app.js
    blocks/
      timeline/
      images/
      videos/
      audio/
      notes/
    templates/
      app-shell.php

Plugin header must include:
  Plugin Name: Moment
  Description: Personal Site Publisher Mode for WordPress.
  Version: 0.1.0
  Requires at least: 6.0
  Requires PHP: 8.1
  Text Domain: moment

On activation:
  - Register rewrite rules for /moment
  - Create /timeline, /images, /videos, /audio, /notes pages
    with corresponding shortcodes if they don't exist
  - Flush rewrite rules
  - Store activation flag in options

On deactivation:
  - Flush rewrite rules
  - Do NOT delete content or meta

After writing all files, run:
  wp plugin activate moment
  wp eval "echo (class_exists('Moment_Plugin') ? 'OK' : 'FAIL');"

Return: activation result + file manifest.
```

**Phase 1 gate:**
```bash
wp plugin activate moment
# Must return: Plugin activated.
wp eval "echo (class_exists('Moment_Plugin') ? 'PASS' : 'FAIL');"
# Must return: PASS
```

---

## Phase 2 — REST API + Publisher

**Delegate to sub-agent: `wp-php-core`** (can run in parallel with Phase 3 frontend scaffold)

```
Sub-agent task: implement the REST API and publisher for Moment.

REST namespace: /wp-json/moment/v1/

Endpoints to implement:
  POST   /moments              - Create a Moment (multipart, nonce required)
  GET    /moments              - Recent Moment summaries
  POST   /ai/suggestions       - AI Assist suggestions (mock or real)
  POST   /moments/{id}/sync-responses  - Import mocked social replies
  GET    /notifications        - Moment-created activity only

All endpoints must:
  - Check current_user_can('edit_posts')
  - Validate WP REST nonce (X-WP-Nonce header)
  - Sanitize all inputs
  - Escape all output

Publisher (class-publisher.php) must:
  - Accept media files (images, video, audio)
  - Validate MIME type before upload
  - Upload via wp_handle_upload()
  - Create standard post (post_type = post)
  - Attach media to post
  - Set featured image for image Moments
  - Generate title from caption or timestamp
  - Apply metadata:
      _moment_is_moment = 1
      _moment_primary_type = (image|video|audio|podcast|note|gallery|mixed)
      _moment_media_ids = JSON array
      _moment_syndication_targets = JSON array
      _moment_default_destinations = JSON array
      _moment_syndication_status = not_attempted|mocked|queued|published|failed
      _moment_external_posts = JSON array
      _moment_comment_backflow_enabled = 1
      _moment_ai_assist_used = 0|1
  - Fire do_action('moment_published', $post_id, $moment_data) after publish

After writing, run:
  wp eval "
    \$r = new WP_REST_Request('GET', '/moment/v1/moments');
    \$r->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    \$response = rest_do_request(\$r);
    echo \$response->get_status() === 401 ? 'UNAUTH_BLOCKED_OK' : 'CHECK_AUTH';
  "

Return: endpoint list + publisher method signatures.
```

**Phase 2 gate:**
```bash
# Unauthenticated request must be blocked
wp eval "
  \$r = new WP_REST_Request('GET', '/moment/v1/moments');
  echo rest_do_request(\$r)->get_status() === 401 ? 'PASS' : 'FAIL';
"
# Must return: PASS
```

---

## Phase 3 — Frontend App Shell

**Delegate to sub-agent: `moment-frontend`** (can run in parallel with Phase 2)

```
Sub-agent task: build the Moment mobile-first frontend app shell.

This is NOT mobile wp-admin. It is a simple mobile publishing surface.
No admin menus. No sidebar. No plugin notices. No theme chrome.

Required screens (all within /moment route, JS-driven transitions):
  1. Moment Home
     - "New Moment" button (large, primary)
     - Recent Moments list (from GET /moment/v1/moments)
     - Navigation: Timeline | Images | Videos | Audio | Notes

  2. Create Moment
     - File picker: accept="image/*,video/*,audio/*" multiple
     - Media preview grid (up to 4 items shown)
     - Caption textarea (large, mobile-friendly)
     - "AI Assist" button
     - "Next: Publish" button

  3. AI Assist Sheet (slide-up panel)
     - Suggested caption (editable)
     - Suggested alt text (editable)
     - Suggested tags (chip list, removable)
     - "Accept All" / "Skip" / "Edit" controls
     - Muted notice: "Using demo suggestions" when mocked

  4. Publish Screen
     - "Your Site" row — always enabled, not toggleable
     - Destination toggles: Bluesky, Mastodon, Threads, Instagram,
       TikTok, YouTube, X — each with "Mocked / Not connected" label
     - Defaults preselected based on detected Moment type:
         note → Bluesky on
         image/gallery → Instagram on
         video → YouTube on
         audio/podcast → none preselected
     - "Publish Now" button
     - "Back" link

  5. Success Screen
     - "Published to your site" confirmation
     - "View on Site" link (opens in new tab)
     - Mocked syndication rows with status badges
     - "Create Another" button

  6. Notifications Screen (/moment/notifications)
     - Fetches from GET /moment/v1/notifications
     - Shows on-site comments and imported social replies
     - Source label chip on each item (e.g. "Reply from Bluesky")
     - Link to source Moment post
     - Empty state: "No new activity"

Design constraints:
  - Mobile-first: target 390px wide minimum
  - Tap targets: minimum 44px height
  - Plain CSS — no build step required for prototype
  - System font stack acceptable
  - Color palette: clean, neutral, minimal
  - DO NOT use admin color scheme variables
  - Accessible: aria-labels on all interactive elements
  - JavaScript: vanilla ES2020, no framework required
  - The JS must read wp_localize_script data for nonce + REST URL

The template (templates/app-shell.php) must:
  - Not load the active theme
  - Load only moment assets
  - Set correct viewport meta
  - Output the manifest link
  - Authenticate check before render (redirect to wp-login if not logged in)

Return: screen list confirmed, CSS custom properties list, JS module map.
```

**Phase 3 gate:**
```bash
# Visit /moment in browser (or curl check)
curl -s -o /dev/null -w "%{http_code}" \
  "$(wp option get siteurl)/moment" \
  -H "Cookie: $(wp eval "wp_set_current_user(1); echo 'wordpress_logged_in_' . COOKIEHASH . '=' . wp_generate_auth_cookie(1, time()+3600);")"
# Must return 200 (or check browser manually)
echo "MANUAL CHECK: Open /moment on a phone-width viewport. Should show Moment Home, not wp-admin."
```

---

## Phase 4 — AI Assist Adapter

**Delegate to sub-agent: `wp-php-core`**

```
Sub-agent task: implement the AI Assist adapter.

Class: Moment_AI_Assist in includes/class-ai-assist.php

Methods required:
  is_available(): bool
    - Returns true if WP_AI_Client class exists AND a connector is configured
    - Returns false otherwise (mock path)

  get_provider_label(): string
    - Returns provider name if real, "Demo Mode" if mocked

  suggest_caption(array $context): string
    - $context contains: text, media_count, media_types, filename
    - Real: call WP AI Client if available
    - Mock: return a gentle caption based on context

  suggest_alt_text(int $attachment_id, array $context): string
    - Real: call WP AI Client if available
    - Mock: return "Photo" / "Video" / "Audio file" based on type

  suggest_tags(array $context): array
    - Real: call WP AI Client if available
    - Mock: return ['moment', media_type, 'personal']

  get_suggestions(array $context): array
    - Returns all three: caption, alt_text, tags
    - Adds is_mocked: bool field to response

WP 7.0 AI Client detection:
  if (class_exists('WP_AI_Client')) {
      // real path — isolate in a try/catch
  } else {
      // mock path
  }

Never throw. Never block publishing. If real path throws, fall back to mock.
Add a developer comment on every mock return explaining what the real path
would call.

REST endpoint POST /moment/v1/ai/suggestions must:
  - Accept: { text, media_ids, primary_type }
  - Return: { caption, alt_text, tags, is_mocked, provider_label }

Return: class method signatures + mock examples.
```

**Phase 4 gate:**
```bash
wp eval "
  \$ai = new Moment_AI_Assist();
  \$s = \$ai->get_suggestions(['text' => 'Testing', 'media_count' => 1, 'media_types' => ['image']]);
  echo isset(\$s['caption'], \$s['alt_text'], \$s['tags'], \$s['is_mocked']) ? 'PASS' : 'FAIL';
"
```

---

## Phase 5 — Syndication Routing + Connector Registry

**Delegate to sub-agent: `moment-syndication`**

```
Sub-agent task: implement the syndication routing layer and connector registry.

IMPORTANT: Do not implement real API publishing. This is all mocked.
The goal is to prove the workflow and routing model, not to complete network integrations.

Interface to implement:
interface Moment_Syndication_Connector {
    public function get_id(): string;
    public function get_label(): string;
    public function supports_moment_type(string $type): bool;
    public function is_connected(): bool;
    public function publish(int $post_id, array $payload): array;
    public function get_status_label(): string; // "Mocked", "Not connected", etc.
}

Implement mocked connectors for:
  Moment_Connector_Bluesky    — supports: note, mixed
  Moment_Connector_Mastodon   — supports: note, image, mixed
  Moment_Connector_Instagram  — supports: image, gallery, mixed
  Moment_Connector_YouTube    — supports: video, mixed
  Moment_Connector_TikTok     — supports: video, mixed
  Moment_Connector_Threads    — supports: image, note, mixed
  Moment_Connector_X          — supports: note, image, mixed

Registry class (class-syndication-registry.php):
  - Register all connectors on init
  - get_defaults_for_type(string $type): array of connector IDs
    Default routing:
      note    → ['bluesky']
      image   → ['instagram']
      gallery → ['instagram']
      video   → ['youtube']
      audio   → []
      podcast → []
      mixed   → []
  - get_connectors(): array of all connectors
  - publish_to_targets(int $post_id, array $target_ids, array $payload): array

After mocked publish, store to post meta:
  _moment_external_posts = [
    'bluesky' => [
      'external_id' => 'mock-bsky-' . $post_id,
      'external_url' => 'https://bsky.app/profile/demo/post/mock-bsky-' . $post_id,
      'label' => 'Bluesky',
      'published_at' => current_time('mysql'),
    ],
  ]

Add hook:
  do_action('moment_syndication_complete', $post_id, $results);

Add comment in class file:
  // Future real connectors can be registered via:
  // add_action('moment_register_connectors', [$my_connector, 'register']);
  // This allows WordPress Connector plugins, social plugins, or
  // native Moment connector plugins to hook in without modifying core.

Return: connector list + routing table + metadata schema.
```

**Phase 5 gate:**
```bash
wp eval "
  \$reg = Moment_Syndication_Registry::instance();
  \$defaults = \$reg->get_defaults_for_type('image');
  echo in_array('instagram', \$defaults) ? 'PASS' : 'FAIL';
"
```

---

## Phase 6 — Conversation Backflow + Notifications

**Delegate to sub-agent: `moment-backflow`**

```
Sub-agent task: implement conversation backflow and the notifications screen.

Goal: prove that external social replies can return to WordPress and appear
alongside on-site comments on a Moment post. All data is mocked.

Part A — Backflow importer:

REST endpoint: POST /moment/v1/moments/{id}/sync-responses

This endpoint (prototype-only, logged-in only):
  - Accepts: { networks: ['bluesky', 'instagram'] }
  - For each network with an external post reference in _moment_external_posts:
    - Create 1–2 sample WordPress comments using wp_insert_comment()
    - Add comment meta:
        _moment_comment_source = 'bluesky' (etc.)
        _moment_comment_source_label = 'Reply from Bluesky'
        _moment_comment_external_id = 'mock-reply-' . uniqid()
        _moment_comment_external_url = 'https://bsky.app/...'
        _moment_comment_external_author = 'Demo User (@demouser.bsky.social)'
        _moment_comment_imported_at = current_time('mysql')
    - Sample texts:
        Bluesky: "Love this."
        Instagram: "Great shot."
        YouTube: "This looks fun."
        Mastodon: "Nice one!"
  - Returns: { imported_count, comments }

Imported comments MUST appear on the Moment's standard WordPress post alongside
normal comments. Do not create a separate display system.

Part B — Notifications endpoint:

GET /moment/v1/notifications
  - Queries: all comments WHERE comment_post_ID is a post with _moment_is_moment = 1
  - Returns each with:
    - comment_ID, comment_content, comment_date
    - source_label (from _moment_comment_source_label or "On-site comment")
    - source_url (from _moment_comment_external_url if available)
    - post_id, post_title, post_url
  - Default exclusion: only Moment posts (do NOT return comments from normal posts)

Part C — Notifications screen (handled by frontend sub-agent in Phase 3):
  Already scaffolded. Wire it to GET /moment/v1/notifications.

Add comment/interface showing future real backflow would use:
  - WordPress Connector plugins that connect to social platform APIs
  - Polling or webhook adapters
  - Per-network comment deduplication by _moment_comment_external_id

Return: mocked comment schema + endpoint signatures.
```

**Phase 6 gate:**
```bash
# Create a test Moment post first, then test backflow
MOMENT_ID=$(wp post create \
  --post_title="Test Moment" \
  --post_status="publish" \
  --meta_input='{"_moment_is_moment":"1","_moment_primary_type":"image"}' \
  --porcelain)
wp eval "
  \$r = new WP_REST_Request('POST', '/moment/v1/moments/$MOMENT_ID/sync-responses');
  \$r->set_param('networks', ['bluesky']);
  // auth check only — no real import in unit context
  echo class_exists('Moment_Notifications') ? 'PASS' : 'FAIL';
"
```

---

## Phase 7 — Blocks and Shortcodes

**Delegate to sub-agent: `moment-blocks`** (sub-agent has access to Bash for file writes)

```
Sub-agent task: implement Moment presentation views.

THINKING CHECKPOINT: Choose between dynamic blocks and shortcodes for
this prototype. Dynamic blocks are preferred but require block.json +
render.php. Shortcodes are faster. Assess the environment and choose.
Document the choice in CLAUDE.md.

If dynamic blocks — implement for each type:
  moment/timeline
  moment/images
  moment/videos
  moment/audio
  moment/notes

Each block needs:
  - blocks/{type}/block.json  (name, title, description, render)
  - blocks/{type}/render.php  (queries WP_Query with _moment_is_moment + type filter)
  - Registered via register_block_type()

If shortcodes — implement:
  [moment_timeline]
  [moment_images]
  [moment_videos]
  [moment_audio]
  [moment_notes]

Each view/shortcode must:
  - Query posts with _moment_is_moment = 1
  - Apply type filter:
      timeline → all
      images   → primary_type IN (image, gallery, mixed) with media
      videos   → primary_type IN (video, mixed) with video
      audio    → primary_type IN (audio, podcast)
      notes    → primary_type = note OR no media
  - Return clean HTML, escaped
  - Show Moment type badge, date, caption excerpt, media thumbnail

Auto-create pages on activation (if not already done in Phase 1):
  - /timeline with [moment_timeline]
  - /images with [moment_images]
  - /videos with [moment_videos]
  - /audio with [moment_audio]
  - /notes with [moment_notes]

Return: block/shortcode list + query structure + activation page list.
```

**Phase 7 gate:**
```bash
wp eval "
  echo shortcode_exists('moment_timeline') ? 'SHORTCODE_PASS' : 'SHORTCODE_FAIL';
"
# OR for blocks:
wp eval "
  echo WP_Block_Type_Registry::get_instance()->is_registered('moment/timeline')
    ? 'BLOCK_PASS' : 'BLOCK_FAIL';
"
```

---

## Phase 8 — PWA + Home Screen

**Delegate to sub-agent: `moment-frontend`** (append to Phase 3 work)

```
Sub-agent task: implement PWA app shell support for Moment.

1. Web App Manifest:
   Serve at /wp-json/moment/v1/manifest.json OR as a static file
   Content:
   {
     "name": "Moment",
     "short_name": "Moment",
     "start_url": "/moment",
     "scope": "/moment",
     "display": "standalone",
     "background_color": "#ffffff",
     "theme_color": "#1a1a1a",
     "icons": [
       { "src": "/wp-content/plugins/moment/assets/icon-192.png",
         "sizes": "192x192", "type": "image/png" },
       { "src": "/wp-content/plugins/moment/assets/icon-512.png",
         "sizes": "512x512", "type": "image/png" }
     ]
   }

   If you cannot generate real PNG icons quickly, generate SVG-based
   placeholder icons and document this as a next step.

2. Manifest link in app-shell.php:
   <link rel="manifest" href="/moment/manifest.json">
   <meta name="theme-color" content="#1a1a1a">
   <meta name="apple-mobile-web-app-capable" content="yes">
   <meta name="apple-mobile-web-app-title" content="Moment">

3. Service Worker (conservative scope):
   Register at /moment-sw.js
   Cache ONLY:
     - /wp-content/plugins/moment/assets/app.css
     - /wp-content/plugins/moment/assets/app.js
   NEVER cache:
     - REST API responses
     - WP nonces
     - /wp-admin/* anything
     - Authenticated media URLs
   Strategy: cache-first for static assets, network-only for everything else.

4. Update README with "Using Moment Like a Phone App" section:
   - iOS: Safari → Share → Add to Home Screen → use https://[yoursite]/moment
   - Android: Chrome → ⋮ menu → Add to Home Screen or Install App
   - Explain: prototype launches as browser shortcut; standalone display
     requires HTTPS (note for local demo setup)

Return: manifest content + service worker scope + README section.
```

**Phase 8 gate:**
```bash
# Manifest must be reachable
curl -s "$(wp option get siteurl)/wp-content/plugins/moment/assets/manifest.json" \
  | grep -q '"name": "Moment"' && echo "MANIFEST_PASS" || echo "MANIFEST_FAIL"
```

---

## Phase 9 — E2E Tests

**Delegate to sub-agent: `moment-tester`** (Bash access: phpunit, playwright or wp-cli)

```
Sub-agent task: implement E2E test coverage for Project Moment.

Use the test scenarios in 13_success_metrics_and_e2e_tests.md as your test plan.

Priority 1 — PHPUnit (unit/integration):
  Test: plugin activates without fatal errors
  Test: Moment_Publisher creates a post with correct metadata
  Test: Moment_Publisher uploads media and attaches to post
  Test: Moment_AI_Assist returns suggestions without a real provider
  Test: Moment_Syndication_Registry returns correct defaults by type
  Test: GET /moment/v1/notifications excludes non-Moment post comments
  Test: plugin deactivation leaves posts and media intact

Priority 2 — WP-CLI-based smoke tests (if Playwright not available):
  wp post create + check meta
  wp comment list for a Moment post
  wp option get to verify activation flags

Priority 3 — Playwright E2E (if environment supports it):
  Test: /moment redirects unauthenticated users to login
  Test: authenticated user sees Moment Home
  Test: file picker accepts image
  Test: Publish creates a standard post in wp-admin
  Test: Timeline view shows the published post

Write tests first. Run them after each phase retroactively where possible.

Scaffold PHPUnit bootstrap at moment/tests/bootstrap.php.
Add composer.json scripts.test = "phpunit" if not already present.

Return: test file list + run command + initial pass/fail results.
```

**Phase 9 gate:**
```bash
cd wp-content/plugins/moment
composer test
# Must show: no fatal errors; at minimum the AI Assist mock test passes
```

---

## Cross-Phase Rules (Enforced Throughout)

These apply in every phase and every sub-agent:

- **Never** use `project-moment` or `project_moment` in code-facing identifiers.
- **Never** store AI provider API keys anywhere in the plugin.
- **Never** block publishing because AI is unavailable.
- **Never** create a custom post type without stopping to explain the reason.
- **Never** expose unauthenticated publishing endpoints.
- **Always** use `current_user_can()` before any write operation.
- **Always** sanitize inputs with `sanitize_text_field()`, `wp_kses_post()`, etc.
- **Always** escape output with `esc_html()`, `esc_attr()`, `esc_url()`.
- **Always** validate upload MIME types before `wp_handle_upload()`.
- **Always** fire `do_action('moment_published', $post_id, $moment_data)` after publish.
- **Always** update Phase status in CLAUDE.md after each phase completes.

---

## Sub-Agent Definitions
### `.claude/agents/wp-php-core.md`

```markdown
---
name: wp-php-core
description: >
  WordPress PHP specialist for Project Moment. Use for plugin bootstrap,
  REST API endpoints, publisher class, AI adapter, database/meta operations,
  and any PHP-level WordPress integration work. Has WP-CLI bash access.
tools: [Read, Write, Edit, Bash]
---

You are a WordPress PHP specialist. You write clean, secure PHP 8.1+ code
following WordPress Coding Standards. You use wp_insert_post(), wp_handle_upload(),
WP_REST_Controller, add_rewrite_rule(), and related WP APIs natively.

You never use a custom post type without stopping to explain why.
You never store provider API keys.
You always check current_user_can() before writes.
You sanitize all input. You escape all output.

Read CLAUDE.md before starting. Update phase status when done.
```

### `.claude/agents/moment-frontend.md`

```markdown
---
name: moment-frontend
description: >
  Mobile-first frontend specialist for Project Moment app shell. Use for
  the /moment route UI, vanilla JS screens, CSS, PWA manifest, and service
  worker. No build step required. No framework.
tools: [Read, Write, Edit]
---

You are a mobile-first frontend engineer. You write vanilla ES2020 JavaScript
and plain CSS. No frameworks. No build steps for the prototype.

Design target: feels like a simple native mobile app, not WordPress admin.
Mobile-first means 390px wide is the base. Tap targets are 44px minimum.
You do not load the active WordPress theme. You control the full app shell.

Read CLAUDE.md before starting. Update phase status when done.
```

### `.claude/agents/moment-syndication.md`

```markdown
---
name: moment-syndication
description: >
  Syndication routing and connector registry specialist for Project Moment.
  Use for the connector interface, mocked connectors, routing defaults,
  and metadata storage for outbound publishing. Does NOT implement real
  social API calls.
tools: [Read, Write, Edit]
---

You are implementing a mocked syndication layer for a WordPress plugin prototype.
Your job is to prove the routing model and connector architecture — not to
publish to real social networks.

Every connector you write returns mocked data and stores mocked external
post references. Every class should have developer comments showing where
real API integration would go.

Read CLAUDE.md before starting. Update phase status when done.
```

### `.claude/agents/moment-backflow.md`

```markdown
---
name: moment-backflow
description: >
  Conversation backflow and notifications specialist for Project Moment.
  Use for the mocked social reply importer, notifications REST endpoint,
  and comment metadata schema. Does NOT implement real social API polling.
tools: [Read, Write, Edit, Bash]
---

You are implementing mocked conversation backflow for a WordPress plugin prototype.
WordPress native comments are the storage layer. Your job is to prove that
replies from social platforms can be imported and displayed alongside on-site
comments — with clear source labeling — without a real social API connection.

Read CLAUDE.md before starting. Update phase status when done.
```

### `.claude/agents/moment-tester.md`

```markdown
---
name: moment-tester
description: >
  Test specialist for Project Moment. Use for PHPUnit tests, WP-CLI smoke
  tests, and Playwright E2E tests. Runs tests and reports failures. Does
  not modify source files — reports issues for other agents to fix.
tools: [Read, Bash]
---

You are a test-only agent. You read source files and run tests. You do not
modify source files. When a test fails, you report the exact failure and the
file + line number so the appropriate specialist agent can fix it.

Use 13_success_metrics_and_e2e_tests.md as your test plan.

Read CLAUDE.md before starting. Report results, not summaries.
```

---

## CLAUDE.md Update Hook

After every phase completes:
```
Update CLAUDE.md phase status checklist.
Update "Key decisions" section with any new decisions made.
Note any deviations from the spec and why.
```

---

## Final Deliverable Checklist

When all phase gates pass, confirm:

- [ ] Plugin activates without fatal errors
- [ ] `/moment` loads for authenticated users, redirects unauthenticated
- [ ] A user can publish an image Moment
- [ ] A user can publish a text-only Moment
- [ ] Both appear as standard `post` in wp-admin
- [ ] Media appears in Media Library
- [ ] Timeline view shows both Moments
- [ ] Images view shows the image Moment
- [ ] Notes view shows the text Moment
- [ ] AI Assist returns suggestions without a real provider
- [ ] Notifications screen shows imported mock social replies
- [ ] Deactivate plugin → posts and media still exist
- [ ] README includes iOS + Android home screen instructions
- [ ] CLAUDE.md is complete with all decisions documented

---

## Strategic Reminder

WordPress does not need to become a social network.
It needs to become the best place for social-shaped content to begin.

Every decision in this build should make that story clearer.
