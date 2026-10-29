# Implementation Plan: Display Name Onboarding

**Branch**: `main` | **Date**: 2026-10-29 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/002-display-name/spec.md`.

The setup script reports feature identifier `002-display-name` as `BRANCH`; the actual Git branch is `main`. No branch was created or switched.

## Summary

Add an optional, non-unique display name and a per-step "resolved" flag to the user account. Two authenticated operations set/replace the name or skip the step; both mark the step resolved and never unresolve it. The existing current-user identity gains `display_name`, `greeting_name` and `onboarding.display_name_resolved`. `greeting_name` is the single shared "name to address the user by" rule (display name, else first word of the registration name), computed once on the backend model so no client or message duplicates it. Mobile gets the Figma-backed screen "Como quer que te chamem?" and a small API service; route gating on app open depends on mobile session/navigation decisions that do not exist yet (see Dependencies).

[Research](research.md) resolves decisions; [contract](contracts/display-name.md) defines HTTP; [data model](data-model.md) defines storage.

## Technical Context

**Language/Version**: Backend PHP 8.5 (Sail), Laravel 13.39.0, Sanctum 4.3.3. Mobile TypeScript 6, Expo SDK 58, Expo Router, React Native 0.88.

**Primary Dependencies**: Existing only. No new backend or mobile dependency.

**Storage**: PostgreSQL 18. Two additive columns on `users`: `display_name` (nullable varchar 50) and `display_name_resolved` (boolean, default false, not null).

**Testing**: PHPUnit through Sail (Feature/Api/Auth for HTTP, Unit/Domain/Auth for the pure greeting rule), Pint. Mobile Jest + Testing Library with existing shared components, Biome lint, `tsc --noEmit`.

**Target Platform**: Existing Laravel API for native Android/iOS clients; Expo app.

**Project Type**: Mobile + API monorepo.

**Performance Goals**: Single-row write by primary key; no extra bound beyond that. No SLA invented.

**Constraints**: Registration/login contracts unchanged except one additive token ability; `/api/user` stays backward compatible (additive fields only); no offline queue; server-confirmed save; letters-and-spaces only, 2–50 letters/chars.

**Scale/Scope**: Two endpoints, one migration, one model method, one screen. One future flag (first account/wallet) reuses the pattern but is not built.

## Constitution Check

| Principle / workflow | Before research | After design | Evidence |
| --- | --- | --- | --- |
| I. Explicit scope | Pass | Pass | Spec + clarifications define rules; first-account step, settings screen, avatar excluded. |
| II. Financial correctness | Pass | Pass | No financial data touched. |
| III. Privacy and authorization | Pass | Pass | Owner derived from Bearer token; no user id accepted; dedicated `profile:write` ability; display name not logged; `no-store`. |
| IV. Approved visuals/accessibility | Pass | Pass (provisional item) | Screen follows Figma; "Pular por agora" recorded as provisional; shared `Input`/`Button`/`ScreenContainer` reused, labelled field, announced errors. |
| V. Ownership and simplicity | Pass | Pass | Auth owns `User`; controller does one validated single-model write; no Action/Service/DTO/Repository. |
| Test-first and evidence | Pass | Pass | Quickstart requires failing tests first. |
| Compatibility | Pass | Pass | Additive columns/fields; additive ability; spec 001 acceptance suite must stay green. |

No exception needed.

## Project Structure

### Documentation (this feature)

```text
specs/002-display-name/
├── spec.md
├── checklists/requirements.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
└── contracts/display-name.md
```

`tasks.md` belongs to `$speckit-tasks`.

### Source Code (repository root)

Planned locations only; create files when implementing:

```text
backend/
├── app/
│   ├── Models/User.php                                   # greetingName(), flag casts
│   ├── Domain/Auth/Actions/{RegisterUserAction,AuthenticateUserAction}.php   # add ability
│   └── Http/
│       ├── Controllers/Api/Auth/DisplayNameController.php
│       ├── Requests/Api/Auth/SetDisplayNameRequest.php
│       └── Resources/Api/Auth/UserResource.php           # additive fields
├── database/migrations/                                  # add display name columns
├── routes/api.php
├── docs/{api,security,database,testing}/README.md
└── tests/
    ├── Feature/Api/Auth/DisplayNameTest.php
    └── Unit/Domain/Auth/GreetingNameTest.php

mobile/
├── src/features/auth/                                    # owner: current user + session state
│   ├── components/display-name-screen.tsx (+ .test.tsx)
│   ├── services/display-name-api.ts (+ .test.ts)
│   ├── types.ts
│   ├── index.ts
│   └── README.md                                         # screen, Figma node, callbacks
├── src/app/                                              # route file added only when gating can be wired
└── docs/navigation/README.md                             # gating dependency (other docs only if touched)
```

**Structure Decision**: Reuse the Auth owner and existing HTTP/test roots on the backend; no `Profile` or `Onboarding` domain is created for one column pair. On mobile the screen lives in `features/auth` beside the welcome screen because it edits the signed-in user's identity; a dedicated `onboarding` feature is justified only when the first-account step and orchestration arrive (two real consumers).

## Phase 0: Research Outcome

Completed in [research.md](research.md): greeting rule placement, endpoint shape, flag representation, validation, ability, abuse limits, mobile scope and gating dependency.

## Phase 1: Design Outcome

- [data-model.md](data-model.md), [contracts/display-name.md](contracts/display-name.md), [quickstart.md](quickstart.md).

## Dependencies and Open Items

- **Mobile gating (FR-007) and session**: the mobile app has no API client, token storage, or route tree yet (see mobile architecture "Decisions still required"). This feature delivers the screen and service with injected authenticated transport; wiring "show on every app open while unresolved" needs the future mobile auth/session integration. Backend fully supports it via `onboarding.display_name_resolved`.
- **Figma**: before building the screen, run the figma-design skill against the official file; "Pular por agora" has no design yet (provisional text-variant `Button`).
- **Spec 001 test shape**: three exact-shape assertions (`RegistrationTest`, `CurrentUserTest`, probably `LoginTest`) must be updated with each additive identity field, test-first (T015, T025, T034).
- **Ability change**: adding `profile:write` amends spec 001's issued abilities; update 001 contract/docs and the two tests asserting the exact ability list in the same change.

## Pattern and Complexity Decision

Pattern reused: framework Form Request + Resource + inline single-model write; the simpler approach suffices, so no Action, DTO, or Strategy is adopted. The shared name rule is one Eloquent model method (entity-local behavior per the architecture guide) exposed through the Resource, rejecting a duplicated client-side function that would drift from future backend-generated text.
