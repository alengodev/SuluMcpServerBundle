# SuluMcpServerBundle

Read-only HTTP API exposing local Sulu template XML files (pages, articles, blocks, snippets) for use by an MCP (Model Context Protocol) server.

> ✅ Built for **Sulu 2.6** (Symfony 6.x / 7.x, PHP ≥ 8.2).

## What it does

Provides two authenticated endpoints mounted under the project's admin API prefix (typically `/admin/api`):

| Method | Path | Returns |
|---|---|---|
| `GET` | `/admin/api/mcp/templates/{type}` | JSON list of template names available for a type |
| `GET` | `/admin/api/mcp/templates/{type}/{name}` | Raw XML body of a single template |

Auth is handled entirely by the Sulu admin firewall: the endpoints live under `/admin/api/*`, so the standard admin firewall (`^/admin`) applies. A request without a valid admin session (cookies) is rejected with `401` before reaching the controller.

## Installation

```bash
composer require alengo/sulu-mcp-server-bundle
```

Register the bundle in `config/bundles.php`:

```php
Alengo\SuluMcpServerBundle\McpServerBundle::class => ['all' => true],
```

Import the routing in `config/routes/alengo_mcp_server.yaml`:

```yaml
alengo_mcp_server:
    resource: "@McpServerBundle/Resources/config/routing_admin_api.yaml"
    prefix: /admin/api
```

The MCP client must send the admin session cookies obtained after authenticating against `/admin/login` with a Sulu admin user. The standard Sulu admin firewall (`^/admin`) protects the path; no `PUBLIC_ACCESS` exception is required in `security.yaml`.

## Configuration

The bundle ships with sensible defaults — no configuration file is required.

To override defaults, create `config/packages/alengo_mcp_server.yaml`:

```yaml
alengo_mcp_server:
    # Template type => directory mapping (paths relative to %kernel.project_dir%).
    template_dirs:
        page:    config/templates/pages
        article: config/templates/articles
        block:   config/templates/includes/blocks
        snippet: config/templates/snippets
```

You can add additional template types by extending `template_dirs` — the controller will resolve any configured type.

## Security model

- **Admin firewall.** The path lives under `/admin/api/*`, so the standard Sulu admin firewall (`^/admin`) applies. Requests without a valid admin session are rejected with `401` and never reach the controller.
- **Read-only.** No write endpoints.

## Compatibility

| Sulu | Symfony | PHP |
|---|---|---|
| `2.6` | `6.x` · `7.x` | `≥ 8.2` |

Requires `sulu/sulu: ^2.6` and `symfony/*: ^6.0 || ^7.0`.

## License

MIT — [alengo.dev](https://alengo.dev)