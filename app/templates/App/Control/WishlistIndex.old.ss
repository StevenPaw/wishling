<div class="card text-center">
    <h2>✨ Erstelle deine Wunschliste</h2>
    <p>Teile deine Wünsche mit Familie und Freunden!</p>
    
    <div class="mt-3">
        <form id="createWishlistForm" class="fade-in">
            <div class="form-group">
                <label for="wishlistName">Name der Wunschliste</label>
                <input type="text" id="wishlistName" class="form-control" 
                       placeholder="z.B. Meine Geburtstagswünsche" required>
            </div>
            
            <div class="form-group">
                <label for="wishlistDescription">Beschreibung (optional)</label>
                <textarea id="wishlistDescription" class="form-control" rows="3" 
                          placeholder="Ein paar Worte zu deiner Wunschliste..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="creatorName">Dein Name</label>
                <input type="text" id="creatorName" class="form-control" 
                       placeholder="Wie sollen dich andere nennen?" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                🎁 Wunschliste erstellen
            </button>
        </form>
        
        <div id="successMessage" class="d-none">
            <h3>🎉 Wunschliste erstellt!</h3>
            <p class="mb-3">Deine Wunschliste wurde erfolgreich erstellt.</p>
            
            <div class="form-group">
                <label>Dein Verwaltungslink (privat):</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="text" id="ownerLink" class="form-control" readonly>
                    <button type="button" class="btn btn-secondary" onclick="copyOwnerLink()">
                        📋 Kopieren
                    </button>
                </div>
                <small style="color: #666;">Mit diesem Link kannst du deine Wunschliste bearbeiten</small>
            </div>
            
            <div class="form-group">
                <label>Teilen-Link für andere (öffentlich):</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="text" id="shareLink" class="form-control" readonly>
                    <button type="button" class="btn btn-secondary" onclick="copyShareLink()">
                        📋 Kopieren
                    </button>
                </div>
                <small style="color: #666;">Diesen Link kannst du mit anderen teilen</small>
            </div>
            
            <div class="mt-3">
                <a id="manageButton" href="#" class="btn btn-primary">
                    ⚙️ Wunschliste verwalten
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createWishlistForm');
    const successDiv = document.getElementById('successMessage');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            name: document.getElementById('wishlistName').value.trim(),
            description: document.getElementById('wishlistDescription').value.trim(),
            creator_name: document.getElementById('creatorName').value.trim()
        };
        
        if (!formData.name || !formData.creator_name) {
            WishlistUtils.showAlert('Bitte fülle alle Pflichtfelder aus.', 'error');
            return;
        }
        
        try {
            WishlistUtils.setLoading(form, true);
            
            const response = await WishlistAPI.request('/wishlists', {
                method: 'POST',
                body: JSON.stringify(formData)
            });
            
            if (response.success) {
                const hash = response.data.hash;
                const baseURL = window.location.origin;
                
                // Links generieren
                const ownerURL = `${baseURL}/wishlist/list/${hash}`;
                const guestURL = `${baseURL}/wishlist/view/${hash}`;
                
                // Success-Ansicht vorbereiten
                document.getElementById('ownerLink').value = ownerURL;
                document.getElementById('shareLink').value = guestURL;
                document.getElementById('manageButton').href = ownerURL;
                
                // Formular ausblenden, Erfolg anzeigen
                form.style.display = 'none';
                successDiv.classList.remove('d-none');
                successDiv.classList.add('fade-in');
                
                WishlistUtils.showAlert('Wunschliste erfolgreich erstellt! 🎉');
            }
        } catch (error) {
            console.error('Error creating wishlist:', error);
            WishlistUtils.showAlert('Fehler beim Erstellen der Wunschliste: ' + error.message, 'error');
        } finally {
            WishlistUtils.setLoading(form, false);
        }
    });
});

function copyOwnerLink() {
    const link = document.getElementById('ownerLink').value;
    WishlistUtils.copyToClipboard(link);
}

function copyShareLink() {
    const link = document.getElementById('shareLink').value;
    WishlistUtils.copyToClipboard(link);
}
</script>
