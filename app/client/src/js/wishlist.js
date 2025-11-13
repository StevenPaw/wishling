// Wishlist functionality for logged-in users
import '../scss/wishlist.scss';

class WishlistManager {
  constructor() {
    this.apiBase = '/api/v1/wishlists';
    this.init();
  }

  init() {
    this.bindEvents();
    this.loadWishlist();
  }

  bindEvents() {
    // Add wish form submission
    const addForm = document.getElementById('add-wish-form');
    if (addForm) {
      addForm.addEventListener('submit', (e) => this.handleAddWish(e));
    }

    // Filter controls
    const statusFilter = document.getElementById('status-filter');
    if (statusFilter) {
      statusFilter.addEventListener('change', () => this.filterWishes());
    }

    const priceFilter = document.getElementById('price-filter');
    if (priceFilter) {
      priceFilter.addEventListener('input', () => this.filterWishes());
    }

    // Delegate click events for dynamic buttons
    document.addEventListener('click', (e) => {
      if (e.target.matches('.edit-btn')) {
        this.handleEditWish(e.target.closest('.wish-card'));
      } else if (e.target.matches('.archive-btn')) {
        this.handleArchiveWish(e.target.closest('.wish-card'));
      } else if (e.target.matches('.unarchive-btn')) {
        this.handleUnarchiveWish(e.target.closest('.wish-card'));
      } else if (e.target.matches('.delete-btn')) {
        this.handleDeleteWish(e.target.closest('.wish-card'));
      }
    });
  }

  async loadWishlist() {
    try {
      const response = await fetch(this.apiBase);
      const data = await response.json();
      
      if (response.ok) {
        this.renderWishes(data.data);
      } else {
        this.showError('Failed to load wishlist');
      }
    } catch (error) {
      this.showError('Network error loading wishlist');
    }
  }

  async handleAddWish(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    try {
      const response = await fetch(this.apiBase, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          title: formData.get('title'),
          description: formData.get('description'),
          price: formData.get('price'),
          url: formData.get('url'),
          priority: formData.get('priority')
        })
      });

      const result = await response.json();
      
      if (response.ok) {
        event.target.reset();
        this.loadWishlist(); // Reload the list
        this.showSuccess('Wish added successfully!');
      } else {
        this.showError(result.message || 'Failed to add wish');
      }
    } catch (error) {
      this.showError('Network error adding wish');
    }
  }

  async handleArchiveWish(wishCard) {
    const wishId = wishCard.dataset.wishId;
    if (!wishId) return;

    try {
      const response = await fetch(`${this.apiBase}/${wishId}/archive`, {
        method: 'POST'
      });

      if (response.ok) {
        this.loadWishlist();
        this.showSuccess('Wish archived successfully!');
      } else {
        this.showError('Failed to archive wish');
      }
    } catch (error) {
      this.showError('Network error archiving wish');
    }
  }

  async handleUnarchiveWish(wishCard) {
    const wishId = wishCard.dataset.wishId;
    if (!wishId) return;

    try {
      const response = await fetch(`${this.apiBase}/${wishId}/unarchive`, {
        method: 'POST'
      });

      if (response.ok) {
        this.loadWishlist();
        this.showSuccess('Wish unarchived successfully!');
      } else {
        this.showError('Failed to unarchive wish');
      }
    } catch (error) {
      this.showError('Network error unarchiving wish');
    }
  }

  async handleDeleteWish(wishCard) {
    const wishId = wishCard.dataset.wishId;
    if (!wishId) return;

    if (!confirm('Are you sure you want to delete this wish?')) {
      return;
    }

    try {
      const response = await fetch(`${this.apiBase}/${wishId}`, {
        method: 'DELETE'
      });

      if (response.ok) {
        this.loadWishlist();
        this.showSuccess('Wish deleted successfully!');
      } else {
        this.showError('Failed to delete wish');
      }
    } catch (error) {
      this.showError('Network error deleting wish');
    }
  }

  renderWishes(wishes) {
    const container = document.getElementById('wishes-container');
    if (!container) return;

    if (wishes.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">📝</div>
          <h3 class="empty-title">No wishes yet</h3>
          <p class="empty-description">Add your first wish using the form above!</p>
        </div>
      `;
      return;
    }

    container.innerHTML = wishes.map(wish => this.renderWishCard(wish)).join('');
  }

  renderWishCard(wish) {
    const isArchived = wish.archived;
    const archiveAction = isArchived ? 'unarchive-btn">🔄 Unarchive' : 'archive-btn">📦 Archive';
    
    return `
      <div class="wish-card" data-wish-id="${wish.id}">
        <div class="wish-content">
          <div class="wish-info">
            <h3 class="wish-title">${this.escapeHtml(wish.title)}</h3>
            ${wish.description ? `<p class="wish-description">${this.escapeHtml(wish.description)}</p>` : ''}
            ${wish.price ? `<p class="wish-price"><span class="price-icon">💰</span>${wish.price}</p>` : ''}
            ${wish.url ? `<a href="${wish.url}" target="_blank" class="wish-url">View Product</a>` : ''}
            <span class="status-badge status-${isArchived ? 'archived' : 'active'}">${isArchived ? 'Archived' : 'Active'}</span>
          </div>
          <div class="wish-actions">
            <button class="action-btn edit-btn" title="Edit">✏️</button>
            <button class="action-btn ${archiveAction}</button>
            <button class="action-btn delete-btn" title="Delete">🗑️</button>
          </div>
        </div>
      </div>
    `;
  }

  filterWishes() {
    // Implement filtering logic
    this.loadWishlist();
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
      document.body.appendChild(notification);
    }

    notification.className = `notification notification-${type}`;
    notification.textContent = message;
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
  new WishlistManager();
});
