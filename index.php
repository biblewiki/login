<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=10, user-scalable=yes">

        <link rel="apple-touch-icon" sizes="180x180" href="core/resources/img/icon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="core/resources/img/icon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="core/resources/img/icon/favicon-16x16.png">
        <link rel="manifest" href="core/resources/img/icon/site.webmanifest">
        <link rel="mask-icon" href="core/resources/img/icon/safari-pinned-tab.svg" color="#4398dd">
        <link rel="shortcut icon" href="core/resources/img/icon/favicon.ico">
        <meta name="apple-mobile-web-app-title" content="BibleWiki">
        <meta name="application-name" content="BibleWiki">
        <meta name="msapplication-TileColor" content="#4398dd">
        <meta name="msapplication-config" content="core/resources/img/icon/browserconfig.xml">
        <meta name="theme-color" content="#ffffff">

        <!-- CSS -->
        <link rel="stylesheet" href="core/resources/css/style.css">
        <link href="core/resources/css/notifications.css" rel="stylesheet" />

        <!-- JS -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="core/js/main.js"></script>
        <script src="core/js/notifications.js"></script>

        <title>Login | BibleWiki</title>

    </head>
    <body>
        <div id="test" class="login-page">
            <div class="form">
                <div class="logo-container">
                    <img class="logo" src="core/resources/img/icon/biblewiki_logo.svg" />
                </div>
                <form class="register-form">
                    <input type="text" placeholder="name"/>
                    <input type="password" placeholder="password"/>
                    <input type="text" placeholder="email address"/>
                    <button>create</button>
                    <p class="message">Already registered? <a href="#">Sign In</a></p>
                </form>
                <form id="login-form" class="login-form" action="javascript:void(0);">
                    <input type="text" placeholder="Benutzername" id="username" required/>
                    <input type="password" placeholder="Passwort" id="password" required/>
                    <button>login</button>
                    <p class="message"><a href="#">Passwort vergessen?</a></p>
                </form>
                <div class="login-methods">
                    <div class="logo-container">
                        <img id="google" class="logo-google" src="core/resources/img/google_logo.png">
                    </div>
                    <div class="logo-container">
                        <img id="telegram" class="logo-telegram" src="core/resources/img/telegram_logo.png">
                    </div>
                    <div class="logo-container">
                        <img class="logo-fingerprint" src="core/resources/img/fingerprint.png">
                    </div>
                </div>
            </div>
        </div>
    </body>
</hmtl>
