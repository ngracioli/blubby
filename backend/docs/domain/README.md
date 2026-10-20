# Domain rules

Owns financial invariants, terminology, examples, and acceptance scenarios. [The MVP](../../../mvp.md) remains the current source of product decisions.

Document operations for accounts and adjustments, transfers, income and expenses, card purchases, installments, bill payments, recurrence posting and cancellation, and monthly projections.

Preserve these invariants:

- User data is isolated.
- A transfer preserves the consolidated balance and is not income or expense.
- Opening balances and adjustments do not increase income or expense totals.
- Paying a bill reduces the selected account balance without counting purchase expenses twice.
- Completing a forecast entry does not create a second financial transaction.
- Canceling a posted occurrence reverses its effect and preserves its canceled status.
- Projections use registered data only and carry each projected closing balance into the next month.

Do not decide unresolved billing exceptions through implementation examples. In particular, cancellation after bill payment needs an explicit product rule before implementation.
