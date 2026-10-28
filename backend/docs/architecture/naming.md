# Backend naming conventions

## Required rules

- Use English identifiers and meaningful domain subjects. `Feature` and `Variant` in templates are placeholders.
- Use StudlyCase for PHP classes and enums, camelCase for methods and variables, and UPPER_SNAKE_CASE for constants.
- PHP filenames match their primary class, such as `MonthlyPlanningStrategy.php`. Follow Laravel conventions for namespaces and directory placement; documentation files and directories use kebab-case.
- For custom pattern interfaces, use `I<Subject><Pattern>`; for implementations, use `<Variant><Subject><Pattern>`. Omit the variant when unnecessary.
- Abstract bases use `Abstract<Subject><Pattern>`. Use `Handler` for Chain of Responsibility participants.
- Role suffixes must describe actual responsibilities. Avoid `Manager`, `Helper`, `Impl`, and numbered implementation names when a domain role can be named.
- Framework names and externally defined interfaces keep their original spelling. Do not create an `I` wrapper solely to rename a Laravel contract.
- Ordinary types keep their real roles: `Account`, `CreateAccountRequest`, `AccountResponse`, `AccountController`, and `AccountRepository`. Do not label every type with a GoF pattern.

## Laravel participants and placement

The [architecture directory reference](overview.md#directory-reference) is authoritative for locations. Create a directory only for its first actual participant. Business participants use `App\Domain\<Domain>\<Role>`; HTTP and framework participants use the matching domain segment in their conventional roots. Keep the existing Action suffix; domain grouping does not require renaming every role.

| Role | Example | Public operation |
| --- | --- | --- |
| Use-case Action | `Domain/Transfers/Actions/TransferFundsAction.php` | `handle(...)` with typed actor and inputs |
| Cohesive service | `Domain/Transactions/Services/InstallmentAllocator.php` | Domain verb such as `allocate(...)`; no mandatory `Service` suffix |
| Read query | `Domain/Planning/Queries/MonthlyProjectionQuery.php` | `get(...)` or `paginate(...)`, matching its actual return |
| DTO | `Domain/Transfers/Data/TransferFundsData.php` | Typed promoted readonly properties; no persistence |
| Value object | `Domain/Transactions/ValueObjects/Money.php` | Named invariant-preserving operations |
| Event / listener / Job | `FundsTransferred` / `SendTransferReceipt` / `GenerateMonthlyExport` | Native Laravel dispatch and `handle()` conventions |
| Request / Resource / Policy | `TransferFundsRequest` / `TransferResource` / `AccountPolicy` | Framework method names |
| Exception / test | `InsufficientFundsException` / `TransferFundsTest` | Observable behavior names for test methods |

No custom interface is required for these examples. Preserve the existing project-specific `I` prefix for custom pattern contracts when a contract is justified; it is a local convention, not Laravel or PHP guidance. Database tables/columns use snake_case, plural tables and singular foreign keys such as `accounts` and `account_id`; migrations use Artisan timestamped snake_case names. Use resource-oriented plural URL segments and named routes. Preserve existing unversioned API routes; versioning is an explicit compatibility decision, not an automatic new prefix.

Namespaces follow paths without extra Composer mappings. Hypothetical examples:

```php
use App\Domain\Transfers\Actions\TransferFundsAction;
use App\Http\Requests\Api\Transfers\TransferFundsRequest;
use App\Http\Resources\Api\Transfers\TransferResource;
use App\Models\Account;
use App\Policies\AccountPolicy;
```

`app/Http/Controllers/Api/Transfers/TransferController.php` declares `App\Http\Controllers\Api\Transfers`; a plain Action declares `App\Domain\Transfers\Actions`. A domain-specific exception belongs to `Domain/<Domain>/Exceptions`, and a justified internal contract to `Domain/<Domain>/Contracts`. Keep protocol adapters in the owner's `Domain/<Domain>/Integrations/<Provider>`.

Use `App\Jobs\Transfers`, `App\Events\Transfers` and `App\Listeners\Transfers` for that workflow's framework participants; they are conditional, not three mandatory files. Policies remain flat by Model name initially. Tests use `Tests\Feature\Api\Transfers` for HTTP, `Tests\Feature\Domain\Transfers` for DB-backed use cases, and `Tests\Unit\Domain\Transfers` for pure rules. A Model method's domain ownership must be documented even though its namespace stays `App\Models`.

## GoF participant examples

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
| Template Method | `AbstractStatementImportTemplate` | `CsvStatementImportTemplate` |

A Decorator implements the wrapped contract, not a redundant `IDecorator`. Factory Method uses an abstract creator with overridable creation; Abstract Factory creates related product families. Both use the `Factory` suffix because their roles are creation, and their structure establishes the specific pattern.

Template Method uses the `Template` suffix for a fixed workflow with overridable steps; no additional interface is required. Use inheritance only when real workflow variants justify it.

These are project conventions, not names required by Refactoring.Guru. Naming a deferred pattern does not authorize implementing it. Keep role vocabulary aligned with the [mobile](../../../mobile/docs/architecture/naming.md).
