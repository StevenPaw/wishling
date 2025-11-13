/* empty css                 */class r{constructor(){this.apiBase="/api/v1/wishlists",this.init()}init(){this.bindEvents(),this.loadWishlist()}bindEvents(){const t=document.getElementById("add-wish-form");t&&t.addEventListener("submit",i=>this.handleAddWish(i));const s=document.getElementById("status-filter");s&&s.addEventListener("change",()=>this.filterWishes());const e=document.getElementById("price-filter");e&&e.addEventListener("input",()=>this.filterWishes()),document.addEventListener("click",i=>{i.target.matches(".edit-btn")?this.handleEditWish(i.target.closest(".wish-card")):i.target.matches(".archive-btn")?this.handleArchiveWish(i.target.closest(".wish-card")):i.target.matches(".unarchive-btn")?this.handleUnarchiveWish(i.target.closest(".wish-card")):i.target.matches(".delete-btn")&&this.handleDeleteWish(i.target.closest(".wish-card"))})}async loadWishlist(){try{const t=await fetch(this.apiBase),s=await t.json();t.ok?this.renderWishes(s.data):this.showError("Failed to load wishlist")}catch{this.showError("Network error loading wishlist")}}async handleAddWish(t){t.preventDefault();const s=new FormData(t.target);try{const e=await fetch(this.apiBase,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({title:s.get("title"),description:s.get("description"),price:s.get("price"),url:s.get("url"),priority:s.get("priority")})}),i=await e.json();e.ok?(t.target.reset(),this.loadWishlist(),this.showSuccess("Wish added successfully!")):this.showError(i.message||"Failed to add wish")}catch{this.showError("Network error adding wish")}}async handleArchiveWish(t){const s=t.dataset.wishId;if(s)try{(await fetch(`${this.apiBase}/${s}/archive`,{method:"POST"})).ok?(this.loadWishlist(),this.showSuccess("Wish archived successfully!")):this.showError("Failed to archive wish")}catch{this.showError("Network error archiving wish")}}async handleUnarchiveWish(t){const s=t.dataset.wishId;if(s)try{(await fetch(`${this.apiBase}/${s}/unarchive`,{method:"POST"})).ok?(this.loadWishlist(),this.showSuccess("Wish unarchived successfully!")):this.showError("Failed to unarchive wish")}catch{this.showError("Network error unarchiving wish")}}async handleDeleteWish(t){const s=t.dataset.wishId;if(s&&confirm("Are you sure you want to delete this wish?"))try{(await fetch(`${this.apiBase}/${s}`,{method:"DELETE"})).ok?(this.loadWishlist(),this.showSuccess("Wish deleted successfully!")):this.showError("Failed to delete wish")}catch{this.showError("Network error deleting wish")}}renderWishes(t){const s=document.getElementById("wishes-container");if(s){if(t.length===0){s.innerHTML=`
        <div class="empty-state">
          <div class="empty-icon">📝</div>
          <h3 class="empty-title">No wishes yet</h3>
          <p class="empty-description">Add your first wish using the form above!</p>
        </div>
      `;return}s.innerHTML=t.map(e=>this.renderWishCard(e)).join("")}}renderWishCard(t){const s=t.archived,e=s?'unarchive-btn">🔄 Unarchive':'archive-btn">📦 Archive';return`
      <div class="wish-card" data-wish-id="${t.id}">
        <div class="wish-content">
          <div class="wish-info">
            <h3 class="wish-title">${this.escapeHtml(t.title)}</h3>
            ${t.description?`<p class="wish-description">${this.escapeHtml(t.description)}</p>`:""}
            ${t.price?`<p class="wish-price"><span class="price-icon">💰</span>${t.price}</p>`:""}
            ${t.url?`<a href="${t.url}" target="_blank" class="wish-url">View Product</a>`:""}
            <span class="status-badge status-${s?"archived":"active"}">${s?"Archived":"Active"}</span>
          </div>
          <div class="wish-actions">
            <button class="action-btn edit-btn" title="Edit">✏️</button>
            <button class="action-btn ${e}</button>
            <button class="action-btn delete-btn" title="Delete">🗑️</button>
          </div>
        </div>
      </div>
    `}filterWishes(){this.loadWishlist()}showSuccess(t){this.showNotification(t,"success")}showError(t){this.showNotification(t,"error")}showNotification(t,s="info"){let e=document.getElementById("notification");e||(e=document.createElement("div"),e.id="notification",document.body.appendChild(e)),e.className=`notification notification-${s}`,e.textContent=t,e.style.display="block",setTimeout(()=>{e.style.display="none"},3e3)}escapeHtml(t){const s=document.createElement("div");return s.textContent=t,s.innerHTML}}document.addEventListener("DOMContentLoaded",()=>{new r});
//# sourceMappingURL=wishlist.js-DDh9VBWB.js.map
