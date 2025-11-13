/* empty css                 */class n{constructor(){this.wishlistId=this.getWishlistIdFromUrl(),this.apiBase=`/api/v1/wishlists/${this.wishlistId}`,this.init()}getWishlistIdFromUrl(){const t=window.location.pathname.split("/");return t[t.length-1]}init(){this.bindEvents(),this.loadWishlist()}bindEvents(){document.addEventListener("click",e=>{e.target.matches(".interest-btn")&&this.handleInterestClick(e.target)});const t=document.getElementById("copy-share-link");t&&t.addEventListener("click",()=>this.copyShareLink())}async loadWishlist(){try{const t=await fetch(this.apiBase),e=await t.json();t.ok?(this.renderWishes(e.data.wishes),this.updateWishlistInfo(e.data)):this.showError("Failed to load wishlist")}catch{this.showError("Network error loading wishlist")}}async handleInterestClick(t){const e=t.closest(".wish-card").dataset.wishId;if(!e)return;t.disabled=!0;const i=t.textContent;t.textContent="Noting...";try{(await fetch(`${this.apiBase}/interest/${e}`,{method:"POST",headers:{"Content-Type":"application/json"}})).ok?(t.classList.add("noted"),t.textContent="✅ Noted",this.showSuccess("Interest noted! The owner will be informed.")):(this.showError("Failed to note interest"),t.textContent=i)}catch{this.showError("Network error noting interest"),t.textContent=i}finally{t.disabled=!1}}renderWishes(t){const e=document.getElementById("wishes-container");if(!e)return;const i=t.filter(s=>!s.archived);if(i.length===0){e.innerHTML=`
        <div class="empty-state">
          <div class="empty-icon">🎁</div>
          <h3 class="empty-title">No wishes to show</h3>
          <p class="empty-description">This wishlist is currently empty or all wishes are archived.</p>
        </div>
      `;return}e.innerHTML=i.map(s=>this.renderWishCard(s)).join("")}renderWishCard(t){return`
      <div class="wish-card" data-wish-id="${t.id}">
        <div class="wish-content">
          <div class="wish-info">
            <h3 class="wish-title">${this.escapeHtml(t.title)}</h3>
            ${t.description?`<p class="wish-description">${this.escapeHtml(t.description)}</p>`:""}
            ${t.price?`<p class="wish-price"><span class="price-icon">💰</span>${t.price}</p>`:""}
            ${t.url?`<a href="${t.url}" target="_blank" class="wish-url" rel="noopener noreferrer">View Product</a>`:""}
          </div>
          <div class="wish-actions">
            <button class="interest-btn" title="Show interest in this wish">
              💝 Interested
            </button>
          </div>
        </div>
      </div>
    `}updateWishlistInfo(t){const e=document.getElementById("wishlist-title");e&&(e.textContent=t.title||"Wishlist");const i=document.getElementById("wishlist-description");i&&t.description&&(i.textContent=t.description,i.style.display="block");const s=document.getElementById("share-link");s&&(s.value=window.location.href)}copyShareLink(){const t=document.getElementById("share-link");t&&(t.select(),document.execCommand("copy"),this.showSuccess("Share link copied to clipboard!"))}showSuccess(t){this.showNotification(t,"success")}showError(t){this.showNotification(t,"error")}showNotification(t,e="info"){let i=document.getElementById("notification");i||(i=document.createElement("div"),i.id="notification",i.style.cssText=`
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 1000;
        max-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      `,document.body.appendChild(i)),i.className=`notification notification-${e}`,i.textContent=t,e==="success"?i.style.backgroundColor="#28a745":e==="error"?i.style.backgroundColor="#dc3545":i.style.backgroundColor="#17a2b8",i.style.display="block",setTimeout(()=>{i.style.display="none"},3e3)}escapeHtml(t){const e=document.createElement("div");return e.textContent=t,e.innerHTML}}document.addEventListener("DOMContentLoaded",()=>{new n});
//# sourceMappingURL=wishlist-guest.js-BjXE57In.js.map
