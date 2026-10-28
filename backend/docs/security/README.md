# Security

Owns authentication, authorization, provider integration, session lifecycle, credential handling, and per-user data isolation.

The MVP includes email/password, Google on both mobile platforms, and Apple on iOS. Provider selection, account linking, recovery, verification, and session transport still require detailed decisions.

Derive user identity from validated authentication. Authorize access to every financial resource; a client-supplied resource or user identifier is not proof of ownership. Apply the same isolation to reads, writes, projections, and background recurrence processing.

Keep secrets out of source control and logs. Do not log passwords, tokens, or raw sensitive financial payloads. Verify cross-user access attempts and expired or invalid sessions.

## Implemented versus planned

Sanctum 4.3.3, `HasApiTokens`, the personal-access-token migration and `auth:sanctum` on `/api/user` are implemented. Authentication endpoints, token issuance/revocation flows, recovery flows and provider integration are not implemented. Password-reset storage is preparation, not proof of a working reset workflow. The MVP's provider requirements do not authorize installing an SDK or choosing account-linking rules now.

## Rules for future API work

- Determine client transport before implementing authentication. Native mobile clients may use Sanctum bearer tokens; a first-party browser SPA should use Sanctum's supported cookie/session and CSRF flow. Do not mix modes accidentally or disable CSRF globally. Configure CORS/stateful domains for actual origins, not wildcard credentials.
- Derive identity from the authenticated principal. Query collections through actor-scoped relationships or explicit ownership predicates. Apply Policies to individual resource operations as well; route-model binding and token abilities alone do not prove ownership. Validate every submitted related ID within the permitted ownership scope. Jobs must receive trusted context and enforce their execution contract.
- Treat token abilities as an additional restriction, never a replacement for policy checks. Sanctum's first-party SPA `tokenCan()` behavior makes that distinction essential. Test both the intended transport and cross-user access.
- When token issuance is implemented, return the plaintext token only at issuance, require HTTPS outside local development, select least-privilege abilities, and decide expiration, device naming, revocation and pruning. Sanctum hashes stored tokens; do not add plaintext storage or log token-bearing headers. Mobile secure storage belongs to the matching client design.
- Keep mass assignment allowlisted; assign `user_id`, roles and privileged fields server-side. Use validated subsets, bounded payloads/files and explicit nested keys. Hash passwords through Laravel facilities/casts; never serialize password hashes, remember tokens, reset tokens or access tokens through a Resource.
- Define named rate limiters for real expensive/credential-sensitive operations. Choose actor/IP identifiers appropriate to the threat and trusted proxy setup; database cache is available today. Test throttling and avoid user enumeration through credential/reset messages. `auth:sanctum` does not install a complete per-endpoint rate policy.
- Use bound SQL values, sort allowlists and validated provider payloads. For future URLs/uploads, prevent SSRF/path traversal by controlling destinations, paths, types and sizes. Do not let user input choose an unrestricted server-side request URL or filesystem path.
- Keep production debug disabled and use safe centralized exception rendering. Log correlation IDs and safe failure categories, not financial payloads or credentials. Never commit `.env` or print secrets during diagnostics. Review accidental data exposure in serialization, logs, pagination metadata and errors.

Before shipping affected behavior, test unauthenticated, expired/revoked token, insufficient ability, cross-user read/write/related-ID access, invalid input, mass-assignment and rate-limit cases as relevant. See [testing](../testing/README.md). References: [Sanctum](https://laravel.com/docs/13.x/sanctum), [Policies/Gates](https://laravel.com/docs/13.x/authorization), [validation](https://laravel.com/docs/13.x/validation).
