# Data Model: Display Name Onboarding

**Status**: Planned design, not deployed schema. See [research](research.md) and [HTTP contract](contracts/display-name.md).

## User account — existing `users` (owner: Auth)

| Field | Storage | Rule |
| --- | --- | --- |
| `display_name` | new, nullable varchar(50) | Null until set. Trimmed; letters (incl. combining marks) and ASCII spaces only; at least 2 letters; at most 50 characters. Not unique, not an identifier. Never mass-assigned: written only by the display-name operation. |
| `display_name_resolved` | new, boolean not null default false | True once a valid name is saved or the step is skipped. Never set back to false. |
| `name` | existing | Registration name, unchanged. Source of the fallback: its first word. |

Both new columns are exposed only to the owner's own identity. No index is needed (looked up by primary key only).

### Derived value

`greeting_name` is not stored: display name when non-null, otherwise the first word of `name` (split on Unicode whitespace after trimming). Never empty for an account with a non-empty `name`.

### State transitions

| From | Event | To |
| --- | --- | --- |
| unresolved, no name | save valid name | resolved, name set |
| unresolved, no name | skip | resolved, no name (fallback applies) |
| resolved, any | save valid name | resolved, name replaced |
| resolved, any | skip | unchanged (idempotent; an existing name is not cleared) |

Invalid input changes nothing.

## Migration

One transactional forward migration adding both columns. `down()` drops them. Existing rows (none in production) default to unresolved with null name. Verify up/down/up only on disposable PostgreSQL data.
