# Frontend design patterns

## Selection policy

This is a contextual shortlist from the [Refactoring.Guru catalog](https://refactoring.guru/design-patterns), not a popularity ranking or an implementation checklist. Start with React composition, props, hooks, plain functions, and existing platform capabilities. Consider a pattern only when a current problem needs it.

For every adoption, record the problem, participating modules, concrete implementations or consumers, simpler alternative rejected, and test that proves its behavior. Keep that explanation with the feature documentation. Apply the [naming conventions](naming.md) to all custom participants. Names must reveal an existing role, not manufacture one.

## First candidates

### Adapter — structural

- **Purpose:** translate an incompatible external interface.
- **Blubby case:** normalize different authentication SDK responses into the app's session contract once those integrations are selected.
- **Names:** `IAuthenticationAdapter`, `GoogleAuthenticationAdapter`, `AppleAuthenticationAdapter`.
- **Guardrails:** use at a real integration seam. Prefer a mapping function for a single data conversion; do not wrap every library or HTTP call. Never embed authorization policy in this translation.
- **Check:** provider failures and session data satisfy the same contract.
- **Source:** [Adapter](https://refactoring.guru/design-patterns/adapter).

### Facade — structural

- **Purpose:** expose a small interface over several coordinated operations.
- **Blubby case:** hide repeated orchestration needed to load a monthly planning screen.
- **Names:** `IMonthlyPlanningFacade`, `MonthlyPlanningFacade`.
- **Guardrails:** use only when orchestration would otherwise spread across consumers. One forwarded request does not need a facade. Keep authoritative financial calculations on the backend.
- **Check:** consumers can handle partial loading and failure through the facade's documented contract.
- **Source:** [Facade](https://refactoring.guru/design-patterns/facade).

### Strategy — behavioral

- **Purpose:** substitute different algorithms through one contract.
- **Blubby case:** interchangeable entry presentation policies, if concrete variations actually emerge.
- **Names:** `IEntryDisplayStrategy`, `CompactEntryDisplayStrategy`, `DetailedEntryDisplayStrategy`.
- **Guardrails:** require at least two current algorithms selected by the same consumer. Prefer a conditional or component variants for small visual differences. A strategy may be a typed function; classes are optional.
- **Check:** each implementation meets the same input/output and error contract.
- **Source:** [Strategy](https://refactoring.guru/design-patterns/strategy).

### Observer — behavioral

- **Purpose:** notify multiple subscribers when something changes.
- **Blubby case:** session lifecycle subscriptions when an authentication integration requires them.
- **Names:** `ISessionObserver`, `SessionExpiryObserver`; subject: `SessionPublisher`.
- **Guardrails:** prefer existing React, SDK, or cache subscriptions. Do not introduce a global event bus for ordinary props or server data. Unsubscribe on disposal and clear user-specific cached data when sessions change.
- **Check:** subscriptions do not leak, duplicate notifications, or retain another user's state.
- **Source:** [Observer](https://refactoring.guru/design-patterns/observer).

## Conditional candidates

| Pattern | Use when | Names | Guardrail and simpler alternative |
| --- | --- | --- | --- |
| [Factory Method](https://refactoring.guru/design-patterns/factory-method) | Creator subclasses must vary the produced object while sharing creation workflow | `AbstractEntryViewFactory`, `ExpenseEntryViewFactory` | Prefer functions, component composition, or a lookup map. A function named `createEntry` is not automatically Factory Method. |
| [Builder](https://refactoring.guru/design-patterns/builder) | Constructing a genuinely complex request requires meaningful steps and validation | `IEntryRequestBuilder`, `ExpenseEntryRequestBuilder` | Prefer an object literal or function. Do not create builders for small forms or serialize invalid intermediate state. |
| [Decorator](https://refactoring.guru/design-patterns/decorator) | Optional behavior wraps a reader while preserving its contract | `IPlanningReader`, `TimedPlanningReaderDecorator` | Prefer existing instrumentation. A layout wrapper, hook, or TypeScript decorator syntax is not automatically GoF Decorator. |
| [State](https://refactoring.guru/design-patterns/state) | Form behavior and legal transitions become substantially different by state | `IEntryFormState`, `SubmittingEntryFormState` | Prefer a discriminated union and reducer. `useState` alone is not the State pattern. Server rules still govern financial transitions. |
| [Command](https://refactoring.guru/design-patterns/command) | Actions need a common execution contract and an actual scheduling or orchestration requirement | `IEntryCommand`, `SubmitExpenseEntryCommand` | Prefer an event handler and mutation function. Do not infer offline queues, automatic write retries, or financial undo from this pattern. |

Test the behavior that justifies each conditional pattern: creation variation, completed construction, contract preservation, legal transitions, or execution semantics. Do not add a separate abstraction solely to make mocking easier.

## Patterns without a current justification

- **Abstract Factory:** requires related product families. Android and iOS support alone does not justify custom widget factories; React Native already addresses platform rendering.
- **Singleton:** do not use custom global mutable instances for sessions, forms, or financial data.
- **Prototype, Bridge, Flyweight, Composite, Iterator, Mediator, Memento, Template Method, Visitor, Chain of Responsibility:** no current frontend requirement establishes their need. Standard collections, React composition, reducers, and platform tooling remain the baseline.

Reconsider a deferred pattern only with a concrete requirement and the same adoption evidence. Document its naming before implementation using the shared subject-and-role convention.
