class c{constructor(){this.apiBase="/api/v1/wishlists",this.init()}init(){this.bindEvents()}bindEvents(){const e=document.getElementById("create-wishlist-form");e&&e.addEventListener("submit",s=>this.handleCreateWishlist(s))}async handleCreateWishlist(e){e.preventDefault();const s=new FormData(e.target),t={name:s.get("name"),description:s.get("description"),creator_name:s.get("creator_name")};if(!t.name||!t.creator_name){this.showError("Bitte fülle alle Pflichtfelder aus.");return}try{const o=e.target.querySelector('button[type="submit"]'),n=o.textContent;o.disabled=!0,o.textContent="Erstelle...";const a=await fetch(this.apiBase,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)}),r=await a.json();a.ok?(window.showSuccessMessage&&window.showSuccessMessage(r.data.owner_hash,r.data.guest_hash),this.showSuccess("Wunschliste erfolgreich erstellt!")):this.showError(r.message||"Fehler beim Erstellen der Wunschliste"),o.disabled=!1,o.textContent=n}catch(o){this.showError("Netzwerkfehler: "+o.message);const n=e.target.querySelector('button[type="submit"]');n.disabled=!1,n.textContent="🎁 Wunschliste erstellen"}}showSuccess(e){this.showNotification(e,"success")}showError(e){this.showNotification(e,"error")}showNotification(e,s="info"){let t=document.getElementById("notification");t||(t=document.createElement("div"),t.id="notification",t.style.cssText=`
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
      `,document.body.appendChild(t)),t.className=`notification notification-${s}`,t.textContent=e,s==="success"?t.style.backgroundColor="#28a745":s==="error"?t.style.backgroundColor="#dc3545":t.style.backgroundColor="#17a2b8",t.style.display="block",setTimeout(()=>{t.style.display="none"},3e3)}}document.addEventListener("DOMContentLoaded",()=>{new c});window.WishlistUtils={showAlert:function(i,e="success"){const s=new c;e==="success"?s.showSuccess(i):s.showError(i)},copyToClipboard:function(i){navigator.clipboard.writeText(i).then(()=>{this.showAlert("In Zwischenablage kopiert!")}).catch(()=>{this.showAlert("Kopieren fehlgeschlagen","error")})}};console.log("Wishling frontend loaded");
//# sourceMappingURL=main.js-hVaghDd8.js.map
