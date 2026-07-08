# Current State

- The Texas SVG map component has been updated to include a staggered scroll reveal animation on load/scroll, bringing it in line with the expected delay behavior from the original K-12 site.
- An `IntersectionObserver` was added globally in `src/layouts/Layout.astro` to detect when elements with `.texas-svg-map` enter the viewport and apply an `.is-visible` class.
- The staggered animations are handled entirely via CSS keyframes in `src/styles/mobile.scss` utilizing `nth-child` delay increments to animate `.texas-region` elements smoothly.
- Changes were safely pushed to `main` following the conflict-free branch merging protocol.
- **Design-Build Styling Update**: Implemented styling updates for the Design-Build page (`/design-build`).
  - Modified `src/styles/mobile.scss` to apply a maximum width of 1200px and centering to `.design-build` containers, including the direct `> .wp-block-columns` child.
  - Constrained block editor mobile columns overrides to 1200px max-width and centered them.
  - Modified `src/styles/desktop.scss` block editor columns overrides to set `max-width: 1200px !important` and centered them.
- **Breadcrumb Industry Fallback**: Fixed client page breadcrumb logic (`src/pages/clients/[slug].astro`) to check the native `industry` taxonomy terms before falling back to legacy meta fields or default K-12. This correctly assigns clients like Goodall-Witcher to Healthcare.
