<div class="wishlist-container card">
    <div class="wishlist-header">
        <div class="wishlist-info">
            <h2 id="wishlist-title">📝 $Wishlist.Name</h2>
            <p class="wishlist-creator">von $Wishlist.CreatorName</p>
            <% if $Wishlist.Description %>
            <p id="wishlist-description" class="wishlist-description">$Wishlist.Description</p>
            <% end_if %>
        </div>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="showShareDialog()">
                🔗 Teilen
            </button>
        </div>
    </div>
    
    <!-- Statistiken -->
    <div class="wishlist-stats">
        <div id="wishlistStats">
            <div class="stat-item">📊 <span id="totalCount">-</span> Wünsche gesamt</div>
            <div class="stat-item">✅ <span id="activeCount">-</span> aktiv</div>
            <div class="stat-item">📦 <span id="archivedCount">-</span> archiviert</div>
        </div>
    </div>
    
    <!-- Wunsch hinzufügen -->
    <div class="wish-form card">
        <h3>➕ Neuen Wunsch hinzufügen</h3>
        <form id="add-wish-form">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="title" class="form-control" 
                           placeholder="Was wünschst du dir?" required>
                </div>
                <div class="form-group">
                    <input type="text" name="price" class="form-control" 
                           placeholder="Preis (optional)">
                </div>
            </div>
            <div class="form-group">
                <textarea name="description" class="form-control" rows="2" 
                          placeholder="Beschreibung (optional)"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="url" name="url" class="form-control" 
                           placeholder="Link zum Produkt (optional)">
                </div>
                <div class="form-group">
                    <select name="priority" class="form-control">
                        <option value="medium">Normale Priorität</option>
                        <option value="high">Hohe Priorität</option>
                        <option value="low">Niedrige Priorität</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Hinzufügen</button>
            </div>
        </form>
    </div>
    
    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <button id="show-active" class="tab-button active" data-filter="active">
            ✅ Aktive Wünsche
        </button>
        <button id="show-archived" class="tab-button" data-filter="archived">
            📦 Archivierte Wünsche  
        </button>
    </div>

    <!-- Wishes Liste -->
    <div id="wishes-container" class="wishes-grid">
        <div class="loading-wishes">
            <p>📦 Lade Wünsche...</p>
        </div>
    </div>
</div>

<!-- Share Dialog -->
<div id="share-dialog" class="modal" style="display: none;">
    <div class="modal-content card">
        <h3>🔗 Wunschliste teilen</h3>
        
        <div class="share-link-group">
            <label>Dein Verwaltungslink (privat):</label>
            <div class="link-input-group">
                <input type="text" id="owner-link" class="form-control" readonly>
                <button class="copy-btn" onclick="copyOwnerLink()">📋</button>
            </div>
            <small>Nur für dich - zum Bearbeiten</small>
        </div>
        
        <div class="share-link-group">
            <label>Teilen-Link für Gäste:</label>
            <div class="link-input-group">
                <input type="text" id="guest-link" class="form-control" readonly>
                <button class="copy-btn" onclick="copyGuestLink()">📋</button>
            </div>
            <small>Diesen Link mit anderen teilen</small>
        </div>
        
        <div class="form-actions">
            <button class="btn btn-secondary" onclick="hideShareDialog()">Schließen</button>
        </div>
    </div>
</div>

<!-- Store wishlist hash for JavaScript -->
<script>
    window.wishlistHash = '$CurrentHash';
    
    // Share dialog functions
    function showShareDialog() {
        const baseURL = window.location.origin;
        document.getElementById('owner-link').value = `${baseURL}/wishlist/list/${window.wishlistHash}`;
        document.getElementById('guest-link').value = `${baseURL}/wishlist/view/${window.wishlistHash}`;
        document.getElementById('share-dialog').style.display = 'flex';
    }
    
    function hideShareDialog() {
        document.getElementById('share-dialog').style.display = 'none';
    }
    
    function copyOwnerLink() {
        const input = document.getElementById('owner-link');
        navigator.clipboard.writeText(input.value).then(() => {
            alert('Verwaltungslink kopiert!');
        });
    }
    
    function copyGuestLink() {
        const input = document.getElementById('guest-link');
        navigator.clipboard.writeText(input.value).then(() => {
            alert('Teilen-Link kopiert!');
        });
    }
</script>

<script type="module" src="$Vite("app/client/src/js/wishlist.js")"></script>
