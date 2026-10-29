# Auth feature

Owns user authentication, sign-up, sign-in, provider adapters (Google, Apple), and session state.
Screen implementations map to Figma section `05.01 — Acesso`.

## Screen ownership

Authentication screens belong in `components/` inside this feature, alongside other feature-specific UI. Expo Router files in `src/app/` should only compose and export the route by importing a screen through this feature's `index.ts` public entry point. Keep screen layout, styles, and authentication-specific presentation out of route files.

The welcome screen is `components/welcome-screen.tsx`. Routes live in `src/app/` and only connect screen callbacks to Expo Router. Registration (`components/registration-screen.tsx`) and login (`components/login-screen.tsx`) use shared `Input`, `Button`, and `ScreenContainer` controls and call the existing API. Their Figma sources are [registration `138:386`](https://www.figma.com/design/QYrBUue2AdyrjvJLiMu7F2/blubby?node-id=138-386) and [login `110:362`](https://www.figma.com/design/QYrBUue2AdyrjvJLiMu7F2/blubby?node-id=110-362).

`auth-context.tsx` owns the session shared across routes. On native platforms, it stores the bearer token and installation UUID in Expo SecureStore, then validates restored tokens with `GET /api/user`. It preserves a token after temporary server/network failures and removes it after `401`. The UUID is generated once with Expo Crypto. On web, both values stay in memory because SecureStore has no web implementation; a reload requires signing in again.

Set `EXPO_PUBLIC_API_URL` to the backend base URL without `/api`. Copy `env.template` to `.env`. A host LAN IP only works for devices on that same LAN. Expo tunnel carries Metro traffic only; it does not proxy API requests. A physical device on another network and Expo Web served over HTTPS both need the API itself at a reachable HTTPS URL. Requests time out after 10 seconds so a stalled connection does not leave authentication actions loading forever. Restart Expo after changing `.env`. The temporary home displays the authenticated name/email and a sign-out action; its visual state is provisional because no home design has been approved. The login recovery link only explains that recovery is unavailable. Google/Apple sign-in and password recovery remain unimplemented because their backend flows are not available.

The welcome visual source is Figma `88:340`, with primary controls from `24:26`. Provider images remain existing local assets. The Apple icon uses the crop specified by Figma's `103:2` slot; its label renders as text so it can scale. Both provider labels use the bundled Google Sans Flex Medium through `theme.typography.providerButton`, matching the font, weight, size, line height, and letter spacing specified by `87:145` and `103:3`. See [font provenance](../../assets/fonts/README.md). Official SDK controls still require native validation before shipping provider authentication.
