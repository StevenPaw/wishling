<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Dein Login-Link für Wishling</title>
</head>
<body style="margin:0;padding:0;background:#0C0A0D;font-family:Arial,Helvetica,sans-serif;color:#FFFFFF;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0C0A0D;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#1E1623;border-radius:12px;padding:32px;">
                    <tr>
                        <td>
                            <h1 style="margin:0 0 16px;font-size:22px;color:#FFFFFF;">
                                <% if $IsNewUser %>Willkommen bei Wishling!<% else %>Hallo wieder!<% end_if %>
                            </h1>
                            <p style="margin:0 0 24px;font-size:16px;line-height:1.5;color:#C7C2CB;">
                                Klicke auf den Button, um dich anzumelden. Der Link ist $ValidMinutes Minuten gültig und kann nur einmal verwendet werden.
                            </p>
                            <p style="margin:0 0 24px;">
                                <a href="$LoginLink" style="display:inline-block;background:#C000C9;color:#FFFFFF;text-decoration:none;font-weight:bold;padding:12px 24px;border-radius:10px;">Jetzt anmelden</a>
                            </p>
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.5;color:#A9A9A9;">
                                Falls der Button nicht funktioniert, kopiere diesen Link in deinen Browser:
                            </p>
                            <p style="margin:0 0 24px;font-size:13px;line-height:1.5;word-break:break-all;color:#A9A9A9;">$LoginLink</p>
                            <p style="margin:0;font-size:13px;line-height:1.5;color:#A9A9A9;">
                                Du hast keinen Login angefordert? Dann kannst du diese E-Mail einfach ignorieren.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
