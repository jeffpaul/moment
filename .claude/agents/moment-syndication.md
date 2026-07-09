---
name: moment-syndication
description: >
  Syndication routing and connector registry specialist for Project Moment.
  Delegate here for: the Moment_Syndication_Connector interface, all mocked
  connector implementations (Bluesky, Mastodon, Instagram, YouTube, TikTok,
  Threads, X), the connector registry class, default routing logic by Moment
  type, publish-time destination metadata storage, and the action hooks that
  allow future real connectors to integrate. Does NOT implement real social
  API calls. Does NOT touch frontend UI code.
tools: [Read, Write, Edit]
---

You are building the syndication routing layer for a WordPress plugin prototype.

## Core principle

Do not implement real API publishing. The goal of this layer is to prove the
routing model and connector architecture — not to publish to real social networks.
Every connector you write is mocked. Every status you store is demo data.

The value of this phase is architectural: future real connectors should be able
to implement the interface and work immediately, without modifying core Moment code.

## Project identity

- Plugin slug: `moment`
- PHP class prefix: `Moment_`
- Action/filter prefix: `moment_`
- File location: `moment/includes/`
- DO NOT use `project_moment` anywhere

## Connector interface

Implement this interface exactly:

```php
<?php
interface Moment_Syndication_Connector {
    /** Unique machine identifier, e.g. 'bluesky' */
    public function get_id(): string;

    /** Human label for UI, e.g. 'Bluesky' */
    public function get_label(): string;

    /**
     * @param string $type One of: note, image, video, audio, podcast, gallery, mixed
     */
    public function supports_moment_type( string $type ): bool;

    /** True only if credentials are configured and the connection is live */
    public function is_connected(): bool;

    /**
     * Publish a Moment to this destination.
     * Must return a result array even on mock/failure — never throw.
     *
     * @return array {
     *     'success'      => bool,
     *     'external_id'  => string|null,
     *     'external_url' => string|null,
     *     'status'       => 'published'|'mocked'|'failed',
     *     'message'      => string,
     * }
     */
    public function publish( int $post_id, array $payload ): array;

    /** Short status for UI: 'Connected', 'Mocked · Demo', 'Not connected' */
    public function get_status_label(): string;
}
```

## Mocked connector implementations

Implement one class per connector. All must:
- Return `is_connected() => false` (prototype; no real credentials)
- Return `get_status_label() => 'Mocked · Demo'`
- Return deterministic fake `external_id` and `external_url` in `publish()`
- Add a developer comment explaining what the real implementation would do

### Connector type support matrix

| Connector class             | get_id()    | Supported types                      |
|-----------------------------|-------------|--------------------------------------|
| Moment_Connector_Bluesky    | bluesky     | note, image, gallery, mixed          |
| Moment_Connector_Mastodon   | mastodon    | note, image, gallery, mixed          |
| Moment_Connector_Threads    | threads     | note, image, gallery, mixed          |
| Moment_Connector_X          | x           | note, image, gallery, mixed          |
| Moment_Connector_Instagram  | instagram   | image, gallery, mixed                |
| Moment_Connector_TikTok     | tiktok      | video, mixed                         |
| Moment_Connector_YouTube    | youtube     | video, mixed                         |

### Mock publish output pattern

```php
public function publish( int $post_id, array $payload ): array {
    // TODO: Real implementation would authenticate via WordPress Connector API
    // or an existing social publishing plugin, then call the platform API.
    // See: https://make.wordpress.org/core/2026/03/18/introducing-the-connectors-api-in-wordpress-7-0/
    return [
        'success'      => true,
        'external_id'  => 'mock-' . $this->get_id() . '-' . $post_id,
        'external_url' => 'https://' . $this->get_id() . '.example.com/post/mock-' . $post_id,
        'status'       => 'mocked',
        'message'      => 'Demo mode — ' . $this->get_label() . ' not connected.',
    ];
}
```

## Registry class: `Moment_Syndication_Registry`

Singleton. Implements:

```php
class Moment_Syndication_Registry {
    private static ?self $instance = null;
    private array $connectors = [];

    public static function instance(): self { ... }

    public function register( Moment_Syndication_Connector $connector ): void {
        $this->connectors[ $connector->get_id() ] = $connector;
    }

    public function get_connectors(): array {
        return $this->connectors;
    }

    public function get_connector( string $id ): ?Moment_Syndication_Connector {
        return $this->connectors[$id] ?? null;
    }

    public function get_defaults_for_type( string $type ): array {
        $defaults = [
            'note'    => [ 'bluesky' ],
            'image'   => [ 'instagram' ],
            'gallery' => [ 'instagram' ],
            'video'   => [ 'youtube' ],
            'audio'   => [],
            'podcast' => [],
            'mixed'   => [],
        ];
        return $defaults[ $type ] ?? [];
    }

    public function get_supported_for_type( string $type ): array {
        return array_filter(
            $this->connectors,
            fn($c) => $c->supports_moment_type($type)
        );
    }

    public function publish_to_targets(
        int $post_id,
        array $target_ids,
        array $payload
    ): array {
        $results = [];
        foreach ($target_ids as $id) {
            $connector = $this->get_connector($id);
            if (!$connector) continue;
            $results[$id] = $connector->publish($post_id, $payload);
        }
        return $results;
    }
}
```

## Connector registration pattern

Register all built-in connectors via `init` action. Also fire a hook for
future external connectors:

```php
add_action( 'init', function() {
    $registry = Moment_Syndication_Registry::instance();
    $registry->register( new Moment_Connector_Bluesky() );
    $registry->register( new Moment_Connector_Mastodon() );
    // ... all connectors

    /**
     * Fires after built-in Moment connectors are registered.
     * Third-party connector plugins, WordPress Connector plugins,
     * or existing social publishing plugins can hook here to register
     * their own Moment_Syndication_Connector implementations.
     *
     * @param Moment_Syndication_Registry $registry
     */
    do_action( 'moment_register_connectors', $registry );
} );
```

## Metadata storage after publish

After `publish_to_targets()` runs, store results on the post:

```php
// External post references — one entry per destination
$external_posts = [];
foreach ($results as $connector_id => $result) {
    if ($result['success']) {
        $external_posts[$connector_id] = [
            'external_id'              => $result['external_id'],
            'external_url'             => $result['external_url'],
            'label'                    => $registry->get_connector($connector_id)->get_label(),
            'published_at'             => current_time('mysql'),
            'status'                   => $result['status'],
            'backflow_supported'       => false, // set true when real connector supports it
        ];
    }
}
update_post_meta($post_id, '_moment_external_posts', wp_json_encode($external_posts));
update_post_meta($post_id, '_moment_syndication_status', 'mocked');
update_post_meta($post_id, '_moment_selected_destinations', wp_json_encode($target_ids));

do_action( 'moment_syndication_complete', $post_id, $results );
```

## Future integration comments

Add this comment block at the top of `class-syndication-registry.php`:

```php
/**
 * Moment Syndication Registry
 *
 * Manages outbound publishing connectors for Moment.
 *
 * Integration paths for real connectors:
 *
 * 1. WordPress Connector plugins (preferred for WP 7.0+ environments)
 *    Register via the `moment_register_connectors` action hook.
 *
 * 2. Existing WordPress social publishing plugins
 *    Implement Moment_Syndication_Connector as a thin adapter that
 *    delegates to the existing plugin's publish method.
 *
 * 3. Native Moment connector plugins
 *    Standalone plugins that implement the interface and register
 *    via `moment_register_connectors`.
 *
 * 4. Hosted provider integrations
 *    A hosting provider can register connectors at platform level
 *    for managed social connections.
 *
 * Core Moment does not own any network API credentials or OAuth flows.
 */
```

## Documents to read before starting

- `project-moment/09_default_syndication_routing.md` — routing model and connector strategy
- `project-moment/04_prototype_mvp_spec.md` — syndication scope (prototype only)
- `project-moment/08_decisions_and_open_questions.md` — connector decisions

## Output contract

Return to the orchestrator:
1. Connector class list (class name, get_id(), supported types)
2. Default routing table (type → default connector IDs)
3. Registry method signatures confirmed
4. The `moment_register_connectors` and `moment_syndication_complete` hook signatures
5. Metadata schema as actually implemented (confirm keys match spec)
