# Project Moment: One-Page Product Brief

## Summary

Project Moment is a phone-first Personal Site Publisher Mode for WordPress.

It is designed for people who primarily create and share content from mobile devices and want the speed of social apps without giving up ownership of their content.

Moment focuses on a single workflow:

Create a Moment. Publish to your site. Syndicate outward using defaults that can vary by Moment type.

Content is stored as standard WordPress posts and media wherever possible, allowing users to maintain portability while optionally distributing to social networks. Moment can preselect outbound destinations based on the type of Moment being published, while keeping the user's site as the canonical destination.

## Problem

Most creators publish the majority of their everyday content to social platforms because those platforms have optimized publishing for phones.

WordPress is powerful, but its primary experiences are still centered around site administration, full editing, and desktop-oriented workflows.

As a result:

- Photos and videos often live exclusively on social platforms.
- Personal websites receive fewer everyday updates.
- New creators rarely see WordPress as their primary publishing home.
- The path from "I want to share something" to "it's published on my site" contains too much friction.

The open web did not lose publishing volume because it lacked ownership or flexibility.

It lost because it became slower than social media.

## Vision

Make publishing to a WordPress site as easy as publishing to Instagram, TikTok, Twitter/X, Threads, Bluesky, Mastodon, or YouTube.

The user's website becomes the source of truth.

Social networks become distribution channels.

For social-first creators, the onboarding experience should be seamless, graceful, and simple: quickly create or connect a site, choose basic defaults, connect optional destinations, and publish the first Moment without first learning wp-admin, themes, plugins, or site configuration.

## Product Concept

Moment introduces Personal Site Publisher Mode for WordPress.

- Admin Mode: manage the site.
- Editor Mode: create richer long-form content.
- Personal Site Publisher Mode: quickly publish text, images, video, audio, and mixed media from a phone, primarily for personal sites that represent an individual.

Moment coexists with wp-admin, Gutenberg, themes, plugins, and full-site editing. It does not replace them. It is primarily designed for personal sites, while still being useful for brands or organizations that publish to one brand identity across social platforms.

## Target Audience

### Primary

Mobile-first creators who primarily publish through social platforms and may not currently think of themselves as website owners.

### Secondary

Existing WordPress users who want a faster phone-based workflow for:

- Photos
- Videos
- Audio or podcast episodes
- Notes
- Status updates
- Travel logs
- Event updates
- Family content
- Hobby content

## MVP

The first MVP should support the simplest Moment:

Open Moment

→ Select one image from the camera roll

→ Add an optional caption

→ Publish as a standard WordPress post

The broader Moment model should support:

- Text
- Images
- Video
- Audio / podcast episodes
- Galleries
- Mixed media
- Short social-shaped updates

The MVP begins with one image, but the product vision is not limited to photos.

## Product Principles

### Publish First

Publishing should be the first experience, not site setup.

### Mobile First

The experience should be designed around phones, not adapted from desktop workflows.

### Ownership by Default

Content lives on the user's website first.

### Portable by Design

Moment should use standard WordPress posts, media, blocks, templates, and minimal metadata wherever possible.

For the prototype, a Moment should be modeled as a standard `post` with attached media, not as a Media Library item by itself and not as a custom post type by default. The media item stores the file and related attachment metadata; the post gives the Moment its permalink, comments, feeds, templates, syndication tracking, and notification context.

If Moment disappears, the user's content should still be usable WordPress content.

### AI Assist, Never AI First

AI can reduce friction, but it should be optional and clearly user-controlled.

### Progressive Complexity

Users can grow into the broader WordPress experience over time without migration.

## Optional AI Assist

Moment should support optional AI Assist through WordPress 7.0+ AI infrastructure.

Potential features:

- Suggested captions
- Alt text generation
- Tag suggestions
- Title suggestions
- Short summaries
- Syndication variants for different networks
- "Turn this into a longer post" assistance

Implementation principles:

- Disabled by default or clearly opt-in.
- No publishing dependency on AI.
- No vendor lock-in.
- Uses WordPress AI Client and Connectors when available.
- Relies on provider plugins rather than bundling a specific AI service.

## Trust and Privacy Principles

- The WordPress site is canonical.
- Social destinations are optional and user-controlled.
- AI Assist is optional and never required to publish.
- AI provider configuration should live in WordPress AI provider or connector plugins, not in Moment itself.
- Imported social replies/comments should preserve source labels and source links where available.
- Users should be able to disable conversation backflow globally or per network.
- Moment notifications should show Moment-created content by default and exclude normal non-Moment post comments unless explicitly enabled.

## Presentation Layer

Moment should include blocks, patterns, templates, and potentially themes that make WordPress feel natural for social-shaped publishing.

Suggested views:

- Main timeline
- Images feed
- Videos feed
- Notes stream
- Profile page
- Homepage sections for recent Moments

Suggested routes:

- `/timeline`
- `/images`
- `/videos`
- `/notes`

All views should be powered by the same underlying WordPress content, filtered and presented differently.

## Distribution

Future integrations may support syndication to:

- Bluesky
- Mastodon
- Threads
- X/Twitter
- Instagram
- TikTok
- YouTube

The website remains the canonical source.


## Conversation Backflow and Notifications

Moment should not only send content outward. It should also be able to bring replies and comments back into WordPress when connected networks support it.

Core behavior:

- Track the external social posts created from each Moment.
- Import replies/comments from those connected networks where technically and contractually possible.
- Represent imported responses as WordPress-native discussion items associated with the original Moment.
- Prefix imported responses with source context, such as `Reply from Bluesky` or `Comment from Instagram`.
- Link back to the original network response when a stable URL is available.
- Show on-site comments and imported social responses together on the individual Moment post.
- Add a Moment notifications screen for new comments/replies on Moment-created content.

By default, the Moment notifications screen should only show activity for Moments, not comments on normal posts created through wp-admin or Gutenberg. That keeps the phone-first Moment experience focused and avoids pulling users into full-site management unexpectedly.

This should be adapter-based. Moment should own the notification and mapping model, while each social connector or existing WordPress social plugin can own the network-specific API work.

## Ecosystem Opportunity

Moment can create a new entry point into WordPress for people who do not currently think of themselves as website owners.

For users, Moment is a simpler way to publish and own their content.

For builders, Moment creates an open source product surface across mobile UX, blocks, themes, syndication, AI Assist, onboarding, and hosting integrations.

For hosts, Moment could become a lightweight publishing plan that helps people start with WordPress before they need the full complexity of a traditional site.

The strongest path is to create enough excitement around the publishing experience that users, builders, and hosts all recognize the same missing front door.

## Success Metrics

- First publish completion rate
- Time from signup to first published Moment
- Monthly active publishers
- Publishing frequency
- Repeat publishing rate
- Number of Moments syndicated outward
- Upgrade rate to fuller WordPress plans or capabilities
- Content portability retained after plugin/theme changes

## Candidate Success Metrics

These are starting metrics to refine before treating them as final:

- First publish completion rate.
- Time to first Moment.
- Home-screen adoption.
- Repeat publishing within seven days.
- Routing comprehension.
- Notification comprehension.
- Portability confidence.
- AI Assist usage and user acceptance/edit rate.
- Builder interest in mobile UX, blocks/patterns, connectors, AI Assist, and prototype engineering.
- Host interest in a low-cost Moment-style onboarding or publishing tier.

## Hosted Moment Option

A future host-supported Moment plan could be an option if the concept creates enough excitement among users, builders, and hosts.

This should not be treated as the required exit strategy or even an early product dependency. The first job is to prove that people want the experience.

If the experience takes hold, a host could reduce onboarding friction by providing site provisioning, authentication, connector setup, PWA/home-screen prompts, media scaling, and an upgrade path into the full WordPress experience.

## Strategic Question

What if WordPress's next growth opportunity isn't helping people build websites first?

What if it is helping people publish their lives first, then grow into everything else WordPress can do?


## Default Syndication Routing

Moment should allow users or site owners to define default outbound destinations by Moment type.

Example defaults:

| Moment type | Default destination |
| --- | --- |
| Text | Bluesky |
| Image | Instagram |
| Video | YouTube |
| Gallery | Instagram |
| Mixed media | User choice or primary content type |

The publish screen should always show the user's site as the required destination. Social destinations should be preselected based on these defaults and editable before publishing.

This keeps the experience fast without making it opaque.

## Connection Strategy

Moment should not assume it must build and maintain every social network integration directly.

Preferred approach:

1. Moment owns the publishing flow, Moment type detection, default routing rules, and publish-time UI.
2. Social network connections are handled through an adapter layer.
3. Where mature WordPress plugins already support a destination, Moment can integrate with those plugins.
4. Where no good integration exists, native Moment connector plugins can be built.
5. The core Moment experience should still work without any social connection enabled.

This mirrors the broader product philosophy: simple workflow, portable content, extensible infrastructure.
