<?php
// Debug mode
$st_debug_mode = true;

// Config
$biwi_config = [


    // ------------------------------------------
    // DB Einstellungen
    // ------------------------------------------

    'database'  => [
        'dsn' => 'mysql:host=mwepf1gm.mysql.db.hostpoint.ch;dbname=mwepf1gm_biblewikicontent',
        'host' => 'mwepf1gm.mysql.db.hostpoint.ch',
        'db' => 'mwepf1gm_biblewikicontent',
        'user' => json_decode(file_get_contents('../../../config/config.json'))->user,
        'password' => json_decode(file_get_contents('../../../config/config.json'))->password
    ],


    // ------------------------------------------
    // Bots
    // ------------------------------------------
    'bot'  => [
        'googleClientId' => json_decode(file_get_contents('../../../config/config.json'))->googleClientId,
        'googleClientSecret' => json_decode(file_get_contents('../../../config/config.json'))->googleClientSecret,
        'googleRedirectUri' => 'http://localhost/login/?bot=google',
        'telegramBotName' => json_decode(file_get_contents('../../../config/config.json'))->telegramBotName,
        'telegramBotToken' => json_decode(file_get_contents('../../../config/config.json'))->telegramBotToken,
        'createUserIfNotExist' => true
    ],

    // ------------------------------------------
    // Verzeichnisse
    // ------------------------------------------
    'paths'  => [
        // Verzeichnis in das Import- und Export-Dateien zur Sicherheit abgelegt werden
        'io' => 'io',
        // Verzeichnis für temporäre Dateien (tmp). Leer für automatisch
        'tempFolder' => '',
        // Verzeichnis für die Sprachdateien inkl. Usages-Datei
        'translationFolder' => 'translate',
        // Verzeichnis für temporäre Dateien, die nicht unmittelbar verarbeitet werden (Uploads, Importe)
        'workDir' => 'workfiles'
    ],


    // ------------------------------------------
    // URL's
    // ------------------------------------------
    'url'  => [
        // Edit Webseite
        'edit' => 'http://localhost/edit'
    ],


    // ------------------------------------------
    // Sicherheit
    // ------------------------------------------
    'security' => [
        'useCaptcha' => true,
        'captcha' => [
            // - immer anzeigen:enable=true
            // - nie anzeigen:enable=false
            // - automatisch anzeigen: enable='auto' auch Anzahl Fehlversuche (invalidLoginLimit)
            //   pro Zeitspanne in sek. (invalidLoginTimespan) angeben
            'enable' => 'auto',
            'invalidLoginLimit' => 2,
            'invalidLoginTimespan' => 60*60
        ],
        'useAutologin' => true,
        'executableFiles' => ['action', 'apk', 'app', 'bat', 'bin', 'cmd', 'com', 'command', 'cpl', 'csh', 'exe', 'gadget', 'inf1', 'ins',
            'inx', 'ipa', 'isu', 'job', 'jse', 'ksh', 'lnk', 'msc', 'msi', 'msp', 'mst', 'osx', 'out', 'paf', 'pif', 'htaccess', 'php', 'sh',
            'prg', 'ps1', 'reg', 'rgs', 'run', 'scr', 'sct', 'shb', 'shs', 'u3p', 'vb', 'vbe', 'vbs', 'vbscript', 'workflow', 'ws', 'wsf', 'wsh']
    ],


    // ------------------------------------------
    // Berechtigungen
    // ------------------------------------------
    'rights' => [
        'lieferantFunction' => 'appKgwebLieferant',
        'fachbereichFunction' => 'appKgwebFachbereich',
        'adminFunction' => 'appKgwebAdmin'
    ],


    // ------------------------------------------
    // Server Einstellungen
    // ------------------------------------------
    'settings' => [
        // Timeout für einen Ajax Request in Sekunden
        'ajaxTimeout' => 300,
        // Maximale Grösse in MB für die totale Grösse der zu importierenden Dateien pro Import.
        // Grössere Importe werden in ein Queue geschrieben und im Hintergrund abgearbeitet.
        // Der User wird per Mail informiert, sobald der Import fortgesetzt werden kann.
        'produktImportSizeLimit' => 20,
        // Der Domainfilter erlaubt den E-Mail-Versand nur an bestimmte Domains
        'mailDomainFilter' => [
            'enable' => true,
            'whitelist' => ['rhel-8.host-only']
        ]
    ],


    // ------------------------------------------
    // Session Einstellungen
    // ------------------------------------------
    // Anweisungen für den Garbage Collector (GC)
    // Beim Wert 0 wird jeweils die Standardeinstellung der php.ini verwendet.
    'session' => [
        // Lebenszeit einer Session in Sekunden. Ältere Sessions werden vom GC gelöscht.
        'gc_maxlifetime' => 10800,                          // P: 10800 (=3 Std.)
        // Der Quotient von gc_probability/gc_divisor gibt an wie oft der GC aufgerufen wird.
        // Beispiel 1/100: Der GC wird bei jedem 100. Aufruf gestartet.
        'gc_probability' => 1,                              // D&P: 1
        'gc_divisor' => 10,                                 // D:1, P:100
        // Anzahl Seiten, die in der History gespeichert werden
        'historySize' => 100                                // 0: keine History, -1: keine Begrenzung
    ],


    // ------------------------------------------
    // Fehlerbehandlung
    // ------------------------------------------
    'exceptionHandling' => [
        // Fehler-Level (überschreibt die php.ini wenn != null
        // Konfiguration für den produktiven Einsatz ab PHP Version 5.3:
        // E_ALL&~E_WARNING&~E_NOTICE&~E_STRICT&~E_DEPRECATED&~E_USER_DEPRECATED
        // Da die Konstanten in neueren PHP-Versionen unterschiedliche Werte haben,
        // ist es bei produktiven Umgebungen besser, hier null einzutragen und
        // in der php.ini den richtigen Wert zu konfigurieren. Sonst muss
        // sichergestellt werden, dass das gewählte Fehler-Level mit der
        // PHP-Version übereinstimmt.
        'error_reporting' => $st_debug_mode ? E_ALL&~E_WARNING&~E_NOTICE&~E_STRICT&~E_DEPRECATED : null,
        // Dasselbe aber für Ausgabe in Console per ChromePhp oder Breakpoint
        'error_reporting_console' => $st_debug_mode ? E_ALL&~E_NOTICE : null,
        // Datei und Zeilennummer anzeigen?
        'showDetails' => $st_debug_mode,                // D:true, P:false
        // Nur allgemeinen Fehler anzeigen (nur wenn showDetails = false möglich)
        'showGeneralErrorMsg' => true,                  // D&P:true
        // Falls Fehlermeldungen in Kurzform dem User angezeigt werden, für SQL-Fehler nur allgemeine Meldung anzeigen?
        'showSqlExceptions' => $st_debug_mode,          // D:true, P:false
        // eigene Fehlerbehandlung für unbehandelte Fehler aktivieren?
        'enableGlobalExceptionHandler' => true,         // D&P:true
        'enableFatalExceptionHandler' => true,          // D&P:true
        // Javascript-Fehler loggen?
        'logJavascriptErrors' => !$st_debug_mode,
        // Errors in Exceptions umwandeln?
        'convertErrors' => true,                        // D&P:true
        // Fehler in die exceptions.log schreiben
        'logfile' => [
            'enable' => true,                           // D&P:true
            'path' => 'log/exceptions.log'
        ],
        // Fehler über E-Mail versenden
        'sendMail' => [
            'enable' => false,                          // D&P: false
            'to' => 'samuel.kipfer@kipferinformatik.ch',
            'from' => 'support@kipferinformatik.ch',
            'subject' => 'suissetec kgweb Exception'
        ]
    ],

    // ------------------------------------------
    // Entwicklung & Debug
    // ------------------------------------------
    'develop' => [
        'debug_mode' => $st_debug_mode,
        'use_chromephp' => false,                               // D:true, P:false
        'use_minify' => false,                                  // D:false, P:true
        // build_minify: D&P:false => ist nur einmal vor dem veröffentlichen nötig. Geht nicht wenn Pfad in URL. OnDemand mit ?buildmin in URL
        'build_minify' => $st_debug_mode ? 'onDemand' : false
    ]
];
