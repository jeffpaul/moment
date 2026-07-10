# Project Moment: Private Demo Storyline

Use this narrative for private conversations with builders, hosts, product leaders, contributors, or potential funders.

## Opening

Most of my everyday content never makes it to my own site.

Photos, videos, quick thoughts, family moments, travel moments, and little updates all go to social apps first because they are fast.

That is not because I trust those platforms more.

It is because they are easier to publish to from my phone.

Project Moment is a simple idea: make WordPress as fast to publish to as social apps, without giving up content ownership.

## The core demo

Show the flow:

1. Open Moment on a phone.
2. Select content from the camera roll.
3. Add a caption or short note.
4. Optionally accept AI suggestions for caption, alt text, or tags.
5. Publish to the WordPress site.
6. See the Moment live in a timeline.
7. Show the same content in `/images`, `/videos`, or `/notes` views.
8. Show default social destinations based on Moment type.
9. Show a mocked reply/comment from a connected social network flowing back into the Moment.
10. Show the Moment notifications screen with on-site comments and imported social replies.

## The key point

Moment is not mobile wp-admin.

It is Personal Site Publisher Mode for WordPress.

Admin mode manages the site.

Editor mode creates rich long-form content.

Personal Site Publisher Mode lets someone quickly share what is happening now.

## Why WordPress

WordPress already has:

- Posts
- Media
- Permalinks
- Themes
- Blocks
- APIs
- Feeds
- Hosts
- Ownership
- Portability

Moment is a new front door into those strengths.

## Why now

People already create constantly from their phones.

AI can now reduce publishing friction through captions, alt text, tags, and syndication variants.

WordPress 7.0+ AI infrastructure can make those AI features provider-agnostic and optional.

The open web needs a simpler publishing path for people who do not want to start by building a full website.

## The strategic line

WordPress does not need to become a social network.

It needs to become the best place for social-shaped content to begin.

## Desired reaction

The desired reaction is not just:

"That's a neat plugin."

It is:

"This should be a new front door for WordPress."


## Demo Beat: Default Outbound Routing

After showing the first Moment published to the site, show the publish screen again with destination defaults.

The key line:

> Moment always publishes to your site first, but it can remember where different kinds of Moments should go next.

Example:

- A note defaults to Bluesky.
- A photo defaults to Instagram.
- A video defaults to YouTube.

The user can override the choices before publishing.

This makes syndication feel like part of the publishing habit rather than another configuration chore.

Positioning:

Moment should own the routing experience, not every social API. The prototype can show these as connected destinations or mocked connectors while leaving room to integrate with existing WordPress social publishing plugins later.


## Conversation backflow demo beat

Show the user that Moment is not just a place to publish from. It is also the place to keep track of responses to what they published.

Example:

- The user publishes an image Moment.
- Moment routes it to Instagram by default.
- A mocked Instagram comment appears later as `Comment from Instagram` with a source link.
- The same Moment also receives an on-site WordPress comment.
- Both appear in the Moment notifications screen.

Default rule: Moment notifications should focus on Moment-created content. Comments on normal posts should not appear there by default.
