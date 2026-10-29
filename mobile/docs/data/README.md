# State and data

Owns local interaction state, form state, session lifecycle, server data, cache ownership, and API error presentation.

Keep transient state close to its consumer. Avoid duplicating server data in a second global store. A cache library and an offline strategy have not been selected.

Authentication session state is shared by the route tree through the auth feature context. Native bearer tokens and installation UUIDs use Expo SecureStore; restored tokens are checked with the backend before protected routes render. Temporary API failures preserve the token for retry, while `401` removes it. Web uses memory-only values because Expo SecureStore has no web implementation; browser reloads therefore end the session.

Before allowing offline writes, define local persistence, conflict resolution, retry semantics, and duplicate prevention with the backend. Network failure does not prove that a financial mutation failed on the server.

Define money and date transport formats jointly with the Laravel backend. Do not use JavaScript floating-point arithmetic as the authoritative source of financial results. Clear user-specific cached and persisted data on account changes according to the chosen session policy.
