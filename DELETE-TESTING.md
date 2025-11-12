# 🗑️ DELETE-Funktionalität in Wishling API testen

## 📋 Verfügbare Test-Methoden

### 1. **REST Client (VS Code) - Schnell & Einfach**
Datei: `api-tests-archiving.http`

```http
### Delete Wish (PERMANENT)
DELETE {{baseUrl}}/api/wishlist/wishlists/{{wishlistHash}}/wishes/{{wishHash}}
Content-Type: application/json
```

### 2. **Postman Collection - Comprehensive Tests**
Datei: `delete-tests.postman_collection.json`

**Features:**
- ✅ Automatisierte Test-Suite
- ✅ Setup von Test-Daten
- ✅ Fehlerbehandlung-Tests
- ✅ Vergleich Archive vs. DELETE
- ✅ Validierung der Löschungen

### 3. **Command Line (cURL)**
```bash
# Wish erstellen
curl -X POST "https://wishling.ddev.site/api/wishlist/wishlists/HASH/wishes" \
  -H "Content-Type: application/json" \
  -d '{"title":"Zum Löschen","description":"Test","price":"19.99"}'

# Wish löschen
curl -X DELETE "https://wishling.ddev.site/api/wishlist/wishlists/HASH/wishes/WISH_HASH"
```

## 🚀 Postman Setup & Nutzung

### **Setup:**
1. Importieren Sie `delete-tests.postman_collection.json` in Postman
2. Importieren Sie das aktualisierte `wishling-local.postman_environment.json`
3. Wählen Sie das "Wishling Local" Environment

### **Ausführung:**
1. **Setup Tests** ausführen → Erstellt Testdaten automatisch
2. **DELETE Tests** ausführen → Testet die Löschfunktionalität
3. **Error Handling Tests** → Testet Fehlerfälle
4. **Archive vs Delete Comparison** → Zeigt Unterschied zwischen Archivierung und Löschung

## 📊 Was wird getestet?

### **Funktionale Tests:**
- ✅ Erfolgreiche Löschung eines Wishes
- ✅ 404-Fehler bei nicht existierendem Wish
- ✅ 404-Fehler bei ungültiger Wishlist
- ✅ Überprüfung dass gelöschte Wishes nicht mehr auffindbar sind
- ✅ Korrekter Zähler nach Löschung

### **Vergleichstests:**
- ✅ **Archive** (Soft Delete) - Wish bleibt zugänglich aber versteckt
- ✅ **DELETE** (Hard Delete) - Wish wird permanent entfernt

## 🔄 Test-Workflow

### **1. Vollständiger Postman-Workflow:**
```
1. Create Test Wishlist → speichert {{testWishlistHash}}
2. Create Test Wish #1 → speichert {{testWish1Hash}}
3. Create Test Wish #2 → speichert {{testWish2Hash}}
4. GET Wishes Before Delete → zeigt 2 Wishes
5. DELETE Test Wish #1 → entfernt Wish permanent
6. GET deleted Wish → 404 Error
7. GET Wishes After Delete → zeigt 1 Wish
```

### **2. Archive vs DELETE Vergleich:**
```
1. Create Wish for Archive Test
2. Archive Wish → Soft Delete (bleibt zugänglich)
3. GET Archived Wish → 200 OK (noch da)
4. DELETE Remaining Wish → Hard Delete
5. GET Deleted Wish → 404 Error (weg)
```

## ⚠️ Wichtige Hinweise

### **Archive vs. DELETE:**
| Aktion | Archive | DELETE |
|--------|---------|--------|
| **Rückgängig** | ✅ Ja (unarchive) | ❌ Nein |
| **Datenverlust** | ❌ Nein | ✅ Ja |
| **Zugänglich** | ✅ Mit Filter | ❌ Nein |
| **Empfehlung** | Für erfüllte Wishes | Nur für Spam/Fehler |

### **Best Practices:**
1. **Verwenden Sie Archive** für normale "Löschungen" (erfüllte Wishes)
2. **Verwenden Sie DELETE** nur für echte Fehler oder Spam
3. **Testen Sie immer** in der Entwicklungsumgebung
4. **Backup** vor DELETE-Operationen in Produktion

## 🔧 Response-Beispiele

### **Erfolgreiche Löschung:**
```json
{
    "success": true,
    "message": "Wish gelöscht"
}
```

### **Wish nicht gefunden:**
```json
{
    "success": false,
    "error": "Wish nicht gefunden"
}
```

### **Wishlist nicht gefunden:**
```json
{
    "success": false,
    "error": "Wishlist nicht gefunden"
}
```

## 📈 Automatische Tests

Die Postman-Collection enthält automatische Assertions:

```javascript
pm.test('Wish deleted successfully', function () {
    pm.response.to.have.status(200);
    const jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.message).to.include('gelöscht');
});
```

## 🎯 Debugging-Tipps

### **Test fehlgeschlagen?**
1. Überprüfen Sie die Environment-Variablen
2. Stellen Sie sicher, dass die Wishlist/Wish existiert
3. Prüfen Sie die Server-Logs: `ddev logs -s web`
4. Validieren Sie die Response in der Postman Console

### **Häufige Probleme:**
- **404 Error:** Hash-Werte falsch oder Wish bereits gelöscht
- **405 Error:** Falsche HTTP-Method (PUT statt DELETE)
- **400 Error:** Ungültige URL-Parameter

## 🚀 Los geht's!

**Für schnelle Tests:** Verwenden Sie die `api-tests-archiving.http` Datei in VS Code

**Für umfangreiche Tests:** Führen Sie die Postman DELETE Collection aus

**Für Debugging:** Nutzen Sie curl mit -v Flag für verbose Output
