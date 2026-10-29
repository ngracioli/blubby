# Feature Specification: Display Name Onboarding

**Feature Branch**: `main` (existing branch; no feature branch created)

**Created**: 2026-10-29

**Status**: Draft

**Input**: User description: "Mobile onboarding screen 'Como quer que te chamem?' (Figma). After registration (spec 001), the user sets a display name / nickname ('Nome ou apelido', placeholder 'Ex.: Ana', used to personalize Blubby, button 'Continuar'). Need a backend route to define/update this display name for the authenticated user, plus the mobile screen consuming it. Backend registration from spec 001 stays unchanged."

## Clarifications

### Session 2026-10-29

- Q: Should users registered before this feature see the screen? → A: Not applicable, no users exist yet. Onboarding progress is instead tracked per step as a resolved flag (filled or skipped); an unresolved step is shown on every app open until the user fills it or taps "Pular por agora". The same pattern will serve the later "first account/wallet" step.
- Q: Which characters may a display name contain? → A: Only letters (with accents) and spaces; no digits, symbols, punctuation, or emoji.
- Q: Minimum display name length? → A: 2 letters (spaces do not count).
- Q: What if the user taps "Continuar" while offline or the save fails? → A: Synchronous save; the button shows loading then an error, and the app only advances after the server confirms. Same for "Pular por agora", since the resolved flag is stored on the server.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Choose How Blubby Addresses Me (Priority: P1)

As a newly registered user, I am asked "Como quer que te chamem?" and can enter a name or nickname that Blubby uses to personalize my experience, then continue.

**Why this priority**: It is the only goal of the screen and the data it collects is needed for personalization.

**Independent Test**: With a freshly registered session, open the screen, enter "Ana", press "Continuar", and verify the name is saved and the user moves forward.

**Acceptance Scenarios**:

1. **Given** a signed-in user with no display name, **When** the user enters a valid name and presses "Continuar", **Then** the display name is saved for that account and the user proceeds to the next onboarding step.
2. **Given** the screen is shown, **When** the field is empty, **Then** the placeholder "Ex.: Ana" is visible and "Continuar" does not save anything.
3. **Given** an empty, whitespace-only, single-letter, or too long value, **When** the user presses "Continuar", **Then** a clear field-level message is shown, nothing is saved, and the user stays on the screen.
4. **Given** the screen is shown, **When** the user taps "Pular por agora", **Then** no name is saved, the step is marked as resolved, and the user proceeds to the next onboarding step.
5. **Given** the save fails (no connection, server error, expired session), **When** the user presses "Continuar", **Then** a generic Portuguese error is shown, the typed text is kept, and the user can retry; an expired session notifies the host app so it can send the user to sign-in (the sign-in navigation belongs to the session integration).

---

### User Story 2 - Change My Display Name Later (Priority: P2)

As a user who already set a display name, I can replace it with another one.

**Why this priority**: Users change their minds; the same capability serves any future settings screen, but no new screen is required now.

**Independent Test**: Set a display name, set a different one, and verify the account shows only the latest.

**Acceptance Scenarios**:

1. **Given** an account with a display name, **When** a new valid one is submitted, **Then** it replaces the previous one.
2. **Given** an authenticated user, **When** a display name is submitted, **Then** only the session owner's account is changed, regardless of any other account identifier submitted.
3. **Given** missing, expired, or revoked credentials, **When** a display name is submitted, **Then** access is denied and no account is changed.

---

### User Story 3 - Keep Being Asked Until Done or Skipped (Priority: P2)

As a user who has not yet chosen a name nor skipped the step, I am asked again every time I open the app; once I fill it or skip it, I am not asked again.

**Why this priority**: Prevents accounts without a name and avoids repeated prompts.

**Independent Test**: Register, close the app without choosing, reopen and see the screen; then fill (or skip), reopen and do not see it.

**Acceptance Scenarios**:

1. **Given** an account whose display name step is unresolved, **When** the app is opened (cold start or return after sign-in), **Then** the screen is shown, every time (this delivery verifies the flag; the app-open wiring is verified when the session and navigation integration exists).
2. **Given** an account whose display name step is resolved by filling it, **When** the app is opened, **Then** the screen is not shown and the identity includes the display name.
3. **Given** an account whose display name step is resolved by skipping, **When** the app is opened, **Then** the screen is not shown, no display name exists, and the first-word fallback applies.
4. **Given** a user signed in on two devices, **When** the step is resolved on one, **Then** the other does not show the screen after its next identity retrieval.
5. **Given** a user who skipped, **When** they later set a display name through the same capability, **Then** it is saved and replaces the fallback.

---

### User Story 4 - Always Addressed by a Sensible Name (Priority: P1)

As a user, every text in Blubby that mentions me by name uses my display name, or the first word of my registration name when I have not set one, so I am never greeted with an empty or wrong name.

**Why this priority**: The display name only has value if every screen uses it consistently; users who skip the step have none.

**Independent Test**: With one account with a display name and one without, open every place that shows the user's name and verify the expected name in each.

**Acceptance Scenarios**:

1. **Given** an account with display name "Ana", **When** any text addresses the user by name, **Then** it shows "Ana".
2. **Given** an account without a display name and registration name "João Silva", **When** any text addresses the user by name, **Then** it shows "João".
3. **Given** the display name is changed, **When** the user returns to any screen that addresses them, **Then** the new name is shown without signing in again.
4. **Given** a registration name with extra spaces around or between words, **When** the fallback is used, **Then** the first word is shown without stray spaces.

### Edge Cases

- Leading/trailing whitespace is ignored; inner spacing is kept. Names with accents (e.g., "João", "Zé") and inner spaces (e.g., "Ana Clara") MUST be accepted; values such as "Ana2", "Ana!", "D'Ávila", or "Ana 😀" MUST be rejected.
- A display name is not unique: two users may both be "Ana". It is not a login identifier.
- Repeated or concurrent submissions of the same name leave one consistent final value.
- The registration `name` from spec 001 is unchanged and independent of the display name; users who skip the step simply have no display name.
- Unexpected fields in the request MUST NOT alter other account attributes.
- Screen must remain usable with the on-screen keyboard open, with large text sizes, and with a screen reader; touch targets are at least the shared Button's 52-point minimum height.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST let an authenticated user set or replace their own display name.
- **FR-002**: The display name MUST be trimmed, MUST contain at least 2 letters (spaces do not count), and MUST NOT exceed 50 characters. Only letters (including those with diacritics) and spaces are allowed; digits, symbols, punctuation (including hyphens and apostrophes), and emoji MUST be rejected. Invalid input MUST produce a field-specific error and change nothing.
- **FR-003**: Only the session owner's account MUST be affected; access without valid credentials MUST be denied without disclosing account data.
- **FR-004**: The current-user identity MUST expose the display name, or an explicit absence of one, and whether the display name step is resolved, without removing or changing existing fields (backward compatible with spec 001).
- **FR-005**: Registration, sign-in, logout, and session behavior from spec 001 MUST remain unchanged; display name is not required to register or sign in. The only permitted changes to spec 001 surfaces are additive: new fields in the user identity and one additional session ability that allows editing the display name.
- **FR-006**: The mobile app MUST show the screen "Como quer que te chamem?" per the official Figma design (title, helper text "Usaremos esse nome para personalizar o Blubby.", label "Nome ou apelido", placeholder "Ex.: Ana", primary button "Continuar") plus a secondary text action "Pular por agora" (provisional until Figma specifies it), reusing the shared component library.
- **FR-007**: The mobile app MUST show the screen after registration and on every app open while the display name step is unresolved, and MUST NOT show it once resolved (filled or skipped). This delivery provides the screen, the resolved flag on every identity, and the callbacks needed to wire it; the app-open gating itself depends on mobile session and navigation decisions that are not yet specified and is delivered with them.
- **FR-008**: The mobile screen MUST show loading state while saving, prevent double submission, show field-level validation messages returned by the server, keep typed text on failure, and be accessible (labelled field, announced errors, interactive controls at least as tall as the shared Button's 52-point minimum). A connection or server failure MUST show a generic Portuguese message ("Não foi possível salvar. Tente novamente.") and an expired or revoked session MUST notify the host app through a session-expired callback while keeping the typed text.
- **FR-009**: User-facing text MUST be in Portuguese as in the design; the display name MUST NOT appear in logs beyond what is needed for diagnostics and MUST be treated as personal data.
- **FR-010**: There MUST be a single shared rule defining the "name to address the user by": the display name when set, otherwise the first word of the registration name. Every user-facing text that mentions the user by name MUST obtain it from that rule; no screen or message may build its own variant.
- **FR-011**: The shared rule MUST ignore surrounding and repeated whitespace in the registration name and MUST never produce an empty result for an account that has a registration name. It MUST reflect a changed display name on the next display of any such text.
- **FR-012**: Existing places that already show the registration name MUST be migrated to the shared rule within this feature; the full name MAY still be shown where a formal full name is intended (e.g., account details).
- **FR-013**: The system MUST record the display name step as resolved when the user saves a valid display name or explicitly skips it, and MUST let the user skip without providing a name. A step MUST start unresolved for every new account and, once resolved, MUST NOT become unresolved again.
- **FR-014**: Saving is synchronous: the app MUST advance to the next step only after the server confirms the name was saved (or the skip was recorded). On offline or failure it MUST stay on the screen, show an error on the button's loading outcome, keep the typed text, and allow retry. No offline queue or local pending name is kept.

### Key Entities *(include if feature involves data)*

- **Display name**: Optional, non-unique text chosen by the user to be addressed by Blubby. Belongs to exactly one user account; separate from the registration name and from the email.
- **Display name step**: Per-account resolved/unresolved state for the display name step (resolved by saving a name or skipping). Designed as one flag per onboarding step so the later first-account/wallet step reuses the same pattern; that step is out of scope here.
- **User account**: Existing identity from spec 001; gains the display name and the display name step state as attributes of its owner's own identity.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A newly registered user can set a display name and reach the next step in under 30 seconds, in one attempt, in at least 90% of usability trials.
- **SC-002**: 100% of submissions that are empty, whitespace-only, shorter than 2 letters, or over 50 characters are rejected with a field-level message and leave the stored name unchanged.
- **SC-003**: 100% of unauthenticated, expired, or revoked requests are denied and change no account.
- **SC-004**: After a name is set or the step is skipped, 100% of subsequent identity retrievals on any session of that account report the step resolved (and include the name when set), and the screen is never shown again; once app-open gating exists, an unresolved step is shown on 100% of app opens.
- **SC-005**: All spec 001 acceptance scenarios continue to pass; the only permitted test changes are the additive identity fields and the additional session ability.
- **SC-006**: In 100% of tested places that address the user by name, an account with a display name shows it and an account without one shows the first word of the registration name; none shows an empty name or the full registration name.

## Assumptions

- Scope covers one backend capability (set/replace own display name, exposed on the current-user identity) and one mobile screen. A settings screen for editing later, avatar, profanity filtering, and uniqueness checks are out of scope.
- The display name is a new attribute separate from the registration `name`; it does not overwrite it. Whether registration `name` stays in the signup form is a spec 001 matter and untouched here.
- The 50-character limit and allowed characters are a product default; adjust in planning if Figma or product states otherwise.
- The "Pular por agora" action is not in the provided Figma image; it is recorded as provisional and its visual replaced when the design exists. The backend does not block other features while the step is unresolved; the app enforces the prompt. The first-account/wallet onboarding step is a separate feature with its own flag.
- After the server confirms the save (or the skip), the screen calls its `onDone` callback; the next onboarding step and navigation destination are not yet designed and belong to the host app and the future session and navigation integration.
- The shared name rule is implemented once on the backend and exposed read-only on the user identity; clients display it and never recompute it. Notifications or emails sent by the backend, if any exist, use the same rule.
- The mobile app does not yet have session storage, route tree, or an API client (its authentication integration and navigation are undecided). This feature delivers the screen and its API service; wiring the on-every-app-open gating (FR-007) happens when that integration exists. The backend fully supports it.
- Technical routes, response formats, and storage belong to the plan, following the backend and mobile architecture guides and the Figma file as source of visuals.
