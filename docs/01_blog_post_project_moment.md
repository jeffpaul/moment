# Project Moment: A New Front Door for WordPress

Most of the photos and videos of my life never make it to my website.

They end up on Bluesky, Mastodon, Instagram, YouTube, or whatever app happened to be open when I wanted to share something. My website gets the occasional blog post, project update, or long-form reflection. The everyday moments that actually make up my life rarely make it there.

That isn't because I don't want them on my site.

It's because publishing to social networks is effortless, while publishing to WordPress still feels like work.

I think there's an opportunity to change that.

## The publishing gap

If I take a photo of my kids, capture a short video while hiking, or want to share a quick thought from my phone, my instinct is to reach for a social app.

Not because I want those platforms to own my content.

Not because I prefer their business models.

Simply because they're fast.

Open app. Select content. Publish.

WordPress, by comparison, was largely designed around a desktop publishing workflow. It's incredibly powerful, but that power comes with complexity. Even with mobile apps and responsive admin screens, the experience is still centered around managing a website rather than quickly sharing a moment.

Over time, that difference in friction changed where people publish.

The web didn't lose because it lacked ownership, flexibility, or openness.

It lost because it became slower than social media.

## Introducing Project Moment

Project Moment is a concept for a phone-first personal site publisher mode for WordPress.

The goal is simple:

Make publishing to your own website as easy as publishing to a social network.

Open Moment.

Choose a Moment.

Add text, images, video, or any mix of those.

Publish.

That's it.

Under the hood, Moment creates standard WordPress posts and uploads media to your site. Your website remains the source of truth for your content.

From there, content can be syndicated to Bluesky, Mastodon, Instagram, Threads, TikTok, YouTube, or wherever your audience spends time.

That syndication should not have to be the same for every kind of content. A text Moment might default to Bluesky. An image Moment might default to Instagram. A video Moment might default to YouTube. Moment should make those defaults easy to configure while still allowing someone to override them each time they publish.

Publish once.

Own it forever.

Distribute everywhere.

## Personal site publisher mode, not mobile admin

Moment is not another version of wp-admin.

It is not a replacement for Gutenberg.

It is a new mode for a different job.

Admin mode is for managing a site.

Editor mode is for crafting richer long-form content.

Personal site publisher mode is for quickly sharing what is happening now from your own site.

That distinction matters because the people WordPress most needs to reach are not always trying to build a website. Many are trying to publish something from their phone.

They should not need to understand menus, settings, plugins, users, themes, or templates before they can publish their first post.

They should be able to start with the thing they already understand: sharing a moment.

## This isn't another WordPress app

Whenever mobile publishing comes up, the obvious question is:

"Don't the WordPress mobile apps already do this?"

Technically, yes.

But I think they're solving a different problem.

A useful analogy is NBC.com versus Hulu in the early days of streaming.

NBC.com tried to bring everything online: clips, photos, schedules, news, games, promotions, and full episodes.

Hulu focused on one thing: watching full episodes.

That focus is what made Hulu compelling.

The WordPress apps are designed to help run a website. They expose users, plugins, comments, pages, settings, analytics, and publishing.

Moment would focus on a single workflow:

Take something from your phone and publish it to your site.

No plugin management.

No user management.

No dashboard.

No configuration screens before you can publish.

Just a simple, graceful publishing flow.

The constraint is the feature.

## Moments, not photos

The first prototype should start with the simplest possible Moment: one image, an optional caption, and a publish button.

But the vision is not limited to photos.

A Moment can be:

- A short text update
- A single image
- A gallery
- A video
- A mix of text, images, and video

What makes it a Moment isn't the media type.

It's the experience.

Moments are lightweight, feed-friendly pieces of content that are easy to create from a phone and easy to browse later.

## Portable by design

Moment should not require a proprietary content model.

In fact, standard WordPress posts are probably the right foundation.

A Moment can be a normal post with normal media, normal blocks, normal permalinks, and minimal metadata. For a photo Moment, the photo should live in the Media Library, but the Moment itself should be the post around it. That gives it a permalink, comments, feeds, templates, syndication tracking, and a place for replies from other networks to flow back into WordPress.

That matters.

If Moment disappears, the content should still be usable WordPress content.

If someone changes themes, moves hosts, or decides to manage everything through the full WordPress admin later, their content should still be there.

Moment should create a simpler front door into WordPress, not another silo inside it.

## Optional AI Assist

There is also a thoughtful role for AI here, but it should not be the center of the product.

Moment should not become "AI posting for WordPress."

It should remain simple publishing.

AI Assist should be optional and designed to reduce friction only when someone wants help.

That could include:

- Suggested captions
- Alt text generation
- Tag suggestions
- Title suggestions
- Short summaries
- Syndication variants for different platforms
- Turning a Moment into a longer post later

The important part is that AI never gets between the person and the publish button.

If AI Assist is enabled, it should connect through WordPress's provider-agnostic AI infrastructure and Connectors system so site owners can choose their provider. Moment should not require a specific AI vendor, and it should not require AI at all.

Publishing has to work without it.

## Smart defaults for sharing outward

Owning content does not mean disappearing from social networks.

Part of what makes Moment useful is that it can understand where different kinds of Moments should go by default.

For example:

- Text Moments could default to Bluesky or Mastodon.
- Image Moments could default to Instagram.
- Video Moments could default to YouTube or TikTok.
- Mixed-media Moments could use the primary media type or let the publisher choose.

This should feel like a shortcut, not automation run amok.

The publish screen should show the user's site as the required destination and then preselect the social destinations that match that Moment type. The user can change those choices before publishing.

The implementation should also be pragmatic. Moment does not need to build and maintain every social network integration itself. In many cases, it may be better to integrate with existing WordPress plugins that already manage those connections. In other cases, a native Moment connector may make sense.

Moment should own the simple publishing experience and the routing logic. The network-specific details can live behind connectors.


## More than capture

Moment isn't just about publishing.

It's also about presentation.

The experience should include blocks, patterns, templates, and potentially themes that make WordPress feel familiar to people who primarily consume content through social feeds.

Imagine:

- A main timeline view
- An images-only feed
- A videos-only feed
- A notes stream
- Homepage sections dedicated to specific content types

A site could have:

- `/images`
- `/videos`
- `/notes`

All powered by the same underlying posts.

The result is a site that can feel like Instagram, TikTok, Twitter, Threads, or something entirely its own, while still retaining all the flexibility of WordPress.


## Bringing the conversation back home

Publishing out to social networks is only half the loop.

If a Moment is shared to Bluesky, Mastodon, Instagram, YouTube, or another connected network, replies and comments there should be able to flow back into WordPress too.

Not as a replacement for on-site comments, and not as another social inbox to manage, but as a way to keep the conversation attached to the original Moment.

A reply from Bluesky could appear on the Moment post as `Reply from Bluesky`, linked back to the original reply. A comment from Instagram could appear as `Comment from Instagram`, linked back to that comment when the platform allows it. On-site comments would appear alongside those imported responses.

Inside the Moment experience, a simple notifications screen could show the conversations happening around Moments, whether those responses happened on the site itself or on the networks where the Moment was shared.

That matters because ownership is not only about the original post. It is also about keeping the surrounding context connected to the thing you own.

## Why this matters

WordPress has spent years improving how sites are built.

Blocks.

Patterns.

Themes.

Site editing.

All of that work is important and should continue.

But more people need a reason to step into the ecosystem in the first place.

Moment isn't a replacement for the block editor.

It's a new entry point that can make the block editor, themes, patterns, and the rest of WordPress matter to more people over time.

A simpler front door.

One that starts with a photo, video, or note instead of a theme.

A post instead of a setup wizard.

A publishing habit instead of a configuration process.

If WordPress wants to attract creators who have never known a web that wasn't dominated by social platforms, I think we need experiences that meet them where they already are.

On their phones.

In their camera rolls.

Publishing throughout the day.

## Where this could go

I would love to see builders, contributors, hosts, and product teams experiment with experiences like this.

Imagine signing up for a simple publishing plan, scanning a QR code, and sharing your first Moment to your own website within minutes.

No theme selection.

No plugin decisions.

No admin screens.

Just publishing.

As users grow, they can unlock the rest of WordPress when they're ready.

The same content.

The same site.

More capabilities over time.

## Why I'm sharing this

I'm not announcing a product.

I'm describing something I wish existed.

I want my photos, videos, audio, and everyday updates to live on my website first and flow outward to social networks second.

I suspect I'm not alone.

WordPress does not need to become a social network.

It needs to become the best place for social-shaped content to begin.

If this resonates with you, I'd love to hear how you'd use it, what you'd change, and whether you think a simpler publishing-focused front door could help bring more people back to the open web.

Maybe WordPress doesn't need another feature.

Maybe it needs a new habit.
