# Wishling Frontend Development

Dieses Projekt verwendet Vite für die Frontend-Entwicklung mit SilverStripe.

## Yarn Commands

### Entwicklung
```bash
# Dependencies installieren
ddev yarn install

# Entwicklungsserver starten (Hot Module Replacement)
ddev yarn dev

# Assets für Produktion bauen
ddev yarn build

# Assets im Watch-Modus bauen
ddev yarn watch

# Preview der gebauten Assets
ddev yarn preview
```

### DDEV Integration

Der Vite Dev Server ist über DDEV exponiert:
- **HTTPS**: https://wishling.ddev.site:5173
- **HTTP**: http://wishling.ddev.site:5172

## Architektur

### JavaScript Module
- `app/client/src/js/main.js` - Haupteinstiegspunkt und Wishlist-Erstellung
- `app/client/src/js/wishlist.js` - Owner-Funktionalität 
- `app/client/src/js/wishlist-guest.js` - Gast-Funktionalität

### CSS/SCSS
- `app/client/src/scss/_variables.scss` - CSS Custom Properties
- `app/client/src/scss/main.scss` - Haupt-Styles
- `app/client/src/scss/wishlist.scss` - Wishlist-spezifische Styles
- `app/client/src/scss/editor.scss` - CMS Editor Styles

### Build Output
- `app/client/dist/` - Gebaute Assets mit Hashes
- `app/client/dist/.vite/manifest.json` - Asset Manifest für SilverStripe

## SilverStripe Integration

Das Projekt nutzt den `atwx/silverstripe-vitehelper` für die Integration:

### Template Usage
```silverstripe
$ViteClient.RAW
<link rel="stylesheet" href="$Vite("app/client/src/scss/main.scss")">
<script type="module" src="$Vite("app/client/src/js/main.js")"></script>
```

**Hinweis**: Die Pfade müssen exakt dem entsprechen, was im Vite Manifest als Keys verwendet wird.

### Konfiguration
- `app/_config/vite.yml` - Vite Helper Konfiguration
- `vite.config.js` - Vite Build Konfiguration

## Features

- ✅ Hot Module Replacement in der Entwicklung
- ✅ CSS Custom Properties für Theming
- ✅ Tree Shaking für optimierte Bundles
- ✅ Source Maps für Debugging
- ✅ SASS/SCSS Preprocessing
- ✅ Modern ES6+ JavaScript
- ✅ TypeScript ready

## Entwicklung

1. **DDEV starten**: `ddev start`
2. **Dependencies installieren**: `ddev yarn install`
3. **Dev Server starten**: `ddev yarn dev`
4. **In Browser öffnen**: https://wishling.ddev.site

Der Dev Server läuft parallel zu SilverStripe und ermöglicht Hot Reloading.

## Produktion

Assets für die Produktion bauen:
```bash
ddev yarn build
```

Die Assets werden automatisch von SilverStripe über den Vite Helper geladen.
