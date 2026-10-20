# Blubby — MVP

## Status and objective

Preliminary specification of product decisions already agreed upon. Remaining details are identified at the end; this document does not define implementation.

Blubby will be a personal finance app for organizing the current month and forecasting upcoming months. Monthly planning will be the center of the experience, summarizing balances, income, expenses, card bills, and financial projections.

The creator will run the first test on their own iPhone. The app will also be available to others, supporting Android and iOS.

## Confirmed scope

### Access and privacy

- Login and registration from the MVP.
- Authentication through email and password or Google on both platforms, and Apple on iOS only.
- Each user can access only their own financial data.
- After login or registration, the user opens the current month's planning directly. On first use, guidance to register the first account or wallet appears in this context, without a mandatory wizard before planning.

### Manual entry

- All financial data will be entered manually.
- The MVP will not include banking integration, Open Finance, or AI.

### Expenses

- Register one-off, recurring, and installment expenses.
- Fields: name, image or icon, category, amount, description, and date.
- Identify an expense through custom entry or by selecting an app from Google Play or the App Store to fill in its name and icon automatically.
- Selecting an app does not determine expense frequency: Spotify may be monthly, while an Uber ride may be one-off.
- The store lookup mechanism and its feasibility still require validation. The proposal does not include reading installed apps, prices, or transactions.

### Income

- Record salary and other income.
- Recurring income and forecast income contribute to monthly projections.
- Distinguish forecast amounts from amounts already received.

### Accounts and wallets

- Register an account or wallet with an opening balance.
- Completed inflows and outflows update its balance.
- The balance can be edited at any time, recorded as an adjustment rather than income or expense.
- Transfers between accounts and wallets update the source and destination, preserve the total, and do not count as income or expenses.

### Cards, installments, and bills

- Register cards with a closing day and a payment due day.
- Link installment expenses to a card, distributing installments across their respective bills.
- Example: a R$ 1,200 purchase in six installments generates R$ 200 per month.
- Purchases before the closing day enter the bill closing in that cycle. Purchases on or after the closing day enter the next cycle.
- The first installment follows this rule; subsequent installments enter consecutive monthly bills. The purchase date and first bill may belong to different periods.
- When registering a purchase, the user can manually adjust the first bill suggested by the automatic rule without changing the actual purchase date. Subsequent installments follow the selected first bill in consecutive months.
- Example: for a card closing on the 10th and due on the 17th, a purchase on October 9 enters the bill due October 17; purchases on October 10 or 11 enter the bill due November 17.
- The purchase is counted once as an expense. Paying the bill reduces the balance of the account used without creating a second expense.
- Bill payments are recorded manually for the full amount. The user chooses the source account and payment date; Blubby reduces that account's balance by the bill total and marks it as paid.
- Automatic bill payment, partial payments, interest, and financing are outside the MVP. Credit limits are not defined in scope.

### Recurrences and cancellation

- Recurring expenses and income are automatically posted on their registered dates.
- When an occurrence is posted, an expense from an account or wallet reduces the balance, and received income increases it. A recurring card expense enters the bill; the account balance is reduced only when that bill is paid.
- The user can cancel an occurrence that has already been posted, reversing its financial effect.
- The canceled occurrence retains that state, and subsequent occurrences are preserved.
- Ending a recurrence prevents its future occurrences.

### Monthly planning

- A central monthly view with balance, income, expenses, card bills, and projections for the current month and the next 12 months, totaling 13 months.
- Distinguish forecast and completed entries without counting the same entry twice.
- Income, recurring expenses, and installments contribute to the forecasts for their respective months.
- The current month starts from the current consolidated balance of accounts and wallets. The projection adds pending income and subtracts pending account and wallet expenses and unpaid bills in the period, without recalculating completed transactions already reflected in the balance.
- Each month's projected closing balance becomes the next month's projected opening balance, to which that period's forecast inflows and outflows are applied.
- In balance projections, card purchases contribute through the corresponding bill amount, without another deduction for the purchase itself. A R$ 600 purchase in three installments deducts R$ 200 in each respective bill month, without also deducting R$ 600 on the purchase date. This does not change the purchase being counted once as an expense.
- In the projection summary, each month shows its projected closing balance. Tapping or selecting a month opens a simple breakdown: balance carried from the previous month, forecast income, forecast account and wallet expenses, and forecast card bills, explaining the result.
- The formula is not the projection's primary content.
- Projections consider registered amounts only, without estimating or inventing missing income or expenses. The absence of entries does not guarantee zero spending.
- An amount reappears in future months only if registered as recurring or explicitly forecast for those periods. A salary registered only for November does not reappear in December on its own.
- The presentation displays the Portuguese UI text "Projeção baseada nos seus registros" and explains that unregistered amounts are excluded, without claiming to identify which information the user forgot.

## Main flow

1. After login or registration, the user opens the current month's planning directly.
2. On first use, guidance within planning explains how to register the first account or wallet with an opening balance, without a mandatory wizard. Cards with closing and due dates are registered when needed.
3. The user records income and expenses. For expenses, they choose custom entry or identification by app.
4. The user defines whether an expense is one-off, recurring, or paid in installments; installment purchases require a card.
5. The user reviews the current month and upcoming projections, distinguishing forecast and completed entries.
6. When needed, the user records transfers, adjusts balances, cancels an occurrence, or ends a recurrence.

## Acceptance criteria

- **Isolation:** after signing in, a user can access their own data and cannot access another user's financial data.
- **Entry and first use:** after login or registration, the current month's planning opens directly. On first use, guidance to register the first account or wallet appears in that context, without a mandatory wizard before planning; the opening balance does not count as income or expense.
- **One-off expense:** a one-off expense can be registered with the specified fields and appears in its corresponding month. When completed through an account or wallet, it reduces the balance once.
- **Recurrence:** on its registered date, a recurring occurrence is automatically posted. Account or wallet expenses reduce the balance, while received income increases it. Recurring card expenses enter the bill without reducing the account balance before that bill is paid.
- **Cancellation:** canceling a posted occurrence reverses its financial effect, preserves its canceled state, and preserves subsequent occurrences. Ending a recurrence prevents new future occurrences.
- **Installments:** a R$ 1,200 purchase in six installments is linked to a card and distributes six R$ 200 installments across consecutive monthly bills, with the first suggested by the closing rule and adjustable by the user during entry.
- **Closing:** purchases before the closing day enter the bill closing in that cycle; purchases on or after the closing day enter the next cycle.
- **First bill adjustment:** for a card closing on the 10th and due on the 17th, a purchase on October 9 in three installments suggests October, November, and December. Adjusting the first bill to November during entry moves the installments to November, December, and January, preserving the October 9 purchase date.
- **Bill without duplication:** when manually recording full bill payment, the user chooses the source account and payment date. That account's balance is reduced by the bill total, and the bill is marked as paid without duplicating purchase expenses.
- **Opening balance and adjustment:** the balance entered during registration is the starting point. Later edits are recorded as adjustments and do not increase income or expense totals.
- **Transfer:** a transfer reduces the source balance and increases the destination balance by the same amount, without changing the total across the accounts and wallets involved or income and expense totals.
- **Projection:** monthly planning covers the current month and the next 12 months, totaling 13 months, and distinguishes forecast and completed entries. An entry changing from forecast to completed is not counted again as a second transaction.
- **Projection calculation:** the current month starts from the current consolidated balance of accounts and wallets, adds pending income, and subtracts pending account and wallet expenses and unpaid bills for the period, without recalculating completed transactions already reflected in the balance. Each month's projected closing balance becomes the next month's projected opening balance.
- **Cards in balance projections:** a R$ 600 purchase in three installments deducts R$ 200 in each respective bill month, without also deducting R$ 600 on the purchase date or duplicating the expense.
- **Projection presentation:** each month shows its projected closing balance in the summary. Tapping or selecting a month lets the user review the balance carried from the previous month, forecast income, forecast account and wallet expenses, and forecast card bills to understand the result, without making the formula the primary content.
- **Missing projection information:** only registered amounts are projected, without estimating missing income or expenses. A salary registered only for November does not appear in December without recurrence or an explicit forecast for that month. The presentation displays "Projeção baseada nos seus registros" and explains that unregistered amounts are excluded, without treating missing entries as certainty of zero spending or claiming to identify forgotten information.

## Outside this stage

- Screen implementation.
- AI, chat, WhatsApp, and OCR.
- Banking integration and Open Finance.
- Notifications, goals, investments, monetization, and advanced reports.

## Open product and technology decisions

- Validate Google Play and App Store lookups for filling in names and icons.
- Detail the remaining aspects of card billing cycles.
- Detail exceptions and validation for purchase entry and bill payment, including cancellation of an occurrence after its bill has been paid.
- Choose mobile technology supporting Android and iOS and testing on the creator's iPhone.
- Evaluate Java with Spring Boot as an initial backend proposal; the choice is not final.
- Define the database and hosting.
