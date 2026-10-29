# Quickstart: Display Name Onboarding

Validation guide. Details live in the [contract](contracts/display-name.md) and [data model](data-model.md). Run backend commands through Sail from `backend/`; mobile commands from `mobile/`.

## Test-first gate

1. Write `tests/Feature/Api/Auth/DisplayNameTest.php` and `tests/Unit/Domain/Auth/GreetingNameTest.php`; run them and confirm they fail because the routes/columns/method are missing, not because of the environment.
2. Implement migration, model method, Resource fields, request, controller, routes, and the new ability; rerun until green.
3. Update the two existing tests that assert the exact ability list; run the full spec 001 suite unchanged otherwise.

## Backend scenarios

| # | Scenario | Expected |
| --- | --- | --- |
| 1 | PUT `Ana` with valid token | `200`; `display_name` "Ana", `greeting_name` "Ana", `onboarding.display_name_resolved` true |
| 2 | PUT `  Ana Clara  ` | Stored "Ana Clara" |
| 3 | PUT `João`, `Zé` | Accepted |
| 4 | PUT empty, spaces, `A`, 51 letters, `Ana2`, `Ana!`, `D'Ávila`, `Ana 😀`, `Ana-Maria` | `422` on `display_name`; nothing stored; flag unchanged |
| 5 | Skip with no name | `200`; resolved true; `display_name` null; `greeting_name` = first word of `name` |
| 6 | Skip with existing name | Name kept |
| 7 | Replace name twice | Latest wins |
| 8 | Body contains `id`, `name`, `display_name_resolved` | Ignored; only the token owner changed |
| 9 | No/expired/revoked token | `401`, nothing changed |
| 10 | Token without `profile:write` | `403` |
| 11 | `name` "  João   Silva " fallback | `greeting_name` "João" |
| 12 | Two sessions; set on one | Other sees resolved on next `GET /api/user` |
| 13 | Registration/login responses | Include new fields; all spec 001 tests pass |
| 14 | Same valid PUT sent twice | Same final state and identical response; concurrent parallel writes are out of scope (last write wins) |

Checks: `vendor/bin/sail artisan test --compact`, `vendor/bin/sail bin pint --dirty`, and migration up/down/up on a disposable database.

## Mobile scenarios

Component tests with the real shared controls and a mocked transport: placeholder "Ex.: Ana"; empty submit shows a field message without a request; loading state blocks double submit; 422 message shows under the field; offline/failure keeps text and shows "Não foi possível salvar. Tente novamente.", no navigation; a 401 calls `onSessionExpired` and keeps the text; success calls the advance callback only after confirmation; "Pular por agora" same rules; accessibility labels and error announcement. Run `npm test`, `npm run lint`, `npm run typecheck`. Verify layout against Figma and with the keyboard open on Android and iOS (not covered by Jest).
