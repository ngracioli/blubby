# Shared component verification

Verified on October 22, 2026. This record separates implementation evidence from checks that require an actual browser or mobile device. Passing automated checks does not establish a universal quality score or complete accessibility certification.

## Implemented scope

| Component | Implemented contract |
| --- | --- |
| [Button](button.md) | Solid, outline, and text variants; numeric minimum size; primary/error tone; explicit light/dark palettes and palette overrides; optional leading/trailing icons; disabled/loading; custom indicator/content; state-aware root, content, label, and icon styles; native events and ref. |
| [Input](input.md) | Controlled/uncontrolled native input; label, helper/error text; numeric minimum size; light/dark palettes and overrides; optional accessories; disabled/read-only/invalid/focus states; styles for each part; native options, events, and ref; web error association. |
| [Divider](divider.md) | Optional built-in/custom label; line color, thickness, gap, and part styles; native root props/ref; decorative lines hidden from accessibility trees. |
| [ScreenContainer](screen-container.md) | Root styling; shared content styling in scroll/static modes; safe areas; maximum width; native root/content props and root ref; separate scroll/keyboard props and styles; intrinsic content growth. |

`WelcomeScreen` consumes the shared controls and accepts feature callbacks. Fixed button heights were replaced with growing controls. Introduction and scroll content can grow with text. Existing local brand/provider assets are reused, with the Apple icon crop matching the current Figma slot. Authentication handlers remain intentionally absent from the route until authentication flows are implemented.

The implementation reuses React composition and native controls. Its shared style/accessory helpers serve both Button and Input. No new production UI dependency, theme provider, class hierarchy, or component factory was introduced.

## Completed checks

| Check | Result |
| --- | --- |
| `npm test -- --watch=false` from `mobile/` | Passed: 14 tests in 5 suites across native and web projects. The suite was reduced from 57 tests to focus on essential contracts. |
| `npm run typecheck` from `mobile/` | Passed, including test types. |
| `CI=1 npx expo export --platform web --output-dir /tmp/blubby-ui-web-font` from `mobile/` | Passed. Web bundle, existing local assets, and Google Sans Flex Medium font exported. This is compilation evidence, not a screenshot comparison. |
| `git diff --check` | Passed before this evidence record was added; checked again with the final documentation. |
| Source review | Reviewed by a separate reviewer. Reported issues with web error association, native ID alias precedence, decorative accessibility, disabled focus, boolean content, and scroll flex sizing were corrected. |
| Figma inspection | Read design context and screenshots for Button `24:26`, Field `24:119`, and Welcome `88:340`. Confirmed default dimensions, typography, state colors, layout, and asset slots in source. |
| Provider font alignment | Rechecked text nodes `87:145` and `103:3` directly: Google Sans Flex Medium, weight 500, size 16, line height 24, zero letter spacing, width 100, roundness 0, slant 0, normal grade. Bundled the official named Medium instance as a static font, confirmed weight 500 and no `fvar` table, checked its hash, and registered the exact alias used by the typography token. See [font provenance](../../src/assets/fonts/README.md). |

Native component tests cover activation, disabled/loading blocks despite visual/accessibility overrides, controlled input edits, read-only restrictions, error accessibility, and representative state-aware customization. Root/content styling is checked in both ScreenContainer modes. The welcome integration test checks each button's corresponding callback and retains the provider font regression assertion.

Web tests render actual React Native Web components through React DOM in jsdom. They cover ref-driven focus/blur, decorative icon hiding, error descriptions, invalid/read-only attributes, and `id`/`nativeID` precedence. jsdom does not implement browser layout.

The focused suite contains four Button tests, four Input tests, two ScreenContainer cases, one welcome integration test, and three web checks. Exhaustive color/theme matrices, wrapper counts, trivial native ref identity checks, duplicated prop cases, and the standalone Divider suite were removed. Visual fidelity and every possible customization combination are not claimed as automated coverage.

## Remaining runtime checks

- Compare rendered screenshots against Figma on supported viewports. No connected browser was available in this session. Figma metadata and successful exports cannot prove visual fidelity.
- Check Yoga layout and scrolling with large system text, compact screens, landscape, and multiline inputs on iOS and Android. Source uses minimum heights and intrinsic content growth; mocked native tests do not execute Yoga.
- Check native keyboard avoidance, selection, autofill, secure entry, read-only behavior, touch targets, RTL, and actual focus outlines.
- Check VoiceOver and TalkBack announcements and focus order. DOM attributes and native accessibility props are covered, but actual assistive technologies were unavailable.
- Compare the bundled Google Sans Flex rendering on target devices and validate official provider controls when implementing Google/Apple authentication. The former Manrope provider fallback has been removed; source/font metadata checks do not prove identical rasterization across platforms.
- Check contrast and interaction targets after consumer overrides; arbitrary customization can remove accessible colors or layout space.

The package manager reported dependency advisories and an optional Expo/worklets peer warning during test setup. A project-wide dependency security audit and dependency migration were outside this component change. Do not treat passing component checks as proof that the entire dependency graph has no advisories.

These remaining checks prevent an honest unconditional “100/100” claim. The implementation closes the concrete code and API findings from the earlier review; runtime acceptance still requires the listed environments.

## Future Figma alignment

Root `AGENTS.md` now requires relevant new or changed Figma drawings to update existing default tokens, styles, consumers, and documentation while preserving customization. Current drawing gaps and their replacement criteria are recorded in [Button](button.md#provisional-visual-states) and [Input](input.md#limits-and-verification). These capabilities are provisional customization, not additional approved Figma presets.
