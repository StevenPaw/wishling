# Wishling API Testing mit Postman

## 🚀 Quick Start

### 1. Postman Collection importieren
1. Öffnen Sie Postman
2. Klicken Sie auf **Import**
3. Importieren Sie diese Dateien:
   - `wishling-api-tests-extended.postman_collection.json` (Erweiterte Tests)
   - `wishling-local.postman_environment.json` (Environment)

### 2. Environment konfigurieren
- Wählen Sie das Environment "Wishling Local"
- Passen Sie `baseUrl` an Ihre lokale Installation an (Standard: `http://wishling.ddev.site`)

## 📋 Verfügbare Tests

### API Controller Tests
- ✅ **API Index** - Basis API Endpoint
- ✅ **API Get Data with ID** - Parameter-Tests

### Wishlist API Tests  
- ✅ **Wishlist API Index** - Wishlist API Status
- ✅ **Get All Wishlists** - Alle Wishlists abrufen
- ✅ **Create Wishlist** - Neue Wishlist erstellen
- ✅ **Get Wishlist by Hash** - Spezifische Wishlist
- ✅ **Create Wish** - Wunsch zu Wishlist hinzufügen
- ✅ **Get Wishes from Wishlist** - Alle Wünsche einer Wishlist

### Error Handling Tests
- ✅ **Test 404 Error** - Nicht existierende Endpoints
- ✅ **Test Invalid Wishlist Hash** - Ungültige Hash-Werte

## 🔧 Automatische Tests

Jeder Request enthält automatische Tests für:
- **Status Codes** (200, 201, 404)
- **Content-Type** (application/json)
- **Response Structure** (erforderliche Felder)
- **CORS Headers** (für Frontend-Integration)
- **Response Time** (Performance)

## 💡 Tipps für effektives Testen

### 1. Variables verwenden
- `{{$timestamp}}` - Eindeutige Zeitstempel
- `{{$randomInt}}` - Zufällige Zahlen
- `{{$randomPrice}}` - Zufällige Preise

### 2. Test Scripts
Tests werden automatisch nach jedem Request ausgeführt:
```javascript
pm.test('Status code is 200', function () {
    pm.response.to.have.status(200);
});

pm.test('Response time is acceptable', function () {
    pm.expect(pm.response.responseTime).to.be.below(500);
});
```

### 3. Environment Variables
Hash-Werte werden automatisch gespeichert:
- Nach "Create Wishlist" → `{{wishlistHash}}`
- Nach "Create Wish" → `{{wishHash}}`

## 🏃‍♂️ Test Workflows

### Vollständiger Workflow:
1. **API Index** → Basis-Connectivity testen
2. **Create Wishlist** → Neue Wishlist erstellen
3. **Get Wishlist by Hash** → Erstellte Wishlist abrufen
4. **Create Wish** → Wunsch hinzufügen
5. **Get Wishes from Wishlist** → Wünsche abrufen

### Schnelle Tests:
- Einzelne Requests ausführen
- Collection Runner für alle Tests
- Monitor für kontinuierliche Tests

## 🔄 Newman (CLI Testing)

Für automatisierte Tests in CI/CD:
```bash
# Newman installieren
npm install -g newman

# Tests ausführen
./run_newman_tests.sh
```

## 📊 Test Reports

Newman erstellt JSON Reports für:
- Detaillierte Testergebnisse
- Performance Metriken  
- Failure Analysis
- CI/CD Integration

## 🐛 Debugging

### Console Ausgaben
Verwenden Sie `console.log()` in Test Scripts:
```javascript
pm.test('Debug response', function () {
    console.log('Response:', pm.response.json());
    console.log('Headers:', pm.response.headers);
});
```

### Network Inspector
- Request/Response Details
- Timing Information
- Headers Analysis

## 🎯 Best Practices

1. **Immer Environment Variables verwenden**
2. **Tests für jeden Request schreiben**
3. **Assertions für kritische Felder**
4. **Error Handling testen**
5. **Performance Monitoring**
6. **Dokumentation aktuell halten**

## 🔗 API Endpoints

| Endpoint | Method | Beschreibung |
|----------|--------|-------------|
| `/api/` | GET | API Status |
| `/api/data/{id}` | GET | Data mit Parameter |
| `/api/test/` | GET | Test API |
| `/api/wishlist/` | GET | Wishlist API Status |
| `/api/wishlist/wishlists` | GET/POST | Wishlists verwalten |
| `/api/wishlist/wishlists/{hash}` | GET/PUT/DELETE | Spezifische Wishlist |
| `/api/wishlist/wishlists/{hash}/wishes` | GET/POST | Wünsche verwalten |

## 📧 Support

Bei Fragen oder Problemen:
- Prüfen Sie die Console Ausgaben
- Überprüfen Sie Environment Variables
- Testen Sie einzelne Requests
- Kontrollieren Sie Server-Logs
