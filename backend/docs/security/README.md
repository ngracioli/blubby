# Security

Owns authentication, authorization, provider integration, session lifecycle, credential handling, and per-user data isolation.

The MVP includes email/password, Google on both mobile platforms, and Apple on iOS. Provider selection, account linking, recovery, verification, and session transport still require detailed decisions.

Derive user identity from validated authentication. Authorize access to every financial resource; a client-supplied resource or user identifier is not proof of ownership. Apply the same isolation to reads, writes, projections, and background recurrence processing.

Keep secrets out of source control and logs. Do not log passwords, tokens, or raw sensitive financial payloads. Verify cross-user access attempts and expired or invalid sessions.
