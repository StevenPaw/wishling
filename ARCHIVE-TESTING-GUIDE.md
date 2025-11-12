# Archivierungstests für Wishling API

## Überblick

Die Wishling API bietet verschiedene Möglichkeiten zum Archivieren und Dearchivieren von Wishes. Diese Dokumentation beschreibt alle verfügbaren Testmethoden.

## Verfügbare Testmethoden

### 1. 📋 VS Code REST Client (`archive-testing.http`)

**Verwendung:**
```bash
# Öffne die Datei in VS Code
code archive-testing.http
# Klicke auf "Send Request" über jeder Anfrage
```

**Features:**
- Interaktive Tests direkt in VS Code
- Automatische Variable-Extraktion
- Schritt-für-Schritt Durchführung
- Perfekt für Entwicklung und Debugging

### 2. 📦 Postman Collection (`archive-tests.postman_collection.json`)

**Import in Postman:**
1. Postman öffnen
2. "Import" klicken
3. Datei `archive-tests.postman_collection.json` auswählen
4. Collection "Wishling Archive Tests" verwenden

**Features:**
- Automatisierte Test-Assertions
- Environment Variables für Hashes
- Vorgefertigte Test-Szenarien
- Professional Testing-Workflows

### 3. 🔧 Bash-Skript (`test_archive_api.sh`)

**Ausführung:**
```bash
./test_archive_api.sh
```

**Features:**
- Vollautomatische Testdurchführung
- JSON-formatierte Ausgabe mit `jq`
- Komplette Test-Suite in einem Durchlauf
- Perfekt für CI/CD und Automatisierung

### 4. ⚡ Einzelne cURL-Kommandos

**Setup:**
```bash
# 1. Wishlist erstellen
WISHLIST_HASH=$(curl -s -X POST "https://wishling.ddev.site/api/wishlist/wishlists" \
  -H "Content-Type: application/json" \
  -d '{"name":"Archive Test","description":"Test"}' | \
  grep -o '"hash":"[^"]*"' | cut -d'"' -f4)

# 2. Wish erstellen
WISH_HASH=$(curl -s -X POST "https://wishling.ddev.site/api/wishlist/wishlists/$WISHLIST_HASH/wishes" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Wish","description":"Test","price":"10.00"}' | \
  grep -o '"hash":"[^"]*"' | cut -d'"' -f4)
```

## Archivierungsmethoden

### Methode 1: POST `/archive` Endpoint

```bash
# Archivieren
curl -X POST "https://wishling.ddev.site/api/wishlist/wishlists/$WISHLIST_HASH/wishes/$WISH_HASH/archive"

# Dearchivieren
curl -X POST "https://wishling.ddev.site/api/wishlist/wishlists/$WISHLIST_HASH/wishes/$WISH_HASH/unarchive"
```

### Methode 2: PUT mit `archived` Parameter

```bash
# Archivieren
curl -X PUT "https://wishling.ddev.site/api/wishlist/wishlists/$WISHLIST_HASH/wishes/$WISH_HASH" \
  -H "Content-Type: application/json" \
  -d '{"archived": true}'

# Dearchivieren
curl -X PUT "https://wishling.ddev.site/api/wishlist/wishlists/$WISHLIST_HASH/wishes/$WISH_HASH" \
  -H "Content-Type: application/json" \
  -d '{"archived": false}'
```

## Filterung und Anzeige

### Aktive Wishes (Standard)
```bash
curl "https://wishling.ddev.site/api/wishlist/wishlists/$WISHLIST_HASH/wishes"
```

### Archivierte Wishes
```bash
curl "https://wishling.ddev.site/api/wishlist/wishlists/$WISHLIST_HASH/wishes?archived=true"
```

### Alle Wishes (aktive + archivierte)
```bash
curl "https://wishling.ddev.site/api/wishlist/wishlists/$WISHLIST_HASH/wishes?show=all"
```

## Erwartete Responses

### Erfolgreiche Archivierung
```json
{
  "success": true,
  "message": "Wish wurde erfolgreich archiviert",
  "data": {
    "hash": "wish_hash",
    "title": "Wish Title",
    "archived": true,
    "created": "2024-01-15T10:30:00+00:00",
    "modified": "2024-01-15T11:45:00+00:00"
  }
}
```

### Erfolgreiche Dearchivierung
```json
{
  "success": true,
  "message": "Wish wurde erfolgreich dearchiviert",
  "data": {
    "hash": "wish_hash",
    "title": "Wish Title",
    "archived": false,
    "created": "2024-01-15T10:30:00+00:00",
    "modified": "2024-01-15T11:50:00+00:00"
  }
}
```

### Statistiken Response
```json
{
  "success": true,
  "count": 1,
  "stats": {
    "total": 2,
    "active": 1,
    "archived": 1,
    "showing": "active"
  },
  "data": [...]
}
```

## Test-Szenarien

### Szenario 1: Basis-Archivierung
1. Wishlist erstellen
2. Wish erstellen
3. Wish archivieren (POST /archive)
4. Status überprüfen
5. Wish dearchivieren (POST /unarchive)
6. Finale Überprüfung

### Szenario 2: PUT-basierte Archivierung
1. Wish via PUT archivieren mit `{"archived": true}`
2. Zusätzliche Felder beim Archivieren ändern
3. Wish via PUT dearchivieren mit `{"archived": false}`

### Szenario 3: Bulk-Archivierung
1. Mehrere Wishes erstellen
2. Verschiedene Archivierungsmethoden testen
3. Filterung und Statistiken überprüfen

### Szenario 4: Edge Cases
1. Bereits archivierte Wishes erneut archivieren
2. Nicht-existierende Wishes archivieren
3. Ungültige Parameter testen

## Debugging-Tipps

### Fehlerbehebung
```bash
# Verbose cURL für detaillierte Ausgabe
curl -v -X POST "..."

# Response mit jq formatieren
curl "..." | jq '.'

# HTTP-Status prüfen
curl -s -o /dev/null -w "%{http_code}" "..."
```

### Häufige Probleme
- **404 Fehler**: Hash-Werte überprüfen
- **500 Fehler**: Server-Logs in DDEV prüfen: `ddev logs`
- **Leere Response**: Content-Type Header setzen bei POST/PUT

## Automatisierte Tests

### GitHub Actions Integration
```yaml
- name: Run Archive Tests
  run: |
    chmod +x ./test_archive_api.sh
    ./test_archive_api.sh
```

### PHPUnit Tests
Für Backend-Tests können die gleichen Endpunkte in PHPUnit-Tests verwendet werden:

```php
public function testWishArchiving()
{
    $response = $this->post('/api/wishlist/wishlists/' . $this->wishlistHash . '/wishes/' . $this->wishHash . '/archive');
    $this->assertEquals(200, $response->getStatusCode());
    // ...
}
```

## Monitoring und Logging

Die API loggt alle Archivierungsaktionen:
```bash
# DDEV Logs anzeigen
ddev logs | grep -i archive

# Spezifische Archiv-Aktionen
ddev logs | grep "archiviert\|dearchiviert"
```

---

**Letzte Aktualisierung:** $(date)
**API Version:** 1.0
**Test Coverage:** Vollständig
