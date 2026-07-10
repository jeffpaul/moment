# Project Moment: Visual Mockup Brief

## Goal

Create cleaner, focused visual mockups for Project Moment that emphasize mobile-first publishing and make the product understandable in seconds.

The mockups should not look like WordPress admin.

They should feel like a simple publishing app powered by WordPress.

## Visual Direction

- Mobile-first.
- Clean, calm, minimal.
- Large tap targets.
- Fast publishing energy.
- Personal creator feel, not enterprise dashboard.
- Subtle WordPress connection, but not wp-admin styling.
- Clear ownership message: publish to your site first, syndicate outward later.

## Mockup Board 1: Publish a Moment

Purpose: show the core creator flow.

Screens:

1. Moment Home
   - Large `New Moment` button.
   - Recent Moments or drafts.
   - No dashboard or site management elements.

2. Choose Content
   - Camera roll grid.
   - Images and videos visible.
   - Multi-select possible.

3. Compose Moment
   - Selected media preview.
   - Caption/text field.
   - Optional `AI Assist` button.
   - Publish button always obvious.

4. AI Assist Sheet
   - Suggested caption.
   - Suggested alt text.
   - Suggested tags.
   - Accept, edit, ignore.
   - AI clearly optional.

5. Publish
   - Destination: `Your Site` required and always on.
   - Social destinations preselected based on Moment type.
   - Example: text to Bluesky, image to Instagram, video to YouTube.
   - User can override toggles before publishing.
   - Publish Now.

6. Published
   - Success state.
   - View on site.
   - Create another Moment.

## Mockup Board 2: Your Site as the Source of Truth

Purpose: show that Moment is more than a capture tool.

Screens:

1. Timeline
   - Mixed text, image, video, gallery Moments.

2. Images
   - Grid view similar to a social profile media tab.

3. Videos
   - Video-first feed.

4. Notes
   - Text-first stream.

5. Audio / Podcast
   - Audio-first episode list or compact player view.

5. Profile/Home
   - Personal social-style landing page powered by WordPress.

Message:

Same content. Different views. Still WordPress.

## Mockup Board 3: First-Run Onboarding

Purpose: show Moment as a new front door for WordPress.

Screens:

1. Choose your Moment address
   - Example: `jane.moment.site` or use your own domain later.

2. Pick a starter profile style
   - Simple visual choices.

3. Optional AI Assist setup
   - Skip by default.
   - Connect provider later through WordPress Connectors.

4. Publish your first Moment
   - Camera roll appears immediately.

5. Your site is live
   - First Moment visible on the user's own site.

Message:

Your first post is your first site.

## Existing Reference Image

Use `assets/moment-reference-mockup-board.png` as a directional reference only.

It captures the broad product idea but should be broken into cleaner, less dense boards for actual demos.


## Mockup Board 4: Default Sharing Rules

Purpose: show that Moment can remember where different types of content should go.

Screens:

1. Sharing Defaults
   - Text Moments → Bluesky
   - Image Moments → Instagram
   - Video Moments → YouTube
   - Gallery Moments → Instagram
   - Mixed media → Ask each time

2. Connected Destinations
   - Your Site connected by default.
   - Bluesky connected.
   - Instagram connected.
   - YouTube connected.
   - Mastodon available.
   - TikTok, Threads, X shown as future or disconnected.

3. Publish Preview
   - Shows a selected image Moment.
   - Your Site enabled.
   - Instagram preselected.
   - Bluesky and Mastodon available but off.

Message:

Moment publishes to your site first and remembers where each kind of Moment should go next.


## Additional Mockup: Home Screen / PWA Launch

Add one small visual sequence showing Moment behaving like a phone app:

1. Phone home screen with a `Moment` icon.
2. Tap icon.
3. Moment opens directly to the `/moment` publishing home.
4. New Moment button is immediately visible.

The goal is to make it clear that Moment can begin as a web/PWA experience without requiring a native app on day one.


## Mockup Board 4: Moment Notifications

Purpose: show that Moment is an all-in publishing and response experience, not just a posting tool.

Screens:

1. Notifications Home
   - New replies/comments grouped by Moment.
   - Source labels like `Reply from Bluesky`, `Comment from Instagram`, and `On-site comment`.
   - No full wp-admin comments UI.

2. Notification Detail
   - Original Moment preview.
   - Thread of on-site comments and imported social responses.
   - Source links back to the original network comment/reply where available.

3. Source Filter
   - All.
   - On-site.
   - Bluesky.
   - Instagram.
   - YouTube.

4. Empty State
   - “Replies and comments on your Moments will appear here.”
   - Clarify that normal post comments are not shown here by default.

The notifications experience should feel native to Moment and focused on Moment-created content. It should not look like WordPress comment moderation.


## Front-End Theme Integration

The site presentation mockups should assume Moment patterns can work inside the latest default WordPress theme.

Show Moment as a set of WordPress-native blocks, patterns, and templates rather than a completely separate front-end system.

Suggested pattern names:

- Moment Timeline
- Moment Image Grid
- Moment Video Shelf
- Moment Audio Feed
- Moment Notes Stream
- Moment Profile Header
- Latest Moments Strip

The goal is social-familiar, WordPress-native presentation for personal sites.
