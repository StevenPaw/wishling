<!doctype html>
<html lang="$CurrentLocale">
    <% include Head %>
    <body>
        <% include Header %>
        <main class="area_content main">
            <section class="section--Wishlist">
                <div class="section_content">
                    <h1>$Wishlist.Title</h1>
                    <p>Wunschliste von $Wishlist.Username</p>
                    <div class="section_wishlist_description">
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
                                    <th>Reservierung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <% loop $Wishes %>
                                    <tr class="wishlist_item" data-wish-hash="$Hash">
                                        <td><strong>$Title</strong></td>
                                        <td>$Description</td>
                                        <td><% if $Price %>€ $Price<% end_if %></td>
                                        <td>
                                            <% if $URL %>
                                                <a href="$URL" target="_blank" rel="noopener noreferrer">Zum Produkt</a>
                                            <% end_if %>
                                        </td>
                                        <td class="reservation-status">
                                            <% if $Reservations.exists %>
                                                <span class="reserved" data-reservation-id="$Reservations.First.ID">Reserviert von $Reservations.First.GuestName</span>
                                            <% else %>
                                                <button type="button" class="reserve-wish-btn" data-wish-hash="$Hash">Reservieren</button>
                                            <% end_if %>
                                        </td>
                                    </tr>
                                <% end_loop %>
                            </tbody>
                        </table>
                    <% else %>
                        <p>Diese Wunschliste enthält noch keine Wünsche.</p>
                    <% end_if %>

                    <div class="wishlist_info">
                        <p>Dies ist eine schreibgeschützte Ansicht. Nur der Ersteller kann Wünsche hinzufügen oder bearbeiten.</p>
                    </div>
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
