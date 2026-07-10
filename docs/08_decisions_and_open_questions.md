# Project Moment: Decisions and Open Questions

## Current Decisions

### Name

Working name: Project Moment.


### Code Slug

Public concept name: Project Moment.

Technical slug: `moment`.

All prototype and eventual plugin code should use `moment`, not `project-moment`, for the WordPress.org plugin slug, plugin directory, main plugin file, text domain, REST namespace, block namespace, hooks, function names, and class prefixes.

### Core Positioning

Moment is a phone-first Personal Site Publisher Mode for WordPress.

### Strategic Line

WordPress does not need to become a social network. It needs to become the best place for social-shaped content to begin.

### Primary Audience

Mobile-first creators who primarily publish through social platforms. Personal sites are the primary target, especially sites representing an individual.

### Secondary Audience

Existing WordPress users who want a simpler phone-based publishing workflow alongside full wp-admin and Gutenberg experiences. Brands and organizations can also use Moment when the WordPress site maps to a single brand identity on social platforms.

### MVP

The first MVP supports the simplest Moment:

- One image.
- Optional caption.
- Published as a standard WordPress post.

The broader product supports text, images, video, audio/podcast episodes, galleries, and mixed media.

### Implementation Preference

Plugin-first, with openness to host-integrated onboarding or a future app/PWA shell.

### Content Model

Use standard WordPress posts and media wherever possible.

For the prototype, a Moment should be a standard `post` with attached media. The media attachment stores the file, but the post is the canonical Moment because it provides the permalink, comments, feeds, templates, syndication tracking, and notifications context.

Avoid a custom post type unless future technical needs prove otherwise. A custom post type may be considered later, but the default path should favor portability and standard WordPress behavior.

### Portability

Portable by design.

If Moment disappears, user content should still be usable WordPress content.

### AI

AI Assist is optional.

It should use WordPress 7.0+ AI Client and Connectors where available.

Moment should not require a specific AI provider.

Moment should not require AI to publish.

### Presentation

Moment should include or inspire blocks, patterns, templates, and potentially themes for:

- Timeline
- Images
- Videos
- Audio / podcast
- Notes
- Profile/Home

### Syndication

Syndication is important to the vision but real outbound publishing is not required for the first prototype.

Moment should support default outbound destinations by Moment type.

Example defaults:

- Note → Bluesky
- Image → Instagram
- Video → YouTube
- Audio / podcast → podcast/audio destination where configured
- Gallery → Instagram
- Mixed media → Ask each time or use primary content type

The first prototype should store intended syndication targets, preselect destinations at publish time, and expose hooks for future integrations.

Moment should own routing rules and publish-time UI. Network-specific authentication and publishing should be handled by an adapter layer that can integrate with WordPress Connector plugins, existing WordPress social publishing plugins, or native Moment connector plugins.


### Conversation Backflow

Moment should be able to pull replies/comments back from social networks when supported by connected destinations.

Default behavior:

- Imported social responses attach to the original Moment post.
- Imported responses are labeled with source context, such as `Reply from Bluesky` or `Comment from Instagram`.
- Imported responses link back to the original social response when available.
- On-site comments and imported social responses can appear together on the Moment post.
- Moment notifications show activity for Moment-created content by default.
- Comments on normal non-Moment posts are excluded from Moment notifications by default.

### Candidate Success Metrics

Use candidate starting metrics rather than fixed commitments until the first prototype and demos produce signal.

Starting metrics should cover first publish completion, time to first Moment, home-screen adoption, repeat publishing, routing comprehension, notification comprehension, portability confidence, builder interest, connector interest, and host interest.

### Hosted Moment

A hosted Moment plan is an optional future path if the concept creates strong demand. It is not the required exit strategy and should not constrain the plugin-first prototype.

## Open Questions

### Product

- Should Moment eventually have its own native mobile app, or is mobile web/PWA enough?
- Should onboarding be host-led, WordPress.com-led, plugin-led, or all of the above?
- Should Moment include a hosted demo/sandbox flow for validation?
- What should the first public call to action be: interest, prototype testers, builders, or funders?

### Technical

- How much metadata is needed to identify Moment posts without creating lock-in?
- Should post formats be used in addition to custom metadata?
- Should Moment auto-create `/images`, `/videos`, `/notes`, and `/timeline` pages?
- Should Moment define block patterns only, dynamic blocks, or both?
- How should AI Assist detect and invoke WordPress AI Client/Connectors across versions?

### Design

- Should the Moment UI feel more like a lightweight app or a website overlay?
- What is the minimum profile/home design that makes a new Moment site feel complete?
- How much syndication UI should be visible before real syndication exists?
- For mixed media Moments, should defaults use the primary item, all matching destinations, or ask every time?
- Which social destinations should be first-class defaults in the prototype?
- Should Moment define a common connector interface for other social publishing plugins to implement?
- Which existing social publishing plugins are mature enough to integrate with instead of building native connectors?

### Business / Adoption

- Is WordPress.com the most natural first partner?
- Would a host support a $5/month lightweight Moment tier?
- What proof would a host need before investing?
- What usage metric would show real market pull?

## Recommended Next Move

Build a private prototype focused on:

1. Publishing one image Moment from a phone.
2. Showing it in a timeline and images view.
3. Demonstrating optional AI Assist.
4. Proving the content remains standard WordPress content.

That is enough to test the core idea in private demos.


## Home Screen / PWA

Decision: The prototype README should document how to add `/moment` to iOS and Android home screens so Moment can be tested like a phone app.

Decision: Best-case prototype behavior includes a lightweight PWA shell with a manifest, standalone display mode, icons, and conservative service worker behavior.

Constraint: PWA support should be scoped to the Moment publishing experience and should not cache authenticated REST responses, nonces, wp-admin pages, or private media URLs.

Open question: Is a PWA sufficient for early user testing, or would private demos eventually require a thin native wrapper for iOS and Android?


### New open questions from conversation backflow

- Which social networks support reliable comment/reply import for content published by the authenticated user?
- Should imported social responses be stored as standard WordPress comments, a custom comment type, or a dedicated data store?
- Should Moment support replying back to social comments from inside WordPress, or only show them initially?
- How should imported social responses interact with WordPress comment moderation?
- Should normal post comments be optionally available in Moment notifications through a setting, even if hidden by default?
- How should deleted, hidden, or moderated social comments be reflected back in WordPress?

- How should audio-only podcast Moments differ from video podcast Moments in routing, presentation, metadata, and feeds?
