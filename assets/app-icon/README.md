# Blubby app icon

Adapted from the symbol provided in `../logo/blubby-symbol-color.png`. The icon uses a white symbol on ocean blue `#1E3A8A`, without text, shadows, or baked-in rounded corners.

## Files

- `blubby-app-icon.svg`: square vector master.
- `blubby-app-icon-1024.png`: 1024 × 1024 RGB master without transparency.
- `blubby-google-play-icon-512.png`: 512 × 512 opaque RGBA PNG, sRGB.
- `blubby-android-foreground.svg`: transparent Android vector foreground.
- `blubby-android-foreground-1024.png`: 1024 × 1024 transparent raster foreground.
- `blubby-symbol-mask.png`: source mask used for vectorization; do not use as the final icon.

## Usage and proportions

The symbol occupies 64% of the height in the master and Google Play asset. Final files remain square; the platform applies the crop.

For Android adaptive icons, use the transparent foreground and a solid `#1E3A8A` background as separate layers. The silhouette occupies 488/1024 of the foreground height and stays within the central 66/108 reference area. The same silhouette can serve as the monochrome layer. The composed Google Play image does not replace these layers.

Integration into iOS and Android projects, platform-specific appearances, and device testing will follow the mobile technology decision. Figma previews show crops and legibility at 32, 48, and 64 units; they do not replace hardware testing.

References:
- https://developer.apple.com/design/human-interface-guidelines/app-icons
- https://developer.android.com/develop/ui/compose/system/icon_design_adaptive
- https://developer.android.com/distribute/google-play/resources/icon-design-specifications

## Origin and verification

The selected mask was created with the integrated imagegen tool, using the provided symbol as a reference. Vectorization preserves three separate bands; the area difference for each contour was below 1% relative to the mask. Composition and exports were completed in Figma.

Transparency extraction attempts that produced noise were not used. Original logos remain in `assets/logo/`.

Verified: dimensions, master opacity, foreground transparency, background color, and margins. No trademark search or exclusivity analysis was performed.

### Final mask prompt

```text
Edit target: attached original Blubby symbol. Make a precise high-contrast MASK of that same symbol. Output an OPAQUE square image: a perfectly flat pure BLACK #000000 background and the existing symbol rendered in perfectly flat pure WHITE #FFFFFF. NO transparency. Preserve its exact abstract lowercase b silhouette, proportions and smooth curves: exactly THREE separate white wave bands and TWO open black wavy gaps. Preserve the tall rounded upper-left ascender and curved lower band. No wordmark. No redesign. No texture, gradients, shine, shading, blur, outlines, noise, dots, extra pixels, shadows, or 3D. Each white band must be a completely solid, uniform filled shape with clean smooth edges. Center the symbol inside the square canvas with 15% or more black margin on every side; the visible symbol should occupy around 66% of the image height. Background absolutely black, foreground absolutely white. This is an engineering luminance mask for an app icon, NOT a presentation or mockup.
```
