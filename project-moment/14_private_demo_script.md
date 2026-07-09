# Project Moment: Private Demo Script

## Demo Goal

Show that WordPress can feel like a simple phone-based publishing app while remaining the canonical home for the user's content.

The demo should land this idea:

> This is WordPress, but it does not feel like managing WordPress.

## Audience

Use this for private conversations with:

- WordPress product leaders.
- WordPress.com or host teams.
- Plugin and theme builders.
- AI/connector contributors.
- Indie creators and personal-site publishers.
- Potential funders or sponsors.

## Setup

Before the demo:

- Install and activate the `moment` prototype plugin.
- Confirm `/moment` works for a logged-in user.
- Add `/moment` to a phone home screen if testing on a real phone.
- Confirm at least one sample normal post exists outside Moment with a comment, so you can show it does not appear in Moment notifications by default.
- Confirm mocked destinations are available: Bluesky, Instagram, YouTube, Mastodon, Threads, TikTok, X.
- Confirm mocked response import works for a published Moment.

## Demo Flow

### 1. Start with the pain

Say:

> Most everyday photos, videos, and quick thoughts go straight to social networks because that is the fastest path from phone to world. Moment asks: what if WordPress had a personal site publisher mode that was just as simple?

### 2. Show the phone launch

Open the phone home screen.

Tap the `Moment` icon.

If PWA support is not complete, explain:

> This can start as a home-screen shortcut to `/moment`, with a PWA shell as the next step. It does not need a native app on day one to prove the flow.

### 3. Show that this is not mobile wp-admin

Open `/moment`.

Point out:

- No dashboard.
- No plugin management.
- No user management.
- No settings maze.
- Just `New Moment`.

Say:

> Admin mode is for managing a site. Editor mode is for richer long-form content. Moment is personal site publisher mode.

### 4. Create an image Moment

Tap `New Moment`.

Select one image from the camera roll.

Add a caption.

Show the optional AI Assist button.

Say:

> AI can help with captions, alt text, or tags, but publishing never depends on AI.

Accept, edit, or ignore the suggestion.

### 5. Show default routing

Open the publish step.

Show:

- `Your Site` required and enabled.
- Instagram preselected for an image Moment.
- Other destinations available but off or marked as mocked/not connected.

Say:

> Moment publishes to WordPress first. Social networks are destinations, not the source of truth.

Optionally toggle a destination to show override behavior.

### 6. Publish

Tap `Publish`.

Show confirmation.

Open the published post.

Point out:

- It is a standard WordPress post.
- The image is in the Media Library.
- The post has Moment metadata.
- The content remains portable.

### 7. Show front-end views

Open:

- Timeline.
- Images.
- Notes, if sample content exists.
- Videos or audio/podcast views, if sample content exists.

Say:

> Moment is not just capture. It also gives personal sites social-familiar views powered by WordPress posts, blocks, patterns, templates, and ideally the latest default theme.

### 8. Show conversation backflow

Run or trigger mocked response sync.

Open the Moment post.

Show imported responses:

- `Comment from Instagram`
- `Reply from Bluesky`
- `On-site comment`

Open `/moment/notifications`.

Show the same responses in the Moment notifications screen.

Say:

> Ownership is not only where the post begins. It is also whether the conversation can come back to something you own.

### 9. Show focused notifications

Open or reference a normal non-Moment post that has comments.

Return to Moment notifications.

Show that normal post comments are excluded by default.

Say:

> Moment notifications stay scoped to the Moment experience by default, so the user is not dragged back into full site management.

### 10. Close with the thesis

Say:

> WordPress should not ask social-first creators to become site administrators before they can become publishers. Moment is a simpler front door: publish first, own the content, grow into the rest of WordPress later.

## Questions to Ask After the Demo

- Did this feel meaningfully different from mobile wp-admin?
- Would you add this to your phone home screen?
- Which Moment type would you publish first: note, image, video, audio, gallery, or mixed media?
- Did the default routing make sense?
- Did the notifications screen make the experience feel more complete?
- Would you expect this to be a plugin, a host-provided product, a PWA, a native app, or some combination?
- What would need to be true for you to use, fund, build, or bundle this?
