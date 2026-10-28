# Mobile testing

Owns component behavior, hooks, navigation integration, and critical user journeys.

Test observable behavior rather than internal component structure. Prioritize session entry, submitting financial forms, loading/empty/error states, duplicate submission prevention, and accessibility. Verify Android and iOS behavior; the creator's first device is an iPhone.

For an adopted design pattern, test the contract or variation that justified it. Tests must not require a class hierarchy when a function is sufficient. Device checks cannot be replaced by mocked native APIs.

The shared UI library uses Jest 29, `jest-expo`, and React Native Testing Library. `npm test -- --watch=false` runs native component checks and a React Native Web project using React DOM in jsdom. `npm run typecheck` checks production code and test types. React Test Renderer is pinned to the installed React version.

Tests live beside the components as `*.test.tsx`; DOM checks use `*.web.test.tsx`. Check observable interaction, prop forwarding, state precedence, refs, and accessible semantics. These suites do not replace an iOS/Android device matrix or a visual comparison against Figma. See [shared component verification](../components/verification.md) for current evidence and remaining checks.

Keep this suite focused on behavior that can break users: activation, blocked interaction, input edits, error announcements, imperative focus, representative customization, and screen integration. Use a representative case rather than a matrix of theme colors, dimensions, every style slot, or native prop combinations. Avoid counting internal wrappers or testing mock methods against themselves. Check visual fidelity against Figma separately; only retain a focused visual regression assertion when it protects a concrete issue, such as the provider font.
