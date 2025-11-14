<!doctype html>
<html lang="$CurrentLocale">
    <% include Head %>
    <body>
        <% include Header %>
        <main class="area_content main">
            <% with $Wishlist %>
                <section class="section--Wishlist">
                    <div class="section_content">
                        <h1>$Title</h1>
                    <button onclick="document.getElementById('editListDialog').showModal();">bearbeiten</button>
                        <p>Wunschliste von $Username</p>
                        <% if $Wishes.exists %>
                            <ul class="wishlist_items">
                                <% loop $Wishes %>
                                    <li class="wishlist_item">
                                        <h2>$Title</h2>
                                        <p>$Description</p>
                                        <% if $Link %>
                                            <p><a href="$Link" target="_blank" rel="noopener">Zum Produkt</a></p>
                                        <% end_if %>
                                    </li>
                                <% end_loop %>
                            </ul>
                        <% else %>
                            <p>Diese Wunschliste ist leer.</p>
                            $ViewLink
                        <% end_if %>

                        <button onclick="document.getElementById('shareDialog').showModal();">Wunschliste teilen</button>
                                <button onclick="document.getElementById('addWishDialog').showModal();">Wunsch hinzufügen</button>
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
                <dialog id="addWishDialog">
                    <h2>Wunsch hinzufügen</h2>
                    <button type="button" onclick="document.getElementById('addWishDialog').close();" value="close" style="float: right;">X</button>
                    $Top.AddWishForm
                </dialog>
            <% end_with %>
        </main>
        <% include Footer %>
        <script src="/_resources/app/assets/js/wishlist.js"></script>
    </body>
</html>
