<div class="card text-center">
    <h2>✨ Erstelle deine Wunschliste</h2>
    <p>Teile deine Wünsche mit Familie und Freunden!</p>
    
    <div class="create-form-container">
        <form id="create-wishlist-form" class="fade-in">
            <div class="form-group">
                <label for="wishlist-name">Name der Wunschliste</label>
                <input type="text" id="wishlist-name" name="name" class="form-control" 
                       placeholder="z.B. Meine Geburtstagswünsche" required>
            </div>
            
            <div class="form-group">
                <label for="wishlist-description">Beschreibung (optional)</label>
                <textarea id="wishlist-description" name="description" class="form-control" rows="3" 
                          placeholder="Ein paar Worte zu deiner Wunschliste..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="creator-name">Dein Name</label>
                <input type="text" id="creator-name" name="creator_name" class="form-control" 
                       placeholder="Wie sollen dich andere nennen?" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    🎁 Wunschliste erstellen
                </button>
            </div>
        </form>
        
        <div id="success-message" class="success-state" style="display: none;">
            <h3>🎉 Wunschliste erstellt!</h3>
            <p>Deine Wunschliste wurde erfolgreich erstellt.</p>
            
            <div class="form-group">
                <label>Dein Verwaltungslink (privat):</label>
                <div class="link-input-group">
                    <input type="text" id="owner-link" class="form-control" readonly>
                    <button type="button" class="btn btn-secondary copy-btn" onclick="copyOwnerLink()">
                        📋 Kopieren
                    </button>
                </div>
                <small>Mit diesem Link kannst du deine Wunschliste bearbeiten</small>
            </div>
            
            <div class="form-group">
                <label>Teilen-Link für andere (öffentlich):</label>
                <div class="link-input-group">
                    <input type="text" id="share-link" class="form-control" readonly>
                    <button type="button" class="btn btn-secondary copy-btn" onclick="copyShareLink()">
                        📋 Kopieren
                    </button>
                </div>
                <small>Diesen Link mit Familie und Freunden teilen</small>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="goToWishlist()">
                    📝 Zur Wunschliste
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let createdWishlistHash = null;
    
    function copyOwnerLink() {
        const input = document.getElementById('owner-link');
        navigator.clipboard.writeText(input.value).then(() => {
            alert('Verwaltungslink kopiert!');
        });
    }
    
    function copyShareLink() {
        const input = document.getElementById('share-link');
        navigator.clipboard.writeText(input.value).then(() => {
            alert('Teilen-Link kopiert!');
        });
    }
    
    function goToWishlist() {
        if (createdWishlistHash) {
            window.location.href = `/wishlist/list/${createdWishlistHash}`;
        }
    }
    
    // Form handling will be done by the main JavaScript module
    window.showSuccessMessage = function(ownerHash, guestHash) {
        createdWishlistHash = ownerHash;
        const baseURL = window.location.origin;
        
        document.getElementById('owner-link').value = `${baseURL}/wishlist/list/${ownerHash}`;
        document.getElementById('share-link').value = `${baseURL}/wishlist/view/${guestHash}`;
        
        document.getElementById('create-wishlist-form').style.display = 'none';
        document.getElementById('success-message').style.display = 'block';
    };
</script>

<script type="module" src="$Vite("app/client/src/js/main.js")"></script>
