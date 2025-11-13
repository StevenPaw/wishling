<!doctype html>
<html lang="$CurrentLocale">
    <head>
        <% base_tag %>
        $MetaTags(false)
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta charset="utf-8">
        <title>$Title - $SiteConfig.Title</title>
        $ViteClient.RAW
        <link rel="stylesheet" href="$Vite("app/client/src/scss/main.scss")">

        <meta name="twitter:card" content="summary" />
        <meta name="twitter:site" content="{$Title}" />
        <meta name="twitter:url" content="{$Link}" />
        <meta name="twitter:title" content="{$Title} - {$SiteConfig.Title}" />
        <meta name="twitter:description" content="{$Title}" />
        <meta name="twitter:image" content="app/client/images/SocialImage.png" />

        <meta property="og:type" content="website" />
        <meta property="og:title" content="{$Title} - {$SiteConfig.Title}" />
        <meta property="og:description" content="{$Title}" />
        <meta property="og:site_name" content="{$SiteConfig.Title}" />
        <meta property="og:url" content="{$Link}" />
        <meta property="og:image" content="app/client/images/SocialImage.png" />

        <link rel="manifest" href="site.webmanifest" />

        <link rel="apple-touch-icon" sizes="120x120" href="/_resources/app/client/icons/fav/apple_touch_icon_120.png" />
        <link rel="apple-touch-icon" sizes="180x180" href="/_resources/app/client/icons/fav/apple_touch_icon_180.png" />
        <link rel="mask-icon" href="/_resources/app/client/icons/fav/safari-pinned-tab.svg" color="#4E9DAE" />
        <meta name="msapplication-TileColor" content="#d54f27" />
        <link rel="apple-touch-icon" sizes="180x180" href="/_resources/app/client/icons/fav/safari-pinned-tab.svg" />
        <link rel="icon" type="image/png" sizes="32x32" href="/_resources/app/client/icons/fav/favicon_32.png" />
        <link rel="icon" type="image/png" sizes="16x16" href="/_resources/app/client/icons/fav/favicon_16.png" />
    </head>
    <body style="background-image: url('/_resources/app/client/images/ca_background_wave.png');" class="$Top.SubSite.ColorTheme">

        <% include Header %>
        <main class="area_content main">
            <% if $IsAutoTranslated %>
                <div class="section section--autoTranslate">
                    <div class="section_content">
                        <div class="section_text">
                            <%t Content.TranslationNote "Dieser Inhalt wurde automatisch übersetzt" %>
                        </div>
                    </div>
                </div>
            <% end_if %>
            $Layout
        </main>
        <script type="module" src="$Vite("app/client/src/js/main.js")"></script>
        <% include Footer %>
    </body>
</html>
