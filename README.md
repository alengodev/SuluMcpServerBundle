# SuluMcpServerBundle

Read-only HTTP API exposing local Sulu template XML files (pages, articles, blocks, snippets, properties) for use by an MCP (Model Context Protocol) server.

## What it does

Provides two authenticated endpoints mounted under the project's admin API prefix (typically `/admin/api`):

| Method | Path | Returns |
|---|---|---|
| `GET` | `/admin/api/mcp/templates/{type}` | JSON list of template names available for a type |
| `GET` | `/admin/api/mcp/templates/{type}/{name}` | Raw XML body of a single template |

Defense-in-depth auth:

1. **Sulu admin session** — the endpoints live under `/admin/api/*`, so the standard admin firewall applies. A request without a valid admin session (cookies) is rejected with `401` before reaching the controller.
2. **Bearer token** — the controller additionally verifies an `Authorization: Bearer <token>` header. This restricts access to a specific MCP-server client even among logged-in admin users.

If the token is not configured (or empty), the API is fully disabled and returns `403`.

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

Set the bearer token in `.env.local`:

```dotenv
MCP_SERVER_TOKEN=<random-secret>
```

Generate one with e.g. `openssl rand -hex 32`.

The MCP client must send **both** the admin session cookies (after authenticating against `/admin/login` with a Sulu admin user) **and** the `Authorization: Bearer <token>` header. The standard Sulu admin firewall (`^/admin`) protects the path; no `PUBLIC_ACCESS` exception is required in `security.yaml`.

## Configuration

The bundle ships with sensible defaults — no configuration file is required.

To override defaults, create `config/packages/alengo_mcp_server.yaml`:

```yaml
alengo_mcp_server:
    # Bearer token. Empty string disables the API. Defaults to the MCP_SERVER_TOKEN env var.
    token: '%env(string:default::MCP_SERVER_TOKEN)%'

    # Template type => directory mapping (paths relative to %kernel.project_dir%).
    template_dirs:
        page:     config/templates/pages
        article:  config/templates/articles
        block:    config/templates/blocks/content
        snippet:  config/templates/snippets
        property: config/templates/properties
```

You can add additional template types by extending `template_dirs` — the controller will resolve any configured type.

## Security model

- **Two-factor auth.** Both a valid Sulu admin session AND the configured bearer token are required.
- **Admin firewall first.** The path lives under `/admin/api/*`. Requests without a session never reach the controller.
- **Bearer token narrows further.** Even logged-in admins cannot call the endpoint without the MCP token — this prevents accidental misuse from other admin tooling.
- **Constant-time comparison** via `hash_equals` to avoid timing attacks.
- **Read-only.** No write endpoints.
- **Token rotation:** change `MCP_SERVER_TOKEN`, clear cache. The next request with the old token returns `403`.

## Requirements

| Package | Version |
|---|---|
| PHP | `^8.2` |
| Symfony | `^7.0` |

## License

MIT — [alengo.dev](https://alengo.dev)