<div class="wishlist-container card">
    <div class="guest-badge">
        <small>👀 Du betrachtest die Wunschliste als Gast</small>
    </div>
    
    <div class="wishlist-header">
        <div class="wishlist-info">
            <h2 id="wishlist-title">🎁 $Wishlist.Name</h2>
            <p class="wishlist-creator">von $Wishlist.CreatorName</p>
            <% if $Wishlist.Description %>
            <p id="wishlist-description" class="wishlist-description">$Wishlist.Description</p>
            <% end_if %>
        </div>
        <div class="header-actions">
            <div class="share-section">
                <input type="text" id="share-link" class="form-control" readonly value="$AbsoluteURL">
                <button class="btn btn-secondary" id="copy-share-link">📋 Teilen</button>
            </div>
        </div>
    </div>
    
    <!-- Wishes Liste -->
    <div id="wishes-container" class="wishes-grid">
        <div class="loading-wishes">
            <p>🎁 Lade Wünsche...</p>
        </div>
    </div>
    
    <!-- Gast Info Box -->
    <div class="guest-info-box">
        <h4>💡 Tipps für Gäste:</h4>
        <div class="guest-tips">
            <ul>
                <li>Klicke auf "💝 Interessiert" bei Wünschen, die dir gefallen</li>
                <li>Der Ersteller wird über dein Interesse informiert</li>
                <li>Nutze die externen Links um Produkte anzusehen</li>
                <li>Teile diese Liste mit anderen Familienmitgliedern oder Freunden</li>
            </ul>
        </div>
    </div>
</div>

<!-- Store wishlist data for JavaScript -->
<script>
    window.wishlistId = '$Wishlist.ID';
    window.wishlistHash = '$CurrentHash';
</script>

<script type="module" src="$Vite("app/client/src/js/wishlist-guest.js")"></script>
