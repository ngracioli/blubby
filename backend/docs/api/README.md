# API

Owns HTTP operations, request and response schemas, authentication requirements, errors, pagination, and compatibility.

Document each operation's authorization, validation, financial effect, transaction boundary, and retry behavior. Define money and date serialization together with the mobile app. Do not expose persistence entities as accidental public contracts.

OpenAPI is the proposed contract format. Select a supported specification version with the eventual tooling. Define duplicate prevention for financial writes before enabling automatic client retries.

Examples must stay within the [MVP](../../../mvp.md). No endpoints are approved or implemented by this guide.

## Current contract and future conventions

New Controllers, Requests and Resources use `app/Http/{Controllers,Requests,Resources}/Api/<Domain>/`, matching the business owner. Domain operations remain transport-independent and do not accept Form Requests or return Resources. `Api` is a namespace grouping, not an automatic versioned route. See [architecture](../architecture/overview.md#directory-reference).

The only API operation is `GET /api/user` under `auth:sanctum`, currently returning the authenticated model. `/up` is a healthcheck; `/` is the default web page. No login, registration, token issuance or business endpoint exists. Preserve their current contracts unless an assigned task changes them.

- New business responses use explicit Laravel API Resources. Keep native `data` wrapping and native paginated `links`/`meta`; do not add `success`, `message` and another nested envelope by habit. A justified non-entity response may use `response()->json()`. Resources serialize allowlisted public fields and loaded relations; they do not query or mutate.
- Use named resource-oriented routes and appropriate methods/status codes: create `201`, successful reads/updates `200`, empty successful deletes `204`, invalid input `422`, unauthenticated `401`, forbidden `403` (or deliberate non-disclosing `404`), missing resource `404`, rate limit `429`. Use `409` only for a defined conflict such as an incompatible replay, not every exception. Document responses and test them; do not build a status-code wrapper library.
- Existing routes are unversioned. Introduce `/api/v1` only with an explicit API compatibility decision and matching client changes; do not let a generator silently change this. The generated Boost default already allows existing API conventions.
- Keep Laravel's native JSON validation error shape (`message`, `errors`) and configured JSON exception handling. Define mappings for actual domain exceptions at the HTTP boundary. Do not expose stack traces or database/provider messages. Avoid a bespoke global exception envelope before a consumer needs it.
- Bound and validate pagination, filters and sort keys. Document ordering, optional fields and nulls. Coordinate money/date/timezone serialization with mobile before the first affected operation. Do not expose every model attribute, relationship or internal identifier automatically.
- Financial writes require a defined replay/idempotency contract before automatic retries: scope of key, storage constraint, same-payload replay result and different-payload conflict. Authorization remains required for replays. Do not infer exactly-once effects from a queued job or client-generated key alone.

Security decisions belong to [security](../security/README.md); query/transaction behavior to [database](../database/README.md). [Resources reference](https://laravel.com/docs/13.x/eloquent-resources) informs native envelopes, not an obligation to rewrite the scaffold route now.
