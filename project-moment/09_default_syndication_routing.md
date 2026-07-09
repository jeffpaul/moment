# Project Moment: Default Syndication Routing

## Product Behavior

Moment supports default outbound destinations by Moment type.

The user's WordPress site remains the required canonical destination for every Moment. Social networks are optional distribution targets.

Example defaults:

| Moment type | Example default destination |
| --- | --- |
| Note / text | Bluesky |
| Image | Instagram |
| Video | YouTube |
| Audio / podcast | Podcast/audio destination where configured |
| Gallery | Instagram |
| Mixed media | Ask each time or use primary type |

The goal is to keep publishing fast while respecting that different social networks are best suited to different formats.

## Publish Flow

When a user creates a Moment, Moment should detect the content type and preselect outbound destinations on the publish screen.

Examples:

- A note/text-only Moment shows `Your Site` plus Bluesky preselected.
- An image Moment shows `Your Site` plus Instagram preselected.
- A video Moment shows `Your Site` plus YouTube preselected.
- An audio/podcast Moment shows `Your Site` plus the configured podcast/audio destination when available.

Users can override those selections before publishing.

The first prototype can mock all outbound destinations. The important part is demonstrating that the product understands routing by content type.

## Settings

Moment should include a simple settings model for default destinations.

Potential settings:

```json
{
  "note": ["bluesky"],
  "image": ["instagram"],
  "video": ["youtube"],
  "audio": [],
  "podcast": [],
  "gallery": ["instagram"],
  "mixed": []
}
```

A host, site owner, or future onboarding flow could provide these defaults.

## Connector Strategy

Moment should not assume it must build every social network integration itself.

Recommended approach:

1. Moment owns the publishing workflow, Moment type detection, default routing rules, and publish-time UI.
2. Social network connections live behind an adapter layer.
3. WordPress Connector plugins that connect directly to social platforms should be a preferred integration path where available.
4. Existing WordPress social publishing plugins can provide adapters where appropriate.
5. Native Moment connector plugins can fill gaps where no good integration exists.
6. The same connector layer can help return comment/reply links back into WordPress where supported.
7. The core Moment plugin remains useful even with zero social connectors enabled.

This keeps Moment focused on the user experience while allowing the ecosystem to solve network-specific integrations.

## Adapter Interface

A future connector interface could include:

```php
interface Moment_Syndication_Connector {
    public function get_id(): string;
    public function get_label(): string;
    public function supports_moment_type( string $type ): bool;
    public function is_connected(): bool;
    public function publish( int $post_id, array $payload ): array;
}
```

Adapters could be provided by:

- WordPress Connector plugins for direct social platform connections.
- Existing WordPress social publishing plugins.
- Native Moment connector plugins.
- Hosting providers.
- Custom site integrations.

## Prototype Requirements

For the first prototype:

- Add mocked destinations for Bluesky, Mastodon, Instagram, YouTube, TikTok, Threads, X, and a generic podcast/audio destination.
- Add default routing settings by Moment type.
- Detect Moment type during composition.
- Preselect destinations during publishing.
- Allow publish-time overrides.
- Store selected destinations in post meta.
- Show mocked syndication status on the created Moment.
- Do not require real outbound API publishing.

## Open Questions

- Should mixed media default to the first selected media type, all matching destinations, or ask every time?
- Should default destinations be configured per site, per user, or both?
- Should onboarding ask users to choose their social defaults immediately or defer until after first publish?
- Should Moment integrate first with existing social publishing plugins, native connector plugins, or a hosted connector service?
- Which networks should be considered first-class in the initial demo?


## Relationship to Conversation Backflow

Default syndication routing should store enough information to support comment/reply backflow later.

When Moment mocks or performs outbound syndication, it should store the external post reference for each destination:

- Destination ID.
- External post ID.
- External post URL.
- Publish timestamp.
- Connector used.
- Whether reply/comment backflow is supported for that destination.

This gives Moment the mapping needed to later ask a connector for responses related to that external post and attach those responses back to the original WordPress Moment.

- How should audio-only podcast Moments differ from video podcast Moments?
