---
name: figma-design
description: Consult and verify visual designs, screens, wireframes, and design decisions in the official Figma file before writing or updating UI code. Use whenever the user discusses UI design, screens, layout, styling, or visual components.
---

# Figma design discipline

## Core principle

**All UI design must come from the official Figma file.** Never invent layouts, arbitrary colors, spacing, typography, or visual components from assumptions.

- **Figma reference documentation:** Consult the [Figma reference map](../../../docs/design/figma-reference-map.md) for current file key, section structure, and known node IDs.
- **Component contract:** Read the [shared component rules](../../../mobile/docs/components/README.md) before implementing mobile screens or components.
- **MCP tools:** Use `get_figma_data` and `download_figma_images` via the `figma` server.

## Procedure

Whenever the user discusses UI design, requests a screen, or asks for visual styling:

### 1. Consult the reference map and search Figma
- Inspect the [Figma reference map](../../../docs/design/figma-reference-map.md) to locate the relevant section and node IDs.
- Call `get_figma_data` using the documented file key and target `nodeId`.
- Read layout, variable fills, typography, dimensions, and Lucide icons.

### 2. If the design is found
- Describe the found Figma elements to the user (node ID, hierarchy, copy, tokens, and layout).
- **Ask for confirmation** from the user before finalizing or writing UI code.
- Ensure the implementation strictly maps to project design tokens in `mobile/src/shared/theme/` and uses `ScreenContainer`.

### 3. Prepare components before composing the screen
- Map the confirmed Figma elements and required states to existing components. Reuse their public API through `@/shared/components`.
- Implement or extend only the missing components and states needed for this screen first, within the same task. Domain-agnostic primitives belong in `mobile/src/shared/components/`; feature-specific compositions stay in their owning feature. Follow the shared component rules for Figma defaults, typed customization, documentation, and interaction/accessibility contracts.
- Compose the screen from these components inside `ScreenContainer`. Do not copy primitive styles or interaction logic into the screen, or recreate library controls with private native implementations. Native layout containers and text are allowed; do not extract every `View` or `Text` or build speculative components.
- Before finishing, verify actual consumers use the component APIs without duplicate primitive implementations and complete the shared component acceptance checks for affected components.

### 4. If the design is NOT found or is incomplete
- Stop immediately. Do not guess or invent visual design.
- Inform the user clearly that the requested screen, element, or state is not present or detailed in the Figma file.
- Discuss the missing design with the user: agree on what is needed, recommend updating or sketching it in Figma first, or establish concrete requirements before writing code.
