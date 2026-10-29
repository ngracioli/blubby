# Entry and authentication

This document records the approved structure of the initial screen before sign-in and follows the [MVP](../../mvp.md). This study does not define the technical implementation of authentication.

## Approved structure

The screen presents the logo and an introductory phrase, followed by:

1. Primary action "Criar conta".
2. Sign-in access "Acessar minha conta".
3. Separator "ou continue com".
4. Continue with Google on iOS and Android.
5. Continue with Apple on iOS only.

Email and password fields belong on subsequent screens; they do not appear on this entry screen. The confirmed methods are email and password, Google, and Apple on iOS.

After successful authentication, the user goes directly to the current month's planning, as defined in the MVP. On first use, guidance to add the first account or wallet appears in that context, without a mandatory setup wizard before planning.

## Visual and copy proposal

The [Figma study](https://www.figma.com/design/QYrBUue2AdyrjvJLiMu7F2/blubby?node-id=88-334) is on page **05 — Telas do MVP**, section **05.01 — Acesso**. It contains two 393 × 852 versions: iOS (`88:340`) and Android (`88:374`).

The proposed copy is:

- "Seu mês organizado. Seu futuro mais claro."
- "Organize suas finanças de hoje e enxergue os próximos meses."

The product typeface is Manrope. The study uses Google Sans Flex Medium on both social button labels (`87:145` and `103:3`): weight 500, size 16, line height 24, and zero letter spacing. The implementation bundles a static Medium font locally and loads it before rendering. The Apple icon uses the official Brazilian Portuguese asset (`pt_BR`). Native provider controls and branding requirements still require validation when authentication is integrated.

Previews are available in [welcome-ios.png](../../assets/wireframes/welcome-ios.png), [welcome-android.png](../../assets/wireframes/welcome-android.png), and [welcome-review.png](../../assets/wireframes/welcome-review.png). Branding assets are [google-g.png](../../assets/auth/google-g.png) and [apple-continue-white.png](../../assets/auth/apple-continue-white.png).

## Limits and open decisions

The welcome, email registration, and email login screens now connect to the implemented backend registration, login, current-user, and logout endpoints (see `backend/docs/api/README.md`). Native sessions persist in secure storage; web sessions last only until page reload. Social providers and account recovery remain unimplemented. The recovery link currently explains that the feature is unavailable. The temporary post-authentication home only confirms the signed-in identity and offers sign-out; it does not replace monthly planning. The screens have not been validated on a device or with enlarged text.

The following remain open:

- Mobile form wording and device-identity storage (backend rules: 12+ character password with upper/lower/digit/symbol, `device_id` UUIDv4).
- Email verification and account recovery.
- Account linking across authentication methods.
- Error, cancellation, and provider return states.
- Technical implementation and validation of official button requirements.

These items are not decisions approved by this study.

## Official references

- [Google — authentication branding guidelines](https://developers.google.com/identity/branding-guidelines).
- [Apple — Sign in with Apple](https://developer.apple.com/design/human-interface-guidelines/sign-in-with-apple).
