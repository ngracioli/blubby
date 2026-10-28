# Backend pattern selection

## Decision procedure

Start with the current problem, not a pattern name. Inspect callers and existing Laravel facilities. Try a function, direct Eloquent query or native framework composition first. Adopt a pattern only when its participants remove demonstrated complexity. Record the problem, concrete participants, rejected simpler alternative and behavioral proof in the owning feature documentation. Keep custom business participants in the owning `app/Domain/<Domain>` role/subcapability; framework Jobs/Events/Listeners remain domain-grouped in native roots. No global `Patterns` folder or speculative interface. Domain folders are organization, not automatically DDD aggregates or a reason for cross-domain contracts. [Architecture](overview.md) owns extraction gates; [naming](naming.md) owns names and locations.

GoF patterns describe object collaboration. MVC/modular monolith/Clean/Hexagonal describe architecture; Service Layer and Repository are enterprise/application patterns. Action is a single-operation application convention; DTO and Value Object describe data responsibilities; DI is a dependency technique. Domain Events describe business facts, and Pipeline composes processing steps. These labels are not interchangeable. Laravel Facades are container proxies, not automatically a custom GoF Facade. Eloquent factories are test-data facilities, not proof of Factory Method.

The examples below are hypothetical, not approved modules, dependencies or business rules. Each candidate includes its mechanism, trigger, simpler/native alternative and proof/cost. Use only the relevant row/section. The [Refactoring.Guru catalog](https://refactoring.guru/design-patterns) defines GoF structure, not Laravel policy.

## GoF candidates

### Strategy — behavioral

**Problem/mechanism:** several actual algorithms must be interchangeable behind the same operation. Example: two supported recurrence calculators implementing `IRecurrenceStrategy::nextDate(...)`, selected by an explicit supported recurrence kind and injected into the caller. At least two real interchangeable algorithms are required for this project.

**Prefer/avoid:** one rule or a short stable conditional needs a function, not a resolver hierarchy. Laravel container binding supplies injection; it does not supply the algorithms. Keep participants within the owning domain service capability and use immutable dates. **Proof/cost:** run shared contract cases for each variant plus month/timezone boundaries; selection and extra classes add maintenance, not database performance. [Definition](https://refactoring.guru/design-patterns/strategy).

### Adapter — structural

**Problem/mechanism:** translate one real external protocol into an application operation. Example: `GoogleIdentityProviderAdapter implements IIdentityProviderAdapter`, using Laravel HTTP client and validating issuer/audience/response data according to the selected integration. One actual incompatible provider is enough to justify an Adapter; provider support remains unimplemented.

**Prefer/avoid:** use a supported native/library integration when it already satisfies the contract; a trivial internal call needs no adapter. Do not reimplement OAuth verification from this sketch. **Proof/cost:** `Http::fake()` malformed/expired/error responses and test normalization without secrets; timeouts, remote latency and failure semantics remain the adapter's responsibility. An adapter does not authorize account linking. [Definition](https://refactoring.guru/design-patterns/adapter).

### Decorator — structural

**Problem/mechanism:** add optional behavior while preserving the wrapped contract. Example: `TimedProjectionReaderDecorator implements IProjectionReader`, delegates to another `IProjectionReader`, records timing and preserves result/errors.

**Prefer/avoid:** Laravel logging/query instrumentation often suffices. Do not add a wrapper for one log statement or cache a user-specific result without isolation/invalidation requirements. **Proof/cost:** run the same contract tests wrapped/unwrapped, including exceptions; wrappers add call depth and cache decorators add staleness risk. PHP attributes are not GoF decorators. [Definition](https://refactoring.guru/design-patterns/decorator).

### Observer — behavioral

**Problem/mechanism:** independent consumers react to a published fact. Example: native `FundsTransferred` event and `SendTransferReceipt` listener after commit when a receipt is actually required. No custom subject/subscriber framework or `IObserver` wrapper around Laravel listeners.

**Prefer/avoid:** direct calls for one required ordered collaborator. Model observers suit persistence lifecycle hooks, not hidden atomic financial workflows; bulk model operations may skip hooks. **Proof/cost:** prove event decision and listener execution, rollback suppression and retry safety. Asynchronous consumers trade immediate consistency for recoverability and operational load; synchronous listeners may fail the caller. [Definition](https://refactoring.guru/design-patterns/observer).

### Command — behavioral

**Problem/mechanism:** make an executable request an object for dispatch/deferred execution. Example: `GenerateMonthlyExport` Job implements native `ShouldQueue`, carries trusted identifiers and invokes a focused operation in `handle()`.

**Prefer/avoid:** call a synchronous Action directly when dispatch adds nothing. Artisan Commands are CLI entry points; DTOs carry data but are not executable. No new command bus, generic undo or interface solely for a Job. **Proof/cost:** test dispatch, execution, retries and authorization context; queued model restoration may observe newer data, so define snapshot versus current-state semantics. Jobs add serialization, latency and worker costs. [Definition](https://refactoring.guru/design-patterns/command).

### Chain of Responsibility — behavioral; Pipeline — composition technique

**Problem/mechanism:** ordered reusable steps may continue or stop processing. Example: a real import Pipeline through `NormalizeImportRow`, `ValidateImportRow`, `PersistImportRow`, each accepting payload and `$next`; the validation stage rejects before persistence.

**Prefer/avoid:** use Laravel validation for field rules, middleware for HTTP cross-cutting concerns, and native `Illuminate\Pipeline\Pipeline` for a justified workflow. A few fixed calls need no chain. Laravel Pipeline can run all stages; define stop/continue/error semantics rather than assume every pipeline is the exact GoF chain. **Proof/cost:** test order, early rejection and partial failure; many tiny stages complicate tracing and transaction ownership. [Definition](https://refactoring.guru/design-patterns/chain-of-responsibility).

### State — behavioral

**Problem/mechanism:** legal operations and substantial behavior change with state, causing repeated branching across methods. Example: `PaidBillState implements IBillState` rejects operations that an `OpenBillState` permits, with transitions owned by the bill operation.

**Prefer/avoid:** begin with a backed enum and explicit transition validation. A status label alone does not justify classes; no new bill states or unresolved cancellation rules from an example. Laravel enum casts store state but do not enforce transitions. **Proof/cost:** test every permitted/forbidden transition and concurrent attempts. Additional classes disperse transition logic unless ownership stays explicit; no inherent query benefit. [Definition](https://refactoring.guru/design-patterns/state).

### Factory Method — creational

**Problem/mechanism:** creator subclasses vary one creation step in an existing shared workflow. Example: `AbstractStatementExportFactory::export()` invokes protected `createWriter()`; `CsvStatementExportFactory` supplies its writer while the workflow remains shared.

**Prefer/avoid:** a constructor, named creation method or container resolution suffices without creator subclasses. A static `create()` is not this pattern. Native Laravel factories supply fixtures; reuse those for tests. **Proof/cost:** prove each creator produces the required product and preserves workflow failures; inheritance couples subclass changes to the base and adds navigation. [Definition](https://refactoring.guru/design-patterns/factory-method).

### Abstract Factory — creational

**Problem/mechanism:** several supported families must create mutually compatible products. Example: a selected provider's `IStatementFamilyFactory` creates its parser and verifier as a matching pair; a second real provider family requires different compatible products.

**Prefer/avoid:** direct injection of the one parser/verifier pair is simpler today. Income and expense objects alone do not establish families. Laravel container bindings can select a family but do not create the need. **Proof/cost:** test compatible family behavior and reject mixed products; this multiplies contracts/classes and may multiply remote setup. Deferred until actual families exist. [Definition](https://refactoring.guru/design-patterns/abstract-factory).

### Builder — creational

**Problem/mechanism:** complex construction needs ordered steps and different representations with final validation. Example: `CardInstallmentScheduleBuilder` collects schedule inputs, constructs allocations and publishes a valid completed schedule via `build()`.

**Prefer/avoid:** a pure `InstallmentAllocator::allocate(...)` returning the complete result is preferable for one calculation. Eloquent's query builder already constructs queries; do not wrap it to demonstrate GoF Builder. **Proof/cost:** assert no partial invalid result escapes and allocation preserves totals; mutable builders need reset/reuse tests and can leak state across workers. More construction objects do not make arithmetic faster. [Definition](https://refactoring.guru/design-patterns/builder).

### Template Method — behavioral

**Problem/mechanism:** real workflows share a fixed sequence while subclasses deliberately specialize steps. Example: `AbstractStatementImportTemplate::import()` owns read/validate/store order, with CSV and another supported format overriding parsing only.

**Prefer/avoid:** prefer composition with a parser dependency if only parsing varies (Strategy/Adapter), or one function for one format. Reuse Laravel console/job lifecycle methods without duplicating their lifecycle framework. Do not create an abstract workflow for a future second format. **Proof/cost:** shared workflow tests plus subclass parsing/error tests; inheritance restricts variation and can hide overridden behavior, with no automatic throughput gain. [Definition](https://refactoring.guru/design-patterns/template-method).

### Facade — structural, conditional

**Problem/mechanism:** a cohesive operation hides coordination across an already complex subsystem. Example: a monthly planning entry point combines real balances, pending entries and bills under one public projection operation.

**Prefer/avoid:** a focused Query or application Action usually suffices. Do not introduce a Facade plus Service plus Action forwarding the same call, or mistake Laravel's `DB` facade for a requirement to build a custom facade. **Proof/cost:** prove projection composition and no double counting; hidden queries can increase latency, so observe the complete operation. [Definition](https://refactoring.guru/design-patterns/facade).

## Application and data techniques

These examples complement the authoritative [responsibility table](overview.md#responsibility-decisions); they do not establish a second set of extraction rules.

| Technique / mechanism | Actual trigger and example | Native/simpler alternative; avoid | Proof and maintenance/performance cost |
| --- | --- | --- | --- |
| Dependency Injection: caller receives a capability | `TransferFundsAction` receives an actually needed allocator via constructor | Laravel container resolves concrete classes; no manual service locator or needless binding | Exercise caller behavior with real pure dependencies; bind only actual contracts. Singleton mutable state can leak between requests/jobs. |
| Action: one application operation | `TransferFundsAction::handle(actor, ...)` coordinates multi-record atomicity | Direct validated single-model update for trivial CRUD; no mandatory interface/base class/package | Feature tests prove authorization, rollback and replay. Extra pass-through files add navigation, not isolation. |
| Service Layer: cohesive application/domain capability | `InstallmentAllocator::allocate(...)` used by two real operations | Local function if one use; do not build generic CRUD service buckets | Pure edge-case tests plus caller integration; duplicated rules disappear without per-model service overhead. |
| Repository: collection-like persistence contract | `ILegacyEntryRepository` hides a real legacy store with domain-specific retrieval | Direct Eloquent/scopes; never wrap every ORM method or pass through Builder | Contract tests against real store plus failure translation; avoid duplicate caching/query APIs and fake repositories that hide DB defects. |
| Specification: composable business predicate | `EligibleForPostingSpecification::isSatisfiedBy(entry)` only when the same compound rule is reused/composed | Boolean method, query scope or validation Rule. In-memory and SQL predicates are not automatically equivalent | Test composition and null/date semantics; test SQL equivalence if both forms exist. Loading rows to filter in PHP can be expensive. |
| DTO: typed data without identity/behavior | `TransferFundsData` carries a reused payload across HTTP and CLI | Few typed arguments or a local validated array; Form Request remains HTTP-only | Test meaningful normalization/boundary conversion, not property assignment. Mapping cost is justified only by stable structure. |
| Value Object: immutable value with invariants | `Money` validates supported units/scale and preserves exact arithmetic | An exact primitive when no repeated invariant exists; no float conversion | Test equality, arithmetic bounds, rounding and allocation. `readonly` is shallow; encapsulated mutable members defeat snapshots. |
| Domain Event: named completed business fact | `FundsTransferred` for actual independent reactions | Direct call if sequencing/return result is required; Laravel events, not an event-sourcing framework | Test commit timing, consumer failures and replay. Delivery guarantees and consistency must be explicit; see architecture/operations. |

### Minimal typed-data sketch

Only after a stable cross-entry-point payload is justified, a DTO can remain ordinary PHP:

```php
final readonly class TransferFundsData
{
    public function __construct(
        public int $sourceAccountId,
        public int $destinationAccountId,
        public string $amount,
        public string $replayKey,
    ) {}
}
```

This is illustrative and assumes an agreed exact decimal-string contract. A DTO does not validate amount scale, authorize account IDs or guarantee replay safety. Those contracts remain with validation, the owning operation and database constraints. Do not copy this type before product/schema decisions. Full hypothetical responsibility flow: [transfer example](decision.md#hypothetical-future-example-transfer-funds).

## Not justified now

No custom Singleton (prefer container lifecycles), Prototype, Bridge, Flyweight, Composite, Iterator, Mediator, Memento or Visitor is justified by current backend code. Use native iterators/collections and framework capabilities first. Reassess each only for a concrete problem; the catalog is not a class-generation backlog.
