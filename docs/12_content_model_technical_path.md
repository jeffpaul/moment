# Content Model Technical Path

## Recommendation

For the prototype, model each Moment as a standard WordPress `post` with attached media.

Do not model a Moment as a Media Library item only.

Do not create a custom post type by default.

## Why the Moment should be a post

A Media Library attachment is useful for storing the uploaded file, attachment metadata, caption, and alt text, but it is too thin to represent the published Moment experience.

A Moment needs:

- A permalink
- A post date
- An author
- A publish status
- Comments
- Feeds
- Theme/template support
- Block markup
- Syndication tracking
- Imported reply/comment backflow
- Notification context

WordPress already provides those behaviors through posts.

## Single-image MVP flow

1. User selects an image from the phone camera roll.
2. Moment uploads the image to the standard WordPress Media Library.
3. Moment creates a standard `post`.
4. Moment attaches the uploaded image to the post.
5. Moment sets the image as the featured image where practical.
6. Moment renders the image and optional caption in `post_content` using normal block markup.
7. Moment marks the post with Moment metadata.
8. Moment applies default syndication routing, such as image Moment to Instagram.
9. Moment keeps the WordPress post as the canonical source of truth.

## Suggested post fields

- `post_type`: `post`
- `post_status`: `publish` or `draft`, based on user capability
- `post_title`: generated from caption or timestamp
- `post_content`: block markup for the selected media and optional text
- `post_excerpt`: optional caption summary
- `featured_image`: primary media attachment where practical
- comments: enabled where site settings allow

## Suggested metadata

- `_moment_is_moment` = `1`
- `_moment_primary_type` = `image`, `video`, `audio`, `podcast`, `note`, `gallery`, or `mixed`
- `_moment_media_ids` = array of media attachment IDs
- `_moment_primary_media_id` = primary media attachment ID
- `_moment_created_from` = `mobile`
- `_moment_syndication_targets` = selected outbound destinations
- `_moment_default_destinations` = defaults applied before publish-time overrides
- `_moment_syndication_status` = `not_attempted`, `mocked`, `queued`, `published`, or `failed`
- `_moment_external_posts` = external post references created through connected networks
- `_moment_comment_backflow_enabled` = boolean
- `_moment_ai_assist_used` = boolean

## Why not a custom post type for the prototype

A custom post type may feel cleaner, but it risks making Moment content feel siloed.

Using `post` preserves:

- Normal WordPress feeds
- Normal comments
- Normal themes and templates
- Existing editorial tools
- Existing plugin compatibility
- Portability if Moment is deactivated

A custom post type can be reconsidered later only if there is a clear technical need that outweighs portability and standard WordPress behavior.

## Instagram example

For an image Moment that defaults to Instagram:

1. Publish the standard WordPress post first.
2. Store selected outbound target metadata on the post.
3. Create or mock a syndication job for Instagram.
4. Store the external Instagram post URL and ID on the Moment post when available.
5. Import or mock Instagram comments back to the Moment post as WordPress comments with source metadata.

The Instagram object is a destination copy.

The WordPress post is canonical.


## Audio / podcast Moment note

Audio-only Moments, including podcast-style episodes, should follow the same canonical model: standard WordPress `post` plus Media Library attachment or embed where practical.

Specific podcast feed behavior, video podcast handling, enclosure metadata, and distribution destinations can be worked out later. For the prototype, it is enough to recognize audio/podcast as a possible Moment type and preserve a path for an audio-focused view.
