# Display Name HTTP Contract

**Status**: Planned. Extends [auth contract](../../001-mobile-auth/contracts/auth.md); same common rules (HTTPS, `Accept: application/json`, Bearer, `Cache-Control: no-store`, native Laravel error shape, Portuguese public messages, unversioned routes).

## Operations

| Method / path | Route name | Authentication | Success |
| --- | --- | --- | --- |
| `PUT /api/user/display-name` | `auth.display-name.update` | Bearer; ability `profile:write` | `200`, current-user identity |
| `POST /api/user/display-name/skip` | `auth.display-name.skip` | Bearer; ability `profile:write` | `200`, current-user identity |

### Set or replace

Body: `{"display_name": "Ana"}`. Surrounding whitespace is removed; inner spaces are kept.

| Rule | Detail |
| --- | --- |
| Required | Missing, null, empty, or whitespace-only → `422` |
| Characters | Letters (any script, with accents) and ASCII spaces only; digits, symbols, punctuation (including `-` and `'`), emoji, tabs, line breaks → `422` |
| Length | At least 2 letters (spaces do not count); at most 50 characters → `422` |
| Other fields | Ignored; `id`, `name`, flags, abilities cannot be set |

On success the name is stored, the step is marked resolved, and the identity is returned. A repeated identical request is safe and returns the same state. Invalid input stores nothing and does not change the flag.

### Skip

No body. Marks the step resolved without changing `display_name` (an existing name is kept). Idempotent.

## Identity response (additive)

`GET /api/user`, set, and skip return the existing unwrapped fields plus:

```json
{
  "id": 1,
  "name": "João Silva",
  "email": "pessoa@example.com",
  "email_verified_at": null,
  "created_at": "2026-10-29T12:00:00.000000Z",
  "updated_at": "2026-10-29T12:00:00.000000Z",
  "display_name": null,
  "greeting_name": "João",
  "onboarding": { "display_name_resolved": false }
}
```

`greeting_name` is read-only: `display_name` if set, otherwise the first word of `name`. Registration and login envelopes embed the same user shape. Existing fields are unchanged.

## Errors

| Condition | Status | Effect |
| --- | --- | --- |
| Invalid `display_name` | `422` | Native `message` + `errors.display_name`; nothing stored |
| Missing/invalid/expired/revoked Bearer | `401` | Generic; nothing stored |
| Token lacks `profile:write` | `403` | Generic; nothing stored |
| Persistence failure | `500`/`503` | Generic; no internal details; client keeps its typed text and may retry |

## Session abilities (amendment to spec 001)

Registration and login now issue `user:read`, `session:revoke`, and `profile:write`. No other registration/login behavior changes. The 001 contract and docs must be updated with this change.
