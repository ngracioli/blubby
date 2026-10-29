# Navigation

Owns the screen map, route names, tabs, stacks, modals, deep links, authentication entry, and back behavior.

The current routes are `/` (welcome or restored-session gate), `/register`, `/login`, and `/home` (authenticated temporary home). `/home` returns unauthenticated users to `/`. The [authentication flow](../../../docs/flows/authentication-entry.md) and [monthly planning flow](../../../docs/flows/monthly-planning.md) remain product references; the temporary home is not the final monthly-planning screen.

For each route, document its purpose, accepted parameters, access requirements, entry and exit paths, and loading/error/empty behavior. Keep route identifiers in English and intentional product labels in Portuguese.

Client route protection improves navigation; backend authorization must still protect every financial operation. Verify initial entry, expired sessions, direct links, and back behavior on Android and iOS.
