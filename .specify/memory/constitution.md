<!--
Sync Impact Report
- Version change: unratified scaffold to 1.0.0
- Modified principles: none; initial principles established below
- Added sections: Core Principles (5), Additional Constraints, Development Workflow
- Removed sections: none
- Follow-up TODOs: confirm original ratification date
-->

# Blubby Constitution

## Core Principles

### I. Product Scope Is Explicit
Implement only behavior supported by an approved product decision. Treat examples, proposals,
wireframes marked for review, and unresolved decisions as context, not authorization. Record
open questions and resolve them before implementation depends on their answers. This protects
users from invented financial behavior and keeps product intent reviewable.

### II. Financial Records Remain Correct
Preserve documented financial invariants across calculations, persistence, retries, concurrent
operations, and user-visible summaries. A transfer preserves total value; an expense, installment,
recurrence, projection, or bill payment must not count the same effect twice. Keep authoritative
financial rules on the backend and verify material calculations and failure paths with evidence.

### III. User Data Is Private and Authorized
Authenticate users and enforce ownership on every operation that reads or changes personal
financial data. Derive identity from the authenticated principal, validate related records within
that user's permitted scope, and apply the same boundary to background work. Keep credentials,
tokens, and sensitive financial payloads out of source control, logs, and unintended responses.

### IV. Interfaces Follow Approved Design and Accessibility
Use the official Figma file as the source for default mobile visuals. Build domain-agnostic controls
in the shared component library and keep feature compositions with their feature. Preserve native
interaction and accessibility contracts when customizing controls. Mark designs without a Figma
specification as provisional; do not present them as approved defaults.

### V. Ownership and Simplicity Guide Structure
Place behavior with the feature or backend domain that owns its rules. Reuse existing framework,
platform, and project capabilities before adding layers, dependencies, or patterns. Add an
abstraction only for a demonstrated responsibility, seam, or repeated use, and document the
problem and behavioral evidence. Keep dependencies intentional and acyclic.

## Additional Constraints

- Mobile targets Android and iOS. Backend and mobile architecture guides define current placement
  and implementation conventions; this constitution does not approve proposals listed as open.
- Keep identifiers and project documentation in English. Preserve intentional Portuguese product
  text and original external names and links.
- Store and calculate money without floating-point representations. Define precision, currency,
  rounding, date, and timezone rules for each approved feature before implementation.
- Make multi-record financial changes atomic. Use database constraints and concurrency controls
  where needed to preserve invariants under retries and simultaneous requests.
- Do not add dependencies or change infrastructure without a concrete requirement and compatibility
  review. Existing installed tools and documented architecture remain the baseline.

## Development Workflow

- Before changing behavior or structure, read the applicable architecture, pattern, naming, and
  domain guidance. Inspect callers and existing implementations; choose the smallest adequate
  change and identify affected contracts.
- For behavior changes and bug fixes, write and run a focused test that demonstrates the missing or
  incorrect behavior before changing implementation. For refactors, run relevant existing checks
  before and after. For documentation-only changes, use focused content validation instead.
- Verify affected behavior at the narrowest reliable level, then run relevant existing checks.
  Include integration, platform, security, or concurrency evidence when the changed contract
  crosses those boundaries.
- Report commands actually run, outcomes, and checks not run with the reason. A skipped, blocked,
  or flaky check is not a passing result.
- Keep each change scoped to its approved outcome. Update affected callers, documentation, and
  tests together; do not bundle unrelated cleanup.

## Governance

The constitution is the project's highest-level governance for product and engineering work.
More specific repository guides may add compatible implementation detail. If current project
documents conflict, identify the conflicting rules and update their canonical source; do not silently
choose one. Environment-level instructions retain their higher priority.

Amendments require a stated reason, an assessment of affected principles and project guidance, and
approval by the project owner. Update this document and any directly affected canonical guidance
in the same change. Review compliance when planning work, during implementation, and before
accepting a change; record justified exceptions with scope, owner, and resolution condition.

Use semantic versioning for this constitution. Increment MAJOR when a principle or governance
rule is removed or redefined incompatibly; MINOR when a principle or section is added or materially
expanded; PATCH for clarifications and non-semantic corrections. Update the amendment date for
every approved content change. The initial ratification date remains fixed.

**Version**: 1.0.0 | **Ratified**: TODO(RATIFICATION_DATE): confirm original adoption date | **Last Amended**: 2026-10-29
