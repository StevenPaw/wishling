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
    this.reservationCookieName = 'wishling_reservations';
    this.guestNameCookieName = 'wishling_guest_name';
    this.init();
  }

  init() {
    this.bindEvents();
    this.updateReservationButtons();
    this.prefillGuestName();
    this.currentSort = { field: null, direction: 'asc' };
    this.applyDefaultSort();
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

    // Handle Reserve Wish Buttons
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('reserve-wish-btn')) {
        this.openReserveDialog(e.target);
      }
    });

    // Handle Unreserve Buttons
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('unreserve-wish-btn')) {
        this.handleUnreserveWish(e.target);
      }
    });

    // Handle Reserve Form Submit
    const reserveForm = document.getElementById('reserveWishForm');
    if (reserveForm) {
      reserveForm.addEventListener('submit', (e) => this.handleReserveWish(e));
    }

    // Handle Sortable Headers
    document.querySelectorAll('.items_cell--sortable').forEach(header => {
      header.addEventListener('click', (e) => {
        const field = e.target.getAttribute('data-sort-field');
        if (field) {
          this.sortWishes(field);
        }
      });
    });

    // Handle Default Sort Select
    const defaultSortSelect = document.getElementById('defaultSortSelect');
    if (defaultSortSelect) {
      defaultSortSelect.addEventListener('change', (e) => this.handleDefaultSortChange(e));
    }

    // Handle Guest Sort Select
    const guestSortSelect = document.getElementById('guestSortSelect');
    if (guestSortSelect) {
      guestSortSelect.addEventListener('change', (e) => this.handleGuestSortChange(e));
    }

    // Handle Search Input
    const searchInput = document.getElementById('wishSearchInput');
    if (searchInput) {
      searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
    }
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
    const wishlistItems = document.querySelector('.wishlist_items');
    if (!wishlistItems) return;

    // Entferne "Noch keine Wünsche" Nachricht falls vorhanden
    const emptyMessage = wishlistItems.querySelector('.items_empty');
    if (emptyMessage) {
      emptyMessage.remove();
    }

    // Formatiere Preis
    const priceDisplay = wish.Price ? `€ ${parseFloat(wish.Price).toFixed(2)}` : '';

    // Erstelle URL-Anzeige mit Icon
    let urlDisplay = '<span class="editable url-text" data-field="URL">-</span>';
    if (wish.URL && wish.URL.trim() !== '') {
      urlDisplay = `
        <span class="editable url-text" data-field="URL">${this.escapeHtml(wish.URL)}</span>
        <a href="${this.escapeHtml(wish.URL)}" target="_blank" rel="noopener" class="button url-icon button--icon-open" title="Link öffnen"></a>
      `;
    }

    // Erstelle neue Zeile
    const row = document.createElement('div');
    row.className = 'items_row';
    row.setAttribute('data-wish-hash', wish.Hash);

    row.innerHTML = `
      <div class="items_cell editable" data-field="Title">${this.escapeHtml(wish.Title)}</div>
      <div class="items_cell editable" data-field="Description">${this.escapeHtml(wish.Description || '')}</div>
      <div class="items_cell editable" data-field="Price">${priceDisplay}</div>
      <div class="items_cell items_cell--limited items_cell--url" data-field="URL">
        ${urlDisplay}
      </div>
      <div class="items_cell items_cell--actions">
        <button type="button" class="archive-wish-btn button--icon-archive" data-wish-hash="${wish.Hash}"></button>
      </div>
    `;

    // Füge vor dem Formular ein
    const formRow = wishlistItems.querySelector('.addnew_form');
    if (formRow) {
      wishlistItems.insertBefore(row, formRow);
    } else {
      wishlistItems.appendChild(row);
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

        // Entferne Zeile aus der Tabelle/Liste
        const row = button.closest('tr, .items_row');
        if (row) {
          row.style.opacity = '0';
          row.style.transition = 'opacity 0.3s';
          setTimeout(() => {
            row.remove();

            // Prüfe ob Liste jetzt leer ist
            const wishlistItems = document.querySelector('.wishlist_items');
            if (wishlistItems) {
              const remainingWishes = wishlistItems.querySelectorAll('.items_row:not(.items_row--header):not(.addnew_form)');
              if (remainingWishes.length === 0) {
                // Füge Empty-State hinzu
                const emptyMessage = document.createElement('div');
                emptyMessage.className = 'items_empty';
                emptyMessage.innerHTML = '<p>Füge jetzt deinen ersten Wunsch hinzu!</p>';

                const formRow = wishlistItems.querySelector('.addnew_form');
                if (formRow) {
                  wishlistItems.insertBefore(emptyMessage, formRow);
                } else {
                  wishlistItems.appendChild(emptyMessage);
                }
              }
            }
          }, 300);
        }
      }
    } catch (error) {
      console.error('Error:', error);
      this.showError('Fehler beim Archivieren');
      button.disabled = false;
      button.textContent = 'Archivieren';
    }
  }

  sortWishes(field, skipToggle = false) {
    const wishlistItems = document.querySelector('.wishlist_items');
    if (!wishlistItems) return;

    // Toggle Sortierrichtung wenn gleiches Feld (außer bei Default-Sort)
    if (!skipToggle) {
      if (this.currentSort.field === field) {
        this.currentSort.direction = this.currentSort.direction === 'asc' ? 'desc' : 'asc';
      } else {
        this.currentSort.field = field;
        this.currentSort.direction = 'asc';
      }
    }

    // Hole alle Wish-Zeilen (nicht Header, nicht Form, nicht Empty)
    const rows = Array.from(wishlistItems.querySelectorAll('.items_row:not(.items_row--header):not(.addnew_form):not(.items_empty)'));

    if (rows.length === 0) return;

    // Sortiere Zeilen
    rows.sort((a, b) => {
      let aValue, bValue;

      if (field === 'Title') {
        aValue = a.querySelector('[data-field="Title"]')?.textContent.trim().toLowerCase() || '';
        bValue = b.querySelector('[data-field="Title"]')?.textContent.trim().toLowerCase() || '';
        return this.currentSort.direction === 'asc'
          ? aValue.localeCompare(bValue)
          : bValue.localeCompare(aValue);
      } else if (field === 'Price') {
        const aPriceText = a.querySelector('[data-field="Price"]')?.textContent.trim() || '';
        const bPriceText = b.querySelector('[data-field="Price"]')?.textContent.trim() || '';

        aValue = aPriceText ? parseFloat(aPriceText.replace('€', '').trim()) : 0;
        bValue = bPriceText ? parseFloat(bPriceText.replace('€', '').trim()) : 0;

        return this.currentSort.direction === 'asc'
          ? aValue - bValue
          : bValue - aValue;
      }

      return 0;
    });

    // Füge sortierte Zeilen wieder ein mit Animation
    const formRow = wishlistItems.querySelector('.addnew_form');

    // Fade out
    rows.forEach(row => {
      row.style.opacity = '0';
      row.style.transition = 'opacity 0.2s ease-out';
    });

    // Nach Fade-out neu sortieren und Fade-in
    setTimeout(() => {
      rows.forEach(row => {
        if (formRow) {
          wishlistItems.insertBefore(row, formRow);
        } else {
          wishlistItems.appendChild(row);
        }
      });

      // Fade in mit leichter Verzögerung für jeden
      rows.forEach((row, index) => {
        setTimeout(() => {
          row.style.opacity = '1';
          row.style.transition = 'opacity 0.3s ease-in';
        }, index * 30); // Gestaffelte Animation
      });
    }, 200);

    // Update Header-Anzeige
    this.updateSortIndicators(field);
  }

  updateSortIndicators(activeField) {
    document.querySelectorAll('.items_cell--sortable').forEach(header => {
      const field = header.getAttribute('data-sort-field');
      header.classList.remove('sorted-asc', 'sorted-desc');

      if (field === activeField) {
        header.classList.add(`sorted-${this.currentSort.direction}`);
      }
    });
  }

  applyDefaultSort() {
    // Edit-View: Dropdown-basiert
    const defaultSortSelect = document.getElementById('defaultSortSelect');
    if (defaultSortSelect && defaultSortSelect.value) {
      const [field, direction] = defaultSortSelect.value.split('-');
      if (field && direction) {
        this.currentSort.field = field;
        this.currentSort.direction = direction;
        this.sortWishes(field, true);
      }
      return;
    }

    // Guest-View: data-attribute-basiert
    const sectionContent = document.querySelector('.section_content[data-default-sort]');
    if (sectionContent) {
      const defaultSort = sectionContent.getAttribute('data-default-sort');
      if (defaultSort) {
        const [field, direction] = defaultSort.split('-');
        if (field && direction) {
          this.currentSort.field = field;
          this.currentSort.direction = direction;
          this.sortWishesGuest(field);
        }
      }
    }
  }

  async handleDefaultSortChange(event) {
    const value = event.target.value;
    const ownerHash = event.target.getAttribute('data-owner-hash');

    if (!ownerHash) return;

    try {
      const response = await fetch('/api/updatewishlist', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          OwnerHash: ownerHash,
          DefaultSort: value
        })
      });

      const data = await response.json();

      if (data.success) {
        this.showSuccess('Standardsortierung gespeichert');

        // Wende neue Sortierung an
        if (value) {
          const [field, direction] = value.split('-');
          if (field && direction) {
            this.currentSort.field = field;
            this.currentSort.direction = direction;
            this.sortWishes(field, true);
          }
        }
      } else {
        this.showError('Fehler: ' + data.error);
      }
    } catch (error) {
      console.error('Error:', error);
      this.showError('Fehler beim Speichern');
    }
  }

  sortWishesGuest(field) {
    const wishlistItems = document.querySelector('.wishlist_items');
    if (!wishlistItems) return;

    const rows = Array.from(wishlistItems.querySelectorAll('.items_row:not(.items_row--header)'));
    if (rows.length === 0) return;

    // Sortiere Zeilen
    rows.sort((a, b) => {
      let aValue, bValue;

      if (field === 'Title') {
        const aTitleCell = a.querySelector('[data-field="Title"]');
        const bTitleCell = b.querySelector('[data-field="Title"]');
        aValue = aTitleCell?.textContent.trim().toLowerCase() || '';
        bValue = bTitleCell?.textContent.trim().toLowerCase() || '';
        return this.currentSort.direction === 'asc'
          ? aValue.localeCompare(bValue)
          : bValue.localeCompare(aValue);
      } else if (field === 'Price') {
        const aPriceCell = a.querySelector('[data-field="Price"]');
        const bPriceCell = b.querySelector('[data-field="Price"]');
        const aPriceText = aPriceCell?.textContent.trim() || '';
        const bPriceText = bPriceCell?.textContent.trim() || '';

        aValue = aPriceText && aPriceText !== '-' ? parseFloat(aPriceText.replace('€', '').trim()) : 0;
        bValue = bPriceText && bPriceText !== '-' ? parseFloat(bPriceText.replace('€', '').trim()) : 0;

        return this.currentSort.direction === 'asc'
          ? aValue - bValue
          : bValue - aValue;
      }

      return 0;
    });

    // Fade out
    rows.forEach(row => {
      row.style.opacity = '0';
      row.style.transition = 'opacity 0.2s ease-out';
    });

    // Nach Fade-out neu sortieren und Fade-in
    setTimeout(() => {
      rows.forEach(row => {
        wishlistItems.appendChild(row);
      });

      // Fade in
      rows.forEach((row, index) => {
        setTimeout(() => {
          row.style.opacity = '1';
          row.style.transition = 'opacity 0.3s ease-in';
        }, index * 30);
      });
    }, 200);
  }

  handleGuestSortChange(event) {
    const value = event.target.value;

    if (!value) {
      // Standard-Sortierung anwenden
      this.applyDefaultSort();
      return;
    }

    const [field, direction] = value.split('-');
    if (field && direction) {
      this.currentSort.field = field;
      this.currentSort.direction = direction;
      this.sortWishesGuest(field);
    }
  }

  handleSearch(searchTerm) {
    const term = searchTerm.toLowerCase().trim();

    // Suche in beiden View-Typen
    const editRows = document.querySelectorAll('.wishlist_items .items_row:not(.items_row--header):not(.addnew_form)');
    const guestRows = document.querySelectorAll('.wishlist_items .items_row:not(.items_row--header)');

    const rows = editRows.length > 0 ? editRows : guestRows;

    rows.forEach(row => {
      if (!term) {
        // Zeige alle wenn Suche leer
        row.style.display = '';
        return;
      }

      // Hole Title und Description aus data-Attributen oder Text
      let title = row.getAttribute('data-wish-title') || '';
      let description = row.getAttribute('data-wish-description') || '';

      // Fallback: Lese aus DOM wenn data-Attribute nicht vorhanden
      if (!title) {
        const titleCell = row.querySelector('[data-field="Title"]');
        title = titleCell ? titleCell.textContent.trim() : '';
      }
      if (!description) {
        const descCell = row.querySelector('[data-field="Description"]');
        description = descCell ? descCell.textContent.trim() : '';
      }

      // Suche in Title und Description
      const matchesTitle = title.toLowerCase().includes(term);
      const matchesDescription = description.toLowerCase().includes(term);

      row.style.display = (matchesTitle || matchesDescription) ? '' : 'none';
    });
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
    let currentValue = element.textContent.trim().replace(/^€\s*/, ''); // Entferne € Zeichen

    // Für URL-Felder: Wenn es ein span.url-text ist, nimm dessen Wert
    if (field === 'URL' && element.classList.contains('url-text')) {
      currentValue = element.textContent.trim();
      if (currentValue === '-') currentValue = '';
    }

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
          // Finde die Parent-Zelle und aktualisiere komplett
          const cell = element.closest('.items_cell--url');
          if (cell) {
            if (value) {
              cell.innerHTML = `
                <span class="editable url-text" data-field="URL">${this.escapeHtml(value)}</span>
                <a href="${this.escapeHtml(value)}" target="_blank" rel="noopener" class="button url-icon button--icon-open" title="Link öffnen"></a>
              `;
            } else {
              cell.innerHTML = `<span class="editable url-text" data-field="URL">-</span>`;
            }
          } else {
            // Fallback für alte Struktur
            element.innerHTML = value
              ? `<a href="${this.escapeHtml(value)}" target="_blank" rel="noopener">Zum Produkt</a>`
              : '-';
          }
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

  openReserveDialog(button) {
    const wishHash = button.getAttribute('data-wish-hash');
    const row = button.closest('tr');
    const wishTitle = row.querySelector('td:first-child strong').textContent;

    const dialog = document.getElementById('reserveDialog');
    const hashInput = document.getElementById('reserveWishHash');
    const titleElement = document.getElementById('reserveWishTitle');
    const form = document.getElementById('reserveWishForm');

    if (dialog && hashInput && titleElement && form) {
      hashInput.value = wishHash;
      titleElement.textContent = `"${wishTitle}" reservieren`;
      form.reset();

      // Befülle Name-Feld mit gespeichertem Namen
      const savedName = this.getGuestName();
      const guestNameInput = document.getElementById('guestName');
      if (guestNameInput && savedName) {
        guestNameInput.value = savedName;
      }

      dialog.showModal();
    }
  }

  async handleReserveWish(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const submitButton = event.target.querySelector('button[type="submit"]');
    const wishHash = formData.get('wishHash');
    const guestName = formData.get('guestName');
    const message = formData.get('message');

    if (!guestName || !wishHash) {
      this.showError('Name ist erforderlich');
      return;
    }

    // Deaktiviere Button
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Reserviere...';
    }

    try {
      const response = await fetch('/api/reservewish', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          Hash: wishHash,
          GuestName: guestName,
          Message: message || ''
        })
      });

      const data = await response.json();

      if (data.error) {
        this.showError('Fehler: ' + data.error);
      } else if (data.success) {
        this.showSuccess('Wunsch erfolgreich reserviert!');

        // Speichere Gastname im Cookie
        this.saveGuestName(guestName);

        // Speichere Reservierung in Cookie
        this.saveReservation(wishHash, data.reservation.id);

        // Aktualisiere UI
        const row = document.querySelector(`tr[data-wish-hash="${wishHash}"]`);
        if (row) {
          const reservationCell = row.querySelector('.reservation-status');
          if (reservationCell) {
            const reservedSpan = document.createElement('span');
            reservedSpan.className = 'reserved';
            reservedSpan.setAttribute('data-reservation-id', data.reservation.id);
            reservedSpan.textContent = `Reserviert von ${this.escapeHtml(guestName)}`;

            // Füge Aufheben-Button hinzu
            const unreserveBtn = document.createElement('button');
            unreserveBtn.type = 'button';
            unreserveBtn.className = 'unreserve-wish-btn';
            unreserveBtn.setAttribute('data-wish-hash', wishHash);
            unreserveBtn.setAttribute('data-reservation-id', data.reservation.id);
            unreserveBtn.textContent = 'Aufheben';
            unreserveBtn.style.marginLeft = '10px';

            reservedSpan.appendChild(unreserveBtn);
            reservationCell.innerHTML = '';
            reservationCell.appendChild(reservedSpan);
          }
        }

        // Schließe Dialog
        const dialog = document.getElementById('reserveDialog');
        if (dialog) {
          dialog.close();
        }
      }
    } catch (error) {
      console.error('Error:', error);
      this.showError('Fehler beim Reservieren');
    } finally {
      // Reaktiviere Button
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = 'Reservieren';
      }
    }
  }

  // Cookie-Hilfsfunktionen
  getReservations() {
    const cookie = document.cookie
      .split('; ')
      .find(row => row.startsWith(this.reservationCookieName + '='));

    if (!cookie) return {};

    try {
      return JSON.parse(decodeURIComponent(cookie.split('=')[1]));
    } catch (e) {
      return {};
    }
  }

  saveReservation(wishHash, reservationId) {
    const reservations = this.getReservations();
    reservations[wishHash] = reservationId;

    // Cookie für 1 Jahr speichern
    const expires = new Date();
    expires.setFullYear(expires.getFullYear() + 1);

    document.cookie = `${this.reservationCookieName}=${encodeURIComponent(JSON.stringify(reservations))}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
  }

  removeReservation(wishHash) {
    const reservations = this.getReservations();
    delete reservations[wishHash];

    const expires = new Date();
    expires.setFullYear(expires.getFullYear() + 1);

    document.cookie = `${this.reservationCookieName}=${encodeURIComponent(JSON.stringify(reservations))}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
  }

  isMyReservation(wishHash, reservationId) {
    const reservations = this.getReservations();
    return reservations[wishHash] == reservationId;
  }

  getGuestName() {
    const cookie = document.cookie
      .split('; ')
      .find(row => row.startsWith(this.guestNameCookieName + '='));

    if (!cookie) return '';

    try {
      return decodeURIComponent(cookie.split('=')[1]);
    } catch (e) {
      return '';
    }
  }

  saveGuestName(name) {
    const expires = new Date();
    expires.setFullYear(expires.getFullYear() + 1);

    document.cookie = `${this.guestNameCookieName}=${encodeURIComponent(name)}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
  }

  prefillGuestName() {
    const guestNameInput = document.getElementById('guestName');
    if (guestNameInput) {
      const savedName = this.getGuestName();
      if (savedName) {
        guestNameInput.value = savedName;
      }
    }
  }

  updateReservationButtons() {
    const reservations = this.getReservations();

    document.querySelectorAll('.reservation-status .reserved').forEach(span => {
      const reservationId = span.getAttribute('data-reservation-id');
      const row = span.closest('tr');
      const wishHash = row ? row.getAttribute('data-wish-hash') : null;

      if (wishHash && reservationId && this.isMyReservation(wishHash, reservationId)) {
        // Zeige "Aufheben"-Button für eigene Reservierungen
        const unreserveBtn = document.createElement('button');
        unreserveBtn.type = 'button';
        unreserveBtn.className = 'unreserve-wish-btn';
        unreserveBtn.setAttribute('data-wish-hash', wishHash);
        unreserveBtn.setAttribute('data-reservation-id', reservationId);
        unreserveBtn.textContent = 'Aufheben';
        unreserveBtn.style.marginLeft = '10px';

        span.appendChild(unreserveBtn);
      }
    });
  }

  async handleUnreserveWish(button) {
    const wishHash = button.getAttribute('data-wish-hash');
    const reservationId = button.getAttribute('data-reservation-id');

    if (!confirm('Möchtest du deine Reservierung wirklich aufheben?')) {
      return;
    }

    button.disabled = true;
    button.textContent = 'Wird aufgehoben...';

    try {
      const response = await fetch('/api/unreservewish', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          Hash: wishHash,
          ReservationID: parseInt(reservationId)
        })
      });

      const data = await response.json();

      if (data.error) {
        this.showError('Fehler: ' + data.error);
        button.disabled = false;
        button.textContent = 'Aufheben';
      } else if (data.success) {
        this.showSuccess('Reservierung wurde aufgehoben');

        // Entferne aus Cookie
        this.removeReservation(wishHash);

        // Aktualisiere UI
        const row = button.closest('tr');
        if (row) {
          const reservationCell = row.querySelector('.reservation-status');
          if (reservationCell) {
            reservationCell.innerHTML = `<button type="button" class="reserve-wish-btn" data-wish-hash="${wishHash}">Reservieren</button>`;
          }
        }
      }
    } catch (error) {
      console.error('Error:', error);
      this.showError('Fehler beim Aufheben der Reservierung');
      button.disabled = false;
      button.textContent = 'Aufheben';
    }
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
