# Project Moment: Candidate Success Metrics and End-to-End Tests

## Purpose

This artifact captures recommended starting success metrics and test-driven end-to-end scenarios for the first private prototype.

The metrics are candidates. They should be refined after the first prototype exists and after a few private demos make the highest-signal behaviors clearer.

## Candidate Success Metrics

### Activation and Onboarding

- First publish completion rate: percentage of new testers who publish their first Moment during the initial session.
- Time to first Moment: time from opening `/moment` to a published Moment.
- Home-screen adoption: percentage of testers who add `/moment` to their phone home screen.
- Onboarding comprehension: percentage of testers who understand that Moment creates a WordPress site/post first and optionally distributes outward.

### Publishing Behavior

- Repeat publishing: percentage of testers who publish three or more Moments in seven days.
- Moment type coverage: number of testers who create more than one Moment type, such as note, image, video, gallery, audio, or mixed media.
- Publish override usage: percentage of Moments where users change the default social destination before publishing.
- AI Assist usage: percentage of Moments where users request, accept, edit, or ignore AI suggestions.

### Ownership and Portability

- Canonical-source comprehension: percentage of testers who understand that the WordPress post is canonical and social posts are destination copies.
- Portability confidence: percentage of testers who understand that disabling Moment does not remove their posts or media.
- Deactivation safety: prototype test confirming Moments remain standard WordPress posts/media after plugin deactivation.

### Syndication and Notifications

- Routing comprehension: percentage of testers who understand type-based defaults such as note to Bluesky, image to Instagram, video to YouTube, and audio/podcast to an audio destination.
- Notification comprehension: percentage of testers who understand that on-site comments and imported social replies/comments appear together inside Moment notifications.
- Focused notification behavior: test confirming comments on normal non-Moment posts are excluded from Moment notifications by default.

### Ecosystem Signal

- Builder interest: number of people who want to contribute to mobile UX, blocks/patterns, connectors, AI Assist, testing, or prototype engineering.
- Connector interest: number of existing WordPress plugin maintainers interested in exposing social publishing or comment/reply backflow through a Moment-compatible connector.
- Host interest: number of host/platform conversations that result in interest in a low-cost Moment-style onboarding or publishing tier.
- User pull: number of people who ask to try the prototype after seeing the demo.

## Test-Driven Prototype Approach

The first prototype should be built against a small set of end-to-end tests. These tests should guide implementation before polishing UI details.

The test names below are intentionally product-oriented so a coding agent does not drift into building mobile wp-admin.

## End-to-End Test Scenarios

### 1. A user can launch Moment like a phone app

Given a logged-in user is on a mobile device or phone-sized viewport
When they open `/moment`
Then they see a focused Moment home screen with a clear `New Moment` action
And they do not see wp-admin menus, plugin settings, user management, or site administration UI

### 2. A user can publish an image Moment

Given a logged-in user can publish posts
When they open `/moment`
And select one image
And add an optional caption
And publish
Then a standard WordPress `post` is created
And the image is uploaded to the Media Library
And the image is attached to the post
And the image is set as featured image where practical
And the post is marked with `_moment_is_moment = 1`
And the Moment appears in the timeline and images views

### 3. A user can publish a note Moment

Given a logged-in user can publish posts
When they create a text-only Moment
Then a standard WordPress `post` is created
And the post is marked as a Moment
And the Moment appears in the timeline and notes views

### 4. Moment type controls default destinations

Given default destinations are configured
When a user creates a note Moment
Then Bluesky is preselected as an optional destination
When a user creates an image Moment
Then Instagram is preselected as an optional destination
When a user creates a video Moment
Then YouTube is preselected as an optional destination
When a user creates an audio/podcast Moment
Then the podcast/audio destination is preselected where configured
And in all cases `Your Site` remains required and enabled

### 5. A user can override destination defaults

Given a destination is preselected based on Moment type
When the user toggles that destination off before publishing
Then the created post stores the user's selected destinations
And the default remains available for future Moments

### 6. AI Assist is optional

Given no AI provider is configured
When a user opens AI Assist
Then Moment returns deterministic mock suggestions or a graceful unavailable state
And the user can still publish without AI

Given an AI provider connector is available
When a user opens AI Assist
Then Moment routes the request through the adapter layer
And publishing remains user-controlled

### 7. Conversation backflow appears in Moment notifications

Given a Moment has mocked external post references
When the user runs the mocked sync action
Then imported responses are attached to the original Moment
And they include source labels such as `Comment from Instagram` or `Reply from Bluesky`
And they appear on the individual Moment post
And they appear in `/moment/notifications`

### 8. Normal post comments are excluded from Moment notifications by default

Given there is a normal WordPress post not created through Moment
And that post has comments
When the user opens `/moment/notifications`
Then those comments do not appear by default

### 9. Moment content remains portable

Given a user has published an image Moment
When the Moment plugin is deactivated
Then the post remains available as a standard WordPress post
And the uploaded media remains available in the Media Library
And the post content remains readable using normal WordPress rendering

### 10. The prototype communicates the product thesis in under 60 seconds

Given a private demo viewer sees the prototype
When they watch the image Moment publishing flow
Then they should understand:

1. This is WordPress.
2. It does not feel like managing WordPress.
3. The user's site is the source of truth.
4. Social networks are destinations.
5. Comments and replies can come back into Moment.
6. Content remains portable WordPress content.

## Suggested Test Tooling

Use whatever is fastest for the coding environment, but prefer tools that can be automated locally:

- Playwright for browser-level end-to-end tests.
- `@wordpress/env` or a local WordPress environment for repeatable setup.
- WordPress unit/integration tests for post creation, metadata, permissions, and REST endpoints where practical.
- Manual mobile-device testing for home-screen/PWA behavior, since browser support differs across platforms.

## Prototype Test Priority

Build in this order:

1. Image Moment creation.
2. Standard post/media storage.
3. Timeline/images display.
4. Destination default preselection.
5. Mocked syndication status.
6. Mocked comment/reply backflow.
7. Moment notifications.
8. Home-screen/PWA behavior.
9. AI Assist fallback.
10. Additional Moment types.
