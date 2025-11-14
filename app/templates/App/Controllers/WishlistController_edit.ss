<!doctype html>
<html lang="$CurrentLocale">
    <% include Head %>
    <body>
        <% include Header %>
        <main class="area_content main">
            <section class="section--Wishlist">
                <div class="section_content">
                    <h1 class="editable-wishlist" data-field="Title" data-owner-hash="$Wishlist.OwnerHash">$Wishlist.Title</h1>
                <button onclick="document.getElementById('editListDialog').showModal();">bearbeiten</button>
                    <p>Wunschliste von <span class="editable-wishlist" data-field="Username" data-owner-hash="$Wishlist.OwnerHash">$Wishlist.Username</span></p>
                    <div class="section_wishlist_description editable-wishlist" data-field="Description" data-owner-hash="$Wishlist.OwnerHash">
                        $Wishlist.Description
                    </div>
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
                                                <a href="$URL" target="_blank" rel="noopener">Zum Produkt</a>
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

                    <button onclick="document.getElementById('shareDialog').showModal();">Wunschliste teilen</button>
                </div>
            </section>
            <dialog id="shareDialog">
                <form method="dialog">
                    <h2>Wunschliste teilen</h2>
                    <p>Teile den folgenden Link, um anderen Zugriff auf deine Wunschliste zu geben:</p>
                    <input type="text" value="$ViewLink(true)" readonly />
                    <button onclick="navigator.clipboard.writeText('$ViewLink(true)')">Link kopieren</button>
                    <button onclick="document.getElementById('shareDialog').close();" value="close">X</button>
                </form>
            </dialog>
            <dialog id="editListDialog">
                <h2>Wunschliste bearbeiten</h2>
                <button type="button" onclick="document.getElementById('editListDialog').close();" value="close" style="float: right;">X</button>
                $Top.EditWishlistForm
            </dialog>
        </main>
        <% include Footer %>
    </body>
</html>
