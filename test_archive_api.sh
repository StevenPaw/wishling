#!/bin/bash

# Archive Tests für Wishling API
# Führt verschiedene Archivierungstests durch

BASE_URL="https://wishling.ddev.site"
API_PATH="/api/wishlist"

echo "🗂️  WISHLING ARCHIVE TESTS"
echo "=========================="
echo

# Erstelle Test-Wishlist
echo "📝 Erstelle Test-Wishlist..."
RESPONSE=$(curl -s -X POST "${BASE_URL}${API_PATH}/wishlists" \
  -H "Content-Type: application/json" \
  -d '{"name":"Archive Tests","description":"Test-Wishlist für Archivierung"}')

WISHLIST_HASH=$(echo $RESPONSE | grep -o '"hash":"[^"]*"' | cut -d'"' -f4)
echo "   Wishlist Hash: $WISHLIST_HASH"
echo

if [ -z "$WISHLIST_HASH" ]; then
  echo "❌ Fehler: Konnte Wishlist nicht erstellen"
  exit 1
fi

# Erstelle Test-Wishes
echo "📋 Erstelle Test-Wishes..."
WISH1_RESPONSE=$(curl -s -X POST "${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Wish 1","description":"Erster Testwunsch","url":"https://example.com/1","price":"10.00"}')

WISH1_HASH=$(echo $WISH1_RESPONSE | grep -o '"hash":"[^"]*"' | cut -d'"' -f4)
echo "   Wish 1 Hash: $WISH1_HASH"

WISH2_RESPONSE=$(curl -s -X POST "${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Wish 2","description":"Zweiter Testwunsch","url":"https://example.com/2","price":"20.00"}')

WISH2_HASH=$(echo $WISH2_RESPONSE | grep -o '"hash":"[^"]*"' | cut -d'"' -f4)
echo "   Wish 2 Hash: $WISH2_HASH"
echo

# Zeige initiale Statistiken
echo "📊 Initiale Statistiken:"
curl -s "${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes" | \
  grep -o '"stats":{[^}]*}' | \
  sed 's/"stats":{\([^}]*\)}/   \1/'
echo
echo

# Test 1: Archivierung via POST
echo "🗄️  Test 1: Archivierung via POST"
echo "--------------------------------"
ARCHIVE_RESPONSE=$(curl -s -X POST "${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes/${WISH1_HASH}/archive")
echo "Response: $ARCHIVE_RESPONSE" | jq '.'
echo

# Test 2: Status nach Archivierung prüfen
echo "📈 Test 2: Status nach Archivierung"
echo "----------------------------------"
curl -s "${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes" | \
  jq '.stats'
echo

# Test 3: Archivierte Wishes anzeigen
echo "🗃️  Test 3: Archivierte Wishes anzeigen"
echo "-------------------------------------"
curl -s "${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes?archived=true" | \
  jq '.data[] | {title: .title, archived: .archived}'
echo

# Test 4: Archivierung via PUT
echo "✏️  Test 4: Archivierung via PUT"
echo "------------------------------"
PUT_RESPONSE=$(curl -s -X PUT "${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes/${WISH2_HASH}" \
  -H "Content-Type: application/json" \
  -d '{"archived":true,"title":"Test Wish 2 - Erfüllt ✅"}')
echo "Response: $PUT_RESPONSE" | jq '.'
echo

# Test 5: Finale Statistiken
echo "📊 Test 5: Finale Statistiken"
echo "-----------------------------"
curl -s "${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes" | \
  jq '.stats'
echo

# Test 6: Dearchivierung
echo "📂 Test 6: Dearchivierung"
echo "------------------------"
UNARCHIVE_RESPONSE=$(curl -s -X POST "${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes/${WISH1_HASH}/unarchive")
echo "Response: $UNARCHIVE_RESPONSE" | jq '.'
echo

# Test 7: Status nach Dearchivierung
echo "📈 Test 7: Status nach Dearchivierung"
echo "------------------------------------"
curl -s "${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes" | \
  jq '.stats'
echo

echo "✅ Archive Tests abgeschlossen!"
echo "   Wishlist Hash: $WISHLIST_HASH"
echo "   Wish 1 Hash: $WISH1_HASH"
echo "   Wish 2 Hash: $WISH2_HASH"
echo
echo "💡 Manuelle Tests möglich mit:"
echo "   curl \"${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes\""
echo "   curl \"${BASE_URL}${API_PATH}/wishlists/${WISHLIST_HASH}/wishes?archived=true\""
