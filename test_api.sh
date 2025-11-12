#!/bin/bash

# API Testing Script für Wishling
echo "=== API Testing für Wishling ==="
echo

# Basis-URL (passen Sie diese an Ihre lokale Umgebung an)
BASE_URL="https://wishling.ddev.site"

echo "1. Test API Controller - Index"
curl -s "$BASE_URL/api/"
echo
echo

echo "2. Test API Controller - getData mit ID"
curl -s "$BASE_URL/api/data/123"
echo
echo

echo "3. Test API Controller - Test Endpoint"
curl -s "$BASE_URL/api/test/"
echo
echo

echo "4. Wishlist API - Index"
curl -s "$BASE_URL/api/wishlist/"
echo
echo

echo "5. Wishlists abrufen"
curl -s "$BASE_URL/api/wishlist/wishlists"
echo
echo

echo "6. Neue Wishlist erstellen (POST)"
curl -X POST "$BASE_URL/api/wishlist/wishlists" \
     -H "Content-Type: application/json" \
     -d '{"name":"Test Wishlist","description":"Test Description"}'
echo
echo

echo "=== API Testing abgeschlossen ==="
