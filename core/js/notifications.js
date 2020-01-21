/* global toastr */

// Notifications anzeigen
function notification(title, text, type = 'info') {

    if (type === 'error') {
        // Notification anzeigen. Wird nicht von selbst ausgeblendet
        toastr[type](text, title, {
            "timeOut": "0",
            "extendedTimeout": "0"
        });
    } else {
        // Notification anzeigen. Wird von selbst ausgeblendet
        toastr[type](text, title);
    }
}
