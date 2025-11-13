// Wishlist creation functionality
class WishlistCreator {
  constructor() {
    this.apiBase = '/api/v1/wishlists';
    this.init();
  }

  init() {
    this.bindEvents();
  }

  bindEvents() {
    const createForm = document.getElementById('create-wishlist-form');
    if (createForm) {
      createForm.addEventListener('submit', (e) => this.handleCreateWishlist(e));
    }
  }

  async handleCreateWishlist(event) {
    event.preventDefault();
    const formData = new FormData(event.target);

    const wishlistData = {
      name: formData.get('name'),
      description: formData.get('description'),
      creator_name: formData.get('creator_name')
    };

    // Basic validation
    if (!wishlistData.name || !wishlistData.creator_name) {
      this.showError('Bitte fülle alle Pflichtfelder aus.');
      return;
    }

    try {
      // Show loading state
      const submitBtn = event.target.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = 'Erstelle...';

      const response = await fetch(this.apiBase, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(wishlistData)
      });

      const result = await response.json();

      if (response.ok) {
        // Show success message with links
        if (window.showSuccessMessage) {
          window.showSuccessMessage(result.data.owner_hash, result.data.guest_hash);
        }
        this.showSuccess('Wunschliste erfolgreich erstellt!');
      } else {
        this.showError(result.message || 'Fehler beim Erstellen der Wunschliste');
      }

      // Reset button
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;

    } catch (error) {
      this.showError('Netzwerkfehler: ' + error.message);

      // Reset button
      const submitBtn = event.target.querySelector('button[type="submit"]');
      submitBtn.disabled = false;
      submitBtn.textContent = '🎁 Wunschliste erstellen';
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
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new WishlistCreator();
});

// Global utility functions
window.WishlistUtils = {
  showAlert: function(message, type = 'success') {
    const creator = new WishlistCreator();
    if (type === 'success') {
      creator.showSuccess(message);
    } else {
      creator.showError(message);
    }
  },

  copyToClipboard: function(text) {
    navigator.clipboard.writeText(text).then(() => {
      this.showAlert('In Zwischenablage kopiert!');
    }).catch(() => {
      this.showAlert('Kopieren fehlgeschlagen', 'error');
    });
  }
};

console.log('Wishling frontend loaded');
