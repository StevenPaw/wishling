#!/bin/bash

# Newman Test Runner für Wishling API
echo "=== Wishling API Tests mit Newman ==="
echo

# Prüfen ob Newman installiert ist
if ! command -v newman &> /dev/null; then
    echo "Newman ist nicht installiert. Installiere Newman..."
    npm install -g newman
fi

echo "Führe API Tests aus..."

# Basis Tests
newman run wishling-api-tests-extended.postman_collection.json \
    -e wishling-local.postman_environment.json \
    --reporters cli,json \
    --reporter-json-export newman-results.json \
    --timeout-request 30000 \
    --delay-request 100

echo
echo "=== Test Results ==="

# Zeige Zusammenfassung wenn JSON Report existiert
if [ -f "newman-results.json" ]; then
    echo "Detaillierte Ergebnisse in newman-results.json gespeichert"
    
    # Extrahiere wichtige Statistiken mit jq (falls verfügbar)
    if command -v jq &> /dev/null; then
        echo
        echo "Schnelle Zusammenfassung:"
        echo "Tests gesamt: $(jq '.run.stats.tests.total' newman-results.json)"
        echo "Tests erfolgreich: $(jq '.run.stats.tests.passed' newman-results.json)"
        echo "Tests fehlgeschlagen: $(jq '.run.stats.tests.failed' newman-results.json)"
        echo "Durchschnittliche Antwortzeit: $(jq '.run.timings.responseAverage' newman-results.json)ms"
    fi
fi

echo
echo "=== Tests abgeschlossen ==="
