<!doctype html>
<html lang="$CurrentLocale">
    <% include Head %>
    <body>
        <% include Header %>
        <main class="area_content main">
            <section class="section--Wishlist">
                <div class="section_content" data-default-sort="$Wishlist.DefaultSort">
                    <h1 class="wishlist_title">$Wishlist.Title</h1>
                    <div class="wishlist_description">
                        $Wishlist.Description
                    </div>
                    <p class="wishlist_meta">
                        Wunschliste von $Wishlist.Username
                        <span class="wishlist_sort_label">Sortierung:</span>
                        <select id="guestSortSelect" class="wishlist_sort_select">
                            <option value="" <% if not $Wishlist.DefaultSort %>selected<% end_if %>>Standard</option>
                            <option value="Title-asc" <% if $Wishlist.DefaultSort == 'Title-asc' %>selected<% end_if %>>Titel (A-Z)</option>
                            <option value="Title-desc" <% if $Wishlist.DefaultSort == 'Title-desc' %>selected<% end_if %>>Titel (Z-A)</option>
                            <option value="Price-asc" <% if $Wishlist.DefaultSort == 'Price-asc' %>selected<% end_if %>>Preis (aufsteigend)</option>
                            <option value="Price-desc" <% if $Wishlist.DefaultSort == 'Price-desc' %>selected<% end_if %>>Preis (absteigend)</option>
                        </select>
                    </p>

                    <div class="wishlist_controls">
                        <input type="text" id="wishSearchInput" class="wish_search" placeholder="Suche nach Titel oder Beschreibung..." />
                    </div>

                    <% if $Wishes.exists %>
                        <div class="wishlist_items">
                            <div class="items_row items_row--header">
                                <div class="items_cell items_cell--header">Titel</div>
                                <div class="items_cell items_cell--header">Beschreibung</div>
                                <div class="items_cell items_cell--header">Preis</div>
                                <div class="items_cell items_cell--header">Link</div>
                                <div class="items_cell items_cell--header items_cell--actions">Reservierung</div>
                            </div>
                            <% loop $Wishes %>
                                <div class="items_row" data-wish-hash="$Hash" data-wish-title="$Title" data-wish-description="$Description">
                                    <div class="items_cell"><strong>$Title</strong></div>
                                    <div class="items_cell">$Description</div>
                                    <div class="items_cell"><% if $Price %>€ $Price<% end_if %></div>
                                    <div class="items_cell items_cell--limited">
                                        <% if $URL %>
                                            <a href="$URL" target="_blank" rel="noopener noreferrer">Zum Produkt</a>
                                        <% end_if %>
                                    </div>
                                    <div class="items_cell items_cell--actions reservation-status">
                                        <% if $Reservations.exists %>
                                            <span class="reserved" data-reservation-id="$Reservations.First.ID">Reserviert von $Reservations.First.GuestName</span>
                                        <% else %>
                                            <button type="button" class="button--icon-reserve reserve-wish-btn" data-wish-hash="$Hash"></button>
                                        <% end_if %>
                                    </div>
                                </div>
                            <% end_loop %>
                        </div>
                    <% else %>
                        <p>Diese Wunschliste enthält noch keine Wünsche.</p>
                    <% end_if %>
                </div>
            </section>

            <dialog id="reserveDialog">
                <h2>Wunsch reservieren</h2>
                <button type="button" class="close-dialog" onclick="document.getElementById('reserveDialog').close();" style="float: right;">×</button>
                <form id="reserveWishForm">
                    <input type="hidden" id="reserveWishHash" name="wishHash" />
                    <p id="reserveWishTitle" style="font-weight: bold; margin-bottom: 1em;"></p>

                    <label for="guestName">Dein Name *</label>
                    <input type="text" id="guestName" name="guestName" required placeholder="Dein Name" />

                    <label for="message">Nachricht (optional)</label>
                    <textarea id="message" name="message" placeholder="Optionale Nachricht an den Ersteller" rows="3"></textarea>

                    <div style="margin-top: 1em;">
                        <button type="submit">Reservieren</button>
                        <button type="button" onclick="document.getElementById('reserveDialog').close();">Abbrechen</button>
                    </div>
                </form>
            </dialog>
        </main>
        <% include Footer %>
    </body>
</html>
