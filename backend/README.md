# Blubby backend

Laravel 13 API with PostgreSQL and Sanctum token support. Laravel Sail runs the local app and database in Docker; Laravel Boost provides AI agent tooling. Login and token issuance flows are not implemented yet.

## Start locally

Requirements: Docker Engine or Docker Desktop must be running, with PHP 8.3+ and Composer 2 available on the host. Run the following from the `backend/` directory on first setup:

```sh
composer run setup
```

The Laravel app is available at `http://localhost:18473`. PostgreSQL is available from the host at `127.0.0.1:15439` (database `blubby`, user `sail`, password `password`).

Next time, start the app and database with `./vendor/bin/sail up -d`. Stop them with `./vendor/bin/sail down`. The app runs on port `18473`, Vite on `15173`, and PostgreSQL is exposed only on localhost at port `15439`.

Run the backend tests with `./vendor/bin/sail artisan test --compact`.

On a physical phone, `localhost` refers to the phone itself. Expo's tunnel exposes the development server, not this API; the phone also needs a reachable backend address or tunnel.

## Code style

- PHP: `./vendor/bin/sail bin pint`
- JavaScript, CSS, and JSON: `./vendor/bin/sail npm run format` and `./vendor/bin/sail npm run lint`

Biome does not support PHP; Pint handles PHP formatting.

Laravel Boost's MCP server is configured in `.agents/mcp_config.json` and runs through Sail so it can access PostgreSQL. Start Sail with `./vendor/bin/sail up -d` before using the MCP server.

Project conventions start at [backend documentation](docs/README.md). [Agent workflow](docs/agent-workflow.md) explains the adopted architecture, on-demand skills, MCP discovery and safe Boost regeneration. Edit custom sources under `.ai/`, not generated official instructions or skills.

## CI

GitHub Actions runs Composer validation, PHPUnit, Pint, Biome, and the asset build on pull requests and pushes to `main`. Hosting is not selected, so deployment is not configured yet.
