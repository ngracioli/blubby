# Blubby iconography

Decision recorded on 2026-10-20: use **Lucide outline** as the main interface icon family. Its restrained, rounded geometry accompanies Manrope and the ocean identity without competing with financial amounts.

## Usage rules

- Use the original 24 × 24 grid, stroke width 2, and rounded caps and joins. Preserve official SVG geometry when scaling.
- Project sizes: 20 for supporting content, 24 by default, and 32 for occasional highlights. Touch targets are independent: at least 48 × 48 units.
- Keep outlines and weight consistent. Selection uses a surface, indicator, and accessible state; do not arbitrarily fill paths or mix families.
- Leave 8 units between an icon and its label, and 12 between an identifying icon and a list item's content. Align with the title in multiline items.
- Use existing semantic tokens in light and dark themes. An expense is not an error; transfers and cancellations use neutral treatment. Do not communicate states through color alone.
- Income, expenses, installments, and projections need supporting text. Icons do not replace currency, sign, date, source, destination, or state.
- Use specific Portuguese UI states such as “Recebido”, “Pago”, and “Realizado” as appropriate. Closing a panel does not cancel a financial occurrence.
- Give buttons without text an accessible action name. Decorative icons beside labels must not duplicate screen-reader announcements.
- Bank, store, and app logos are separate assets, outside the interface icon family.

## Gradual adoption

The family and usage rules are defined. The [lucide](lucide/) directory contains 12 official SVGs, with [LICENSE](lucide/LICENSE) and [sources.json](lucide/sources.json): `chevron-right`, `chevron-down`, `eye`, `credit-card`, `arrow-down-left`, `arrow-up-right`, `utensils`, `shopping-cart`, `wallet`, `calendar-days`, `list`, and `settings`.

These icons also form 12 initial `Icon/Lucide` components on the Figma page **03 — Componentes**, used in monthly planning wireframes. The navigation experiment adds `NavigationTab` (`82:138`) and `BottomNavigation/Wireframe` (`82:139`), retaining Lucide's 24 × 24 grid and stroke width 2. These components belong to a proposal under review, not an approved final navigation system.

No mobile package has been installed. Add only the icons needed as screens are designed, recording each concept, official icon name, and usage. The proposed inventory guides selection; it does not define app navigation.

Review legibility at 20 and 24 units, in light and dark themes, and alongside enlarged text before completing each icon's adoption. Device, dark-theme, and enlarged-text legibility have not been validated. The mobile technology and integration package remain undecided.

## Sources and license

- [Official catalog](https://lucide.dev/icons/)
- [Official documentation](https://lucide.dev/guide/)
- [Official license](https://lucide.dev/license)

Lucide uses the ISC license, with Feather-derived icons subject to the MIT notices listed on its official page. When incorporating assets or a package, preserve the notices and include the license for the version used. This documentation does not replace the license file distributed with the SVGs.

In Figma: rules under **02 — Fundamentos → Iconografia**; initial components under **03 — Componentes**.
