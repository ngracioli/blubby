# Provider typography

`google-sans-flex-medium.ttf` is the locally bundled static Google Sans Flex Medium used by the social button labels. The default typography follows Figma text nodes [Google `87:145`](https://www.figma.com/design/QYrBUue2AdyrjvJLiMu7F2/blubby?node-id=87-145) and [Apple `103:3`](https://www.figma.com/design/QYrBUue2AdyrjvJLiMu7F2/blubby?node-id=103-3): weight 500, font size 16, line height 24, zero letter spacing, width 100, roundness 0, and upright slant 0.

The app loads this font in `src/app/_layout.tsx` under `GoogleSansFlex_500Medium`. `theme.typography.providerButton` selects that family and its Figma metrics. Product typography continues to use Manrope.

## Source and license

- Official source: [Google Fonts](https://github.com/google/fonts/tree/bd8f81ddb5c74d5c8897b36ad88b440266245103/ofl/googlesansflex).
- Upstream commit: `bd8f81ddb5c74d5c8897b36ad88b440266245103`.
- Original file: `GoogleSansFlex[ROND,opsz,slnt,wdth,wght].ttf`, version `5.000;[cedf1e9]`.
- License and copyright: [SIL Open Font License 1.1](OFL.txt), retained with the asset.
- Original SHA-256: `a9ac770798857d5e8ac593ced42b22e40a7c08cec98319f92b0eccc2783a77ba`.
- Static file SHA-256: `d918a3dc4ee539f66f3e91cd1dbfef8884730b50bf8fd5b57f4fc74289671e01`.

The original variable font was instantiated at its named Medium location with fontTools 4.67.0: `wght=500`, `wdth=100`, `ROND=0`, `slnt=0`, and upstream default `opsz=18`. Figma exposes no optical-size override; the remaining coordinate therefore follows the named font instance. Figma's `GRAD=0` is the normal grade; the upstream distribution has no variable grade axis. The generated file has no `fvar` table and has `OS/2.usWeightClass=500`.

The static font keeps the complete upstream glyph set. Font preparation used a temporary build tool outside the app; no font conversion runs in production and no application dependency was added.

## Reproduction

Download the original TTF and `OFL.txt` from the pinned official source and use [fontTools instancer](https://fonttools.readthedocs.io/en/latest/varLib/instancer.html):

```sh
python3 -m fontTools.varLib.instancer \
  'GoogleSansFlex[ROND,opsz,slnt,wdth,wght].ttf' \
  wght=500 wdth=100 ROND=0 slnt=0 opsz=18 \
  --update-name-table --output=google-sans-flex-medium.ttf
```

Recheck the Figma font family and variation settings before replacing this asset. Preserve its license and update source/version/hash evidence when the upstream asset changes. Font rendering still needs comparison on the target runtime; matching font metadata does not guarantee identical rasterization across platforms.
