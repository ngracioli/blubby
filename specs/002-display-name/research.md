# Research: Display Name Onboarding

## 1. Where the shared "name to address the user by" lives

- **Decision**: `User::greetingName()` on the backend model (display name, else first Unicode-whitespace-delimited word of `name`), exposed read-only as `greeting_name` in the current-user identity. Clients display it and never recompute it.
- **Rationale**: One implementation serves current clients and any future backend text (notifications/e-mail). The model owns the data; entity-local behavior is allowed on models. A response refresh after saving updates it on the next display (FR-011).
- **Alternatives**: Client-only pure function (rejected: duplicates the rule once the backend sends text, needs parity tests across two languages). Both (rejected: speculative).

## 2. Endpoint shape

- **Decision**: `PUT /api/user/display-name` (body `display_name`) sets or replaces and resolves the step; `POST /api/user/display-name/skip` resolves without changing the name. Both return `200` with the unwrapped current-user identity.
- **Rationale**: Resource-oriented, idempotent set; skip is a distinct intent and not a state change that a PUT with empty body should imply. Returning the identity lets the client refresh `greeting_name` and the flag in one round trip.
- **Alternatives**: `PATCH /api/user` generic profile update (rejected: unspecified scope, allows mass-assignment surface). One endpoint with `skip: true` (rejected: overloaded validation).

## 3. Flag representation

- **Decision**: `display_name_resolved` boolean, default false, never reset to false. Exposed as `onboarding.display_name_resolved`; the nested object leaves room for a later `first_account_resolved` without a breaking change.
- **Rationale**: The product defines two booleans, one per step; a timestamp would add unrequested information.
- **Alternatives**: Derive from `display_name IS NOT NULL` (rejected: cannot represent "skipped").

## 4. Validation

- **Decision**: Form Request on `display_name`: required string, at most 50 characters, regex `/^(?=(?:.*\p{L}){2})[\p{L}\p{M} ]+$/u` (letters, combining marks, ASCII space; at least two letters). Surrounding whitespace removed by the existing trimming middleware before validation; inner spacing kept. Portuguese messages. Validation precedes any write.
- **Rationale**: One rule expresses FR-002 including min 2 letters; `\p{M}` keeps decomposed accents valid. Server is authoritative; the client only avoids sending an empty value and renders server messages, so the rule is not duplicated.
- **Alternatives**: Unicode normalization via `intl` (rejected: no new extension requirement).

## 5. Authorization and ability

- **Decision**: New ability `profile:write`, issued at registration and login; routes use `auth:sanctum` + `abilities:profile:write`. Owner is always the Bearer principal; no id is accepted.
- **Rationale**: Existing abilities are `user:read` and `session:revoke`; writing under a read ability would be wrong. No sessions exist yet in production, so no legacy-token backfill is needed; a token without the ability gets `403`.
- **Alternatives**: Wildcard ability (rejected: overbroad).

## 6. Abuse limits

- **Decision**: No new limiter. The operation is authenticated, single-row, idempotent, and bounded to 50 characters and the framework's default body limits.
- **Rationale**: Spec 001 limits protect unauthenticated credential attempts; nothing here is guessable or costly. Revisit if write endpoints multiply.

## 7. Concurrency

- **Decision**: Last write wins on the name; the flag only transitions false→true. One `UPDATE` by primary key; no explicit transaction.
- **Rationale**: Concurrent submissions leave one consistent final value (spec edge case) without locking.

## 8. Mobile scope

- **Decision**: Build `DisplayNameScreen` (shared `Input`, `Button`, `ScreenContainer`; text-variant `Button` for the provisional skip) taking `onSave(name)`, `onSkip()`, `onDone()`, and `onSessionExpired()` callbacks, with loading, field error, generic failure message, and retry behavior; plus `display-name-api.ts` accepting an authenticated `fetch`-like function. Do not invent session storage, navigation tree, or a server-cache library.
- **Rationale**: Mobile docs list authentication integration, navigation and cache as undecided; inventing them here violates the constitution's explicit-scope principle. FR-007 gating is wired when that integration is specified.
- **Alternatives**: Include a minimal session + router gate (rejected: sets cross-cutting decisions without approval).
