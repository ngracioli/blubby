# Agent workflow and Laravel Boost

## Context ownership and routing

Repository `AGENTS.md` owns general project rules. Backend `AGENTS.md` contains Boost-generated Laravel instructions plus the project routing guideline. `CLAUDE.md` remains the bare `@AGENTS.md` import. Stable architecture decisions live in documentation, not inferred memory or repeated per-agent copies. [The docs map](README.md) identifies each topic's owner.

Identify the owning business domain first. Start with architecture, naming and relevant pattern guidance for behavior/refactors, then implementation conventions. New extracted business code goes under `app/Domain/<Domain>`; HTTP and framework entry points retain the agreed conventional roots. Review dependency direction and rule ownership even when Eloquent Models stay global. Load API/security when changing HTTP/access, database when touching persistence/concurrency, domain/MVP for financial rules, testing for proof, and operations for workers/deployment. Do not import every specialized document into always-loaded instructions. Research/comparison in `architecture/decision.md` is background for reassessment, not mandatory context for every CRUD task.

| Task | Project skill | Detail owners |
| --- | --- | --- |
| Implement feature, fix behavior, refactor structure | `backend-development` | Domain ownership/dependencies, architecture, affected contracts, testing |
| Review backend diff/branch, audit architecture or assess instruction compliance | `backend-review` | Domain placement/coupling, connected implementation and relevant contract owners |
| Refresh/debug agent instructions | `boost-maintenance` | This guide, installed Boost source/config |

The project guideline explicitly specializes the official testing skill's generic mirrored-class file layout and per-class grouping: HTTP, DB integration and pure tests follow the project's behavior-based paths in `docs/testing/README.md`. Official copies remain intact.

Official `laravel-best-practices`, `testing-best-practices`, `infer-conventions` and `tailwindcss-development` remain installed. Activate only relevant skills. `infer-conventions` discovers actual repeated code conventions; scaffold-only code cannot establish rich domain patterns, and inference is not a replacement for an explicit architecture decision. Do not run inference now to invent rules from examples.

### Conflict policy and observable workflow

Environment instruction priority always applies; no project Markdown file changes that priority. Within compatible project instructions, repository rules apply throughout the checkout and backend rules specialize backend work. The adopted architecture and topic owners specialize generic package guidance. Skills provide task procedures, not a competing architecture. Generated files are outputs, not an additional source of decisions. Existing scaffold and historical alternatives do not override the adopted decision. If two current project owners genuinely contradict each other, identify the conflicting passages and correct the canonical source within the authorized scope; ask when a product/architecture decision remains unresolved. Never edit a vendor skill to conceal a conflict.

The seven-step `backend-development` workflow owns the before/during/after implementation procedure; do not duplicate it per client. Before generation, name the owner, relevant documents, existing participants, smallest adequate structure and affected integrity/security contracts. During implementation, follow that placement and use test-first proof. Before completion, review dependencies and introduced violations, run relevant tests/formatting and correct introduced defects. Report the actual domain/placement decision, abstraction justification, executed checks and blockers. For reviews/audits, use `backend-review` and distinguish static inspection, planning exercises and observed client execution. Explicitly reading a canonical skill is a fallback procedure, not proof the client's activation mechanism ran.

## Native Boost 2.10.3 integration

The installed package and CLI were inspected, not just current web docs:

- `src/Install/GuidelineComposer.php` discovers Markdown/Blade under `.ai/guidelines`; a distinct `project-architecture.md` complements native rules without replacing official names. Custom content is composed into the generated block. Do not edit that block by hand.
- `src/Install/SkillComposer.php` discovers `.ai/skills/*/SKILL.md` (or Blade), parses YAML `name` and `description`, and merges user skills. Project names are distinct from official skills. A source folder alone is not an agent installation.
- `src/Install/SkillWriter.php` links plain custom skills to their canonical source when symlinks work, otherwise copies them; Blade content is rendered/copied. Official skills are copied and generated directories may be replaced. Do not customize official destination copies or assume an untracked edit there survives updates.
- `src/Install/GuidelineWriter.php` replaces the first `<laravel-boost-guidelines>` block and preserves surrounding user content. Preserve content outside the block; ensure there is only one block. Existing customized native blocks must be migrated into canonical sources before regeneration.
- `src/Install/Agents/ClaudeCode.php` would target an existing `CLAUDE.md` by default. The small `config/boost.php` override sends Claude guidelines to `AGENTS.md`, retaining its bare import and avoiding duplicate native blocks. Other package defaults are inherited; no MCP or runtime application setting is changed.
- `src/Console/UpdateCommand.php` delegates guideline/skill regeneration to the installer, using configured agents. It has `--discover` (default), `--no-discover` and `--ignore-skills`, plus standard console options. New third-party package selection prompts only interactively; `--no-interaction` does not mean “select every new package”. `boost:update` does not upgrade Composer packages. No unsupported `--force` or `--dry-run` is assumed.

Official references: [Boost custom guidelines/skills](https://laravel.com/docs/13.x/boost), [MCP](https://laravel.com/docs/13.x/mcp). Web docs can evolve faster than the installed patch; verify source/help when behavior matters.

## Source and generated tree

```text
AGENTS.md                                      repository rules + backend routing
backend/
├── .ai/
│   ├── guidelines/project-architecture.md      canonical concise always-loaded routing
│   └── skills/
│       ├── backend-development/SKILL.md        canonical implementation/refactor workflow
│       ├── backend-review/SKILL.md             canonical focused review workflow
│       └── boost-maintenance/SKILL.md          canonical regeneration workflow
├── config/boost.php                            Claude guideline destination override
├── boost.json                                  Boost-owned agent/features/skill selection
├── AGENTS.md                                   generated native + project guideline
├── CLAUDE.md                                   @AGENTS.md
├── .agents/skills/<project-skill>               generated link/copy (four agents)
├── .claude/skills/<project-skill>               generated link/copy (Claude)
├── .factory/skills/<project-skill>              generated link/copy (Factory)
├── .grok/skills/<project-skill>                 generated link/copy (Grok)
├── .mcp.json, .agents/mcp_config.json           existing Sail MCP registrations
├── .factory/mcp.json, .codex/config.toml        existing client registrations
└── docs/
    ├── README.md, agent-workflow.md            topic map and this integration procedure
    ├── architecture/
    │   ├── overview.md, naming.md              responsibilities, paths and names
    │   ├── design-patterns.md, conventions.md  conditional design / PHP-Laravel rules
    │   └── decision.md                        audit, alternatives, sources and example
    └── {api,database,security,testing,operations,domain}/README.md
```

The `.agents/skills` destination is shared by Amp, Antigravity, Codex and OpenCode (four configured agents); Claude, Factory and Grok have distinct destinations. Root `.agents/skills` continues to contain repository-wide skills; this task does not duplicate backend sources there.

For a session launched inside `backend/`, Boost targets each configured client's local skill directory; actual catalog discovery still requires observation in that client/version. For a session launched at repository root, do not assume recursive skill discovery: root `AGENTS.md` explicitly routes to backend instructions and the canonical skill path. Read that skill directly if it is absent from the client's skill catalog. Restart/reload the client if its catalog was cached before generation. `SKILL.md` name/description frontmatter is the discovery metadata parsed by the installed Boost version; client activation remains separate from Boost parsing. CLI generation and readable links are verified separately from real client activation; generating files cannot prove every editor loaded them.

### Repeatable compliance checks

Run `./vendor/bin/sail artisan test --compact tests/Feature/Architecture/AgentInstructionsTest.php`. This existing PHPUnit pipeline checks actual Boost skill parsing/selection, content parity at each configured agent destination, guideline composition versus the generated entry point, the Claude import, required document references, backend-local Markdown file links, PSR-4 application namespaces and forbidden global business directories. It runs in the normal suite and therefore in existing CI without workflow changes. It reads files only and does not regenerate them or access the database.

The backend-only Sail mount cannot inspect repository-level instruction files or links outside `backend/`; check those on the host as well. The link check covers inline file links, not external availability, reference-style syntax or section anchors. Namespace checks do not parse dynamic dependencies or prove Eloquent model ownership. No fixed dependency graph exists yet for business domains; add focused import/cycle checks only when actual ownership contracts exist. Action necessity, invariant reuse, transaction completeness, hidden Model dependencies and concurrency still require contextual review and behavioral tests. A passing instruction test does not prove an agent obeyed the instructions. See [the dated audit](agent-compliance-audit.md) for scenarios, client evidence and remaining limits.

## MCP and diagnosis

Existing registrations run `vendor/bin/sail artisan boost:mcp`; start Sail from `backend/` and configure the client's working directory there. A root-launched process using this relative command without the correct directory will fail. Do not duplicate a server registration or expose Boost as an unauthenticated public service to work around that.

Inspect exposed tools rather than copying a list from older docs. The live installed server exposes `application-info`, `browser-logs`, `database-connections`, `database-query`, `database-schema`, `get-absolute-url`, `last-error`, `read-log-entries`, `record-rule` and `search-docs`. `Tinker.php` exists in vendor source but is not in the live default tool list; use the supported Sail Artisan Tinker fallback only for appropriate diagnostics, not data mutations replacing tests.

Use application info for versions, schema before relevant DB work, read-only database query for inspection, and package-filtered search for version-specific APIs. Broad searches can return irrelevant packages; inspect result package/version and refine. Boost 2.10.3's MCP `serverInfo.version` reports `0.0.1`, so it is not the Composer package version. Use lockfiles/package metadata for that.

The `record-rule` facility and `.ai/rules` index are native options. No new rules tree or `@scoped` extraction is needed now: the concise guideline plus on-demand docs avoid another canonical copy. Scoped framework extraction is opt-in (`boost.rules.scoped_guidelines`, false by default). If the rules tree exists in future, obey Boost's index lookup, keep enduring project rules in canonical instruction files and link specialized docs rather than record their content twice. Do not manually create unindexed rule files.

## Safe regeneration

Run from `backend/` after reading the `boost-maintenance` skill:

```sh
git status --short
./vendor/bin/sail artisan boost:update --help
./vendor/bin/sail artisan boost:update --no-discover --no-interaction
git diff --stat
git diff -- AGENTS.md CLAUDE.md boost.json config/boost.php .ai .agents .claude .factory .grok
git diff --check
```

Before the update, inspect custom source edits and all installed guideline/skill destinations, including untracked files/symlinks. Save a scoped backup outside the tree if existing uncommitted customizations could be overwritten. Do not use a blanket restore/stash that loses unrelated work. Potentially affected outputs are backend `AGENTS.md`, configured agent skill directories, `boost.json` and, if enabled, scoped framework rules/index. MCP configuration is not selected for rewriting by this update path. `CLAUDE.md` remains untouched through the override.

Afterward compare native sections, project guideline, official skills, custom source/link targets and all preexisting custom content. Check `boost.json` records the intended three custom skills plus the existing four. Run the same update again and compare content/link hashes for idempotency. Inspect file contents even when the command prints success: the update command delegates silently and its success line alone is not proof of every output.

`--ignore-skills` is for an intentional guideline-only refresh, never a skill install. To include a new package's guidance, inspect its source and run interactive discovery deliberately instead of changing dependencies or assuming noninteractive discovery selects it. Do not add a Composer update hook just for this task; regeneration stays explicit and reviewable.

## Validation record

Initial integration, 2026-10-28 (historical results; not rerun evidence for the later domain-oriented update):

| Executed check | Result |
| --- | --- |
| `./vendor/bin/sail artisan boost:update --help` | Installed flags confirmed, including `--no-discover` and `--ignore-skills` |
| `./vendor/bin/sail artisan boost:mcp` with JSON-RPC initialize, tools/list, application-info and search-docs | Server responds; 10 tools exposed; PHP 8.5 / Laravel 13.39.0 confirmed; transaction/after-commit search returns framework 13.x sources |
| `./vendor/bin/sail artisan boost:update --no-discover --no-interaction` twice | Both successful; content and link manifest identical after the second run |
| Scoped pre/post SHA-256 and symlink checks | Existing official skill files and MCP configurations unchanged; Claude import intact; one generated guideline block; 12 custom links resolve to three canonical skills |
| `quick_validate.py` from the installed skill-creator, on each of the three canonical skills | All three valid |
| Local Markdown link resolution over backend docs, custom sources, repository instructions and backend README | No broken file links; external link availability and section anchors are not proven by this check |
| `./vendor/bin/sail bin pint --dirty --format agent` | Failed: Git metadata is outside the backend-only container mount; focused fallback used |
| `./vendor/bin/sail bin pint config/boost.php --format agent` | Passed; only PHP change is the guideline destination setting |
| `./vendor/bin/sail exec -T -e PAO_FORCE=1 laravel.test php artisan test --compact` | Pao reports passed: 2 tests, 2 assertions; this is the existing scaffold suite |
| `git diff --check` and independent cavecrew review | Whitespace checked; inconsistent Factory/Template example naming found and corrected |

No business behavior or migrations changed. Biome/build, new authentication/financial/concurrency tests, destructive migration checks and editor-by-editor activation were not run. No Composer update or dependency installation occurred. CLI generation, valid frontmatter and readable links prove installation outputs, not that every editor has refreshed its cached catalog. Sail initially lacked Docker socket access in the sandbox; the same commands succeeded with approved local Docker access.

Future maintenance must report its own commands/results, not reuse an old green result. Source links/frontmatter, installed destinations, preservation and repeat regeneration are the relevant proofs for instruction-only changes. No business feature is proven by these checks.

## Domain-oriented evolution validation — 2026-10-28

This update preserved the existing authorized uncommitted work in `/tmp/blubby-domain-evolution-before.tar.gz` before changing the domain-oriented guidance. The initial integration results above remain historical.

| Executed check | Result |
| --- | --- |
| `./vendor/bin/sail artisan boost:update --help` | Confirmed the installed command and flags |
| `./vendor/bin/sail artisan boost:update --no-discover --no-interaction` | Three successful runs: initial generation, generation after the explicit test-layout override, and repeat generation; the last two content and symlink manifests were identical |
| Installed skill-creator `quick_validate.py` on the three existing canonical skills | All passed; no new skills created |
| Local Markdown file and section-anchor checks | 80 internal references valid |
| Generated skill links | All 12 links resolve to their canonical sources |
| Task-start SHA-256 and symlink comparison | Native guidance from the foundation marker onward, official skills, Claude import, MCP settings, `config/boost.php`, `boost.json`, production code, tests, database files, routes and CI preserved |
| Scope and consistency inspection | Only 13 Markdown files changed since this task began; no domain scaffold created |
| Static cavecrew search and final independent review | Generic native mirrored-test paths and per-class grouping conflicted with the project layout; corrected through an explicit canonical project guideline override while preserving official copies. Final review found no remaining issues. |
| `git diff --check` | Passed |

PHPUnit, Pint, Biome, asset builds and migrations were not rerun for this Markdown-only update; previous suite results are not current execution evidence. Client-by-client skill activation was not tested.
