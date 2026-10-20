# Quality gate implementation plan

Status: proposed; documentation only. No tooling, dependencies, thresholds, skills, or CI gates are implemented by this plan.

## Purpose and scope

Increase confidence in frontend and backend changes, including code written with coding agents. Passing checks provides evidence; it cannot guarantee correctness or appropriate design.

Expo, React Native, and TypeScript are the intended frontend direction. Java, Spring Boot, and PostgreSQL remain backend proposals until explicitly decided. Confirm versions and scaffolds before selecting compatible tools.

Build on the [project rules](../../AGENTS.md), [frontend testing guide](../../frontend/docs/testing/README.md), and [backend testing guide](../../backend/docs/testing/README.md). Keep architecture and pattern decisions in their existing guides.

## Responsibilities

| Layer | Responsibility |
| --- | --- |
| Root `AGENTS.md` | Always-on instructions: inspect existing solutions, assess risk, run applicable checks, and report evidence. Confirm each coding client's loading mechanism. |
| Reusable skills | Procedures for choosing tests, handling failures, reviewing dependencies, and evaluating exceptions. Skills supplement rules; they are not assumed to run automatically. |
| Scripts and analysis tools | Measure outcomes and return failures consistently. Rules and skills cannot replace executable checks. |
| Review | Assess semantics, unnecessary abstractions, useful missing patterns, and justified exceptions. |

Plan one documented local entrypoint per side, with fast and extended profiles. Command names and implementations will be decided after scaffolding; no such commands are claimed to exist today. Remote CI can later run the same checks without requiring a particular provider.

## Checks required on both sides

| Check | Frontend evidence | Backend evidence |
| --- | --- | --- |
| Unit and regression | Form validation, state transitions, hooks, components, and observable loading/error behavior | Domain rules, calculations, financial invariants, and failure behavior |
| Integration | Navigation, API contracts, session state, and platform boundaries | Application/API contracts, authorization, persistence, transactions, and constraints |
| Coverage | Lines and branches in changed executable code and critical client logic | Lines and branches in changed executable code and critical domain/application logic |
| Mutation | Meaningful mutations of supported client logic; trial framework and transform compatibility first | Meaningful mutations of domain/application logic; trial runtime and framework compatibility first |
| E2E | Actual Android and iOS journeys through the app and test backend | Observable API-to-database scenarios plus participation in mobile-to-backend journeys |
| Dependencies | Direct/transitive packages, native compatibility, bundle/runtime cost, and supply-chain findings | Direct/transitive libraries, runtime compatibility, resource cost, and supply-chain findings |
| Size and complexity | Function, file, and feature size; cyclomatic complexity per function | Method/function, file, and business-module size; cyclomatic complexity per method/function |
| Duplication | Candidate repeated logic and structures, reviewed against existing reusable code | Candidate repeated domain/application logic and structures, reviewed for semantic equivalence |
| Violations | Formatting, lint, types, forbidden imports, feature boundaries, accessibility/security rules | Formatting, compiler/static checks, forbidden dependencies, domain isolation, and security rules |

Regression is a purpose of tests across levels, not a separate duplicate suite. Every bug fix needs a check that reproduces the observable failure and passes after the fix. Prefer the lowest reliable level; use device E2E when the defect crosses native or full-system boundaries.

## Financial and user-visible acceptance cases

- Cross-user access must fail through API authorization; hiding UI elements is insufficient.
- Transfers conserve value; installment allocations sum to the original amount using the agreed rounding rule.
- Paying a bill does not count the expense twice; retrying a financial operation does not duplicate it.
- Closing-day boundaries, recurrence cancellation, and projection carryover follow documented product rules.
- Concurrent updates and partial failures preserve database consistency and roll back appropriately.
- Frontend tests cover submission prevention, validation, empty/error states, session entry, and accessible interactions.
- Device E2E covers agreed critical journeys on Android and iOS with deterministic fixtures and isolated accounts. Select builds and automation that support the required native behavior; do not assume Expo Go is sufficient.

Only implement acceptance cases for approved behavior. Resolve offline/synchronization requirements before defining its expected results.

## Measurement and guardrails

### Coverage and mutation

Measure changed-code coverage and critical areas separately from repository totals. Set line and branch thresholds from a representative baseline and risk assessment before making them blocking. Avoid a blanket 100% target and tests that merely execute code without meaningful assertions.

Require explicit coverage for important financial branches regardless of the aggregate percentage. Scope exclusions to genuinely non-executable or generated material; do not exclude difficult business logic to improve a score.

For mutation testing, record tested scope, killed/surviving mutants, timeouts, unsupported transformations, and exclusions. Investigate surviving mutants as possible missing assertions or redundant logic. Document equivalent mutants with a reason; do not hide them silently or classify infrastructure failures as successful results. Calibrate score thresholds and runtime budgets after a compatibility trial.

### Dependencies

Before adding a dependency, inspect existing project, platform, and framework capabilities. Record why the dependency is necessary, maintenance/release evidence, license compatibility, direct/transitive vulnerability findings, and measurable cost where relevant.

Review exploitability and reachable affected usage instead of treating every advisory identically. Fix or explicitly disposition findings according to an agreed severity policy. Do not blindly update, suppress findings, or add overlapping packages solely to satisfy a scanner.

### Size, complexity, duplication, and violations

Track logical lines per function/method, file, and owning feature/module, with generated code excluded explicitly. Define module boundaries before measuring them. Limits should prompt cohesive extraction or simplification; splitting files or functions only to evade a limit is unacceptable.

Limit cyclomatic complexity per function/method using a defined tool and counting convention. Cognitive complexity may be reported separately; it is a different metric and must not silently replace the requested measure.

Use duplication detection to find candidates, then check their meaning and ownership. Reuse an existing implementation when behavior and contracts match. Do not force unrelated UI or domain rules into a shared abstraction just to lower duplication.

Classify violations by rule and severity. Check architecture boundaries, forbidden imports, domain isolation, lint/types, security, and formatting with appropriately scoped tools. Confirm false positives before suppressing them. Each waiver needs a narrow scope, reason, owner, review/expiry date, and follow-up action; do not disable a rule globally for one case.

## Execution frequency

| When | Planned checks |
| --- | --- |
| Each relevant change | Formatting, types/compilation, lint, focused unit/regression tests, changed-code coverage, and fast size/complexity/duplication/architecture checks |
| Before completing affected flows | Integration and contract checks; device E2E for changed critical journeys; dependency assessment when dependencies change |
| Scheduled or explicit extended run | Full E2E matrix, broader dependency scans, mutation testing, and repository-wide metric trends |

All check types must be available for both sides. Expensive checks may run on demand or on a schedule, but high-risk financial/security changes can require them before completion. Document the profile required for each risk category and never claim an unexecuted check passed.

## Implementation phases

1. **Define scope and baseline.** Inventory scaffolds, existing tests/tools, module boundaries, critical flows, and approved behavior. Produce a frontend/backend check matrix, risk categories, proposed thresholds, and runtime budgets. Give existing debt owners and remediation dates rather than grandfathering it indefinitely.
2. **Research and trial tools.** Verify official documentation, current versions, licensing, and compatibility. Run small trials on actual Expo/React Native transforms, mutation support, device builds, and the selected Java/database stack. Choose tools only after evidence; no runner or dependency list is approved by this plan.
3. **Automate the fast profile.** Reuse installed tooling where adequate. Implement the local entrypoints, regression fixtures, static rules, coverage reports, and calibrated limits. Establish thresholds before switching checks to blocking; prohibit unexplained deterioration in changed code.
4. **Add extended evidence.** Introduce real-database integration checks, Android/iOS E2E, mutation profiles, and broader dependency scans. Control fixture isolation, flakiness, secrets, and runtime. Define failure triage instead of rerunning until green.
5. **Write agent procedures.** Update root rules and add focused reusable skills for assessment, execution, and reporting. Preserve the existing `CLAUDE.md` linkage; verify Codex, Claude, and Antigravity discover the canonical rules. Avoid duplicated agent-specific rule catalogs and do not assume identical skill support.
6. **Prove and adopt the gate.** On a temporary validation branch or workspace, deliberately introduce representative failing tests, prohibited imports, duplicate/complex code, and a safe dependency finding fixture. Verify relevant checks fail, then restore a clean passing state. Do not commit broken validation examples to the product. Add remote CI only if selected.

## Evidence and exceptions

Each completion report should list scope, tool versions, exact commands, outcomes, coverage/mutation metrics, violations, and applicable exceptions. State checks not run and why, including unsupported cases and platform gaps. Keep sensitive data out of logs and reports.

An exception is explicit and time-limited, with an owner and resolution condition. A missing tool, timeout, skipped platform, or flaky test is unresolved evidence, not a passing gate. Keep the initial gate small enough to run reliably.

## Completion checklist

- [ ] Every requested check has an owner, scope, tool, threshold/policy, and execution frequency for both sides.
- [ ] Local entrypoints and required profiles are documented and reproducible.
- [ ] Critical regression cases and financial invariants have meaningful assertions.
- [ ] Android/iOS, database integration, and mutation compatibility are demonstrated.
- [ ] Deliberate failures are detected and the restored repository passes required checks.
- [ ] Agent procedures reference canonical rules; reports and expiring waivers are usable.

## Decisions pending

Confirm stack versions, database choice, offline behavior, critical journey/device matrix, coverage/mutation and metric thresholds, runtime budgets, dependency severity/license policy, artifact retention, and whether/where to enforce remote CI. No dependencies are installed and no tooling is configured by this document.
