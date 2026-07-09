# Project Moment: Conversation Backflow and Notifications

## Product Behavior

Moment supports a two-way publishing loop where possible:

1. Publish the Moment to the user's WordPress site.
2. Optionally syndicate the Moment to selected social networks.
3. Track the external social posts created from that Moment.
4. Pull replies/comments back into WordPress when supported by connected networks.
5. Show those responses alongside on-site comments on the original Moment.
6. Surface those responses inside a Moment notifications screen.

The user's WordPress site remains the canonical home for the Moment and the best place to see the full context around it.

## Product Goal

Moment should feel like an all-in phone publishing experience, not just a post composer.

A creator should be able to publish from Moment, share outward, and then return to Moment to see the responses that came back from their site and connected social networks.

## Default Notification Scope

By default, Moment notifications should only show activity for Moment-created content.

Include:

- On-site comments on Moment posts.
- Imported replies/comments from social networks for Moment posts.
- Syndication status updates for Moment posts, if useful.

Exclude by default:

- Comments on normal posts created through wp-admin, Gutenberg, XML-RPC, imports, or other non-Moment workflows.

Reason: Moment is meant to be a focused phone-first publishing experience. Showing normal post comments by default could pull users into broader site-management behavior and make the experience feel more like wp-admin.

A future setting could allow advanced users to include comments from all posts, but the default should stay Moment-focused.

## Imported Response Display

Imported social replies/comments should be clearly labeled.

Examples:

- `Reply from Bluesky`
- `Comment from Instagram`
- `Comment from YouTube`
- `Reply from Mastodon`
- `On-site comment`

Each imported response should include, when available:

- Source network.
- External author display name or handle.
- Response text.
- Source timestamp.
- Link back to the original social comment/reply.
- Import timestamp.

## WordPress Representation

Preferred implementation direction:

- Store imported social responses as WordPress comments attached to the original Moment post.
- Use comment meta to store source network, external ID, external URL, external author, and import metadata.
- Use a source label when rendering the comment in Moment views.

Possible metadata:

```text
_moment_comment_source
_moment_comment_source_label
_moment_comment_external_id
_moment_comment_external_url
_moment_comment_external_author
_moment_comment_external_created_at
_moment_comment_imported_at
```

This keeps the conversation attached to standard WordPress content while preserving source context.

## Connector Strategy

Moment should not hard-code all social network comment/reply APIs.

Moment should own:

- The source-of-truth post mapping.
- The external post reference model.
- The imported response model.
- The notifications UI.
- The default rule that Moment notifications focus on Moment-created content.

Connectors should own:

- Authentication.
- Platform-specific API calls.
- Reply/comment fetching.
- Deduplication based on external IDs.
- Network-specific capability checks.

Connector options:

- WordPress Connector plugins that connect directly to social platforms.
- Existing WordPress social publishing plugins.
- Native Moment connector plugins.
- Hosted integrations provided by a host.
- Direct platform APIs where appropriate.

## Prototype Behavior

The first prototype should mock this behavior.

Required demo behavior:

1. Publish a Moment.
2. Mock syndication to one or more destinations.
3. Store external post references as post meta.
4. Trigger a mocked sync action.
5. Create example imported comments/replies.
6. Display imported responses on the Moment post with source labels.
7. Show imported responses and on-site comments in `/moment/notifications`.
8. Confirm normal non-Moment post comments do not appear in Moment notifications by default.

Out of scope for the first prototype:

- Real social network API polling.
- Webhooks.
- Replying back to social networks from Moment.
- Cross-network moderation workflows.
- Production-grade deduplication.
- Handling deleted, hidden, or edited social responses.

## Why This Matters

Ownership is not only about where the original post lives.

It is also about whether the conversation around that post can remain connected to something the user owns.

Moment should make WordPress the place where social-shaped content begins and where the conversation can come back together.
