# Architecture decision and evidence

Initial decision and domain-oriented evolution: 2026-10-28. Status: domain-oriented hybrid adopted for future backend work; the initial global business-directory placement is superseded below. This record explains the choice; [overview](overview.md) owns normative responsibilities and paths. Reassess when its evolution gates are met, not on a calendar or popularity ranking.

## Initial integration audit (historical)

Inspected `composer.json`, lockfiles, `compose.yaml`, routes, bootstrap, models, migrations, tests, existing docs, root/backend agent files, MCP settings, `boost.json`, installed Boost source and backend CI. The working tree was clean before that initial integration. The subsequent domain-oriented update starts from its authorized uncommitted documentation, guidelines and skills; those changes are preserved, not reset.

| Evidence | Finding |
| --- | --- |
| `composer.lock` | Laravel 13.39.0, Sanctum 4.3.3, Boost 2.10.3, MCP 1.1.2, Sail 1.69.0, Pao 1.1.5, PHPUnit 12.5.42 |
| `compose.yaml` and CI | PHP 8.5, PostgreSQL 18; persistent local volume and database healthcheck. No Redis or hosting deployment. |
| `routes/api.php`, `routes/web.php`, `bootstrap/app.php` | `/api/user` with Sanctum, default welcome page, `/up`, JSON API errors; no business routes |
| `app/Models/User.php`, migrations | Sanctum trait, fillable/hidden attributes, hashed password cast, users/reset/session/cache/job/token storage |
| `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php` | Scaffold tests only; no evidence of completed authentication or financial behavior |
| `docs/` | Existing owners for architecture, patterns, naming, API, database, security, testing, operations and domain. Architecture was proposed; a stale Spring reference existed in the pattern guide. |
| `AGENTS.md`, `CLAUDE.md`, `boost.json` | Native Boost instructions, Claude import and four official skills for seven configured agents; no custom `.ai` source tree |
| `.github/workflows/backend-ci.yml` | Composer validation/install, Pint, Biome, assets, migrations and PHPUnit; no deployment |

The backend's actual state takes precedence over hypothetical examples. Product uncertainties (money representation, provider linking, paid-bill cancellation) remain decisions to resolve before implementing affected behavior. Neither instruction task adds business PHP, endpoints, dependencies or infrastructure services.

## Domain-oriented evolution: adopted decision

The initial decision favored conventional technical roots with capability subfolders because no business behavior existed. The revised requirement explicitly prioritizes business locality before many features accumulate. Repository inspection confirms there is still no business code to migrate: adopting domain-first placement for future extracted behavior has a low transition cost. MVC, conditional extraction, Eloquent, native HTTP facilities and one deployable remain unchanged. The old comparison below is historical reasoning, not current placement guidance.

Choose `app/Domain/<Domain>/{Actions,Services,Queries,...}` only for meaningful extracted behavior. Models stay in `app/Models`; HTTP classes use `Http/{Controllers,Requests,Resources}/Api/<Domain>`. Jobs/Events/Listeners retain native roots with domain subfolders. This is **Laravel-first + Domain-Oriented Organization + Modular Monolith Ready**, not implemented module isolation or full DDD.

### Revised five-way comparison

Assessments are project-specific. Organization affects navigation and change locality, not HTTP/database throughput by itself. All alternatives still need authorization, integrity and behavior tests.

| Alternative | Maintenance / navigation as features grow | Testability / coupling | Initial and evolution cost / Laravel fit / agent usability |
| --- | --- | --- | --- |
| MVC with global technical business folders | Familiar initially; related behavior spreads across role folders | Native feature tests, but ownership and rule reuse become harder to see | Lowest initial cost; idiomatic framework roots. Agents need more cross-folder searches as behavior grows. Original choice, now superseded for extracted business classes. |
| MVC with domain-grouped business behavior | Co-locates operations, queries and rules; HTTP remains predictable | Same PHPUnit/Eloquent tests; explicit owner/dependency review limits accidental coupling | Low initial cost, no package/discovery changes for plain classes. Chosen: owner-first paths help agents locate related behavior without mandatory extra files. |
| Entirely feature-oriented tree, including HTTP/Models/jobs | Strong locality for independent vertical features, but shared models/rules can fragment | Good use-case tests; duplicate rule ownership and framework discovery need discipline | More registration/namespace conventions and migration cost. Agents must learn more exceptions to framework defaults; defer full relocation. |
| Explicit modular monolith | Public/internal operations and ownership can be enforced across stable modules | Contract/integration/architecture tests; shared DB/model access must obey declared limits | Medium/high initial cost. Native Laravel inside modules is possible, but providers/registration/enforcement need real benefits. Evolution target when current boundaries prove insufficient. |
| DDD Domain/Application/Infrastructure layers | Strong responsibility separation for complex domain semantics | Pure domain tests plus mapping/integration contracts; ORM-independent domain costs duplicate representations | High initial structure and learning cost. No current independence/aggregate need; agents may generate ceremonial mappings. Tactical techniques remain conditional, full layering deferred. |

### Research supporting the adjustment

- **Official capability, not an official architecture mandate:** [Laravel 13 structure](https://laravel.com/docs/13.x/structure) permits organization under Composer-autoloaded namespaces; [container](https://laravel.com/docs/13.x/container) resolves concrete collaborators. A plain `App\Domain` class needs no module package. [Events](https://laravel.com/docs/13.x/events) provides listener discovery/configuration; keeping framework participants in native roots avoids changing that mechanism now.
- **Primary production-code example:** Monica's [`App\Domains\Contact\ManageContact\Services\CreateContact`](https://github.com/monicahq/monica/blob/main/app/Domains/Contact/ManageContact/Services/CreateContact.php) groups operations by business capability and a subcapability, while [`Contact`](https://github.com/monicahq/monica/blob/main/app/Models/Contact.php) remains in `App\Models`. This supports the hybrid's feasibility in a long-lived product. Its common BaseService, ServiceInterface and model inheritance/cross-domain details are not adopted: their existence is not evidence this scaffold needs them. It is an organization example, not verified Laravel 13 code to copy.
- **Primary practitioner report:** Spatie's [Reading code with AI, not generating it](https://spatie.be/blog/reading-code-with-ai-not-generating-it) describes onboarding into a large Laravel project, domain-oriented navigation, Boost and project skills. It supports discoverable ownership and targeted context. It does not establish a universal folder layout or prove autonomous agents can enforce boundaries without review.
- **Counterexample/pragmatism:** [solidtime's Controller](https://github.com/solidtime-io/solidtime/blob/main/app/Http/Controllers/Api/V1/TimeEntryController.php) demonstrates conventional HTTP/Model/Service organization in a maintained product. [Fortify's Controller](https://github.com/laravel/fortify/blob/1.x/src/Http/Controllers/AuthenticatedSessionController.php) composes actual focused steps with a native Pipeline. Preserve Laravel facilities and meaningful operations; do not infer one Action per endpoint or domain per model.
- **Opinion, not consensus:** [practitioners discussing large Laravel layouts](https://www.reddit.com/r/laravel/comments/1pmnfxh/whats_your_goto_approach_for_structuring_large/) disagree on domain folders and warn about custom discovery and unclear ownership. This motivates one explicit hybrid convention and preserved framework roots, rather than unconstrained feature layouts. [Refactoring.Guru](https://refactoring.guru/design-patterns) remains the pattern taxonomy, not an argument for domain interfaces/events everywhere.

### Adjustments to the proposed tree and accepted costs

Use `Api/<Domain>` consistently for **Resources as well as Controllers/Requests**, since all three adapt HTTP contracts; this is namespace grouping, not route versioning. Retain the existing `*Action` suffix and `handle()` for continuity instead of renaming ordinary use cases to match an example. Add `Feature/Domain/<Domain>` for non-HTTP DB integration tests; Unit tests cannot prove locks or PostgreSQL constraints. Keep conditional DTOs/value objects/exceptions/integrations within their owner rather than new global business folders.

Conventional Models and framework participants still distribute some files across roots, and domain limits are initially enforced by documentation/review rather than a module runtime. Accept that cost to preserve discovery and native tooling. Direct scoped Eloquent reads and intentional concrete calls are allowed; existing owner rules must not be copied or bypassed. The [ownership table](overview.md#domain-ownership-and-dependencies) is authoritative for cross-domain writes and atomic workflows.

Subdivide only when stable rule ownership, independent change drivers or repeatedly disjoint changes reveal a real subcapability. Stricter modules become worthwhile when team ownership/public APIs or recurring forbidden coupling require enforceable boundaries. No file-count threshold, new module package, mandatory contracts or mass move is introduced. Existing tests and client contracts must bracket any future code relocation.

## Original comparison (historical; placement superseded)

These are project-specific assessments, not framework restrictions. All choices can run on Laravel 13; their fit with Eloquent differs. “Cost” includes extra files, mappings, bindings and concepts to maintain. None substitutes for constraints, query tuning or workers when scaling runtime load.

| Approach | Eloquent fit / coupling | Complexity, understanding and structural cost | Maintenance / testability / evolution | Value now and overengineering risk |
| --- | --- | --- | --- | --- |
| Traditional MVC | Native Active Record, intentionally framework-coupled | Low; familiar; few files | HTTP/DB tests easy; complex controllers/models can become change hotspots | Best baseline; weak alone for multi-record use cases |
| MVC + conditional Actions | Native ORM in use cases; HTTP separated | Low to medium; one meaningful operation per class | Reusable entry points, clear atomicity, easy feature tests | Chosen extension; mandatory one-line Actions would add noise |
| MVC + Service Layer | Native ORM; coherent capabilities possible | Medium; names/responsibilities need discipline | Reuse helps; entity-wide CRUD services become coupled buckets | Selectively chosen; no parallel service for every Action |
| Domain-oriented organization | Eloquent can stay; capability ownership improves locality | Medium; namespace/discovery conventions needed | Useful for repeated cross-folder changes; integration tests still needed | Evolution option; domain boundaries not established yet |
| Feature modules | Laravel conventions inside each feature; explicit dependencies needed | Medium; more registration and navigation | Better team ownership if features are cohesive; coupling may just move folders | Defer full relocation; capability subfolders suffice now |
| Modular monolith | Compatible; shared DB can leak module internals | Medium to high; public/internal rules and contracts | Good independent ownership; contract tests enforce boundaries | Defer until several stable modules exist; folders alone do not prove modularity |
| DDD | Tactical value objects fit; persistence-independent aggregates add mapping | High for full strategic/tactical adoption; shared language required | Strong for complex invariants; domain tests plus DB integration | Borrow invariant thinking; no aggregate/repository scaffolding without rules |
| Clean Architecture | Strict dependency direction conflicts with direct ORM domain use | High; separate entities, ports, mapping and adapters | Isolated domain tests; more integration contracts and synchronized representations | No current independence requirement; high duplication risk |
| Hexagonal Architecture | Fits external seams; full ORM isolation needs ports/adapters | Medium selectively, high globally | Good protocol contract tests; replacing every Laravel facility adds indirection | Use a real external Adapter later; no universal persistence port now |
| Incremental hybrid | Native Laravel + explicit use-case seams | Low initially; complexity paid only when needed | Preserves scaffold, enables focused extraction and later modules | Selected; conditional rules require reviewers to apply judgment consistently |

Accepted costs: application operations depend on Eloquent/Laravel; capability code can span several technical folders; module encapsulation is reviewed rather than enforced by an added tool. These are smaller costs than speculative mapping and abstract contracts today. No new static analyzer, module package, Actions library, Pest conversion or repository framework is justified by current code. Reassess tooling when a concrete defect class or enforcement need warrants it.

## Research and evidence strength

Research consulted official manuals, primary source code, author-maintained libraries and firsthand community discussions. References below were accessed on the decision date. Branch links are moving examples, not version-locked dependencies; installed source/lockfiles settle this repository's API compatibility. Search failures and paywalled material were not treated as implementation evidence.

### Official guidance and framework capabilities

- [Laravel 13 structure](https://laravel.com/docs/13.x/structure): conventional `app/` layout is a starting point with namespace flexibility. It does not mandate DDD, Actions or a Repository layer. Keep framework discovery working rather than fight it.
- [Container](https://laravel.com/docs/13.x/container), [Eloquent](https://laravel.com/docs/13.x/eloquent) and [relationships](https://laravel.com/docs/13.x/eloquent-relationships): concrete dependency resolution, Active Record, casts/scopes and eager loading remove reasons for generic wrappers.
- [Validation](https://laravel.com/docs/13.x/validation), [authorization](https://laravel.com/docs/13.x/authorization), [Resources](https://laravel.com/docs/13.x/eloquent-resources), [Sanctum](https://laravel.com/docs/13.x/sanctum): use distinct native HTTP/input/access/serialization responsibilities. Sanctum supplies authentication primitives, not the missing product flows.
- [Transactions](https://laravel.com/docs/13.x/database#database-transactions) and [queues](https://laravel.com/docs/13.x/queues#jobs-and-database-transactions): native transaction retries and after-commit dispatch support consistency. Queue delivery still needs idempotency and operational recovery.
- [Boost](https://laravel.com/docs/13.x/boost), [MCP](https://laravel.com/docs/13.x/mcp) and installed `vendor/laravel/boost/src/`: native custom guideline/skill composition and MCP are sufficient. Latest web documentation is checked against 2.10.3 rather than assumed identical. Exact integration evidence lives in [agent workflow](../agent-workflow.md).
- [PHP 8.5 release](https://www.php.net/releases/8.5/en.php), [readonly](https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.readonly-properties), [enums](https://www.php.net/manual/en/language.enumerations.php): plain typed PHP supports DTOs and closed sets; no DTO package is required.
- PostgreSQL 18 [constraints](https://www.postgresql.org/docs/18/ddl-constraints.html), [isolation](https://www.postgresql.org/docs/18/transaction-iso.html), [indexes](https://www.postgresql.org/docs/18/indexes.html), [CREATE INDEX](https://www.postgresql.org/docs/18/sql-createindex.html), [numeric types](https://www.postgresql.org/docs/18/datatype-numeric.html), [EXPLAIN](https://www.postgresql.org/docs/18/using-explain.html): database rules address referential integrity, concurrency, exact values and measured query performance. Do not copy MySQL unsigned/index/DDL assumptions.

### Observed professional practices, not universal prescriptions

- Laravel's own [Fortify controller](https://github.com/laravel/fortify/blob/1.x/src/Http/Controllers/AuthenticatedSessionController.php) composes a native Pipeline of focused [Actions](https://github.com/laravel/fortify/blob/1.x/src/Actions/AttemptToAuthenticate.php), a limiter and contracts. This is maintained framework-ecosystem code, demonstrating a real multi-step workflow. It does not prove every CRUD operation needs an Action. Fortify is not installed or proposed here.
- [solidtime](https://github.com/solidtime-io/solidtime), a maintained time-tracking product with organizations, billing rates, tests and self-hosting, uses conventional `app/Models`, `Http/Requests`, `Http/Resources`, Jobs and `app/Service`. Its [TimeEntryController](https://github.com/solidtime-io/solidtime/blob/main/app/Http/Controllers/Api/V1/TimeEntryController.php) mixes Eloquent, authorization, service calls and job dispatch; [TimeEntryService](https://github.com/solidtime-io/solidtime/blob/main/app/Service/TimeEntryService.php) owns rounding-related query details. Useful evidence for ORM pragmatism and focused capabilities; the large controller is also a reason to define extraction gates, not copy its size or dependencies.
- Spatie's [queueable-action implementation and examples](https://github.com/spatie/laravel-queueable-action) and [Beyond CRUD course outline](https://spatie.be/courses/laravel-beyond-crud/actions) show domain grouping, typed data and focused operations as an established author/team approach. Public package source demonstrates behavior reuse; paid lessons were not inspected. Adopt the useful idea as ordinary PHP, not the package or an obligatory domain tree.

These samples establish recurring tools and several valid organizations, not a statistically representative claim about all professional Laravel projects. Their differing responsibilities matter more than star counts.

### Community opinions and educational taxonomy

- [Laravel News: Actions](https://laravel-news.com/organize-laravel-applications-with-actions) describes a third-party package, not official Laravel architecture. Checked against the [package author's README](https://github.com/lorisleiva/laravel-actions); it provides an alternative execution model, not a dependency we need.
- [Laracasts discussion](https://laracasts.com/discuss/channels/general-discussion/best-practices-services-repositories) contains firsthand but old advice about service classes versus single-operation commands. It informs the trade-off, not Laravel 13 syntax or security rules.
- [Reddit: service organization](https://www.reddit.com/r/laravel/comments/1tishwa/how_do_you_structure_service_classes_in_larger/) contains conflicting practitioner preferences on Actions, services, interfaces and domains. Treat as experience reports, not consensus. Reject the blanket requirement for an interface around every I/O operation.
- [Boost issue #948](https://github.com/laravel/boost/issues/948) flags noninteractive package discovery surprises. Verify actual flags and source before updates; use `--no-discover` deliberately when regenerating an existing selection.
- [Refactoring.Guru](https://refactoring.guru/design-patterns) defines GoF intent and structure. It informs correct distinctions (especially Factory Method versus simple factories, and Adapter versus Decorator). It does not authorize product features or determine Laravel directory layout. The [practical guide](design-patterns.md) maps candidates to native facilities and simpler alternatives.

## Hypothetical scenarios

These are documentation-only examples. Accounts, Transfers and Transactions are possible owners, not created or approved modules. Product decisions on exact money representation, currency, account closure, rounding and retries remain prerequisites to affected implementation.

### Scenario A: simple account CRUD

For renaming an account display name without additional business workflow:

| Participant | Location and responsibility |
| --- | --- |
| Controller | `app/Http/Controllers/Api/Accounts/AccountController.php`: authorize the actor, update the allowlisted name on the scoped Account, return a Resource |
| Validation | `app/Http/Requests/Api/Accounts/UpdateAccountRequest.php` for meaningful validation/policy delegation; inline validation remains acceptable for a tiny local contract |
| Model / Policy | `app/Models/Account.php` and `app/Policies/AccountPolicy.php`: persistence/relationships and access decisions |
| Resource | `app/Http/Resources/Api/Accounts/AccountResource.php`: explicit public fields, no extra queries |
| Proof | `tests/Feature/Api/Accounts/UpdateAccountTest.php`: success, invalid name, cross-user refusal and unchanged privileged fields |

The owner is Accounts even if `app/Domain/Accounts` does not physically exist. No Action, Service, Query, DTO, Event or Job is justified for this single validated write. If renaming later triggers real coordinated rules, reevaluate extraction rather than prebuild it. Do not treat closing an account or changing its opening balance as equivalent trivial CRUD.

### Hypothetical future example: transfer funds

Transfers owns the complete outcome, conservation/replay orchestration and the outer transaction. Accounts owns account eligibility and balance rules; Transactions owns recording rules when such a capability actually exists. These responsibilities need not correspond to three domain folders/classes on day one.

| File / participant | Responsibility and creation reason |
| --- | --- |
| `app/Http/Requests/Api/Transfers/TransferFundsRequest.php` | Validate IDs, agreed exact amount and replay key; delegate policy access. `exists` validation cannot prove ownership or current balance. |
| `app/Http/Controllers/Api/Transfers/TransferController.php` | Pass authenticated actor and validated values to `App\Domain\Transfers\Actions\TransferFundsAction`; return Resource/status. No balance arithmetic. |
| `app/Domain/Transfers/Actions/TransferFundsAction.php` | Enforce both accounts' authorization, claim uniquely constrained actor/replay identity, lock accounts in stable ID order, recheck invariants and coordinate both financial effects in one transaction. Define same-payload replay and reject a different payload under the same key. |
| Accounts collaboration | Use existing `App\Models\Account` behavior or a justified `App\Domain\Accounts\Services` capability for account rules. A trivial save is not a reason for a separate debit/credit Action. Mutable eligibility/balance checks occur after locking. |
| Transactions collaboration | Use an existing recording operation or invariant-bearing Model behavior for ledger rules. If recording is primitive persistence only, direct Model writes by the transfer workflow require documented invariant/constraint ownership, as specified in the architecture guide. Do not invent a generic transaction service. |
| Models / constraints | `app/Models/Account.php`, `Transfer.php` and any actually needed transaction Model; migrations protect exact values, ownership/replay uniqueness and relationships. Models stay conventional. |
| `app/Http/Resources/Api/Transfers/TransferResource.php` | Serialize agreed public fields and loaded relations; no internal credentials or accidental queries. |
| `tests/Feature/Api/Transfers/TransferFundsTest.php` | HTTP validation, authorization, cross-user/related-ID refusal and response contract |
| `tests/Feature/Domain/Transfers/TransferFundsTest.php` | Operation without HTTP: conservation, second-write rollback, replay/mismatched payload, two-connection concurrency and collaboration rules |

The dependency direction is Transfers to Accounts/Transactions behavior; neither collaborator calls Transfers back. Document model-method dependencies as well as domain namespace imports. Collaborators join the caller's transaction and never independently commit, publish required effects asynchronously or perform network I/O while locks are held. Use [database rules](../database/README.md) for duplicate-constraint errors and retry handling; a constraint error cannot be caught and ignored inside an aborted PostgreSQL transaction.

Start with typed parameters, not a DTO merely because an Action exists. A real shared payload may justify `Domain/Transfers/Data/TransferFundsData.php`. A reusable invariant-bearing monetary value may justify an owner-defined Value Object; it is not duplicated into each domain. No Repository, Strategy, interface, generic `AccountService` or owner rule copy is required for one transfer algorithm. Tests exercise the owning operation and actual collaborators, not only mocked call order.

### Scenario C: notification or asynchronous processing

No asynchronous participant is required for Scenario A or the atomic effects in Scenario B. When a real notification/export requirement arrives, choose according to its execution semantics:

| Need | Smallest choice and conditional location | Proof / consequence |
| --- | --- | --- |
| Required result or invariant in this operation | Direct synchronous call to the actual owner's operation; keep atomic DB effects in the outer transaction | Caller receives errors; tests prove rollback and invariants. Events must not hide a required result. |
| One independent receipt reaction | Direct native notification after commit can suffice; add `app/Notifications/Transfers/TransferReceipt.php` only when required | Test no send on rollback and chosen failure behavior. Do not create Event + Listener + Job merely for symmetry. |
| Several independent consumers of a completed fact | `app/Events/Transfers/FundsTransferred.php` with listeners grouped by the domain owning each reaction, such as `app/Listeners/Transfers/SendTransferReceipt.php` | Define after-commit timing and failure semantics. Laravel events/listeners suffice; no custom observer/bus. A synchronous listener still runs in the caller. |
| Work needs deferred completion or retry | Native queued notification/listener, or a standalone `app/Jobs/Transfers/GenerateTransferExport.php` when it represents actual deferred work; invoke the owning domain behavior | Test execution, duplicate delivery, bounded retry and authorization context, not only dispatch. Database queue requires a worker; no Redis prerequisite. |

Avoid a Job that merely dispatches another queued listener/notification doing the same work. Pass trusted identifiers or a justified snapshot according to the requirement. After-commit dispatch prevents a worker reading uncommitted data, but publication can still be lost after a process crash: guaranteed delivery may later justify a durable outbox. It is not scaffolded now. [Operations](../operations/README.md) owns workers and recovery; [patterns](design-patterns.md) owns the relevant conditional design choices.
