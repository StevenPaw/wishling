<!DOCTYPE html>
<html lang="de" data-theme="light">
<head>
    <% base_tag %>
    $MetaTags(false)
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>$MetaTitle</title>
    <meta name="description" content="Erstelle deine eigene Wunschliste und teile sie mit Freunden und Familie">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    $ViteClient.RAW
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>🎁 Wishling</h1>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            $Layout
        </div>
    </main>
    
    <script type="module" src="$Vite("app/client/src/js/main.js")"></script>
</body>
</html>
