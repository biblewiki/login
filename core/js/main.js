
/* global grecaptcha */

$(document).ready(function() {

    // Bot auslesen wenn vorhanden
    let bot = $.urlParam('bot');

    if (bot) {

        if (bot === 'emailLink') {
            let jsonTx = {
                action: 'confirmEmail',
                data: {
                    bot: bot,
                    userId: $.urlParam('userId'),
                    emailToken: $.urlParam('emailToken')
                }
            };

            $.ajaxRequest(jsonTx);
        }

        if (bot === 'passwordLink') {
            let jsonTx = {
                action: 'checkResetPasswordToken',
                data: {
                    bot: bot,
                    userId: $.urlParam('userId'),
                    passwordToken: $.urlParam('passwordToken')
                }
            };

            $.ajax({
                type: 'POST',
                url: 'core/php/RequestHandler.php',
                dataType: 'json',
                data: JSON.stringify(jsonTx),
                success: function(data) {
                    if (data.success) {
                        $("#reset-password-request-form").hide();
                        $("#reset-password-form").show();
                        $("#login-form").hide();
                        $("#login-form-telegram").hide();
                        $("#login-form-key").hide();
                        $("#register-form").hide();
                    } else {

                        // Fehler anzeigen
                        if (data.errorMsg) {
                            notification('Fehler', data.errorMsg, 'error'); // Fehler anzeigen
                        }

                        // Warnungen anzeigen
                        if (data.warnMsg) {
                            notification('Fehlgeschlagen', data.warnMsg, 'warning'); // Warnung anzeigen
                        }
                    }

                    // Info anzeigen
                    if (data.infoMsg) {
                        notification('Info', data.infoMsg); // Info anzeigen
                    }
                }
            });
        }

        if (bot === 'google') {
            let jsonTx = {
                action: 'checkGoogleLogin',
                data: {
                    bot: bot,
                    code: $.urlParam('code')
                }
            };

            $.ajaxRequest(jsonTx);
        }

        if (bot === 'telegram') {

            let url = new URL(window.location.href);
            let search_params = new URLSearchParams(url.search);
            let params = {};

            for(var i of search_params) {
                if (i[0] !== 'bot') {
                    params[i[0]] = i[1];
                }
            }

            let jsonTx = {
                action: 'checkTelegramLogin',
                data: {
                    bot: bot,
                    params: params
                }
            };

            $.ajaxRequest(jsonTx);
        }
    }

    // Google klick
    $("#google").click(function() {

        let jsonTx = {
            action: 'getGoogleAuthLink'
        };

        $.ajaxRequest(jsonTx);
    });

    // Login Form übermitteln
    $("#login-form").submit(function() {

        let username = $('#username').val();
        let password = $('#password').val();

        // Überprüfen ob Captcha ausgefüllt wurde
        if (grecaptcha.getResponse()) {

            let jsonTx = {
                action: 'checkPasswordLogin',
                data: {
                    username: username,
                    password: password,
                    referrer: document.referrer,
                    captcha: grecaptcha.getResponse()
                }
            };

            $.ajaxRequest(jsonTx);
        } else {
            notification('Captcha', 'Captcha wurde nicht ausgefüllt.', 'warning'); // Warnung anzeigen
        }
    });

    // Login Form Key übermitteln
    $("#login-form-key").submit(function() {

        let username = $('#username-key').val();

        // Überprüfen ob Benutzername nicht leer ist
        if (username !== '') {

            let jsonTx = {
                action: 'getToken',
                data: {
                    username: username,
                    referrer: document.referrer
                }
            };

            $.ajaxRequest(jsonTx);
        }
    });

    // Passwort Reset Request Form übermitteln
    $("#reset-password-request-form").submit(function() {

        let username = $('#username-password-reset').val();

        // Überprüfen ob Benutzername nicht leer ist
        if (username !== '') {

            let jsonTx = {
                action: 'resetUserPasswordRequest',
                data: {
                    username: username
                }
            };

            $.ajaxRequest(jsonTx);
        }
    });

    // Passwort Reset Form übermitteln
    $("#reset-password-form").submit(function() {

        let password = $('#newpassword-password-reset').val();
        let passwordRepeat = $('#password-repeat-password-reset').val();

        // Überprüfen ob Passwörter übereinstimmen
        if (password === passwordRepeat) {

            let jsonTx = {
                action: 'resetUserPassword',
                data: {
                    userId: $.urlParam('userId'),
                    passwordToken: $.urlParam('passwordToken'),
                    password: password,
                    passwordRepeat: passwordRepeat
                }
            };

            $.ajax({
                type: 'POST',
                url: 'core/php/RequestHandler.php',
                dataType: 'json',
                data: JSON.stringify(jsonTx),
                success: function(data) {
                    if (data.success) {
                        $("#reset-password-request-form").show();
                        $("#reset-password-form").hide();
                        $("#login-form").hide();
                        $("#login-form-telegram").hide();
                        $("#login-form-key").hide();
                        $("#register-form").hide();
                    } else {

                        // Fehler anzeigen
                        if (data.errorMsg) {
                            notification('Fehler', data.errorMsg, 'error'); // Fehler anzeigen
                        }

                        // Warnungen anzeigen
                        if (data.warnMsg) {
                            notification('Fehlgeschlagen', data.warnMsg, 'warning'); // Warnung anzeigen
                        }
                    }

                    // Info anzeigen
                    if (data.infoMsg) {
                        notification('Info', data.infoMsg); // Info anzeigen
                    }
                }
            });
        } else {
            notification('Fehler', 'Passwörter stimmen nicht überein', 'error'); // Fehler anzeigen
        }
    });

    $("#forgot-password").click(function() {
        $("#reset-password-request-form").show();
        $("#reset-password-form").hide();
        $("#login-form").hide();
        $("#login-form-telegram").hide();
        $("#login-form-key").hide();
        $("#register-form").hide();
    });

    // Login klick
    $("#login").click(function() {
        $("#reset-password-request-form").hide();
        $("#reset-password-form").hide();
        $("#register-form").hide();
        $("#login-form-key").hide();
        $("#login-form-telegram").hide();
        $("#login-form").show();
    });

    // Telegram klick
    $("#telegram").click(function() {
        $("#reset-password-request-form").hide();
        $("#reset-password-form").hide();
        $("#register-form").hide();
        $("#login-form-key").hide();
        $("#login-form-telegram").show();
        $("#login-form").hide();

        let jsonTx = {
            action: 'getTelegramButton'
        };

        $.ajax({
            type: 'POST',
            url: 'core/php/RequestHandler.php',
            dataType: 'json',
            data: JSON.stringify(jsonTx),
            success: function(data) {
                $("#login-form-telegram").html(data.button);
            }
        });
    });

    // Login Key klick
    $("#login-key").click(function() {
        $("#reset-password-request-form").hide();
        $("#reset-password-form").hide();
        $("#register-form").hide();
        $("#login-form-key").show();
        $("#login-form-telegram").hide();
        $("#login-form").hide();
    });

    // Registrieren klick
    $("#register").click(function() {
        $("#reset-password-request-form").hide();
        $("#reset-password-form").hide();
        $("#login-form").hide();
        $("#login-form-telegram").hide();
        $("#login-form-key").hide();
        $("#register-form").show();
    });

    // Registrierungs Form übermitteln
    $("#register-form").submit(function() {

        let username = $('#newusername').val();
        let firstName = $('#firstname').val();
        let lastName = $('#lastname').val();
        let email = $('#email').val();
        let password = $('#newpassword').val();
        let passwordRepeat = $('#password-repeat').val();

        // Überprüfen ob Passwörter übereinstimmen
        if (password === passwordRepeat) {

            let jsonTx = {
                action: 'registerPasswordUser',
                data: {
                    username: username,
                    firstName: firstName,
                    lastName: lastName,
                    email: email,
                    password: password,
                    passwordRepeat: passwordRepeat,
                    referrer: document.referrer
                }
            };

            $.ajax({
                type: 'POST',
                url: 'core/php/RequestHandler.php',
                dataType: 'json',
                data: JSON.stringify(jsonTx),
                success: function(data) {
                    if (data.success) {
                        $("#reset-password-request-form").hide();
                        $("#reset-password-form").hide();
                        $("#login-form").show();
                        $("#login-form-telegram").hide();
                        $("#login-form-key").hide();
                        $("#register-form").hide();
                    } else {

                        // Fehler anzeigen
                        if (data.errorMsg) {
                            notification('Fehler', data.errorMsg, 'error'); // Fehler anzeigen
                        }

                        // Warnungen anzeigen
                        if (data.warnMsg) {
                            notification('Fehlgeschlagen', data.warnMsg, 'warning'); // Warnung anzeigen
                        }
                    }

                    // Info anzeigen
                    if (data.infoMsg) {
                        notification('Info', data.infoMsg); // Info anzeigen
                    }
                }
            });
        } else {
            notification('Fehler', 'Passwörter stimmen nicht überein', 'error'); // Fehler anzeigen
        }
    });
});

$.urlParam = function(name){
    let results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    return results ? results[1] : null;
};


$.ajaxRequest = function(jsonTx) {
    $.ajax({
        type: 'POST',
        url: 'core/php/RequestHandler.php',
        dataType: 'json',
        data: JSON.stringify(jsonTx),
        success: function(data) { console.log(data);

            // Wenn User eingeloggt
            if (data.success) {

                // Info anzeigen
                if (data.infoMsg) {
                    notification('Info', data.infoMsg); // Info anzeigen
                }

                // Weiterleiten wenn eine URL übergeben wurde
                if(data.url) {
                    window.location.href = data.url; // Weiterleiten wenn eingeloggt
                }
            } else {
                grecaptcha.reset();

                // Fehler anzeigen
                if (data.errorMsg) {
                    notification('Fehler', data.errorMsg, 'error'); // Fehler anzeigen
                }

                // Warnungen anzeigen
                if (data.warnMsg) {
                    notification('Fehlgeschlagen', data.warnMsg, 'warning'); // Warnung anzeigen
                }

                // Info anzeigen
                if (data.infoMsg) {
                    notification('Info', data.infoMsg); // Info anzeigen
                }
            }
        }
    });
};