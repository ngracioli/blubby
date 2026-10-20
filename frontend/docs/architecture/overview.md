# Frontend architecture

## Status

Documentation conventions are established here. The application structure below is a proposed baseline, not an implementation or a finalized technology decision.

## Proposed responsibilities

- Routes define navigation and compose screens. With Expo Router, keep non-route modules outside `src/app`.
- Feature modules own their screens, feature-specific components, interactions, and API operations.
- Shared UI owns reusable visual behavior without financial rules or network requests.
- Data integration owns transport, serialization, and translation of server errors.
- The backend remains authoritative for authorization and financial mutations. Client validation provides feedback but cannot replace server validation.

Keep feature code together. Extract shared modules only when there are actual consumers. Avoid generic base screens and a global folder of unrelated helpers.

Follow [naming conventions](naming.md) and the [pattern selection guide](design-patterns.md). A pattern belongs inside its owning feature unless a real shared responsibility justifies another location.

## Decisions still required

Navigation structure, authentication integration, server cache library, persistence, and offline writes remain open. Do not infer these decisions from pattern examples.

## Reference

[Expo Router core concepts](https://docs.expo.dev/router/basics/core-concepts/).
