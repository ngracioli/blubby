# Agent architecture compliance audit — 2026-10-28

## Result and evidence boundary

The existing Laravel-first, domain-oriented, modular-monolith-ready decision is preserved. Boost connects the canonical guideline to the backend entry point and parses/installs the three project skills. This audit verifies those mechanisms through installed source, runtime composition, generated outputs and direct MCP execution. It does not certify automatic activation or implementation behavior in seven separate clients.

The checkout already contained substantial uncommitted architecture/Boost work. Entry files, Boost sources/configuration, installed skills, MCP registrations, application/tests/routes/database files and CI were archived before changes in `/tmp/blubby-compliance-before.tar.gz` with a SHA-256/link manifest in `/tmp/blubby-compliance-before.json`. Backend documentation was not included in that archive; the workflow was changed through targeted additions and replacements. These are local session artifacts, not prerequisites for future checks. No business code, architecture decision, dependency, migration or CI workflow was changed.

Installed lockfile versions: Laravel 13.39.0, Boost 2.10.3, MCP 1.1.2, Sanctum 4.3.3 and PHPUnit 12.5.42. Direct MCP application info reports PHP 8.5, Laravel 13.39.0 and `pgsql`. This check does not independently establish the running PostgreSQL server version.

## Discovery chain

1. Repository `AGENTS.md` routes backend tasks to `backend/AGENTS.md`, the workflow and canonical skills. The root instructions were supplied in this session. Backend instructions and all three project skills were explicitly read; the three skills were absent from this session's initial catalog. That is observed fallback loading, not automatic skill activation.
2. Installed `vendor/laravel/boost/src/Install/GuidelineComposer.php` discovers `.ai/guidelines` and composes `.ai/project-architecture` before native guidelines. The new PHPUnit test calls this implementation and compares rendered project content with the generated entry point.
3. `SkillComposer.php` parses YAML `name` and `description`, discovers `.ai/skills/*/SKILL.md` and merges custom skills. The test uses this actual composer, checks the selected names in `boost.json`, canonical paths, nonempty descriptions and installed content.
4. Configured agent implementations provide guideline/skill destinations. `SkillWriter.php` uses symlinks for these plain custom sources, with copy fallback. Current twelve distinct links resolve to three canonical sources. The test compares content, so readable but stale copies fail too.
5. `GuidelineWriter.php` replaces the first managed block and preserves outside content. Tests require one block and the bare Claude import. `config/boost.php` directs Claude generation to `AGENTS.md`.
6. Clients must discover their local entry/skill paths and choose the relevant skill. Boost writes resources; it does not enforce the client's model decisions. Root sessions must follow the explicit fallback rather than assume recursive skill discovery.

The [official Boost documentation](https://laravel.com/docs/13.x/boost) describes custom guidelines, custom skills and `--no-discover`. Installed source and runtime outputs determine the patch-specific results here; current web documentation alone does not prove client loading.

| Configured agent | Guideline output / entry candidate | Skill destination under backend | MCP configuration detected | Loading actually observed |
| --- | --- | --- | --- | --- |
| Amp | `AGENTS.md` | `.agents/skills` | `.amp/settings.json` | No client session observed |
| Antigravity | `AGENTS.md` | `.agents/skills` | `.agents/mcp_config.json` | No client session observed |
| Claude Code | `AGENTS.md`, imported by `CLAUDE.md` | `.claude/skills` | `.mcp.json` | Import inspected; no Claude session observed |
| Codex | `AGENTS.md` | `.agents/skills` | `.codex/config.toml` | Root instructions supplied; backend/skills explicitly read in this session |
| Factory | `AGENTS.md` | `.factory/skills` | `.factory/mcp.json` | No client session observed |
| Grok Build | `AGENTS.md` | `.grok/skills` | `.grok/config.toml` | No client session observed |
| OpenCode | `AGENTS.md` | `.agents/skills` | `opencode.json` | No client session observed |

For every row, destinations and content are verified through Boost's configured agent classes. Client-side catalog discovery, initial-context injection and automatic activation remain unverified. Local Codex configuration is ignored by Git; its presence here is not a promise that a fresh clone has that registration. Configured agents are not necessarily installed or authenticated clients.

All detected MCP commands use relative `vendor/bin/sail` without an explicit working directory. That executable exists in `backend/` and not at repository root. The supported current setup therefore requires the MCP process to run from `backend/`; root startup without adjustment cannot resolve it. This existing documented limitation is retained instead of adding unproven per-client launch settings or machine-specific absolute paths. Boost is not exposed in this session's connector catalog; the direct stdio probe proves server execution, not automatic connector registration.

## Concrete gaps and corrections

| Finding before correction | Impact | Smallest correction |
| --- | --- | --- |
| Review skill description only covered diffs/branches/refactors and excluded whole-repository audits | Architectural/instruction audits had no explicit discovery trigger | Expanded the existing `backend-review` metadata to scoped architectural/compliance audits and routed integration changes to maintenance |
| No single explicit conflict policy; rules were distributed across topic owners | A generic skill or inferred scaffold convention could be mistaken for an architecture decision | Added policy to the existing workflow; made the canonical global guideline explicitly mandatory and linked that policy |
| Workflow stated supported clients discover skills without a per-client observation | Installation could be mistaken for observed client loading | Replaced the claim with the actual Boost destination guarantee and separate client observation requirement |
| Prior validation records were manual and historical | Stale copies, missing skills and broken entry/link integration had no repeatable regression gate | Added one PHPUnit architecture test file using installed Boost APIs and the existing suite/CI |

The native testing skill's mirrored-file/per-class grouping conflict was already resolved by the explicit project testing override. It remains intact. Historical alternatives in `architecture/decision.md` are explicitly superseded, not competing active architecture. No additional architectural contradiction was found in the reviewed owners; this is a scoped assessment, not a proof that all possible interpretations agree.

Changed files in this audit: `.ai/skills/backend-review/SKILL.md`, `.ai/guidelines/project-architecture.md`, generated `AGENTS.md`, `docs/agent-workflow.md`, this report and `tests/Feature/Architecture/AgentInstructionsTest.php`. Other dirty files predate this audit. No new skill or rules tree was created. The development workflow already covers creation, modification, refactoring, domain selection, specialized documents, risk analysis, test-first work and post-change validation, so it was preserved.

## Practical planning exercises

These are read-only reasoning exercises by the current agent and a delegated investigator after reading the canonical documents. They are not production implementations, stochastic model evaluations or external client sessions. All names are hypothetical; no domain scaffold was created.

| Scenario | Expected decision and observed plan | Required behavioral proof | Result |
| --- | --- | --- | --- |
| A: basic account display-name CRUD | Owner `Accounts`; `Http/{Controllers,Requests,Resources}/Api/Accounts`, `Models/Account`, conventional Policy. Scoped validated write can stay in Controller. No Action, Service, DTO, interface or Repository | HTTP success, invalid input, cross-user access and privileged-field rejection | Conforming plan; not executed |
| B: multi-entity financial transfer | Owner `Transfers`; justified `Domain/Transfers/Actions/TransferFundsAction::handle(...)`. One `DB::transaction`, ordered locks or guarded updates, exact agreed amounts, mutable checks under lock, replay identity and constraints. No remote I/O in retrying callbacks | PostgreSQL conservation, rollback, replay/conflicting payload and controlled independent concurrent transactions; HTTP authorization/validation | Conforming plan; not executed |
| C: two collaborating domains | Transfers calls existing Accounts eligibility/balance and Transactions recording operations synchronously. Reuse owner invariants; one transaction owner; no reverse dependency, separate commit or required queued financial write. Include Model-method dependencies in review | Owner rule tests and caller integration proving atomic rollback and invariant reuse | Conforming plan; not executed |
| D: feature in an existing domain | Given a hypothetical existing Accounts fixture, reuse its paths and operations. Reject parallel `AccountManagement` or global business layers | Actual callers/contracts and HTTP/integration tests for the new behavior | Conforming hypothetical plan; no implemented business domain currently exists to observe actual reuse |
| E: overloaded Controller | Keep transport validation, authorization and serialization in HTTP; extract cohesive multi-write orchestration into the owner's Action. Shared Service only for a real reused capability; no line-count threshold or class per statement | Relevant existing tests before/after, affected entry points and rollback paths | Conforming plan; not executed |

Planning criteria checked in every row: owner, paths, abstraction justification, dependency direction, native Laravel facilities, absence of speculative layers and test strategy. Evidence owners: `architecture/overview.md` responsibility/dependency tables, `architecture/naming.md`, `architecture/decision.md` hypothetical CRUD/transfer examples, and database/testing guides. Currency, rounding and other unresolved financial product choices remain unresolved.

To verify a real client later, start a fresh session separately at root and backend, record client/version/catalog, give each scenario without restating the architecture, and capture the actual document/skill reads plus proposed file paths and rationale. Evaluate against the table, then repeat after a relevant update. Do not count a client's unsupported self-report of “loaded” as evidence. A representative real implementation still needs code review and tests; no prompt-only benchmark guarantees future compliance.

## Automated and contextual boundaries

The new four-test gate checks actual Boost parsing/selection, all seven destination mappings and content, guideline composition parity, marker uniqueness, Claude import, required routing references, backend-local inline Markdown file links, application PSR-4 namespaces and absence of global `app/Actions`, `app/Services` and `app/Queries`. Metadata wording checks only guard audit discoverability intent; they do not prove a model activated the skill. The test uses existing dependencies and requires no CI edit.

Host validation additionally covers repository-level links and instruction entries outside Sail's mount. The automated link test does not check anchors, reference-style links or remote availability. It deliberately excludes links outside the backend mount; those are checked separately on the host.

No static cycle/import allowlist is added: no concrete business domains/dependency graph exist yet. Namespaces alone cannot detect cycles hidden in Model methods, callbacks or runtime resolution. Future explicit dependency contracts can justify narrow checks then. Review still decides owner correctness, Action/Service necessity, rule duplication, authorization completeness, transactional boundaries and native-API suitability. Behavioral tests must prove financial integrity, retries and concurrency.

## Acceptance assessment

Statuses refer to evidence collected in this audit, not the historical green tables in the workflow.

| Criterion | Status | Evidence / remaining boundary |
| --- | --- | --- |
| 1. Essential rules in relevant entries | Partially verified | Root routing and all Boost destination contents verified; external initial-context loading not observed |
| 2. Three skill discovery mechanisms | Partially verified | Actual Boost parser, selection and installed parity verified; client catalog/activation not observed |
| 3. Specialized references valid | Verified | Backend test and host file-link checks; anchors/remote availability excluded |
| 4. No relevant instruction contradiction | Verified | Scoped owner/native review; existing testing specialization retained and conflict policy explicit |
| 5. Explicit code placement criteria | Verified | Canonical guideline, overview/naming and development steps 1–3 |
| 6. No indiscriminate Actions/Services | Verified | Responsibility criteria, explicit prohibitions and A/E planning results |
| 7. Validation workflow documented | Verified | Development steps 4–7 and workflow evidence requirements |
| 8. Justified automated checks | Verified | Four PHPUnit architecture checks in existing suite; contextual limits documented |
| 9. Boost preserved | Verified | Source-based regeneration, repeat output comparison and preservation manifest |
| 10. Practical scenarios evaluated | Partially verified | A–E planning recorded; no real feature implementation or seven-client behavioral run |

The configured root MCP launch limitation fails if the process actually starts from root; it does not invalidate the verified backend-directory stdio execution. No claim of complete functional compatibility is made.

## Execution record

Commands run from `backend/` unless stated otherwise:

- `git status --short` on the host: existing dirty work inventoried before edits; scoped archive/manifest saved.
- `./vendor/bin/sail artisan boost:update --help`: installed flags verified. Initial sandbox execution could not reach Docker; the same command succeeded with approved local Docker access.
- New focused PHPUnit gate before correction: after fixing test setup errors, 4 tests, 3 passed and 1 failed because `backend-review` lacked the architectural audit trigger. Setup errors were not treated as the intended failing proof.
- `./vendor/bin/sail artisan boost:update --no-discover --no-interaction` twice: regenerated after canonical guideline changes; the second run produced an identical 177-entry content/link manifest. This is a resource refresh, not a package upgrade. The only native-block change from task start was Boost's conditional test-enforcement section: installed `InstallCommand::determineTestEnforcement()` detects that the increased test count meets its threshold. Existing native sections and official skill copies were preserved.
- Focused PHPUnit after the source correction: 4 passed, 205 assertions. Full suite after Pint: 6 passed, 207 assertions. Independent review found the empty-agent/disabled-guideline gate gap and the overbroad archive description; both were corrected. Final full suite: 6 passed, 209 assertions; final focused Pint passed.
- `./vendor/bin/sail bin pint tests/Feature/Architecture/AgentInstructionsTest.php --format agent`: passed after formatting the new test. Explicit path selection avoids the documented backend-only mount limitation of `--dirty`.
- Host inline Markdown file-link validation: 93 references checked, no missing targets, including references outside Sail's mount. This does not validate section anchors or external URLs.
- Negative link probe: a temporary documentation fixture referenced a nonexistent backend-local file. The focused link test failed on that exact target as expected; the fixture was removed in `finally` before the final passing suite. No production code was changed for this probe.
- Preservation comparison: only the generated entry, canonical guideline and canonical review skill differ among the 177 archived paths. Existing official skill copies, MCP settings, Claude import, Boost configuration, production code, prior tests, database files, routes and CI are unchanged. The workflow and new report/test are separately reviewed; they are not covered by that archive.
- Direct stdio MCP probe through Sail: initialize negotiated `2025-11-25`; `tools/list` exposed ten tools; `application-info` returned PHP/Laravel/engine facts. Package-filtered `search-docs` returned framework 13.x content without error; the broad search's first result was irrelevant to locking, so this does not validate locking API semantics.

No external agent session, financial/concurrency implementation, database mutation, package installation, build or deployment was performed. Client-side automatic loading remains the principal unverified link.

Changes remain uncommitted. Canonical sources, generated links and the new test/report must be included with the existing intended work before fresh checkouts can reproduce this state. No commit was created as part of this audit.

## What happens on the next Laravel request

Root routing points the agent to backend instructions. The generated backend entry carries mandatory architecture/placement constraints and skill routing. Installed metadata offers development/review/maintenance discovery; an absent catalog entry requires explicitly reading the canonical skill. The development workflow then requires owner identification, relevant documentation, reuse, smallest structure, tests and final review. The new test gate can reject objective instruction/placement regressions in the existing PHPUnit/CI path. Parsing, outputs, direct MCP execution and current-session explicit reading are proven. Automatic activation and consistent decisions in another client still require that client's observed execution; Boost and Markdown cannot guarantee model obedience.
