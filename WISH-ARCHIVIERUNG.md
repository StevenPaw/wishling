# Wish-Archivierung Tests für Wishling API

## 📋 Verfügbare Archivierungs-Endpoints

### 1. **Spezielle Archivierungs-Endpoints (empfohlen)**

#### Archivieren:
```bash
POST /api/wishlist/wishlists/{hash}/wishes/{wishHash}/archive
```

#### Dearchivieren:  
```bash
POST /api/wishlist/wishlists/{hash}/wishes/{wishHash}/unarchive
```

### 2. **Update-Endpoint (flexibel)**

```bash
PUT /api/wishlist/wishlists/{hash}/wishes/{wishHash}
Content-Type: application/json

{"archived": true}    # Archivieren
{"archived": false}   # Dearchivieren
```

## 🔍 Wishes abrufen mit Archiv-Filter

### Nur aktive Wishes (Standard):
```bash
GET /api/wishlist/wishlists/{hash}/wishes
```

### Nur archivierte Wishes:
```bash
GET /api/wishlist/wishlists/{hash}/wishes?archived=true
```

### Nur aktive Wishes (explizit):
```bash
GET /api/wishlist/wishlists/{hash}/wishes?archived=false
```

## 📊 Response-Struktur

### Archivierung Response:
```json
{
    "success": true,
    "message": "Wish archiviert",
    "data": {
        "id": 1,
        "title": "Test Wunsch",
        "archived": true,
        "lastEdited": "2025-11-12 21:16:57"
    }
}
```

### Liste mit Statistiken:
```json
{
    "success": true,
    "data": [...],
    "count": 0,
    "stats": {
        "total": 1,
        "active": 0,
        "archived": 1,
        "showing": "active"
    }
}
```

## 🎯 Anwendungsfälle

### 1. **Wish erfüllt → archivieren**
```bash
curl -X POST "https://wishling.ddev.site/api/wishlist/wishlists/HASH/wishes/WISH_HASH/archive"
```

### 2. **Archivierte Wishes anzeigen**
```bash
curl -X GET "https://wishling.ddev.site/api/wishlist/wishlists/HASH/wishes?archived=true"
```

### 3. **Wish wieder reaktivieren**
```bash
curl -X POST "https://wishling.ddev.site/api/wishlist/wishlists/HASH/wishes/WISH_HASH/unarchive"
```

### 4. **Gleichzeitig bearbeiten und archivieren**
```bash
curl -X PUT "https://wishling.ddev.site/api/wishlist/wishlists/HASH/wishes/WISH_HASH" \
-H "Content-Type: application/json" \
-d '{"title": "Neuer Titel", "archived": true}'
```

## ✅ Features

- ✅ **Soft Delete** - Wishes werden nicht gelöscht, nur archiviert
- ✅ **Filterung** - Einfache Trennung zwischen aktiven/archivierten Wishes  
- ✅ **Statistiken** - Überblick über aktive/archivierte Wishes
- ✅ **Flexible API** - Mehrere Wege zur Archivierung
- ✅ **Rückgängig machen** - Archivierung kann rückgängig gemacht werden

## 🔧 Frontend Integration

```javascript
// Wish archivieren
async function archiveWish(wishlistHash, wishHash) {
    const response = await fetch(`/api/wishlist/wishlists/${wishlistHash}/wishes/${wishHash}/archive`, {
        method: 'POST'
    });
    return response.json();
}

// Archivierte Wishes anzeigen
async function getArchivedWishes(wishlistHash) {
    const response = await fetch(`/api/wishlist/wishlists/${wishlistHash}/wishes?archived=true`);
    return response.json();
}

// Wish dearchivieren
async function unarchiveWish(wishlistHash, wishHash) {
    const response = await fetch(`/api/wishlist/wishlists/${wishlistHash}/wishes/${wishHash}/unarchive`, {
        method: 'POST'
    });
    return response.json();
}
```
