# Plan: PageSpeed Insights ≥ 95 — Athicon Web

## Context
El sitio es HTML/PHP estático con Bootstrap 5, jQuery, Owl Carousel y WOW.js. El score actual estimado en mobile ronda 20-35 principalmente por los videos de 84 MB y 29 MB en autoplay. El objetivo es llevar todos los indicadores por encima de 95.

No se usa ningún build tool (sin webpack, sin npm). Todos los cambios son HTML, CSS y JS puros.

---

## Archivos críticos a modificar
- `index.html` y `en/index.html` — carousel de videos + todas las optimizaciones
- `about.html`, `quality.html`, `safety.html` y sus equivalentes en `en/`
- `js/main.js` — eliminar console.logs, agregar lazy-load de videos del carousel
- `css/style.css` — ocultar videos en mobile, inline de keyframes de animate.css

---

## Prioridades (orden por impacto en PageSpeed)

### PRIORIDAD 1 — Re-encodar videos (trabajo externo)
**Impacto estimado: +25 a +35 puntos. Métrica: LCP, FCP**

Requiere FFmpeg instalado. Ejecutar fuera del editor:
```
ffmpeg -i assets/video/video1.mp4 -vf scale=1280:720 -c:v libx264 -crf 28 -preset slow -an assets/video/video1-opt.mp4
ffmpeg -i assets/video/video1.mp4 -vf scale=1280:720 -c:v libvpx-vp9 -crf 33 -b:v 0 -an assets/video/video1-opt.webm
ffmpeg -i assets/video/video2.mp4 -vf scale=1280:720 -c:v libx264 -crf 28 -preset slow -an assets/video/video2-opt.mp4
ffmpeg -i assets/video/video2.mp4 -vf scale=1280:720 -c:v libvpx-vp9 -crf 33 -b:v 0 -an assets/video/video2-opt.webm
```
Target: video1 < 8 MB, video2 < 5 MB. El `-an` elimina audio (ya son muted, sin pista de audio).

### PRIORIDAD 2 — Lazy-load de videos no activos en el carousel + poster como hero en mobile
**Impacto estimado: incluido en Prioridad 1. Métrica: LCP, FCP, TTI**

**En `index.html` y `en/index.html`:**

Slide 1 (activo): carga inmediata con WebM + MP4 optimizados, `fetchpriority="high"`, corregir typo `plays-inline` → `playsinline`:
```html
<video autoplay loop muted playsinline id="video1" class="main-banner__video"
    poster="assets/video/video1.jpg" fetchpriority="high">
    <source src="assets/video/video1-opt.webm" type="video/webm">
    <source src="assets/video/video1-opt.mp4" type="video/mp4">
</video>
```

Slides 2, 3, 4: sin `<source>`, usar `data-src-webm` y `data-src-mp4`, sin `autoplay` (se activan por JS al hacer slide):
```html
<video loop muted playsinline id="video2" class="main-banner__video"
    poster="assets/video/video2.jpg"
    data-src-webm="assets/video/video2-opt.webm"
    data-src-mp4="assets/video/video2-opt.mp4">
</video>
```

Agregar ID a cada `carousel-item`: `id="slide-1"`, `id="slide-2"`, etc.

**En `js/main.js`** — agregar listener que inyecta sources al hacer slide:
```javascript
document.getElementById('header-carousel').addEventListener('slide.bs.carousel', function(e) {
    var video = e.relatedTarget.querySelector('video[data-src-mp4]');
    if (video && !video.querySelector('source')) {
        var webm = document.createElement('source');
        webm.src = video.dataset.srcWebm;
        webm.type = 'video/webm';
        var mp4 = document.createElement('source');
        mp4.src = video.dataset.srcMp4;
        mp4.type = 'video/mp4';
        video.appendChild(webm);
        video.appendChild(mp4);
        video.load();
        video.play();
    }
});
```

**En `css/style.css`** — ocultar videos en mobile y usar poster como fondo:
```css
@media (max-width: 991px) {
    .main-banner__video { display: none; }
    #slide-1 { background: url('../assets/video/video1.jpg') center/cover no-repeat; min-height: 500px; }
    #slide-2 { background: url('../assets/video/video2.jpg') center/cover no-repeat; min-height: 500px; }
    #slide-3 { background: url('../assets/video/video1.jpg') center/cover no-repeat; min-height: 500px; }
    #slide-4 { background: url('../assets/video/video2.jpg') center/cover no-repeat; min-height: 500px; }
}
```

### PRIORIDAD 3 — Async load de CSS de iconos
**Impacto estimado: +10 a +15 puntos. Métrica: FCP, TBT**

En **todos los HTML** reemplazar los dos `<link>` de Font Awesome y Bootstrap Icons por carga async:
```html
<!-- Preconnects adicionales -->
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="preconnect" href="https://cdn.jsdelivr.net">

<!-- Font Awesome async -->
<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css"></noscript>

<!-- Bootstrap Icons async -->
<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css"></noscript>
```

### PRIORIDAD 4 — `defer` en todos los scripts
**Impacto estimado: +5 a +8 puntos. Métrica: TBT, TTI**

En **todos los HTML**, en el bloque de scripts al final del body, agregar `defer` a todos:
```html
<script defer src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="lib/php-email-form/validate.js"></script>
<script defer src="lib/wow/wow.min.js"></script>
<script defer src="lib/easing/easing.min.js"></script>
<script defer src="lib/waypoints/waypoints.min.js"></script>
<script defer src="lib/counterup/counterup.min.js"></script>
<script defer src="lib/owlcarousel/owl.carousel.min.js"></script>
<script defer src="js/main.js?v=2.3"></script>
```
El `defer` preserva el orden de ejecución, jQuery seguirá estando disponible para main.js.

Eliminar también el `videoModule` IIFE del final de `index.html` (es código comentado con solo un resize listener vacío).

### PRIORIDAD 5 — Corregir el atributo `preload` de Google Fonts
**Impacto estimado: +2 a +3 puntos. Métrica: FCP**

El `preload` actual en el `<link>` es un atributo inválido (no hace nada). En **todos los HTML**:

Reemplazar:
```html
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet" type="text/css" preload>
```

Por:
```html
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap"></noscript>
```

### PRIORIDAD 6 — Async load de animate.min.css y owl.carousel.min.css
**Impacto estimado: +4 a +6 puntos. Métrica: FCP, LCP**

En **todos los HTML**:
```html
<link rel="preload" href="lib/animate/animate.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="lib/animate/animate.min.css"></noscript>

<link rel="preload" href="lib/owlcarousel/assets/owl.carousel.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="lib/owlcarousel/assets/owl.carousel.min.css"></noscript>
```

### PRIORIDAD 7 — Lazy loading + srcset + width/height en imágenes
**Impacto estimado: +5 a +8 puntos. Métrica: LCP, CLS**

En **todos los HTML**, para todas las imágenes below-the-fold:
- Agregar `loading="lazy"`
- Completar `srcset` con los archivos WebP de múltiples resoluciones que ya existen en `/img/`
- Agregar `width` y `height` con las dimensiones reales para evitar CLS

Ejemplo para la imagen del about en `index.html`:
```html
<img class="position-absolute w-100 h-100 rounded wow zoomIn"
     loading="lazy"
     src="img/about4-516x516.webp"
     srcset="img/about4-516x516.webp 516w, img/about4-724x724.webp 724w, img/about4-1032x1032.webp 1032w"
     sizes="(max-width: 991px) 0px, (max-width: 1199px) calc(41.66vw - 30px), 516px"
     width="516" height="516"
     style="object-fit: cover;" alt="about">
```

No aplicar lazy loading al logo del navbar ni a las imágenes above-the-fold.

### PRIORIDAD 8 — Preload del poster del primer video
**Impacto estimado: +2 a +4 puntos. Métrica: LCP**

En el `<head>` de `index.html` y `en/index.html`:
```html
<link rel="preload" href="assets/video/video1.jpg" as="image" fetchpriority="high">
```
Para `en/index.html`:
```html
<link rel="preload" href="../assets/video/video1.jpg" as="image" fetchpriority="high">
```

Para `about.html`, `quality.html`, `safety.html`: preload del background-image del header CSS:
```html
<!-- about.html -->
<link rel="preload" href="assets/video/video1.jpg" as="image" fetchpriority="high">
<!-- quality.html - verificar nombre del archivo real -->
<link rel="preload" href="img/quality-header2.webp" as="image" fetchpriority="high">
<!-- safety.html - verificar nombre del archivo real -->
<link rel="preload" href="img/safety-header.webp" as="image" fetchpriority="high">
```

### PRIORIDAD 9 — Eliminar console.log de main.js
**Impacto estimado: +1 a +2 puntos. Métrica: TBT**

En `js/main.js` líneas 25 y 30, eliminar:
```javascript
console.log('scroll > 45');  // línea 25
console.log('scroll < 45');  // línea 30
```

### PRIORIDAD 10 — Inline de critical CSS (hacer último)
**Impacto estimado: +3 a +5 puntos. Métrica: FCP, LCP**

Hacer **solo después** de que las prioridades 1-9 estén en producción y confirmadas.

Usar Chrome DevTools Coverage tab para identificar las reglas CSS usadas en el first paint de cada página. Inlinear esas reglas en un `<style>` en el `<head>` y convertir el `<link>` de bootstrap.min.css a carga async.

---

## Estimación de ganancia total
| Prioridad | Cambio | Score gain |
|-----------|--------|-----------|
| 1-2 | Videos re-encodados + lazy carousel | +25 a +35 |
| 3 | Async icon CSS | +10 a +15 |
| 4 | defer en scripts | +5 a +8 |
| 5 | Fix Google Fonts preload | +2 a +3 |
| 6 | Async animate + owl CSS | +4 a +6 |
| 7 | Lazy loading + srcset + w/h | +5 a +8 |
| 8 | Preload poster | +2 a +4 |
| 9 | Eliminar console.logs | +1 a +2 |
| 10 | Inline critical CSS | +3 a +5 |

**Total estimado: +57 a +86 puntos sobre score base de ~20-30 → objetivo 95+ alcanzable**

Para páginas sin video (about, quality, safety), el score base ya es ~40-55, con estas optimizaciones llegan a 95+ con prioridades 3-9 solamente.

---

## Verificación
1. Ejecutar PageSpeed Insights en https://pagespeed.web.dev/ para `https://www.athicon.com/`
2. Verificar que el carousel sigue funcionando (videos cargan al hacer slide)
3. Verificar que los íconos aparecen (pueden tardar un frame extra por el async — es aceptable)
4. Verificar CLS en DevTools → Performance → Layout Shifts
5. Confirmar que WOW.js animations siguen funcionando tras agregar `defer`
