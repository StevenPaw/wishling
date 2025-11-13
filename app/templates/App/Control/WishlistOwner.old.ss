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
</div>

<!-- Filter Tabs -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
    <button id="showActive" class="btn btn-primary" onclick="showWishes('active')">
        ✅ Aktive Wünsche
    </button>
    <button id="showArchived" class="btn btn-secondary" onclick="showWishes('archived')">
        📦 Archivierte Wünsche  
    </button>
</div>

<!-- Wishes Liste -->
<div id="wishesContainer">
    <div id="loadingWishes" class="text-center">
        <p>📦 Lade Wünsche...</p>
    </div>
    
    <div id="wishesGrid" class="d-none" style="display: grid; gap: 1rem;">
        <!-- Wishes werden hier per JavaScript eingefügt -->
    </div>
    
    <div id="noWishes" class="d-none text-center card">
        <h3>🌟 Noch keine Wünsche</h3>
        <p>Füge deinen ersten Wunsch hinzu!</p>
    </div>
</div>

<!-- Share Dialog -->
<div id="shareDialog" class="d-none" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; 
     background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 500px; width: 90%; margin: 2rem;">
        <h3>🔗 Wunschliste teilen</h3>
        
        <div class="form-group">
            <label>Dein Verwaltungslink (privat):</label>
            <div style="display: flex; gap: 0.5rem;">
                <input type="text" id="modalOwnerLink" class="form-control" readonly>
                <button class="btn btn-secondary" onclick="WishlistUtils.copyToClipboard(document.getElementById('modalOwnerLink').value)">📋</button>
            </div>
            <small style="color: #666;">Nur für dich - zum Bearbeiten</small>
        </div>
        
        <div class="form-group">
            <label>Teilen-Link für Gäste:</label>
            <div style="display: flex; gap: 0.5rem;">
                <input type="text" id="modalShareLink" class="form-control" readonly>
                <button class="btn btn-secondary" onclick="WishlistUtils.copyToClipboard(document.getElementById('modalShareLink').value)">📋</button>
            </div>
            <small style="color: #666;">Diesen Link mit anderen teilen</small>
        </div>
        
        <div class="text-center mt-3">
            <button class="btn btn-secondary" onclick="hideShareDialog()">Schließen</button>
        </div>
    </div>
</div>

<!-- Edit Wish Modal -->
<div id="editWishModal" class="d-none" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; 
     background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 500px; width: 90%; margin: 2rem;">
        <h3>✏️ Wunsch bearbeiten</h3>
        <form id="editWishForm">
            <input type="hidden" id="editWishHash">
            
            <div class="form-group">
                <label>Titel:</label>
                <input type="text" id="editWishTitle" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Beschreibung:</label>
                <textarea id="editWishDescription" class="form-control" rows="3"></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Preis:</label>
                    <input type="text" id="editWishPrice" class="form-control">
                </div>
                <div class="form-group">
                    <label>Link:</label>
                    <input type="url" id="editWishURL" class="form-control">
                </div>
            </div>
            
            <div class="text-center mt-3" style="display: flex; gap: 1rem; justify-content: center;">
                <button type="submit" class="btn btn-success">💾 Speichern</button>
                <button type="button" class="btn btn-secondary" onclick="hideEditModal()">Abbrechen</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentWishlistHash = '$CurrentHash';
let currentView = 'active';
let allWishes = [];

document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    loadWishes();
    setupShareDialog();
});

function setupEventListeners() {
    // Wunsch hinzufügen
    document.getElementById('addWishForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await addNewWish();
    });
    
    // Wunsch bearbeiten
    document.getElementById('editWishForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await saveWishEdit();
    });
}

async function addNewWish() {
    const formData = {
        title: document.getElementById('newWishTitle').value.trim(),
        description: document.getElementById('newWishDescription').value.trim(),
        price: document.getElementById('newWishPrice').value.trim(),
        url: document.getElementById('newWishURL').value.trim()
    };
    
    if (!formData.title) {
        WishlistUtils.showAlert('Bitte gib einen Titel ein.', 'error');
        return;
    }
    
    try {
        const form = document.getElementById('addWishForm');
        WishlistUtils.setLoading(form, true);
        
        const response = await WishlistAPI.request(`/wishlists/${currentWishlistHash}/wishes`, {
            method: 'POST',
            body: JSON.stringify(formData)
        });
        
        if (response.success) {
            WishlistUtils.showAlert('Wunsch hinzugefügt! 🎉');
            form.reset();
            loadWishes();
        }
    } catch (error) {
        WishlistUtils.showAlert('Fehler beim Hinzufügen: ' + error.message, 'error');
    }
}

async function loadWishes() {
    try {
        const response = await WishlistAPI.request(`/wishlists/${currentWishlistHash}/wishes?archived=${currentView === 'archived'}`);
        
        if (response.success) {
            allWishes = response.data || [];
            updateStats(response.stats);
            renderWishes(allWishes);
        }
    } catch (error) {
        console.error('Error loading wishes:', error);
        WishlistUtils.showAlert('Fehler beim Laden der Wünsche: ' + error.message, 'error');
    }
}

function updateStats(stats) {
    if (stats) {
        document.getElementById('totalCount').textContent = stats.total || 0;
        document.getElementById('activeCount').textContent = stats.active || 0;  
        document.getElementById('archivedCount').textContent = stats.archived || 0;
    }
}

function renderWishes(wishes) {
    const container = document.getElementById('wishesContainer');
    const grid = document.getElementById('wishesGrid');
    const noWishes = document.getElementById('noWishes');
    const loading = document.getElementById('loadingWishes');
    
    loading.classList.add('d-none');
    
    if (!wishes || wishes.length === 0) {
        grid.classList.add('d-none');
        noWishes.classList.remove('d-none');
        return;
    }
    
    noWishes.classList.add('d-none');
    grid.classList.remove('d-none');
    
    grid.innerHTML = wishes.map(wish => `
        <div class="card" style="position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <h4>${wish.title}</h4>
                    ${wish.description ? `<p style="color: #666;">${wish.description}</p>` : ''}
                    ${wish.price ? `<div style="font-weight: bold; color: #28a745;">💰 ${wish.price}</div>` : ''}
                    ${wish.url ? `<a href="${wish.url}" target="_blank" style="color: #667eea; text-decoration: none;">🔗 Link anzeigen</a>` : ''}
                </div>
                <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                    <button class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.5rem;" 
                            onclick="editWish('${wish.hash}')">✏️</button>
                    ${wish.archived ? 
                        `<button class="btn btn-success" style="font-size: 0.8rem; padding: 0.5rem;" 
                                 onclick="unarchiveWish('${wish.hash}')">↩️</button>` :
                        `<button class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.5rem;" 
                                 onclick="archiveWish('${wish.hash}')">📦</button>`
                    }
                    <button class="btn" style="background: #dc3545; color: white; font-size: 0.8rem; padding: 0.5rem;" 
                            onclick="deleteWish('${wish.hash}')">🗑️</button>
                </div>
            </div>
        </div>
    `).join('');
}

function showWishes(view) {
    currentView = view;
    
    // Buttons aktualisieren
    document.getElementById('showActive').className = view === 'active' ? 'btn btn-primary' : 'btn btn-secondary';
    document.getElementById('showArchived').className = view === 'archived' ? 'btn btn-primary' : 'btn btn-secondary';
    
    loadWishes();
}

async function archiveWish(wishHash) {
    if (!confirm('Wunsch archivieren?')) return;
    
    try {
        const response = await WishlistAPI.request(`/wishlists/${currentWishlistHash}/wishes/${wishHash}/archive`, {
            method: 'POST'
        });
        
        if (response.success) {
            WishlistUtils.showAlert('Wunsch archiviert 📦');
            loadWishes();
        }
    } catch (error) {
        WishlistUtils.showAlert('Fehler beim Archivieren: ' + error.message, 'error');
    }
}

async function unarchiveWish(wishHash) {
    try {
        const response = await WishlistAPI.request(`/wishlists/${currentWishlistHash}/wishes/${wishHash}/unarchive`, {
            method: 'POST'
        });
        
        if (response.success) {
            WishlistUtils.showAlert('Wunsch wiederhergestellt ↩️');
            loadWishes();
        }
    } catch (error) {
        WishlistUtils.showAlert('Fehler beim Wiederherstellen: ' + error.message, 'error');
    }
}

async function deleteWish(wishHash) {
    if (!confirm('Wunsch endgültig löschen? Diese Aktion kann nicht rückgängig gemacht werden.')) return;
    
    try {
        const response = await WishlistAPI.request(`/wishlists/${currentWishlistHash}/wishes/${wishHash}`, {
            method: 'DELETE'
        });
        
        if (response.success) {
            WishlistUtils.showAlert('Wunsch gelöscht 🗑️');
            loadWishes();
        }
    } catch (error) {
        WishlistUtils.showAlert('Fehler beim Löschen: ' + error.message, 'error');
    }
}

function editWish(wishHash) {
    const wish = allWishes.find(w => w.hash === wishHash);
    if (!wish) return;
    
    document.getElementById('editWishHash').value = wish.hash;
    document.getElementById('editWishTitle').value = wish.title || '';
    document.getElementById('editWishDescription').value = wish.description || '';
    document.getElementById('editWishPrice').value = wish.price || '';
    document.getElementById('editWishURL').value = wish.url || '';
    
    document.getElementById('editWishModal').classList.remove('d-none');
}

function hideEditModal() {
    document.getElementById('editWishModal').classList.add('d-none');
}

async function saveWishEdit() {
    const wishHash = document.getElementById('editWishHash').value;
    const formData = {
        title: document.getElementById('editWishTitle').value.trim(),
        description: document.getElementById('editWishDescription').value.trim(),
        price: document.getElementById('editWishPrice').value.trim(),
        url: document.getElementById('editWishURL').value.trim()
    };
    
    if (!formData.title) {
        WishlistUtils.showAlert('Bitte gib einen Titel ein.', 'error');
        return;
    }
    
    try {
        const response = await WishlistAPI.request(`/wishlists/${currentWishlistHash}/wishes/${wishHash}`, {
            method: 'PUT',
            body: JSON.stringify(formData)
        });
        
        if (response.success) {
            WishlistUtils.showAlert('Wunsch gespeichert! 💾');
            hideEditModal();
            loadWishes();
        }
    } catch (error) {
        WishlistUtils.showAlert('Fehler beim Speichern: ' + error.message, 'error');
    }
}

function setupShareDialog() {
    const baseURL = window.location.origin;
    const ownerURL = `${baseURL}/wishlist/list/${currentWishlistHash}`;
    const guestURL = `${baseURL}/wishlist/view/${currentWishlistHash}`;
    
    document.getElementById('modalOwnerLink').value = ownerURL;
    document.getElementById('modalShareLink').value = guestURL;
}

function showShareDialog() {
    document.getElementById('shareDialog').classList.remove('d-none');
}

function hideShareDialog() {
    document.getElementById('shareDialog').classList.add('d-none');
}

// ESC-Key zum Schließen von Modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideEditModal();
        hideShareDialog();
    }
});
</script>
