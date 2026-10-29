---

description: "Task list for Display Name Onboarding"
---

# Tasks: Display Name Onboarding

**Input**: Design documents from `specs/002-display-name/`

**Prerequisites**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/display-name.md](contracts/display-name.md), [quickstart.md](quickstart.md)

**Tests**: Included. The project's test-first discipline requires a failing test, confirmed to fail for the right reason, before implementation code.

**Organization**: Grouped by user story. Backend commands run through Sail from `backend/`; mobile commands from `mobile/`.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependency on an incomplete task)
- **[Story]**: User story from spec.md (US1–US4)

---

## Phase 1: Setup

- [ ] T001 Read before coding: `backend/AGENTS.md`, `backend/docs/agent-workflow.md`, `backend/docs/architecture/{overview,naming,conventions}.md`, `backend/.ai/skills/backend-development/SKILL.md`, `mobile/docs/architecture/{overview,naming,design-patterns}.md`, `mobile/docs/components/README.md`, `.agents/skills/figma-design/SKILL.md`
- [ ] T002 Record baseline results (no edits): backend `vendor/bin/sail artisan test --compact` and mobile `npm test`, `npm run lint`, `npm run typecheck`; stop and report if the baseline is red

---

## Phase 2: Foundational (blocks all stories)

**Purpose**: Schema and the new token ability that every story needs.

- [ ] T003 [P] Write failing assertions that registration and login issue `['user:read', 'session:revoke', 'profile:write']`: update `backend/tests/Feature/Api/Auth/RegistrationTest.php` (lines asserting the exact list) and add the equivalent assertion in `backend/tests/Feature/Api/Auth/LoginTest.php`; run them and confirm they fail because the ability is missing
- [ ] T004 Create forward migration `backend/database/migrations/2026_10_29_000003_add_display_name_to_users_table.php` adding `display_name` (nullable string 50) and boolean `display_name_resolved` (not null, default false); `down()` drops both; verify up/down/up on a disposable PostgreSQL database only
- [ ] T005 Add `profile:write` to the issued abilities in `backend/app/Domain/Auth/Actions/RegisterUserAction.php` and `backend/app/Domain/Auth/Actions/AuthenticateUserAction.php`; rerun T003 tests until green and the spec 001 suite stays green
- [ ] T006 [P] Amend spec 001 documents for the new ability: `specs/001-mobile-auth/contracts/auth.md` (common rules line on abilities), `specs/001-mobile-auth/data-model.md` (token `abilities` row), `backend/docs/security/README.md`

**Checkpoint**: schema present, sessions carry `profile:write`, 001 tests green.

---

## Phase 3: User Story 1 - Choose How Blubby Addresses Me (Priority: P1) 🎯 MVP

**Goal**: An authenticated user saves a valid display name; the mobile screen "Como quer que te chamem?" collects it with correct loading, error, and skip-callback behavior.

**Independent Test**: With a freshly registered token, PUT "Ana" returns the identity with `display_name` "Ana"; invalid values return 422 and store nothing; the screen renders the Figma content and only advances after the injected save resolves.

### Tests first (US1)

- [ ] T007 [P] [US1] Write `backend/tests/Feature/Api/Auth/DisplayNameTest.php` covering quickstart scenarios 1–4 and 8: valid names ("Ana", "  Ana Clara  " stored trimmed, "João", "Zé") return 200 with `display_name`; invalid values (empty, whitespace, "A", 51 letters, "Ana2", "Ana!", "D'Ávila", "Ana-Maria", "Ana 😀") return 422 on `display_name` and leave name/flag unchanged; extra fields (`id`, `name`, `display_name_resolved`) are ignored
- [ ] T008 [US1] Read the Figma design for this screen using the figma-design skill (official file; confirm node, spacing, title/helper typography). Before any screen code, list the required components and states against the Figma file and `mobile/docs/components/` (`ScreenContainer`, `Input` with helper/error and focus states, solid `Button` with loading, text-variant `Button` for the provisional "Pular por agora"), reuse existing ones, and note any missing primitive to add to `mobile/src/shared/components/` first. Record the node id for T020
- [ ] T009 [US1] Write `mobile/src/features/auth/components/display-name-screen.test.tsx` (after T008): title/helper/label/placeholder "Ex.: Ana"/"Continuar"/"Pular por agora" render; empty submit shows a field message and calls nothing; loading blocks double submit; a rejected save keeps the typed text, shows "Não foi possível salvar. Tente novamente.", and does not call `onDone`; a server field error shows under the input; a session-expired error calls `onSessionExpired` and keeps the text; success calls `onDone` only after the save resolves; skip follows the same rules; input has an accessible label and errors are announced; controls are at least 52 points tall
- [ ] T010 [P] [US1] Write `mobile/src/features/auth/services/display-name-api.test.ts`: `saveDisplayName` sends `PUT /api/user/display-name` with `{display_name}` through the injected request function, returns the user on 200, and maps 422 `errors.display_name`, 401, and network failure to typed errors
- [ ] T011 [US1] Run T007, T009, T010 and confirm each fails because the route, service, or screen is missing, not because of the environment

### Implementation (US1)

- [ ] T012 [US1] Create `backend/app/Http/Requests/Api/Auth/SetDisplayNameRequest.php`: `display_name` required string, max 50, regex `/^(?=(?:.*\p{L}){2})[\p{L}\p{M} ]+$/u`, Portuguese messages, `validated()` only
- [ ] T013 [US1] Create `backend/app/Http/Controllers/Api/Auth/DisplayNameController.php` with `update`: set `display_name` and `display_name_resolved = true` on `$request->user()` (explicit assignment, not mass assignment), save, return `UserResource` with `Cache-Control: no-store`; do not log the submitted value
- [ ] T014 [US1] Register `PUT /user/display-name` (`auth.display-name.update`, middleware `auth:sanctum`, `abilities:profile:write`) in `backend/routes/api.php`
- [ ] T015 [US1] First update the exact-shape assertions of the spec 001 tests (`backend/tests/Feature/Api/Auth/RegistrationTest.php` around lines 35 and 206, `CurrentUserTest.php` around line 25, and `LoginTest.php` if it asserts keys) to include `display_name` and confirm they fail; then add `display_name` to `backend/app/Http/Resources/Api/Auth/UserResource.php` and run T007 and the updated 001 tests until green
- [ ] T016 [P] [US1] Create `mobile/src/features/auth/types.ts` with the current-user type (including `display_name`, `greeting_name`, `onboarding`) and typed display-name errors (field, session expired, connection)
- [ ] T017 [US1] Create `mobile/src/features/auth/services/display-name-api.ts` with `saveDisplayName(request, name)` using an injected authenticated request function (no token storage, no new dependency); run T010 until green
- [ ] T018 [US1] Create `mobile/src/features/auth/components/display-name-screen.tsx` per T008's component list; props `onSave(name)`, `onSkip()`, `onDone()`, `onSessionExpired()`; keep typed text on failure; run T009 until green
- [ ] T019 [US1] Export `DisplayNameScreen` and the service from `mobile/src/features/auth/index.ts`
- [ ] T020 [US1] Update `mobile/src/features/auth/README.md` with the screen, its Figma node, the provisional skip action, and the injected-callback contract (including `onSessionExpired`)

**Checkpoint**: backend save works and the screen is independently testable (MVP).

---

## Phase 4: User Story 4 - Always Addressed by a Sensible Name (Priority: P1)

**Goal**: One shared rule gives the name to address the user by; every place that mentions the user by name uses it.

**Independent Test**: Account with display name "Ana" yields `greeting_name` "Ana"; account without it and `name` "  João   Silva " yields "João".

- [ ] T021 [P] [US4] Write `backend/tests/Unit/Domain/Auth/GreetingNameTest.php` for `User::greetingName()`: display name wins; first word of `name` otherwise; extra and non-ASCII whitespace ignored; single-word name; never empty when `name` is non-empty
- [ ] T022 [P] [US4] Add to `backend/tests/Feature/Api/Auth/CurrentUserTest.php` a case asserting the value of `greeting_name` through `/api/user` for an account with a display name and for one without (the exact-key shape is handled in T025)
- [ ] T023 [US4] Run T021–T022 and confirm they fail because `greetingName()`/`greeting_name` do not exist
- [ ] T024 [US4] Add `User::greetingName()` to `backend/app/Models/User.php` (display name, else first word of trimmed `name` split on Unicode whitespace)
- [ ] T025 [US4] First update the exact-shape assertions of the spec 001 tests (`RegistrationTest.php`, `CurrentUserTest.php`, `LoginTest.php` if applicable) to include `greeting_name` and confirm they fail; then add `greeting_name` to `backend/app/Http/Resources/Api/Auth/UserResource.php`; run T021–T022 and the updated 001 tests until green
- [ ] T026 [US4] Audit current usages of the registration name that address the user: `rg -n "->name|\.name" backend/app/Http/Resources backend/resources mobile/src/features mobile/src/app`, plus any notification/mail class if one exists; migrate each to `greeting_name`, or record in the final report that none existed (FR-012)
- [ ] T027 [P] [US4] In `mobile/src/features/auth/types.ts` document that `greeting_name` is the only value screens use to address the user; no client-side recomputation

**Checkpoint**: shared name rule available everywhere via the identity.

---

## Phase 5: User Story 2 - Change My Display Name Later (Priority: P2)

**Goal**: A user who already has a name can replace it; only the token owner is affected; unauthorized calls change nothing.

**Independent Test**: Set "Ana" then "Bia"; the account shows "Bia". Missing/expired/revoked tokens get 401; a token without `profile:write` gets 403.

- [ ] T028 [US2] Extend `backend/tests/Feature/Api/Auth/DisplayNameTest.php` with scenarios 7, 9, 10: replacement keeps only the latest; two users, only the token owner changes; 401 for missing/expired/revoked tokens; 403 for a token without `profile:write`; also a repeated identical PUT leaves the same state (scenario 14); run and confirm any that fail do so for the right reason (several may already pass from US1; keep only tests covering distinct behavior)
- [ ] T029 [US2] Fix any gap T028 exposes in `backend/routes/api.php` or `backend/app/Http/Controllers/Api/Auth/DisplayNameController.php`; rerun until green

**Checkpoint**: replacement and authorization proven.

---

## Phase 6: User Story 3 - Keep Being Asked Until Done or Skipped (Priority: P2)

**Goal**: Backend records whether the display name step is resolved (filled or skipped); the identity exposes it on every session; mobile can skip.

**Independent Test**: New account reports unresolved; after skip or save it reports resolved on every session and never reverts; skip keeps an existing name.

- [ ] T030 [P] [US3] Extend `backend/tests/Feature/Api/Auth/DisplayNameTest.php` with scenarios 5, 6, 12: `POST /api/user/display-name/skip` resolves without a name and `greeting_name` falls back to the first word; skip keeps an existing name and is idempotent; a second session sees `onboarding.display_name_resolved` true; a new account starts false; invalid save never flips the flag; registration/login envelopes include `display_name`, `greeting_name`, and `onboarding`
- [ ] T031 [P] [US3] Extend `mobile/src/features/auth/services/display-name-api.test.ts` for `skipDisplayName` (`POST /api/user/display-name/skip`, no body, returns user, maps network failure)
- [ ] T032 [US3] Run T030–T031 and confirm they fail because the skip route/service and `onboarding` field are missing
- [ ] T033 [US3] Add `skip` to `backend/app/Http/Controllers/Api/Auth/DisplayNameController.php` (set resolved true only, never clear the name) and `POST /user/display-name/skip` (`auth.display-name.skip`, same middleware) in `backend/routes/api.php`
- [ ] T034 [US3] First update the exact-shape assertions of the spec 001 tests to include `onboarding` and confirm they fail; then add `onboarding => ['display_name_resolved' => ...]` to `backend/app/Http/Resources/Api/Auth/UserResource.php`; run T030 and the updated 001 tests until green
- [ ] T035 [US3] Create `skipDisplayName(request)` in `mobile/src/features/auth/services/display-name-api.ts`; run T031 until green
- [ ] T036 [P] [US3] Document the gating dependency in `mobile/docs/navigation/README.md`: show the screen on every app open while `onboarding.display_name_resolved` is false, to be wired when mobile session/navigation is specified; no invented routes

**Checkpoint**: all four stories backend-complete; mobile screen and services complete; gating wiring explicitly deferred.

---

## Phase 7: Polish & Cross-Cutting

- [ ] T037 [P] Update `backend/docs/api/README.md` (new operations, abilities, additive identity fields), `backend/docs/security/README.md` (display name as personal data, `profile:write`), `backend/docs/database/README.md` (new columns), `backend/docs/testing/README.md` (new tests); verify FR-009 by searching the new controller, request, and exception rendering for logging of the submitted value (`rg -n "Log::|logger\(|report\(" backend/app/Http/Controllers/Api/Auth/DisplayNameController.php backend/app/Http/Requests/Api/Auth/SetDisplayNameRequest.php`) and confirm none exists
- [ ] T038 [P] Update `backend/docs/architecture/overview.md` current-state paragraph (display name owned by Auth, no new pattern)
- [ ] T039 Run backend `vendor/bin/sail bin pint --dirty`, then the full `vendor/bin/sail artisan test --compact`; confirm every spec 001 test still passes (SC-005)
- [ ] T040 Run mobile `npm test`, `npm run lint`, `npm run typecheck`
- [ ] T041 Walk through [quickstart.md](quickstart.md) scenarios 1–14 and the mobile checklist; manually verify the screen with keyboard open, large text, and a screen reader on Android and iOS if devices are available, otherwise report them as not run
- [ ] T042 Set `spec.md` status to Implemented (backend + screen) and note the deferred FR-007 gating; final report states the pattern used (framework Form Request/Resource, single-model write, no Action) and the checks actually run

---

## Dependencies & Execution Order

- Phase 1 → Phase 2 → stories. Phase 2 blocks everything.
- US1 and US4 (both P1) are independent after Phase 2, but both edit `UserResource.php`: do US1 first (T014), then US4 (T025), then US3 (T034). US2 only adds tests to US1's route; US3 builds on the controller and Resource.
- Mobile tasks T008–T011 and T016–T020 depend only on the contract and the T016/T017 types and service; they can run alongside backend work once the contract is fixed. T009 must follow T008.
- Tests precede implementation within each story and must be seen failing first (T011, T023, T032). Any 001 test whose exact shape changes is updated first and seen failing (T015, T025, T034).

## Parallel Examples

- Phase 2: T003 and T006 together.
- US1: T007, T008, and T010 together; T009 after T008; later T016 together with backend T012–T015.
- US4 tests: T021 and T022 together.
- Polish: T037 and T038 together.

## Implementation Strategy

- **MVP**: Phases 1–3 (US1): save a valid display name on the backend and a tested screen. Add US4 right after (it is also P1 and tiny), then US2 and US3.
- Deliver incrementally and commit per phase using the commit skill, only when asked.
- Deferred by design: FR-007 on-every-app-open gating until mobile session/navigation exists; first-account/wallet step; settings screen.
