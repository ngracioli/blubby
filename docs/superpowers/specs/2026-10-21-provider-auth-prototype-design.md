# Social provider sign-in prototype

## Goal

Show what happens after a user selects Google or Apple on the entry screen, without pretending to authenticate or reproducing provider-owned interfaces.

## Scope

- Update the existing Figma access flow only.
- Reuse the iOS and Android entry frames and the existing monthly planning frame.
- Google is available on iOS and Android; Apple is available on iOS only.
- From a provider button, show a prototype handoff labeled as an external Google or Apple interface. The handoff is a placeholder, not a provider screen.
- Let the reviewer choose one of three prototype outcomes: success, cancellation, or failure.
- Success links to the existing current-month planning screen.
- Cancellation returns to the platform's entry frame without an app message.
- Failure returns to the entry frame with an alert: “Não foi possível entrar. Tente novamente.” The alert offers “Tentar novamente” and “Fechar”. Retry returns to the external handoff; close dismisses the alert.
- Mark the handoff and outcome controls as prototype-only. They do not call a provider or create a session.

## Out of scope

- App code, SDK selection, provider configuration, credentials, backend exchange, session persistence, and account linking.
- Custom Google or Apple consent/account-selection screens.
- New success screen; the approved destination is the existing monthly planning view.

## Design direction

Keep the existing entry and planning frames unchanged. Add the minimum prototype-only handoff and failure state needed to demonstrate the approved outcomes. Use the existing Figma components and tokens where applicable. The handoff must clearly identify provider UI as external; the failure alert must be labeled illustrative because its final appearance belongs to the platform's native UI.

## Acceptance checks

- iOS Google and Apple buttons reach their matching external handoff placeholder.
- Android Google reaches its external handoff placeholder; Android has no Apple path.
- Success, cancellation, and failure can each be previewed from the handoff.
- Success reaches current-month planning; cancellation returns to entry quietly; failure shows retry and close actions.
- Retry returns to the matching provider handoff, and close dismisses the failure alert.
- No screen suggests a real provider authentication occurred.
