# Auth feature

Owns user authentication, sign-up, sign-in, provider adapters (Google, Apple), and session state.
Screen implementations map to Figma section `05.01 — Acesso`.

## Screen ownership

Authentication screens belong in `components/` inside this feature, alongside other feature-specific UI. Expo Router files in `src/app/` should only compose and export the route by importing a screen through this feature's `index.ts` public entry point. Keep screen layout, styles, and authentication-specific presentation out of route files.

The welcome screen is `components/welcome-screen.tsx`. Its route is `src/app/index.tsx`. It composes shared `Button`, `Divider`, and `ScreenContainer` controls and accepts `onCreateAccount`, `onSignIn`, `onGoogleSignIn`, and `onAppleSignIn` callbacks. Authentication orchestration remains in this feature. The route supplies no handlers until the authentication flows are implemented; the screen does not simulate login or invent navigation destinations.

The visual source is Figma `88:340`, with primary controls from `24:26`. Provider images remain existing local assets. The Apple icon uses the crop specified by Figma's `103:2` slot; its label renders as text so it can scale. Both provider labels use the bundled Google Sans Flex Medium through `theme.typography.providerButton`, matching the font, weight, size, line height, and letter spacing specified by `87:145` and `103:3`. See [font provenance](../../assets/fonts/README.md). Official SDK controls still require native validation before shipping provider authentication.
