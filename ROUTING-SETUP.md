# Silverstripe ohne SiteTree - Routing & Controller Setup

## Übersicht

Dieses Projekt verwendet Silverstripe OHNE das CMS-Modul und SiteTree. Stattdessen wird ein reines Controller/Route-basiertes System verwendet, während das Admin-Backend für die Verwaltung von DataObjects erhalten bleibt.

## Struktur

### Controller (`app/src/Control/`)
- `HomeController.php` - Behandelt die Root-URL (/)
- `ApiController.php` - Beispiel für einen API-Endpoint (/api/...)

### Routing (`app/_config/routes.yml`)
Alle Routes werden hier definiert. Das Standard-SiteTree-Routing ist deaktiviert.

### DataObjects (`app/src/Model/`)
- `ExampleDataObject.php` - Beispiel für ein DataObject

### ModelAdmin (`app/src/Admin/`)
- `ExampleAdmin.php` - Admin-Interface für ExampleDataObject

## ✅ Umstellung abgeschlossen!

Die folgenden CMS-Module wurden entfernt:
- `silverstripe/recipe-cms`
- `silverstripe/cms` 
- `silverstripe/errorpage`
- `silverstripe/siteconfig`
- `dnadesign/silverstripe-elemental`
- `silverstripe/campaign-admin`
- `silverstripe/reports`

### Datenbank wurde neu aufgebaut

Die Datenbank wurde bereits mit `dev/build` neu aufgebaut.
ExampleDataObject wurde erfolgreich erstellt.

### 3. Neue Seiten erstellen

Um eine neue Seite hinzuzufügen:

1. Erstelle einen neuen Controller in `app/src/Control/`
2. Füge eine Route in `app/_config/routes.yml` hinzu
3. Erstelle ein Template in `app/templates/App/Control/`

#### Beispiel:

**Controller:** `app/src/Control/AboutController.php`
```php
<?php

namespace App\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;

class AboutController extends Controller
{
    private static $allowed_actions = ['index'];
    
    private static $url_handlers = ['' => 'index'];
    
    public function index(HTTPRequest $request)
    {
        return $this->customise([
            'Title' => 'Über uns',
            'Content' => 'Über uns Text...',
        ])->renderWith(['App\\Control\\AboutController', 'Page']);
    }
    
    public function Link($action = null)
    {
        return Controller::join_links('about', $action);
    }
}
```

**Route in routes.yml:**
```yaml
'about': 'App\Control\AboutController'
```

### 4. DataObjects im Backend verwalten

Das Admin-Backend ist unter `/admin` erreichbar. Dort kannst du:
- DataObjects über ModelAdmin verwalten
- Dateien hochladen und verwalten
- Benutzer verwalten

### 5. Wichtige Änderungen

- ❌ Kein SiteTree mehr
- ❌ Keine Pages in der Datenbank
- ❌ Kein CMS-Editor für Seiteninhalte
- ✅ Admin-Backend für DataObjects
- ✅ Vollständige Kontrolle über Routing
- ✅ Moderne Controller-Architektur
- ✅ API-Endpoints möglich

## Templates

Templates folgen der Silverstripe-Konvention und liegen in:
- `app/templates/App/Control/` für Controller-Templates
- Standard-Templates können mit `->renderWith()` verwendet werden

## Vorteile dieses Ansatzes

1. **Volle Route-Kontrolle**: Keine automatischen URLs mehr, du definierst jede Route
2. **Leichtgewichtiger**: Weniger Overhead ohne CMS-Module
3. **Flexibler**: Perfekt für Webapps, APIs und SPAs
4. **Backend bleibt**: ModelAdmin für strukturierte Datenverwaltung
5. **Modern**: Klare Trennung von Concerns

## Weitere Schritte

- Templates anpassen und erweitern
- Weitere Controller für verschiedene Bereiche erstellen
- DataObjects für deine Daten anlegen
- API-Endpoints nach Bedarf erweitern
- Frontend-Framework (React, Vue, etc.) integrieren, falls gewünscht
