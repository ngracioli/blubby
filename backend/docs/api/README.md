# API

Owns HTTP operations, request and response schemas, authentication requirements, errors, pagination, and compatibility.

Document each operation's authorization, validation, financial effect, transaction boundary, and retry behavior. Define money and date serialization together with the frontend. Do not expose persistence entities as accidental public contracts.

OpenAPI is the proposed contract format. Select a supported specification version with the eventual tooling. Define duplicate prevention for financial writes before enabling automatic client retries.

Examples must stay within the [MVP](../../../mvp.md). No endpoints are approved or implemented by this guide.
