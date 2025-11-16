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
                    <p class="wishlist_meta">Wunschliste von <span class="editable-wishlist editable_icon" data-field="Username" data-owner-hash="$Wishlist.OwnerHash">$Wishlist.Username</span></p>
                    <% if $Wishes.exists %>
                        <table class="wishlist_items">
                            <thead>
                                <tr>
                                    <th>Wunsch</th>
                                    <th>Beschreibung</th>
                                    <th>Preis</th>
                                    <th>Link</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody id="wishlist-body">
                                <% loop $Wishes %>
                                    <tr class="wishlist_item" data-wish-hash="$Hash">
                                        <td class="editable" data-field="Title">$Title</td>
                                        <td class="editable" data-field="Description">$Description</td>
                                        <td class="editable" data-field="Price"><% if $Price %>€ $Price<% end_if %></td>
                                        <td class="editable" data-field="URL">
                                            <% if $URL %>
                                                <a href="$URL" target="_blank" rel="noopener">$URL</a>
                                            <% else %>-<% end_if %>
                                        </td>
                                        <td>
                                            <button type="button" class="archive-wish-btn" data-wish-hash="$Hash">Archivieren</button>
                                        </td>
                                    </tr>
                                <% end_loop %>
                                <tr class="addnew_form">
                                    <td>
                                        <input type="text" name="Title" placeholder="Wunsch" required form="Form_AddWishForm" />
                                    </td>
                                    <td>
                                        <input type="text" name="Description" placeholder="Beschreibung" form="Form_AddWishForm" />
                                    </td>
                                    <td>
                                        <input type="text" name="Price" placeholder="Preis (optional)" form="Form_AddWishForm" />
                                    </td>
                                    <td>
                                        <input type="text" name="URL" placeholder="Link (optional)" form="Form_AddWishForm" />
                                    </td>
                                    <td>
                                        <button type="submit" form="Form_AddWishForm">+</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <% with $Top %>
                            <form id="Form_AddWishForm" style="display: none;">
                                <input type="hidden" name="ParentID" value="$Wishlist.ID" />
                                <input type="hidden" name="OwnerHash" value="$Wishlist.OwnerHash" />
                            </form>
                        <% end_with %>
                    <% else %>
                        <p>Diese Wunschliste ist leer.</p>

                        <table class="wishlist_items">
                            <thead>
                                <tr>
                                    <th>Wunsch</th>
                                    <th>Beschreibung</th>
                                    <th>Preis</th>
                                    <th>Link</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody id="wishlist-body">
                                <tr class="addnew_form">
                                    <td>
                                        <input type="text" name="Title" placeholder="Wunsch" required form="Form_AddWishForm" />
                                    </td>
                                    <td>
                                        <input type="text" name="Description" placeholder="Beschreibung" form="Form_AddWishForm" />
                                    </td>
                                    <td>
                                        <input type="text" name="Price" placeholder="Preis (optional)" form="Form_AddWishForm" />
                                    </td>
                                    <td>
                                        <input type="text" name="URL" placeholder="Link (optional)" form="Form_AddWishForm" />
                                    </td>
                                    <td>
                                        <button type="submit" form="Form_AddWishForm">+</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <% with $Top %>
                            <form id="Form_AddWishForm" style="display: none;">
                                <input type="hidden" name="ParentID" value="$Wishlist.ID" />
                                <input type="hidden" name="OwnerHash" value="$Wishlist.OwnerHash" />
                            </form>
                        <% end_with %>
                    <% end_if %>
                </div>
            </section>
        </main>
        <% include Footer %>
    </body>
</html>
