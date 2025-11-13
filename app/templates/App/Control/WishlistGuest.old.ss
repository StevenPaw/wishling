<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
        <div>
            <h2>🎁 $Wishlist.Name</h2>
            <p style="color: #666; margin: 0;">von $Wishlist.CreatorName</p>
            <% if $Wishlist.Description %>
            <p style="margin-top: 0.5rem;">$Wishlist.Description</p>
            <% end_if %>
        </div>
        <div style="text-align: right;">
            <div style="background: #e3f2fd; padding: 0.5rem 1rem; border-radius: 8px; margin-bottom: 0.5rem;">
                <small style="color: #1976d2;">👁️ Gäste-Ansicht</small>
            </div>
        </div>
    </div>
    
    <!-- Statistiken für Gäste -->
    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <div id="wishlistStats" style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <div>🎯 <span id="totalCount">-</span> Wünsche</div>
            <div>✨ <span id="activeCount">-</span> verfügbar</div>
        </div>
    </div>
</div>

<!-- Wishes anzeigen -->
<div id="wishesContainer">
    <div id="loadingWishes" class="text-center">
        <p>🎁 Lade Wunschliste...</p>
    </div>
    
    <div id="wishesGrid" class="d-none" style="display: grid; gap: 1rem;">
        <!-- Wishes werden hier per JavaScript eingefügt -->
    </div>
    
    <div id="noWishes" class="d-none text-center card">
        <h3>🌟 Noch keine Wünsche</h3>
        <p>$Wishlist.CreatorName hat noch keine Wünsche hinzugefügt.</p>
    </div>
</div>

<!-- Info-Box für Gäste -->
<div class="card" style="background: #fff3cd; border: 1px solid #ffeaa7;">
    <h4 style="margin-bottom: 0.5rem;">💡 Für Gäste</h4>
    <p style="margin-bottom: 0.5rem;">Du siehst hier die Wunschliste von <strong>$Wishlist.CreatorName</strong>.</p>
    <p style="margin: 0; font-size: 0.9rem; color: #856404;">
        • Klicke auf die Links, um Produkte anzuschauen<br>
        • Die Preise sind nur Richtwerte<br>
        • Du kannst diese Liste nicht bearbeiten
    </p>
</div>

<!-- Eigene Wishlist erstellen Link -->
<div class="text-center mt-3">
    <a href="/wishlist/" class="btn btn-primary">
        ✨ Eigene Wunschliste erstellen
    </a>
</div>

<script>
let currentWishlistHash = '$CurrentHash';
let allWishes = [];

document.addEventListener('DOMContentLoaded', function() {
    loadWishes();
});

async function loadWishes() {
    try {
        // Nur aktive Wünsche für Gäste anzeigen
        const response = await WishlistAPI.request(`/wishlists/${currentWishlistHash}/wishes?archived=false`);
        
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
        <div class="card" style="position: relative; transition: transform 0.3s ease;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <h4 style="margin-bottom: 0.5rem;">🎯 ${wish.title}</h4>
                    ${wish.description ? `<p style="color: #666; margin-bottom: 0.5rem;">${wish.description}</p>` : ''}
                    ${wish.price ? `<div style="font-weight: bold; color: #28a745; margin-bottom: 0.5rem;">💰 ca. ${wish.price}</div>` : ''}
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        ${wish.url ? `
                            <a href="${wish.url}" target="_blank" class="btn btn-primary" 
                               style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                🔗 Produkt anschauen
                            </a>
                        ` : ''}
                        <button class="btn btn-success" 
                                style="font-size: 0.9rem; padding: 0.5rem 1rem;" 
                                onclick="markAsIdea('${wish.hash}', '${wish.title}')">
                            💡 Interessiert mich
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function markAsIdea(wishHash, wishTitle) {
    // Feedback für Gast ohne Backend-Änderung
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '✅ Notiert!';
    button.style.background = '#28a745';
    button.disabled = true;
    
    WishlistUtils.showAlert(`"${wishTitle}" als Geschenkidee notiert! 💡`);
    
    // Button nach 3 Sekunden zurücksetzen
    setTimeout(() => {
        button.innerHTML = originalText;
        button.style.background = '';
        button.disabled = false;
    }, 3000);
    
    // Optional: In Local Storage für spätere Referenz speichern
    const savedIdeas = JSON.parse(localStorage.getItem('giftIdeas') || '[]');
    if (!savedIdeas.find(idea => idea.hash === wishHash)) {
        savedIdeas.push({
            hash: wishHash,
            title: wishTitle,
            wishlist: '$Wishlist.Name',
            creator: '$Wishlist.CreatorName',
            timestamp: new Date().toISOString()
        });
        localStorage.setItem('giftIdeas', JSON.stringify(savedIdeas));
    }
}

// Hover-Effekt für Wish-Cards
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
    `;
    document.head.appendChild(style);
});
</script>
