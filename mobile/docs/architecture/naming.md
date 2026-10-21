# Mobile naming conventions

## Required rules

- Use English identifiers and descriptive domain names. `Feature` and `Variant` in templates are placeholders, not literal prefixes.
- Use PascalCase for components, classes, interfaces, and types; camelCase for functions, methods, properties, and variables.
- Use kebab-case for custom TypeScript files and directories, such as `monthly-planning-strategy.ts` and `account-card.tsx`. Preserve Expo route notation and framework filenames such as `_layout.tsx`.
- Use `use` plus a meaningful PascalCase subject for hook function identifiers (`useMonthlyPlanning`), and kebab-case with `use-` prefix for hook filenames (`use-monthly-planning.ts`).

## File and directory naming conventions

| Entity | Location | Directory convention | File convention | Example |
| --- | --- | --- | --- | --- |
| Route / Layout | `src/app/` | `(group)/` or `[param]/` | `_layout.tsx`, `index.tsx`, `[id].tsx` | `src/app/(auth)/login.tsx` |
| Feature module | `src/features/` | `kebab-case` | `index.ts`, `README.md` | `src/features/monthly-planning/` |
| Feature component | `src/features/<feat>/components/` | `components/` | `kebab-case.tsx` | `summary-row.tsx` |
| Shared UI primitive | `src/shared/components/` | `components/` | `kebab-case.tsx` | `screen-container.tsx`, `button.tsx` |
| Custom hook | `hooks/` | `hooks/` | `use-kebab-case.ts` | `use-session.ts`, `use-monthly-planning.ts` |
| API / Service | `services/` | `services/` | `kebab-case-api.ts` or `-service.ts` | `planning-api.ts`, `auth-service.ts` |
| Types & models | Feature root or `types/` | n/a | `types.ts` or `<subject>-types.ts` | `src/features/entries/types.ts` |
| Design tokens | `src/shared/theme/` | `theme/` | `kebab-case.ts` | `colors.ts`, `typography.ts`, `spacing.ts` |
| Static assets | `src/assets/` | `assets/<category>/` | `kebab-case.png`, `index.ts` | `src/assets/logo/blubby-logo-horizontal.png` |

- For custom pattern interfaces, use `I<Subject><Pattern>`; for implementations, use `<Variant><Subject><Pattern>`. Omit the variant when no distinction is needed.
- For abstract pattern base classes, use `Abstract<Subject><Pattern>`.
- Match the role suffix to the actual pattern: `Strategy`, `Factory`, `Builder`, `Adapter`, `Facade`, `Decorator`, `Observer`, `State`, or `Command`. Use `Handler` for Chain of Responsibility participants if that pattern is later justified.
- Use complete subjects: `IEntryDisplayStrategy`, `CompactEntryDisplayStrategy`, and `entry-display-strategy.ts`. Avoid names such as `BaseManager`, `FeatureHelper`, or `StrategyImpl`.
- Use `<Component>Props` for React props and domain names for data types. The `I` prefix applies to custom behavioral contracts; do not turn every object shape into a pattern interface.
- Preserve external and framework-owned names. This convention does not rename dependencies or require wrappers around them.

## Pattern participants

| Pattern | Contract | Implementation example |
| --- | --- | --- |
| Strategy | `IEntryDisplayStrategy` | `CompactEntryDisplayStrategy` |
| Factory Method | `AbstractEntryViewFactory` | `ExpenseEntryViewFactory` |
| Abstract Factory | `IPlatformUiFactory` | `AndroidPlatformUiFactory` |
| Builder | `IEntryRequestBuilder` | `ExpenseEntryRequestBuilder` |
| Adapter | `IAuthenticationAdapter` | `GoogleAuthenticationAdapter` |
| Facade | `IMonthlyPlanningFacade` | `MonthlyPlanningFacade` |
| Decorator | `IPlanningReader` | `TimedPlanningReaderDecorator` |
| Observer | `ISessionObserver` | `SessionExpiryObserver` |
| State | `IEntryFormState` | `SubmittingEntryFormState` |
| Command | `IEntryCommand` | `SubmitExpenseEntryCommand` |

A Decorator implements the wrapped contract; it does not require a separate `IDecorator` contract. A Factory Method base declares the creation method; a factory function alone does not establish that pattern. Abstract Factory appears here to define naming if eventually adopted, not to approve its use.

These are project conventions, not names required by Refactoring.Guru. Follow the same role vocabulary as the [backend](../../../backend/docs/architecture/naming.md).
