# Backend architecture

## Decision and current state

Adopt **Laravel-first + Domain-Oriented Organization + Modular Monolith Ready** in one deployable application. Group business behavior under `app/Domain/<Domain>/`; retain Laravel HTTP and Eloquent conventions, conditional use-case Actions and cohesive services. Domain organization is a location and ownership decision, not full DDD or a mandatory Controller–Action–Service–Repository chain. [Evidence and alternatives](decision.md) explain the decision and its accepted costs.

Laravel 13, PostgreSQL 18 and Sail are implemented. Sanctum is installed on `User`; token storage, password-reset storage and JSON API errors are prepared. Only `/api/user` (authenticated), `/up` and the default `/` exist. Login, registration, token issuance and financial modules are not implemented. Database sessions, cache and queues, local storage and log mail are current infrastructure. Redis, cloud hosting and external providers are not prerequisites.

This evolves the initial technical-directory decision before business features accumulate. Existing scaffold stays in place. New extracted business operations belong to their owning domain from their first consumer; do not create global `app/Actions`, `Services` or `Queries` for them. A simple feature may need only HTTP classes, a Model and a Policy, with no physical `Domain/` folder. Do not create empty layers or move `User` to demonstrate the decision.

## Responsibility decisions

| Participant | Keep here | Extract or avoid when |
| --- | --- | --- |
| Controller / route | HTTP coordination, policy invocation, one straightforward scoped query or validated single-model write, response selection | Extract branching business rules, multi-record consistency or behavior reused by HTTP/CLI/jobs. A line count alone is not a reason. Existing scaffold closures need no rewrite. |
| Form Request | Input shape, field validation, transport normalization, policy delegation in `authorize()` where appropriate | Do not pass it into an Action or treat `exists` validation as ownership proof. Recheck mutable invariants inside the transaction. |
| Action | One use case with atomic multi-write orchestration, meaningful business branching, or actual reuse across entry points | A single `Model::create()` forwarding class adds no value. Use a plain injectable class with `handle()`, not an Actions package or common base. |
| Service | A cohesive reusable capability, such as recurrence calculation, shared by actual operations | Avoid `AccountService` as a CRUD bucket and Action/Service pairs that only forward calls. Name the capability (`InstallmentAllocator`), not a vague category. |
| Model | Relationships, casts, local predicates and behavior tightly tied to that entity | No request handling, remote calls, cross-model workflows or hidden financial effects in observers. Eloquent models remain usable by Actions. |
| Query | One query inline; reusable predicates as local scopes; a custom Eloquent builder for a coherent reusable query vocabulary | Extract a query class for a substantial report/aggregate or reused multi-model query. No repository for each model. |
| Repository | A real persistence contract hiding a remote/legacy store or materially different storage semantics | Do not wrap `find`, `save`, `paginate` or return an unrestricted Eloquent builder under a claimed ORM-independent interface. |
| DTO | A stable typed payload crossing HTTP/CLI/provider boundaries, repeated array shapes or confusing groups of parameters | Validated local arrays or a few typed arguments suffice otherwise. Models represent persisted identity; Form Requests represent HTTP. DTOs do neither. |
| Interface | An actual substitution point, external protocol seam, public module contract or independently maintained dependency | Inject concrete classes by default. One external adapter may justify a contract; two implementations are not a universal requirement. Do not create interfaces just to mock Eloquent. |
| Resource | Explicit public serialization for new business entities and collections | No generic response service or second success envelope. Preserve existing `/api/user` until a task explicitly changes its contract. |

Actions receive explicit actor/context and typed inputs, never obtain the current HTTP request from globals. HTTP authorization may delegate to the same Policy used by an Action. An Action callable outside HTTP must enforce its authorization contract itself, or be private to an explicitly authorized workflow. Document that contract; do not leave CLI/job callers unprotected. Avoid circular Action or Service calls; extract a smaller shared capability only when both operations need it.

## Transactions, events and execution

The outer application operation owns the transaction covering its complete consistency requirement. That is usually an Action when several records must change together. A trivial single write needs no ceremonial transaction. A shared inner calculation must not commit its caller's transaction. See [database rules](../database/README.md) for locking, retries and constraints.

Run required invariants synchronously and atomically. Direct calls are preferable for required ordered collaborators. Use a domain event (a past-tense fact) when independent real consumers should react; use Laravel events rather than a custom bus. An event is synchronous unless its listener is queued. Model events describe persistence lifecycle, not automatically business facts; bulk updates may bypass them.

Use a queued Job when latency, retries or independently recoverable work justify eventual completion. Jobs call the same use-case operation rather than duplicating it. Dispatch work depending on committed rows after commit; test actual listener/job execution as well as dispatch. The database queue is adequate today. After-commit dispatch prevents premature consumption but does not guarantee delivery across process failure after commit; a durable outbox needs a demonstrated delivery requirement, not advance scaffolding. [Operations](../operations/README.md) owns workers and recovery.

## Directory reference

`[existing]` describes audited code. `[conditional]` gives the approved location for a future real consumer, not a directory to scaffold now. `<Domain>` and example names are placeholders, not approved modules.

```text
backend/
├── app/
│   ├── Domain/<Domain>/                         [conditional; business behavior]
│   │   ├── Actions/*Action.php                  [conditional; one meaningful use case]
│   │   ├── Services/*.php                       [conditional; cohesive capability]
│   │   ├── Queries/*Query.php                   [conditional; substantial read]
│   │   ├── Data/*Data.php                       [conditional; justified typed payload]
│   │   ├── ValueObjects/, Enums/, Exceptions/   [conditional; domain-owned types]
│   │   ├── Contracts/                          [conditional; an actual public seam]
│   │   └── Integrations/<Provider>/            [conditional; owned protocol adapters]
│   ├── Http/Controllers/Controller.php         [existing]
│   ├── Http/Controllers/Api/<Domain>/          [conditional]
│   ├── Http/Requests/Api/<Domain>/             [conditional]
│   ├── Http/Resources/Api/<Domain>/            [conditional]
│   ├── Models/User.php                         [existing; new models stay here too]
│   ├── Policies/*Policy.php                    [conditional; conventional model policy]
│   ├── Jobs/<Domain>/                          [conditional; framework entry points]
│   ├── Events/<Domain>/                        [conditional; named facts]
│   ├── Listeners/<Domain>/                     [conditional; event reactions]
│   └── Providers/AppServiceProvider.php        [existing]
├── bootstrap/app.php                           [existing; API exception rendering]
├── config/                                     [existing]
├── database/{migrations,factories,seeders}/     [existing]
├── routes/{api,web,console}.php                 [existing]
└── tests/{Feature,Unit}/                        [existing; scaffold examples]
    ├── Feature/Api/<Domain>/                   [conditional; HTTP contracts]
    ├── Feature/Domain/<Domain>/                [conditional; non-HTTP DB integration]
    └── Unit/Domain/<Domain>/                   [conditional; pure rules]
```

All conditional paths are created only for actual code. Use singular `Domain` in the namespace (`App\Domain\Transfers\Actions\TransferFundsAction`) and a meaningful business capability as the next segment. A domain is not automatically one model/table, one endpoint or a bounded context. Reuse an existing owner when the same rules and changes belong together. For the three [hypothetical scenarios](decision.md#hypothetical-scenarios), Accounts, Transfers and Transactions illustrate ownership only; they are not a required domain catalog. Preserve the existing `Action` suffix and `handle()` convention.

### Framework and domain placement

- Controllers, Requests and Resources all use `Api/<Domain>` for predictable HTTP navigation. `Api` identifies transport, not API version; no `/v1` route is introduced. Explicitly reference Resources rather than relying on basename discovery across namespaces. Non-API transports belong in their own HTTP grouping only when needed.
- Models initially stay flat in `App\Models`; relationships, local scopes and entity-local behavior remain there. Custom Eloquent builders stay in `app/Models/Builders` when warranted. Record each new model's business owner in the affected feature/domain documentation; conventional storage does not imply unrestricted mutation by every domain. Do not duplicate Eloquent models with artificial entities.
- Policies stay in `App\Policies` with conventional model names, minimizing discovery setup. Jobs, Events and Listeners use their Laravel roots with domain subfolders to preserve generator/discovery familiarity and avoid flat directories. Jobs/listeners adapt execution to domain operations; they do not become a second business implementation. Group Notifications, Mail and Console Commands similarly within their conventional roots only when first needed. Providers remain framework wiring.
- A domain-specific provider integration belongs to that domain's `Integrations/<Provider>`; use Laravel HTTP client before adapters/frameworks. A genuinely shared integration keeps one explicit owner and deliberate operations for its actual consumers; do not duplicate the protocol or create `Shared`/`Common` as a default escape hatch.
- Keeping all business and framework types inside a feature would increase locality further but require more discovery/registration decisions. Moving Jobs/Events/Listeners/Policies into a domain is a later cohesive migration only if a real ownership benefit exceeds those costs. Prove listener/policy/factory discovery and routes after any move. No parallel placements for equivalent new classes within the same feature.
- `App\` already maps to `app/` through Composer PSR-4. Plain domain classes need no module loader, provider per domain or Composer mapping. They may use Eloquent, Laravel transactions and native facilities; `Domain` is not a framework-independent DDD layer. They must not depend on `App\Http` or accept Form Requests/Resources. Pure rules remain independent of transport and I/O where appropriate.

## Domain ownership and dependencies

Identify the capability that owns the requested outcome before choosing files. Assign the complete use case to that owner even when several models participate. Keep a short ownership record in the existing affected domain/API documentation when behavior is introduced: owned models/rules, callable operations, allowed dependencies, transaction owner and proof. A separate interface or public-API directory is not required; PHP `public` visibility alone does not make every method a supported cross-domain operation.

| Interaction | Allowed approach | Problematic coupling |
| --- | --- | --- |
| Data access | Scoped Eloquent relationships, documented fields and focused read queries across conventional Models; keep authorization and public serialization at the proper entry point | Repeating another owner's compound query/rule, unscoped user data, writing another owner's state because the Model is globally accessible |
| Rule reuse | Call the owner's existing Model behavior, concrete service or documented operation synchronously | Copying calculations/transition rules, reaching into another domain's internal helper or importing its HTTP Request/Controller |
| Cross-domain workflow | One owning Action composes required operations under one transaction and explicit actor/context; collaborators join it | Controller/Job chains unrelated Actions without an atomic owner; independently committing collaborators or A-to-B-to-A domain calls |
| Information transfer | Pass only needed typed arguments/Models or an existing result; add a DTO for an actual stable payload need | DTO/interface mapping for every call, exposing unrestricted mutation as a supposed read contract, callbacks that hide reverse dependencies |
| Independent reaction | Native Event for a completed fact and actual independent consumers; use after-commit dispatch when required | Events as request/reply calls, hidden required financial writes, events used to disguise a dependency cycle |

Direct dependencies within this monolith are acceptable when intentional, narrow and acyclic. Do not add an interface solely because a call crosses a folder. For cross-owner writes, reuse the owner's invariant-preserving operation. If only primitive persistence is needed and no reusable owner rule exists, the workflow may write those Models directly **only with that collaboration and the workflow's invariant/constraint ownership documented**; do not create one-line debit/save Actions to avoid Eloquent. If an owner operation later appears, route affected writers through it in that task. A raw update must never bypass an existing invariant-bearing operation.

For the transfer example, `Transfers` owns orchestration, replay handling and the outer transaction. A hypothetical `Transactions` domain owns financial records, not generic database transaction management. It calls `Accounts`' existing eligibility/balance behavior (which may be Model methods, not new services), and `Transactions`' existing recording behavior when those rules exist. Accounts/Transactions do not call Transfers back. Read/lock the required Models and recheck mutable state in the same transaction; delegates must not commit separately or enqueue required ledger effects. The dependency record must include ownership of Model methods as well as namespace imports: moving a reverse call into `App\Models` does not remove the business cycle.

Resolve cycles by correcting ownership, moving orchestration to its actual use-case owner, or extracting a real common rule used by both. Do not introduce a generic orchestrator domain, callback, service locator, interface or asynchronous event just to hide a loop. Persistent cycles or frequently bypassed operations are evidence that a boundary should be merged or redesigned. [The transfer walkthrough](decision.md#hypothetical-future-example-transfer-funds) details collaboration without mandatory extra classes.

## Evolution gates

- Extract an Action when orchestration, atomicity or actual caller reuse meets the table above. Extract a service when separate operations share a real capability, not just a similar method name.
- Add a DTO/value object when typed structure or invariant ownership eliminates observed ambiguity. Add a contract only with an identified seam and contract tests.
- Split a domain only when independent business vocabulary/rules and different change drivers are evident, related changes repeatedly touch disjoint class clusters, and dependencies can be separated without constant back-and-forth. File count alone is insufficient. For a cohesive but growing domain, first group a real subcapability as `Domain/Accounts/Closing/{Actions,Queries,Services}/` rather than splitting every endpoint into a domain. Mirror that subcapability only in affected HTTP/test paths. Do not mix competing layouts for equivalent operations; update callers, namespaces and documentation for the cohesive slice being moved.
- Move from domain organization to stricter modular-monolith boundaries when stable domains need enforceable public/internal access rules, independent team ownership or repeatedly regress through forbidden coupling. Record a dependency direction and public operations; add focused architecture checks only when a concrete rule warrants enforcement. It is more than folders: prove calls follow module contracts. Keep one deployable and database unless separate deployment has a measured requirement.
- Use tactical DDD for complex invariants and shared domain language when simpler models/functions no longer express them. Clean/Hexagonal separation is selective for real volatile integrations or domain independence, not an excuse to duplicate Eloquent entities.
- Extract a shared participant only for actual shared consumers. Before any structural move, run existing behavior tests, move the smallest cohesive set and rerun them. Consider moving/grouping Models only when discovery/collision/navigation pain is demonstrated and a small cohesive migration improves ownership. Verify relationships, policy/factory discovery, morph aliases and queued model restoration for affected code. Keep one convention per participant role; no mass relocation solely for symmetry or file counts.

Record each newly adopted pattern in the owning feature's domain/API documentation: current problem, participants, rejected simpler alternative and behavioral proof. [Patterns](design-patterns.md), [naming](naming.md) and [PHP/Laravel conventions](conventions.md) complete this guide. Product scope still belongs to [the MVP](../../../mvp.md).
