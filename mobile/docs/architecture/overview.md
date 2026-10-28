# Mobile architecture

## Status

Implemented baseline with Expo SDK 58, Expo Router, and TypeScript. The application follows a Feature-Based Architecture, separating thin navigation routes from encapsulated business features and shared design primitives.

## Directory structure rules

Every new file and directory must adhere to this structural hierarchy:

```text
mobile/src/
├── app/                  # Routing layer (Expo Router) ONLY
├── features/             # Business domain modules
├── shared/               # Cross-cutting UI primitives & tokens
└── assets/               # Brand images and typed asset exports
```

### 1. `src/app/` — Routing layer
- Houses exclusively navigation routes, route groups, and layout files (`_layout.tsx`, `index.tsx`, `(auth)/`, `(tabs)/`).
- Must remain a thin composition layer. Never write domain logic, heavy JSX, forms, or network requests directly inside `src/app/`.
- Routes import screens and containers from `src/features/<feature>/` or `src/shared/`.

### 2. `src/features/<feature-name>/` — Domain modules
- Group code by business capability (`auth`, `planning`, `entries`, `accounts`).
- A feature owns its domain logic, screen views, feature-specific components, and API integration:
  - `components/`: Feature-specific UI components (e.g. `login-form.tsx`, `summary-row.tsx`).
  - `hooks/`: Feature-specific React hooks (e.g. `use-monthly-planning.ts`).
  - `services/`: Feature API endpoints and client transport (e.g. `planning-api.ts`).
  - `types.ts`: Domain models and payload types specific to this feature.
  - `index.ts`: Barrel export of the feature's public contract.
- Never let one feature reach into another feature's internal components. Communicate via public interfaces or shared modules.

### 3. `src/shared/` — Reusable design system & utilities
- `shared/theme/`: Single source of truth for Figma tokens (`colors.ts`, `typography.ts`, `spacing.ts`, `radii.ts`, `shadows.ts`).
- `shared/components/`: Pure, domain-agnostic UI primitives (e.g. `screen-container.tsx`, `button.tsx`, `text-input.tsx`).
  - Shared components never decide financial rules or perform API requests.
- Design-system primitives such as buttons and text inputs belong in `shared/components/` from their first consumer. Follow the [shared component rules](../components/README.md) for their public contracts and customization. Other feature-specific compositions move to `src/shared/` only when two or more actual consumers across different features share the same responsibility.

### 4. `src/assets/` — Static brand assets
- Brand logos, icons, and third-party auth images with typed exports via `index.ts`.

### 5. Path aliases
- Use `@/...` configured via `tsconfig.json` paths for all cross-directory imports. Never use long relative traversal paths like `../../../../`.

## Decisions still required

Navigation structure, authentication integration, server cache library, persistence, and offline writes remain open. Do not infer these decisions from pattern examples.

## Reference

[Expo Router core concepts](https://docs.expo.dev/router/basics/core-concepts/).
