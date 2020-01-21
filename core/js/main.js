
$(document).ready(function() {

    // Bot auslesen wenn vorhanden
    let bot = $.urlParam('bot');

    if (bot) {

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
    }

    // Login Button klick
    $("#login-form").submit(function() {

        let username = $('#username').val();
        let password = $('#password').val();

        // Überprüfen ob Benutzername nicht leer ist
        if (username !== '') {

            let jsonTx = {
                action: 'checkPasswordLogin',
                data: {
                    username: username,
                    password: password,
                    referrer: document.referrer
                }
            };

            $.ajaxRequest(jsonTx);
        }
    });

    // Google klick
    $("#google").click(function() {

        let jsonTx = {
            action: 'getGoogleAuthLink'
        };

        $.ajaxRequest(jsonTx);
    });
});

$.urlParam = function(name){
    let results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    console.log(results);
    return results ? results[1] : null;
};


$.ajaxRequest = function(jsonTx) {
    $.ajax({
        type: 'POST',
        url: 'core/php/RequestHandler.php',
        dataType: 'json',
        data: JSON.stringify(jsonTx),
        success: function(data) { console.log(data);
            // Fehler anzeigen
            if (data.errorMsg) {
                data.errorMsg.forEach(function(errMsg) {
                    notification('Fehler', errMsg, 'error'); // Fehler anzeigen
                });
            }

            // Warnungen anzeigen
            if (data.warnMsg) {
                data.warnMsg.forEach(function(errMsg) {
                    notification('Fehlgeschlagen', errMsg, 'warning'); // Fehler anzeigen
                });
            }

            // Wenn User eingeloggt
            if (data.success) {
               window.location.replace(data.url); // Weiterleiten wenn eingeloggt
            }
        }
    });
};