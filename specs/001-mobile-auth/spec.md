# Feature Specification: Mobile Registration and Sign-In

**Feature Branch**: `main` (existing branch; no feature branch created)

**Created**: 2026-10-29

**Status**: Implemented (backend)

**Input**: User description: "Specify the discussed backend registration and login scope for native mobile clients: a separate session per device, current-user lookup, logout, and expiration after 30 days of inactivity. Active use renews the inactivity period; expiration is not measured from the original login. Include brute-force protection, rate limits by IP address and device, and passwords requiring a minimum length, uppercase and lowercase letters, a number, and a symbol."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Create an Account (Priority: P1)

As a new user, I can register with my name, email address, password, matching password confirmation, and device name, and immediately access my own account.

**Why this priority**: Registration gives a new user access without requiring a separate sign-in immediately afterward.

**Independent Test**: Register an unused email address and use the resulting session to retrieve the new user's public identity.

**Acceptance Scenarios**:

1. **Given** an unused email and valid details, **When** registration succeeds, **Then** exactly one account and an authenticated session for the requesting device are created.
2. **Given** missing or invalid fields, a password shorter than 12 characters, or mismatched confirmation, **When** registration is attempted, **Then** field-specific errors are returned and no account or session is created.
3. **Given** an existing email, **When** registration uses the same address with different casing or surrounding whitespace, **Then** no second account is created and registration reports that the email cannot be used.
4. **Given** simultaneous registrations for the same normalized email, **When** both are processed, **Then** at most one account is created and the unsuccessful request receives a controlled error without internal details.
5. **Given** initial session creation fails, **When** registration ends, **Then** no partial account remains and the user can retry registration.
6. **Given** an otherwise valid password of at least 12 characters, **When** registration is attempted with each required category omitted in turn (uppercase letter, lowercase letter, digit, or symbol), **Then** each omission produces a password validation error and creates no account or session.
7. **Given** passwords containing every required category, **When** registration is attempted with 11 characters and then with exactly 12 characters using otherwise valid details, **Then** the 11-character password is rejected and the 12-character password is accepted.
8. **Given** a password containing letters and digits but only whitespace in place of a symbol, **When** registration is attempted, **Then** it is rejected because whitespace does not satisfy the symbol requirement.

---

### User Story 2 - Sign In and Retrieve My Identity (Priority: P1)

As a registered user, I can sign in with my email and password on a named device and retrieve only my own public identity.

**Why this priority**: Existing users need to return and establish independent sessions on their devices.

**Independent Test**: Sign in with a pre-existing account, retrieve its identity, and verify that invalid credentials cannot establish a session.

**Acceptance Scenarios**:

1. **Given** a registered account, **When** the correct email, password, and device name are submitted, **Then** a session is issued and the user's public identity is returned.
2. **Given** a registered email, **When** sign-in uses different casing or surrounding whitespace and the correct password, **Then** the same account is authenticated.
3. **Given** an unknown email or an incorrect password, **When** sign-in is attempted, **Then** the same public credential error is returned and no session is issued.
4. **Given** a valid session, **When** the current user's identity is requested, **Then** only the session owner's public identity is returned, regardless of any other account identifier submitted.
5. **Given** missing, malformed, expired, or revoked credentials, **When** the current user's identity is requested, **Then** access is denied without disclosing account data.
6. **Given** an account signed in on one device, **When** sign-in succeeds on another device, **Then** both sessions remain independently usable.
7. **Given** an existing account whose password predates the new registration composition rules, **When** its correct password is submitted, **Then** sign-in verifies the existing password without applying new-password composition validation.

---

### User Story 3 - Stay Signed In While Active (Priority: P1)

As a user who continues using the app, I remain signed in without entering my password every 30 days.

**Why this priority**: Session continuity is an explicit product decision. A fixed lifetime from sign-in would interrupt active users.

**Independent Test**: Advance a controlled clock across the original sign-in's 30-day anniversary, with and without authenticated activity on each device.

**Acceptance Scenarios**:

1. **Given** a new session, **When** its first authenticated use occurs before 30 days have elapsed, **Then** access succeeds and a new 30-day inactivity period starts from that activity.
2. **Given** authenticated activity 29 days after sign-in, **When** the session is used 31 days after sign-in, **Then** access succeeds because it has not been inactive for 30 days.
3. **Given** a session with no authenticated activity for exactly 30 days or longer, **When** it is presented, **Then** access is denied and a new sign-in is required.
4. **Given** an expired session, **When** further requests present its credentials, **Then** those requests never renew or reactivate it.
5. **Given** two sessions for one account, **When** only one remains active, **Then** its activity does not extend the other's inactivity period.
6. **Given** a session approaching expiry, **When** a request fails authentication, **Then** its deadline is not extended.

---

### User Story 4 - Sign Out of the Current Device (Priority: P1)

As a signed-in user, I can end my current session without signing out my other devices.

**Why this priority**: Users need to remove a device's access independently of inactivity expiry.

**Independent Test**: Establish two sessions, sign out one, and verify that only the other can still retrieve the user's identity.

**Acceptance Scenarios**:

1. **Given** a valid session, **When** logout completes, **Then** subsequent requests using that session are denied.
2. **Given** another active session for the same account, **When** the current session signs out, **Then** the other session remains usable.
3. **Given** missing, expired, or revoked credentials, **When** logout is attempted, **Then** access is denied and no other session is changed.

---

### User Story 5 - Limit Abusive Authentication Attempts (Priority: P1)

As an account owner, I am protected against repeated password guessing and excessive registrations without my account becoming permanently locked.

**Why this priority**: Independent limits reduce brute-force attempts even when an attacker changes one source of identification.

**Independent Test**: Exercise each limit separately using controlled attempt counts and time, varying the other identifiers to prove that each protection remains effective.

**Acceptance Scenarios**:

1. **Given** the configured attempt budget for one IP address, **When** registration or sign-in attempts exceed that budget while device identifiers and emails vary, **Then** excess attempts are temporarily rejected without creating accounts or sessions.
2. **Given** the configured budget for one recognized device or app installation, **When** registration or sign-in attempts exceed that budget while IP addresses and emails vary, **Then** excess attempts are temporarily rejected without creating accounts or sessions.
3. **Given** the configured sign-in budget for one normalized email, **When** password guesses exceed that budget while IP addresses and devices vary, **Then** excess attempts are temporarily rejected without issuing sessions or revealing whether the account exists.
4. **Given** each independently enforced limit, **When** attempts reach its documented boundary and retry window, **Then** allowed attempts remain eligible up to its budget, excess attempts are rejected, and attempts become eligible after the retry restriction expires unless another limit still applies.
5. **Given** an IP or email restriction already applies, **When** a requester changes the device name or supplies a missing, malformed, rotated, or forged device identifier, **Then** that restriction remains effective and the requester gains no exemption from other protections.
6. **Given** simultaneous attempts against a nearly exhausted limit, **When** they are evaluated together, **Then** accepted attempts do not exceed the documented budget and rejected attempts create no accounts or sessions.

### Edge Cases

- Device names are human-readable labels, not proof of device identity. Matching names must not merge sessions or revoke another session implicitly.
- The device or installation signal used for abuse limits is distinct from the device name. A supplied identifier is not trusted proof of hardware identity; missing or changed identifiers must not disable IP or email protections.
- Users may share an IP address. Source restrictions must remain temporary and must not permanently disable accounts or revoke unrelated authenticated sessions.
- Repeating registration must not create duplicate accounts; repeating a successful sign-in can create an independent session.
- Registration and login must agree on email normalization. Password contents must not be trimmed, lowercased, or silently changed.
- Expiration uses elapsed time: 30 consecutive periods of 24 hours. Device clocks, local midnight, and daylight-saving changes do not alter the deadline.
- Before the first authenticated use, inactivity is measured from session creation; subsequent activity is measured independently for each session.
- An offline app opening, a delivered notification, or a failed sign-in does not establish authenticated activity.
- Concurrent requests must not move a deadline backward or restore a revoked or expired session. Cleanup must not remove a session renewed concurrently.
- Repeated credential attempts and excessive registrations must be temporarily limited without creating accounts or sessions for rejected attempts.
- Oversized or malformed fields must produce controlled validation errors. Unexpected fields must not set privileged attributes or select another account owner.
- Storage failures must not return success or expose credentials, persistence errors, or internal diagnostics.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST support native mobile registration using name, email, password, matching password confirmation, and a required device name. Registration MUST establish the initial session without a second sign-in.
- **FR-002**: The system MUST treat email addresses as case-insensitive identifiers and ignore surrounding whitespace consistently for registration and sign-in. Concurrent registration MUST NOT create duplicate normalized identities.
- **FR-003**: Invalid registration MUST produce field-specific errors. New passwords MUST contain at least 12 characters, including at least one uppercase letter, one lowercase letter, one digit, and one non-whitespace symbol (a character other than a letter or digit). Whitespace MUST NOT satisfy the symbol requirement. Passwords MUST be verified exactly as submitted and MUST NOT be stored in recoverable form. These composition rules apply to new passwords at registration, not to verification of existing passwords during sign-in.
- **FR-004**: Account and initial session creation MUST succeed or fail together. Failed registration MUST leave neither a usable session nor a partial account.
- **FR-005**: Sign-in MUST require email, password, and device name. Incorrect passwords and unknown emails MUST produce the same public credential error without issuing a session.
- **FR-006**: Every successful sign-in MUST create an independently revocable session belonging to the authenticated account and submitted device label. It MUST NOT implicitly invalidate existing sessions.
- **FR-007**: A session MUST expire after 30 consecutive days without authenticated activity, measured from creation or its latest authenticated activity. Valid activity before expiry MUST restart that session's 30-day inactivity period, including beyond 30 days after original sign-in.
- **FR-008**: Inactivity expiry MUST be checked before accepting activity as renewal. Activity at or after the deadline, revoked credentials, and failed authentication MUST NOT restore or extend a session.
- **FR-009**: Activity and expiry MUST be independent per session. Activity on one device MUST NOT keep another signed in. Concurrent activity MUST NOT shorten a renewed deadline or undo revocation.
- **FR-010**: An authenticated user MUST be able to retrieve their own public identity. Existing public current-user lookup fields MUST remain compatible; no new private account attributes may be exposed.
- **FR-011**: Logout MUST revoke only the session authorizing that operation. Missing or invalid credentials MUST NOT permit identity lookup or changes to another session.
- **FR-012**: Session credentials MUST be disclosed only when issued. Passwords, confirmations, stored credential verifiers, and session credentials MUST NOT appear in ordinary identity responses, logs, or diagnostic errors. Credential exchange MUST use encrypted transport outside local development.
- **FR-013**: Registration and sign-in MUST protect against brute-force and excessive attempts through independently enforced limits per IP address and per recognized device or app installation. Sign-in MUST additionally enforce an independent limit per normalized email. A combined IP/device/email limit alone MUST NOT substitute for these separate protections. Changing other identifiers MUST NOT reset the budget for a still-recognized restricted source. Restrictions MUST be temporary, MUST NOT permanently lock an account, and MUST NOT create accounts or issue sessions for rejected attempts. Planning MUST define explicit budgets, retry windows, trustworthy source identification, device-signal provenance and validation, and handling of missing, malformed, rotated, or forged identifiers before implementation. A device name or client-supplied identifier MUST NOT be treated as trusted hardware identity; unavailable or manipulated device signals MUST NOT bypass IP or email limits. Delivery MUST verify each dimension's boundaries, concurrent attempts, and recovery after the retry window.
- **FR-014**: All authentication inputs MUST have explicit length and shape bounds. Only allowed account fields may be assigned; submitted ownership, verification, or privilege attributes MUST NOT be accepted as authoritative.
- **FR-015**: Expired and revoked sessions MUST remain unusable regardless of record cleanup timing. A defined cleanup policy MUST remove obsolete session credentials without removing accounts or active sessions.

### Key Entities *(include if feature involves data)*

- **User account**: Registered identity with a name, normalized unique email, protected password verifier, and public identifier. Owns independently managed sessions.
- **Device session**: Authenticated grant belonging to one account, with a device label, creation time, last authenticated activity, inactivity deadline, and revocation state. Labels do not uniquely identify hardware.
- **Attempt restriction**: Temporary registration or sign-in restriction with an explicit scope, attempt budget, and retry window. Independent scopes include IP address, recognized device or app installation, and normalized email for sign-in. Does not change account ownership or permanently disable an account.
- **Device abuse signal**: A device or app-installation identifier used to associate authentication attempts for rate limiting, distinct from the human-readable session label. Its provenance and limitations must be defined before implementation; it is not proof of account ownership or physical hardware identity.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Every valid registration acceptance case creates exactly one account and permits identity lookup without separate sign-in; every invalid case creates zero accounts and zero usable sessions.
- **SC-002**: All acceptance cases involving incorrect passwords, unknown accounts, and missing, expired, or revoked credentials deny access without disclosing protected account data.
- **SC-003**: A user active on day 29 can access their account on day 31 without signing in again. A session unused for exactly 30 consecutive days is denied on its next use.
- **SC-004**: In every two-device acceptance case, renewing or signing out one session leaves the other's validity and inactivity deadline unchanged.
- **SC-005**: Two simultaneous registrations for the same normalized email create at most one account; interrupted registration leaves no partial account.
- **SC-006**: Every independent IP, device or installation, and sign-in email limit rejects excess attempts for its documented retry window and allows eligible attempts afterward unless another limit still applies. Varying other identifiers and simultaneous attempts do not bypass an applicable budget; all rejected attempts create zero accounts and zero sessions, without permanent account lockout.
- **SC-007**: Every tested public identity response and authentication failure excludes passwords, stored credential verifiers, session credentials, and internal error details.
- **SC-008**: Cleanup acceptance cases remove obsolete session credentials while preserving 100% of active sessions and accounts, including sessions renewed concurrently with cleanup.
- **SC-009**: Every registration case missing an uppercase letter, lowercase letter, digit, or non-whitespace symbol is rejected. A password with every required category is rejected at 11 characters and accepted at 12 characters when all other registration details are valid. Correct existing passwords remain usable for sign-in regardless of new-password composition rules.

## Assumptions

- This delivery implements backend behavior for native mobile clients. Mobile forms, navigation, secure credential storage, and handling expired sessions are separate client work; no screen is specified here.
- Scope includes email/password registration, sign-in, current-user lookup, and current-session logout. Google, Apple, account linking, password recovery, email verification flows, session-management screens, and logout of all devices are excluded. Email verification is not a prerequisite for this initial sign-in flow.
- The user explicitly requires password composition and IP/device brute-force protections. The existing 12-character minimum is retained as a requirement alongside uppercase, lowercase, digit, and non-whitespace symbol requirements; this is the feature's policy, not a claim about a universal industry standard.
- Authenticated activity means a request accepted as belonging to a valid session by the backend. Unauthenticated traffic and offline interaction do not count. This delivery introduces no periodic keepalive solely to prevent expiry.
- Device names label independently issued sessions. Device or installation abuse identification is required for rate limiting, but permanent hardware fingerprinting and session deduplication by physical device are not mandated.
- Numeric attempt budgets, retry windows, trustworthy IP identification, device-signal provenance and validation, fallback behavior for unavailable or manipulated identifiers, field maximum lengths, credential permissions, and cleanup scheduling are security and operational parameters to resolve explicitly during planning under the existing [security rules](../../backend/docs/security/README.md). They do not authorize an absolute session lifetime or change inactivity behavior.
- Existing account persistence and authentication support are prerequisites, not evidence that this feature is implemented. Planning follows the established [backend architecture](../../backend/docs/architecture/overview.md) and preserves current-user lookup compatibility. Technical routes, response formats, stack choices, and schema changes belong in the plan and contracts.
- The inactivity rule comes from the user's explicit correction. The [Telegram device-management reference](https://telegram.org/blog/protected-content-delete-by-date-and-more) demonstrates inactivity-based logout in a real app; it does not establish a universal 30-day duration or prescribe implementation.
