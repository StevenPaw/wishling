<!doctype html>
<html lang="$CurrentLocale">
    <% include Head %>
    <body>
        <header class="area_header" id="PageTop">
            <div class="section_content">
                <a href="/" class="logo">
                    <img src="/_resources/app/client/icons/wishling_logo.svg" alt="Wishling Logo" />
                </a>
                <div class="section_actions">
                    <button class="button--icon-link" onclick="navigator.clipboard.writeText('$ViewLink(true)')">Link kopieren</button>
                    <!-- Teilen Button -->
                     <button class="button--icon-share" onclick="navigator.share({ title: 'Schau auf meine Wunschliste', url: '$ViewLink(true)' })">Liste teilen</button>
                </div>
            </div>
        </header>
        <main class="area_content main">
            <section class="section--Wishlist">
                <div class="section_content">
                    <h1 class="wishlist_title editable-wishlist editable_icon" data-field="Title" data-owner-hash="$Wishlist.OwnerHash">$Wishlist.Title</h1>
                    <div class="wishlist_description editable-wishlist editable_icon" data-field="Description" data-owner-hash="$Wishlist.OwnerHash">
                        $Wishlist.Description
                    </div>
                    <p class="wishlist_meta">
                        Wunschliste von <span class="editable-wishlist editable_icon" data-field="Username" data-owner-hash="$Wishlist.OwnerHash">$Wishlist.Username</span>
                        <span class="wishlist_sort_label">Standardsortierung:</span>
                        <select id="defaultSortSelect" class="wishlist_sort_select" data-owner-hash="$Wishlist.OwnerHash">
                            <option value="" <% if not $Wishlist.DefaultSort %>selected<% end_if %>>Keine</option>
                            <option value="Title-asc" <% if $Wishlist.DefaultSort == 'Title-asc' %>selected<% end_if %>>Wunsch (A-Z)</option>
                            <option value="Title-desc" <% if $Wishlist.DefaultSort == 'Title-desc' %>selected<% end_if %>>Wunsch (Z-A)</option>
                            <option value="Price-asc" <% if $Wishlist.DefaultSort == 'Price-asc' %>selected<% end_if %>>Preis (aufsteigend)</option>
                            <option value="Price-desc" <% if $Wishlist.DefaultSort == 'Price-desc' %>selected<% end_if %>>Preis (absteigend)</option>
                        </select>
                    </p>

                    <div class="wishlist_controls">
                        <input type="text" id="wishSearchInput" class="wish_search" placeholder="Suche nach Titel oder Beschreibung..." />
                    </div>

                        <div class="wishlist_items">
                            <div class="items_row items_row--header">
                                <div class="items_cell items_cell--header items_cell--sortable" data-sort-field="Title">Titel</div>
                                <div class="items_cell items_cell--header">Beschreibung</div>
                                <div class="items_cell items_cell--header items_cell--sortable" data-sort-field="Price">Preis</div>
                                <div class="items_cell items_cell--header">Link</div>
                                <div class="items_cell items_cell--header items_cell--actions">Archiv</div>
                            </div>
                            <% if $Wishes.exists %>
                                <% loop $Wishes %>
                                    <div class="items_row" data-wish-hash="$Hash">
                                        <div class="items_cell editable" data-field="Title">$Title</div>
                                        <div class="items_cell editable" data-field="Description">$Description</div>
                                        <div class="items_cell editable" data-field="Price"><% if $Price %>€ $Price<% end_if %></div>
                                        <div class="items_cell items_cell--limited items_cell--url" data-field="URL">
                                            <span class="editable url-text" data-field="URL">
                                                <% if $URL %>$URL<% else %>-<% end_if %>
                                            </span>
                                            <% if $URL %>
                                                <a href="$URL" target="_blank" rel="noopener" class="button url-icon button--icon-open" title="Link öffnen"></a>
                                            <% end_if %>
                                        </div>
                                        <div class="items_cell items_cell--actions">
                                            <button type="button" class="archive-wish-btn button--icon-archive" data-wish-hash="$Hash"></button>
                                        </div>
                                    </div>
                                <% end_loop %>
                            <% else %>
                                <div class="items_empty">
                                    <p>Füge jetzt deinen ersten Wunsch hinzu!</p>
                                </div>
                            <% end_if %>
                            <div class="items_row addnew_form">
                                <div class="items_cell">
                                    <input type="text" name="Title" placeholder="Wunsch" required form="Form_AddWishForm" />
                                </div>
                                <div class="items_cell">
                                    <input type="text" name="Description" placeholder="Beschreibung" form="Form_AddWishForm" />
                                </div>
                                <div class="items_cell">
                                    <input type="text" name="Price" placeholder="Preis (optional)" form="Form_AddWishForm" />
                                </div>
                                <div class="items_cell">
                                    <input type="text" name="URL" placeholder="Link (optional)" form="Form_AddWishForm" />
                                </div>
                                <div class="items_cell items_cell--actions">
                                    <button class="button--icon-add" type="submit" form="Form_AddWishForm"></button>
                                </div>
                            </div>
                        </div>

                        <% with $Top %>
                            <form id="Form_AddWishForm" style="display: none;">
                                <input type="hidden" name="ParentID" value="$Wishlist.ID" />
                                <input type="hidden" name="OwnerHash" value="$Wishlist.OwnerHash" />
                            </form>
                        <% end_with %>
                </div>
            </section>
        </main>
        <% include Footer %>
    </body>
</html>
