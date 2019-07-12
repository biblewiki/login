$(function() {

    var login = getUrlVars()["login"];

    if (typeof login !== 'undefined') {
        notification(login, 'login');
        console.log(login);
    }
});

// URL Parameter auslesen
function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
        vars[key] = value;
    });

    return vars;
}

// Notifications anzeigen
function notification(type, code) {

    // JSON einlesen
    $.getJSON("js/notifications_DE.json", function(data) {

        // Notification Parameter auslesen
        var notification = data[type][code];

        if (type === 'error') {
            //Notification anzeigen. Wird nicht von selbst ausgeblendet
            toastr[type](notification.text, notification.title, {
                "timeOut": "0",
                "extendedTimeout": "0"
            });
        } else {
            //Notification anzeigen. Wird nicht von selbst ausgeblendet
            toastr[type](notification.text, notification.title);
        }


    });


}