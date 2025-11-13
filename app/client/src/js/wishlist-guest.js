// Wishlist functionality for guest users
import '../scss/wishlist.scss';

class WishlistGuest {
  constructor() {
    this.wishlistId = this.getWishlistIdFromUrl();
    this.apiBase = `/api/v1/wishlists/${this.wishlistId}`;
    this.init();
  }

  getWishlistIdFromUrl() {
    // Extract wishlist ID from URL path
    const pathParts = window.location.pathname.split('/');
    return pathParts[pathParts.length - 1];
  }

  init() {
    this.bindEvents();
    this.loadWishlist();
  }

  bindEvents() {
    // Interest button clicks
    document.addEventListener('click', (e) => {
      if (e.target.matches('.interest-btn')) {
        this.handleInterestClick(e.target);
      }
    });

    // Copy share link
    const copyBtn = document.getElementById('copy-share-link');
    if (copyBtn) {
      copyBtn.addEventListener('click', () => this.copyShareLink());
    }
  }

  async loadWishlist() {
    try {
      const response = await fetch(this.apiBase);
      const data = await response.json();
      
      if (response.ok) {
        this.renderWishes(data.data.wishes);
        this.updateWishlistInfo(data.data);
      } else {
        this.showError('Failed to load wishlist');
      }
    } catch (error) {
      this.showError('Network error loading wishlist');
    }
  }

  async handleInterestClick(button) {
    const wishId = button.closest('.wish-card').dataset.wishId;
    if (!wishId) return;

    // Disable button during request
    button.disabled = true;
    const originalText = button.textContent;
    button.textContent = 'Noting...';

    try {
      const response = await fetch(`${this.apiBase}/interest/${wishId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        }
      });

      if (response.ok) {
        button.classList.add('noted');
        button.textContent = '✅ Noted';
        this.showSuccess('Interest noted! The owner will be informed.');
      } else {
        this.showError('Failed to note interest');
        button.textContent = originalText;
      }
    } catch (error) {
      this.showError('Network error noting interest');
      button.textContent = originalText;
    } finally {
      button.disabled = false;
    }
  }

  renderWishes(wishes) {
    const container = document.getElementById('wishes-container');
    if (!container) return;

    // Filter out archived wishes for guests
    const activeWishes = wishes.filter(wish => !wish.archived);

    if (activeWishes.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">🎁</div>
          <h3 class="empty-title">No wishes to show</h3>
          <p class="empty-description">This wishlist is currently empty or all wishes are archived.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = activeWishes.map(wish => this.renderWishCard(wish)).join('');
  }

  renderWishCard(wish) {
    return `
      <div class="wish-card" data-wish-id="${wish.id}">
        <div class="wish-content">
          <div class="wish-info">
            <h3 class="wish-title">${this.escapeHtml(wish.title)}</h3>
            ${wish.description ? `<p class="wish-description">${this.escapeHtml(wish.description)}</p>` : ''}
            ${wish.price ? `<p class="wish-price"><span class="price-icon">💰</span>${wish.price}</p>` : ''}
            ${wish.url ? `<a href="${wish.url}" target="_blank" class="wish-url" rel="noopener noreferrer">View Product</a>` : ''}
          </div>
          <div class="wish-actions">
            <button class="interest-btn" title="Show interest in this wish">
              💝 Interested
            </button>
          </div>
        </div>
      </div>
    `;
  }

  updateWishlistInfo(wishlist) {
    // Update page title if element exists
    const titleElement = document.getElementById('wishlist-title');
    if (titleElement) {
      titleElement.textContent = wishlist.title || 'Wishlist';
    }

    // Update description if element exists
    const descElement = document.getElementById('wishlist-description');
    if (descElement && wishlist.description) {
      descElement.textContent = wishlist.description;
      descElement.style.display = 'block';
    }

    // Update share link
    const shareLinkInput = document.getElementById('share-link');
    if (shareLinkInput) {
      shareLinkInput.value = window.location.href;
    }
  }

  copyShareLink() {
    const shareLinkInput = document.getElementById('share-link');
    if (shareLinkInput) {
      shareLinkInput.select();
      document.execCommand('copy');
      this.showSuccess('Share link copied to clipboard!');
    }
  }

  showSuccess(message) {
    this.showNotification(message, 'success');
  }

  showError(message) {
    this.showNotification(message, 'error');
  }

  showNotification(message, type = 'info') {
    // Create or update notification element
    let notification = document.getElementById('notification');
    if (!notification) {
      notification = document.createElement('div');
      notification.id = 'notification';
      notification.style.cssText = `
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
      `;
      document.body.appendChild(notification);
    }

    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Set background color based on type
    if (type === 'success') {
      notification.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
      notification.style.backgroundColor = '#dc3545';
    } else {
      notification.style.backgroundColor = '#17a2b8';
    }
    
    notification.style.display = 'block';

    // Auto-hide after 3 seconds
    setTimeout(() => {
      notification.style.display = 'none';
    }, 3000);
  }

  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new WishlistGuest();
});
