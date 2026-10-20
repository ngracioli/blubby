# Backend naming conventions

## Required rules

- Use English identifiers and meaningful domain subjects. `Feature` and `Variant` in templates are placeholders.
- Use PascalCase for Java classes, interfaces, records, and enums; camelCase for methods, fields, and parameters; UPPER_SNAKE_CASE for constants.
- Java source filenames match their public type, such as `MonthlyPlanningStrategy.java`. Java packages use lowercase names; documentation files and directories use kebab-case.
- For custom pattern interfaces, use `I<Subject><Pattern>`; for implementations, use `<Variant><Subject><Pattern>`. Omit the variant when unnecessary.
- Abstract bases use `Abstract<Subject><Pattern>`. Use `Handler` for Chain of Responsibility participants.
- Role suffixes must describe actual responsibilities. Avoid `Manager`, `Helper`, `Impl`, and numbered implementation names when a domain role can be named.
- Framework names and externally defined interfaces keep their original spelling. Do not create an `I` wrapper solely to rename a Spring interface.
- Ordinary types keep their real roles: `Account`, `CreateAccountRequest`, `AccountResponse`, `AccountController`, and `AccountRepository`. Do not label every type with a GoF pattern.

## Pattern participants

| Pattern | Contract | Implementation example |
| --- | --- | --- |
| Strategy | `IRecurrenceStrategy` | `MonthlyRecurrenceStrategy` |
| Factory Method | `AbstractEntryFactory` | `ExpenseEntryFactory` |
| Abstract Factory | `IEntryFamilyFactory` | `RecurringEntryFamilyFactory` |
| Builder | `IInstallmentScheduleBuilder` | `CardInstallmentScheduleBuilder` |
| Adapter | `IIdentityProviderAdapter` | `GoogleIdentityProviderAdapter` |
| Facade | `IMonthlyPlanningFacade` | `MonthlyPlanningFacade` |
| Decorator | `IProjectionReader` | `TimedProjectionReaderDecorator` |
| Observer | `IEntryObserver` | `PlanningRefreshEntryObserver` |
| State | `IBillState` | `PaidBillState` |
| Command | `IAccountCommand` | `TransferAccountCommand` |
| Chain of Responsibility | `IEntryValidationHandler` | `AmountEntryValidationHandler` |

A Decorator implements the wrapped contract, not a redundant `IDecorator`. Factory Method uses an abstract creator with overridable creation; Abstract Factory creates related product families. Both use the `Factory` suffix because their roles are creation, and their structure establishes the specific pattern.

These are project conventions, not names required by Refactoring.Guru. Naming a deferred pattern does not authorize implementing it. Keep role vocabulary aligned with the [frontend](../../../frontend/docs/architecture/naming.md).
