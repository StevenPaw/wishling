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

// Wish management functionality
class WishManager {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
  }

  bindEvents() {
    // Handle Add Wish Form
    const addWishForm = document.getElementById('Form_AddWishForm');
    if (addWishForm) {
      addWishForm.addEventListener('submit', (e) => this.handleAddWish(e));
    }

    // Handle Archive Wish Buttons (delegated event)
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('archive-wish-btn')) {
        this.handleArchiveWish(e.target);
      }
    });

    // Handle Inline Editing - Wishes
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('editable') && !e.target.querySelector('input')) {
        this.makeEditable(e.target, 'wish');
      }
    });

    // Handle Inline Editing - Wishlist
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('editable-wishlist') && !e.target.querySelector('input')) {
        this.makeEditable(e.target, 'wishlist');
      }
    });
  }

  async handleAddWish(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const submitButton = event.target.querySelector('button[type="submit"]');

    // Deaktiviere Submit-Button während des Requests
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Wird gespeichert...';
    }

    const wishData = {
      OwnerHash: formData.get('OwnerHash'),
      Title: formData.get('Title'),
      Description: formData.get('Description'),
      Price: formData.get('Price'),
      URL: formData.get('URL')
    };

    // Validierung
    if (!wishData.Title || !wishData.OwnerHash) {
      this.showError('Titel ist erforderlich');
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = 'Wunsch hinzufügen';
      }
      return;
    }

    try {
      const response = await fetch('/api/addwish', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(wishData)
      });

      const data = await response.json();

      if (data.error) {
        this.showError('Fehler: ' + data.error);
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent = 'Wunsch hinzufügen';
        }
      } else if (data.success) {
        // Zeige Erfolgsbenachrichtigung
        this.showSuccess('Wunsch "' + data.wish.Title + '" wurde erfolgreich hinzugefügt!');

        // Füge neuen Wunsch zur Tabelle hinzu (optimistic UI)
        this.addWishToTable(data.wish);

        // Formular zurücksetzen
        event.target.reset();

        // Button wieder aktivieren
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent = 'Wunsch hinzufügen';
        }
      }
    } catch (error) {
      console.error('Error:', error);
      this.showError('Es gab einen Fehler beim Speichern');
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = 'Wunsch hinzufügen';
      }
    }
  }

  addWishToTable(wish) {
    const tbody = document.getElementById('wishlist-body');
    if (!tbody) return;

    // Entferne "Noch keine Wünsche" Nachricht falls vorhanden
    const emptyMessage = tbody.querySelector('tr td[colspan]');
    if (emptyMessage) {
      emptyMessage.parentElement.remove();
    }

    // Erstelle neue Tabellenzeile
    const tr = document.createElement('tr');
    tr.className = 'wishlist_item';
    tr.setAttribute('data-wish-hash', wish.Hash);

    // Formatiere Preis
    const priceDisplay = wish.Price ? `${parseFloat(wish.Price).toFixed(2)} €` : '-';

    // Erstelle Link falls vorhanden
    const linkDisplay = wish.URL
      ? `<a href="${this.escapeHtml(wish.URL)}" target="_blank" rel="noopener noreferrer">Link</a>`
      : '-';

    tr.innerHTML = `
      <td><strong>${this.escapeHtml(wish.Title)}</strong></td>
      <td>${this.escapeHtml(wish.Description || '')}</td>
      <td>${priceDisplay}</td>
      <td>${linkDisplay}</td>
      <td>
        <button type="button" class="archive-wish-btn" data-wish-hash="${wish.Hash}">Archivieren</button>
      </td>
    `;

    // Füge vor dem Formular ein (letztes tr ist das Formular)
    const formRow = tbody.querySelector('tr.addnew_form');
    if (formRow) {
      tbody.insertBefore(tr, formRow);
    } else {
      tbody.appendChild(tr);
    }
  }

  async handleArchiveWish(button) {
    const wishHash = button.getAttribute('data-wish-hash');
    if (!wishHash) return;

    // Bestätigungsdialog
    if (!confirm('Möchtest du diesen Wunsch wirklich archivieren?')) {
      return;
    }

    // Deaktiviere Button
    button.disabled = true;
    button.textContent = 'Archiviere...';

    try {
      const response = await fetch('/api/archivewish', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ Hash: wishHash })
      });

      const data = await response.json();

      if (data.error) {
        this.showError('Fehler: ' + data.error);
        button.disabled = false;
        button.textContent = 'Archivieren';
      } else if (data.success) {
        this.showSuccess('Wunsch wurde archiviert');

        // Entferne Zeile aus der Tabelle
        const row = button.closest('tr');
        if (row) {
          row.style.opacity = '0';
          row.style.transition = 'opacity 0.3s';
          setTimeout(() => row.remove(), 300);
        }
      }
    } catch (error) {
      console.error('Error:', error);
      this.showError('Fehler beim Archivieren');
      button.disabled = false;
      button.textContent = 'Archivieren';
    }
  }

  escapeHtml(text) {
    if (!text) return '';
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
  }

  makeEditable(element, type) {
    const field = element.getAttribute('data-field');
    const currentValue = element.textContent.trim().replace(/^€\s*/, ''); // Entferne € Zeichen

    // Erstelle Input-Feld
    const input = document.createElement('input');
    input.type = 'text';
    input.value = currentValue;
    input.style.width = '100%';
    input.style.boxSizing = 'border-box';

    // Ersetze Inhalt mit Input
    element.innerHTML = '';
    element.appendChild(input);
    input.focus();
    input.select();

    // Speichern bei Blur
    const saveEdit = async () => {
      const newValue = input.value.trim();

      if (newValue === currentValue) {
        // Keine Änderung - zurücksetzen
        element.textContent = currentValue;
        return;
      }

      if (type === 'wish') {
        await this.updateWish(element, field, newValue);
      } else if (type === 'wishlist') {
        await this.updateWishlist(element, field, newValue);
      }
    };

    input.addEventListener('blur', saveEdit);
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        input.blur();
      } else if (e.key === 'Escape') {
        element.textContent = currentValue;
      }
    });
  }

  async updateWish(element, field, value) {
    const row = element.closest('tr');
    const wishHash = row.getAttribute('data-wish-hash');

    try {
      const response = await fetch('/api/updatewish', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          Hash: wishHash,
          [field]: value
        })
      });

      const data = await response.json();

      if (data.success) {
        // Aktualisiere Anzeige
        if (field === 'Price') {
          element.textContent = value ? `€ ${parseFloat(value).toFixed(2)}` : '';
        } else if (field === 'URL') {
          element.innerHTML = value
            ? `<a href="${this.escapeHtml(value)}" target="_blank" rel="noopener">Zum Produkt</a>`
            : '-';
        } else {
          element.textContent = value;
        }
        this.showSuccess('Wunsch aktualisiert');
      } else {
        element.textContent = element.textContent; // Zurücksetzen
        this.showError('Fehler: ' + data.error);
      }
    } catch (error) {
      console.error('Error:', error);
      this.showError('Fehler beim Aktualisieren');
    }
  }

  async updateWishlist(element, field, value) {
    const ownerHash = element.getAttribute('data-owner-hash');

    try {
      const response = await fetch('/api/updatewishlist', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          OwnerHash: ownerHash,
          [field]: value
        })
      });

      const data = await response.json();

      if (data.success) {
        element.textContent = value;
        this.showSuccess('Wunschliste aktualisiert');
      } else {
        element.textContent = element.textContent;
        this.showError('Fehler: ' + data.error);
      }
    } catch (error) {
      console.error('Error:', error);
      this.showError('Fehler beim Aktualisieren');
    }
  }

  showSuccess(message) {
    this.showNotification(message, 'success');
  }

  showError(message) {
    this.showNotification(message, 'error');
  }

  showNotification(message, type = 'info') {
    // Erstelle Notification-Element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    // Füge zum Body hinzu
    document.body.appendChild(notification);

    // Zeige Notification mit Animation
    setTimeout(() => {
      notification.classList.add('show');
    }, 10);

    // Entferne nach 3 Sekunden
    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => {
        notification.remove();
      }, 300);
    }, 3000);
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new WishlistCreator();
  new WishManager();
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
