# Navigation

Owns the screen map, route names, tabs, stacks, modals, deep links, authentication entry, and back behavior.

The [monthly planning flow](../../../docs/flows/monthly-planning.md) and [authentication flow](../../../docs/flows/authentication-entry.md) are the starting references. Their tentative navigation is not an approved final route tree.

For each route, document its purpose, accepted parameters, access requirements, entry and exit paths, and loading/error/empty behavior. Keep route identifiers in English and intentional product labels in Portuguese.

Client route protection improves navigation; backend authorization must still protect every financial operation. Verify initial entry, expired sessions, direct links, and back behavior on Android and iOS.
