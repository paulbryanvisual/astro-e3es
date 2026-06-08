# E3 Headless Custom Gutenberg Blocks Specification

This document defines the exact BEM HTML structures and classes output by the custom WordPress blocks in the `e3es-headless-helper` plugin. These must match the CSS styling rules defined in `src/styles/mobile.scss` and `src/styles/desktop.scss`.

---

## 1. Intro Banner (`e3es/intro-banner`)
Renders a full-width hero header section at the top of subpages.

### HTML Structure
```html
<section class="wp-block-e3es-intro-banner db-page-hero" style="background-image: linear-gradient(rgba(14, 53, 27, 0.7), rgba(14, 53, 27, 0.7)), url('{bgImageUrl}');">
    <div class="db-page-hero__container">
        <h1 class="db-page-hero__title">{title}</h1>
        <!-- Optional Intro Text -->
        <div class="db-page-hero__intro">
            <p>{excerptText}</p>
        </div>
    </div>
</section>
```

---

## 2. Two Column Feature (`e3es/two-column`)
Renders a text content block on one side and a stylized skewed image on the other.

### HTML Structure
```html
<!-- Background style modifiers: db-feature--white, db-feature--grey, db-feature--green -->
<!-- Image alignment modifiers: db-feature__container--reverse (puts image on left) -->
<section class="wp-block-e3es-two-column db-feature db-feature--{bgStyle}">
    <div class="db-feature__container {reverse ? 'db-feature__container--reverse' : ''}">
        <div class="db-feature__content">
            <div class="db-feature__icon">
                <!-- SVG Icon (e.g. clock, shield, dollar) -->
            </div>
            <h2>{heading}</h2>
            <p>{content}</p>
        </div>
        <div class="db-feature__image-wrapper">
            <img src="{imageUrl}" alt="{imageAlt}" class="db-feature__image" />
        </div>
    </div>
</section>
```

---

## 3. Design-Build Advantage Card Grid (`e3es/design-build-advantage`)
Renders a section with a grid layout containing design-build cards.

### Grid Container
```html
<section class="design-build">
    <div class="design-build__container">
        <div class="design-build__grid">
            <!-- design-build__card blocks go here -->
        </div>
    </div>
</section>
```

### Card Block (`e3es/design-build-card`)
```html
<div class="design-build__card">
    <div class="design-build__icon">
        <!-- SVG Icon -->
    </div>
    <h3 class="design-build__card-title">{title}</h3>
    <p class="design-build__card-text">{text}</p>
</div>
```

---

## 4. Core Pillars Grid (`e3es/core-pillars`)
Renders a grid block container holding solution pillar components.

### Pillars Container
```html
<section class="db-pillars">
    <div style="max-width:1200px; margin:0 auto; display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:3rem">
        <!-- e3es/core-pillar blocks go here -->
    </div>
</section>
```

### Pillar block (`e3es/core-pillar`)
```html
<div style="background:white; padding:2.5rem; box-shadow:0 10px 30px rgba(0,0,0,0.05); border-top:4px solid var(--color-primary-green)">
    <h3 style="color:var(--color-primary-green); font-size:1.25rem; margin-bottom:1rem; text-transform:uppercase; letter-spacing:1px; line-height:1.3">{title}</h3>
    <p style="margin-bottom:0">{text}</p>
</div>
```

---

## 5. CTA Banner (`e3es/cta-banner`)
Renders a bright call-to-action banner block.

### HTML Structure
```html
<section class="cta-banner">
    <div class="cta-banner__container">
        <h2 class="cta-banner__title">{title}</h2>
        <p class="cta-banner__text">{text}</p>
        <a href="{btnUrl}" class="btn btn--primary cta-banner__btn">{btnText}</a>
    </div>
</section>
```

---

## 6. Mini Testimonial (`e3es/mini-testimonial`)
Renders a simple inline blockquote quote card.

### HTML Structure
```html
<div class="mini-testimonial">
    <blockquote>{quote}</blockquote>
    <cite>{cite}</cite>
</div>
```

---

## 7. Texas Interactive Map (`e3es/texas-interactive-map`)
Renders the regional vector map. Interactivity is hydrated client-side on the BEM selectors.

### HTML Structure
```html
<section class="map-section">
    <div id="region-links-list">
        <ul style="list-style: none; padding: 0; display: flex; flex-direction: row; justify-content: center; flex-wrap: wrap; gap: 1rem;">
            <li><a href="#" class="btn btn--outline region-link" data-region="panhandle">Far West Texas</a></li>
            <li><a href="#" class="btn btn--outline region-link" data-region="west">West Texas</a></li>
            <li><a href="#" class="btn btn--outline region-link" data-region="north">North Texas</a></li>
            <li><a href="#" class="btn btn--outline region-link" data-region="northeast">North East Texas</a></li>
            <li><a href="#" class="btn btn--outline region-link" data-region="southeast">South East Texas</a></li>
            <li><a href="#" class="btn btn--outline region-link" data-region="central">Central Texas</a></li>
            <li><a href="#" class="btn btn--outline region-link" data-region="hill-country">Hill Country</a></li>
            <li><a href="#" class="btn btn--outline region-link mockup-clickable" data-region="south">South Texas</a></li>
        </ul>
    </div>
    <div class="map-container">
        <div class="map-left">
            <svg id="texas-map-svg" viewBox="0 0 941.76 907.17" class="texas-svg-map">
                <!-- Group elements for regions (e.g. data-region="panhandle") -->
                <g class="texas-region" data-region="panhandle">
                    <path d="..." />
                </g>
                <!-- Other regions follow -->
            </svg>
        </div>
        <div class="map-right">
            <div>
                <h2 id="content-title" class="map-content-title" style="display: none;"></h2>
                <img id="content-img" src="" alt="Region" class="map-content-img" style="display: none;">
                <p id="content-text" class="map-content-text" style="display: none;"></p>
                <a href="#" id="content-btn" class="btn btn--primary" style="display: none;">View Regional Case Studies</a>
            </div>
        </div>
    </div>
</section>
```
