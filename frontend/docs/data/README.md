# State and data

Owns local interaction state, form state, session lifecycle, server data, cache ownership, and API error presentation.

Keep transient state close to its consumer. Avoid duplicating server data in a second global store. A cache library and an offline strategy have not been selected.

Before allowing offline writes, define local persistence, conflict resolution, retry semantics, and duplicate prevention with the backend. Network failure does not prove that a financial mutation failed on the server.

Define money and date transport formats jointly with the backend. Do not use JavaScript floating-point arithmetic as the authoritative source of financial results. Clear user-specific cached and persisted data on account changes according to the chosen session policy.
