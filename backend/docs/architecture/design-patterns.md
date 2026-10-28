# Backend design patterns

## Selection policy

This contextual shortlist comes from the [Refactoring.Guru catalog](https://refactoring.guru/design-patterns). It is not a popularity ranking, a mandatory set of classes, or a reason to expand the MVP.

Before adoption, document the current problem, participating modules, actual variants or consumers, rejected simpler solution, and behavioral verification. Use the [naming conventions](naming.md) for every custom participant. Keep patterns in their business modules and reuse Spring capabilities before building equivalent infrastructure.

## First candidates

### Strategy — behavioral

- **Purpose:** substitute algorithms behind a shared contract.
- **Blubby case:** recurrence date calculation if multiple supported recurrence rules actually require different algorithms.
- **Names:** `IRecurrenceStrategy`, `MonthlyRecurrenceStrategy`; other variants only when supported.
- **Guardrails:** require at least two current algorithms used interchangeably. Prefer one function or a short conditional for one rule. Do not invent recurrence frequencies absent from product decisions.
- **Check:** shared date semantics, month boundaries, and rounding where relevant.
- **Source:** [Strategy](https://refactoring.guru/design-patterns/strategy).

### Adapter — structural

- **Purpose:** translate an external interface into an application contract.
- **Blubby case:** integrate an identity provider after deciding which responsibilities belong to the backend.
- **Names:** `IIdentityProviderAdapter`, `GoogleIdentityProviderAdapter`, `AppleIdentityProviderAdapter`.
- **Guardrails:** use a real protocol or provider mismatch. Prefer existing security integrations. An adapter must validate provider responses; it cannot replace authorization or expose credentials through errors.
- **Check:** invalid credentials, provider failures, and identity normalization follow the same contract.
- **Source:** [Adapter](https://refactoring.guru/design-patterns/adapter).

### Facade — structural

- **Purpose:** expose a small interface over coordinated subsystem behavior.
- **Blubby case:** monthly planning combines account balances, pending entries, and bills.
- **Names:** `IMonthlyPlanningFacade`, `MonthlyPlanningFacade`.
- **Guardrails:** adopt when callers need a cohesive operation over several modules. Prefer an application service for straightforward orchestration. Do not build a facade that owns every financial responsibility or simply forwards a method.
- **Check:** projection behavior matches the MVP without counting completed entries or card purchases twice.
- **Source:** [Facade](https://refactoring.guru/design-patterns/facade).

## Conditional candidates

| Pattern | Use when | Names | Guardrail and simpler alternative |
| --- | --- | --- | --- |
| [Factory Method](https://refactoring.guru/design-patterns/factory-method) | Creator subclasses must specialize object creation within a shared workflow | `AbstractEntryFactory`, `ExpenseEntryFactory` | Prefer constructors or named creation functions. Dependency injection and a static `create` method do not establish Factory Method. |
| [Builder](https://refactoring.guru/design-patterns/builder) | Constructing a complex installment schedule needs deliberate steps and final validation | `IInstallmentScheduleBuilder`, `CardInstallmentScheduleBuilder` | Prefer a pure calculation function or constructor. Do not publish incomplete schedules or bypass financial invariants. |
| [Decorator](https://refactoring.guru/design-patterns/decorator) | Optional instrumentation extends an existing reader without changing its contract | `IProjectionReader`, `TimedProjectionReaderDecorator` | Prefer framework instrumentation. Preserve errors and transaction semantics. PHP attributes are not automatically GoF Decorator. |
| [State](https://refactoring.guru/design-patterns/state) | Behavior and legal operations vary substantially across bill states | `IBillState`, `PaidBillState` | Prefer an enum and explicit transition validation first. Do not invent billing states, partial payments, or cancellation rules left unresolved by the MVP. |
| [Command](https://refactoring.guru/design-patterns/command) | Requests must be executable objects with a real shared dispatch or scheduling requirement | `IAccountCommand`, `TransferAccountCommand` | Prefer an application method for a synchronous operation. A request DTO is not automatically Command. A reversal is a financial operation, not generic object undo. |
| [Observer](https://refactoring.guru/design-patterns/observer) | Independent consumers react to an event after its owning operation | `IEntryObserver`, `PlanningRefreshEntryObserver`; subject: `EntryPublisher` | Prefer direct calls for a single required dependency. Do not move atomic balance changes into unreliable asynchronous listeners. Define delivery and failure semantics first. |
| [Chain of Responsibility](https://refactoring.guru/design-patterns/chain-of-responsibility) | Independently reusable handlers need explicit ordering and stop/continue behavior | `IEntryValidationHandler`, `AmountEntryValidationHandler` | Prefer Laravel validation and middleware. Do not build another security pipeline or a chain for a few ordinary field checks. |

Verification must demonstrate the claimed benefit: creation variation, completed construction, wrapper compatibility, legal transitions, execution semantics, subscriber failure handling, or handler ordering. Test financial results through the owning application operation as well as focused calculations.

## Patterns without a current justification

- **Abstract Factory:** use only if multiple real families of related products must be created consistently. Income and expense types alone do not establish such families. Reserve `IEntryFamilyFactory` and `<Variant>EntryFamilyFactory` if that need arises.
- **Singleton:** use container-managed lifecycles instead of custom global instances. A singleton-scoped Laravel service must not hold per-user mutable financial state.
- **Prototype, Bridge, Flyweight, Composite, Iterator, Mediator, Memento, Template Method, Visitor:** no current backend requirement justifies custom implementations. Reconsider individually with adoption evidence.

Repository, DTO, dependency injection, and transaction boundaries remain important architectural techniques, but they are not entries in this GoF catalog. Do not force them into GoF names or duplicate capabilities already provided by a chosen persistence framework.
