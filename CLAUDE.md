# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Static HTML/PHP corporate website for Athicon (engineering/construction company based in Argentina). No build tools — no npm, no webpack, no package manager. All changes are direct edits to HTML, CSS, and JS files.

**Live site:** https://www.athicon.com/ (hosted on Ferozo)

## Architecture

### Page structure
- Root-level pages (Spanish): `index.html`, `about.html`, `quality.html`, `safety.html`, `contact.html`, `detail.html`
- English versions mirror the Spanish pages under `en/` — paths to assets use `../` prefix (e.g., `../css/style.css`, `../img/`)
- Both language versions share the same CSS, JS, and assets

### Key files
- `css/style.css` — custom styles; CSS variables define the color palette (`--primary: #06A3DA`, `--dark: #091E3E`)
- `js/main.js` — all custom JS: spinner, sticky navbar, dropdown hover, counter-up, carousels (testimonial + vendor), back-to-top, lazy-load of carousel videos on slide
- `forms/contact.php` — PHPMailer SMTP handler; credentials are hardcoded (Ferozo SMTP on port 465)

### Third-party libraries (vendored under `lib/`)
jQuery 3.4.1, Bootstrap 5, Owl Carousel, WOW.js, easing.js, waypoints, counterup, php-email-form validate.js — all loaded via CDN or from `lib/` with `defer`.

### Images
Multiple WebP resolutions per image (e.g., `img/about4-516x516.webp`, `724x724`, `1032x1032`) for use in `srcset`. New images should follow this pattern.

### Videos
Hero carousel uses two source videos (`assets/video/video1-opt.webm/mp4`, `video2-opt.webm/mp4`). Slide 1 loads eagerly; slides 2–4 lazy-load on carousel transition via `data-src-webm` / `data-src-mp4` attributes injected by `main.js`.

## Performance guidelines (`plan-performance.md`)

An active PageSpeed optimization plan exists. Key conventions already applied:
- CSS loaded async via `rel="preload"` + `onload` swap + `<noscript>` fallback (all stylesheets)
- All scripts use `defer`
- Videos hidden on mobile (`max-width: 991px`) via `.main-banner__video { display: none }` — poster images used as CSS `background` instead
- Images use `loading="lazy"`, `srcset`, and explicit `width`/`height` to prevent CLS
- LCP poster preloaded with `fetchpriority="high"`

When modifying pages, maintain these patterns. The next pending step in the plan is inline critical CSS (Priority 10 in `plan-performance.md`) — do this only after confirming all other optimizations are live.

## Bilingual maintenance

When making content or structural changes to any page, apply the same change to its `en/` counterpart. The two versions are manually kept in sync — there is no automated translation pipeline.
