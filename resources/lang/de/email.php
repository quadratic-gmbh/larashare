<?php
return [
  'footer' => 'Das Projekt "KlimaEntLaster go Smart Cities" wird vom österreichischen Klima- und Energiefonds gefördert',
  'survey' => [
    'subject' => 'Danke für Deine Ausleihe',
    'text_top' => "**Danke, dass du ein Transportrad von KlimaEntLaster genutzt hast!**<br><br>Wir hoffen du hattest eine gute Fahrt und viel Freude mit dem KlimaEntLaster-Transportrad. Um unser Angebot zu verbessern, möchten wir dir ein paar Fragen stellen.",
    'text_bottom' => "Die Umfrage wird rund 2 Minuten in Anspruch nehmen. Deine Daten werden **ausschließlich für Forschungs- und Analysezwecke** verwendet und **keinesfalls an Dritte weitergegeben**. Weitere Informationen entnimmst du bitte der **Datenschutzerklärung**.<br><br>Es wird **empfohlen** den Fragebogen auf einem **PC oder Tablet** auszufüllen, da er für diese Geräte optimiert wurde.<br><br>**Bei Fragen** zur Umfrage schreibst du bitte ein Mail an <info@klimaentlaster.at><br><br> **Vielen Dank für deine Unterstützung!**<br>Das KlimaEntLaster Projektteam",    
    'button' => 'Zur Umfrage',
  ],
  'password_reset' => [
    'subject' => 'Passwort-Reset Benachrichtigung',
    'line_1' => 'Du erhälst diese E-Mail weil wir eine Passwort-Reset Anfrage für deinen Account bekommen haben.',
    'line_2' => 'Dieser Link ist nach :count Minuten ungültig.',
    'line_3' => 'Falls du keinen Passwort-Reset angefragt hast, kannst du diese E-Mail ignorieren.',    
    'button' => 'Passwort zurücksetzen',
  ],
  'rental_period_reminder' => [
    'text' => 'Die Verleihzeiten für das Lastenrad :name laufen innerhalb der nächsten 30 Tage ab. Bitte trage neue Verleihzeiten ein.',
    'button' => 'Zu den Verleihzeiten',
    'subject' => 'Ablauf Verleihzeiten',
  ],
  'rental_place' => [
    'new_reservation' => [
      'inquiry' => [
        'text' => [
          'new' => "Es gibt eine neue Anfrage des Transportrads **:b_name** für den Zeitraum **:timeframe**.\n\n **Ausleiher/in:**\n\n:u_name\n\n:u_email:u_phone",
          'update' => "Es gibt eine bearbeitete Anfrage des Transportrads **:b_name** für den Zeitraum **:timeframe**.\n\n **Ausleiher/in:**\n\n:u_name\n\n:u_email:u_phone",
        ],
        'subject' => [
          'new' => 'Neue Anfrage für :name',
          'update' => 'Bearbeitete Anfrage für :name',
        ],
        'button' => 'Zur Anfrage',
      ],
      'instant' => [
        'text' => [
          'new' => "Es gibt eine neue Reservierung des Transportrads **:b_name** für den Zeitraum **:timeframe**.\n\n**Ausleiher/in:**\n\n:u_name\n\n:u_email:u_phone",
          'update' => "Es gibt eine bearbeitete Reservierung des Transportrads **:b_name** für den Zeitraum **:timeframe**.\n\n**Ausleiher/in:**\n\n:u_name\n\n:u_email:u_phone",
        ],
        'subject' => [
          'new' => 'Neue Reservierung für :name',
          'update' => 'Bearbeitete Reservierung für :name',
        ],
        'button' => 'Zur Reservierung',
      ],
    ],
    'cancelled_reservation' => [
      'confirmed' => [
        'text' => 'Die Reservierung für **:b_name** im Zeitraum **:timeframe** wurde durch **:u_name** storniert.',
        'subject' => 'Stornierte Reservierung für :name',        
      ],
      'unconfirmed' => [
        'text' => 'Die Anfrage für **:b_name** im Zeitraum **:timeframe** wurde durch **:u_name** storniert.',
        'subject' => 'Stornierte Anfrage für :name',
      ],
    ]
  ],
  'bike' => [
    'cancelled_reservation' => [
      'info' => 'Du erhältst diese E-Mail, da das Lastenrad nicht mehr verfügbar ist.',
      'confirmed' => [
        'text' => 'Die Reservierung für **:b_name** im Zeitraum **:timeframe** wurde durch **:u_name** storniert.',
        'subject' => 'Stornierte Reservierung für :name',
      ],
      'unconfirmed' => [
        'text' => 'Die Anfrage für **:b_name** im Zeitraum **:timeframe** wurde durch **:u_name** storniert.',
        'subject' => 'Stornierte Anfrage für :name',
      ],
    ]
  ],
  'user' => [
    'new_reservation' => [
      'text' => [
        'new' => "Danke für deine Reservierung des Transportrads **:b_name** Anfrage für Zeitraum **:timeframe**.\n\n Wenn es noch Unklarheiten zur Abholung oder Rückgabe gibt, setze dich bitte mit der/dem Verleiher/in in Verbindung. [Kontakt aufnehmen](:url_chat)",
        'update' => "Danke für deine bearbeitete Reservierung des Transportrads **:b_name** Anfrage für Zeitraum **:timeframe**.\n\n Wenn es noch Unklarheiten zur Abholung oder Rückgabe gibt, setze dich bitte mit der/dem Verleiher/in in Verbindung. [Kontakt aufnehmen](:url_chat)",
      ],
      'subject' => [
        'new' => 'Deine Lastenrad-Reservierung',
        'update' => 'Deine bearbeitete Lastenrad-Reservierung',
      ],
      'button' => 'Zur Reservierung'
    ],
    'cancelled_reservation' => [      
      'confirmed' => [
        'text' => 'Deine Reservierung für **:b_name** im Zeitraum **:timeframe** wurde storniert.',
        'subject' => 'Stornierte Reservierung für :name',
      ],
      'unconfirmed' => [
        'text' => 'Die Reservierung des **:b_name** im Zeitraum **:timeframe** ist **leider nicht möglich.**',
        'subject' => 'Ablehnung der Anfrage für :name',
      ],
    ],
    'confirmed_reservation' => [      
      'text' => 'Deine Anfrage für **:b_name** im Zeitraum **:timeframe** wurde bestätigt. Wenn es noch Unklarheiten zur Abholung oder Rückgabe gibt, setze dich bitte mit der/dem Verleiher/in in Verbindung. [Kontakt aufnehmen](:url_chat)',
      'subject' => 'Reservierungs-Bestätigung für :name', 
      'button' => 'Zur Reservierung',
    ],    
  ],
  'notification' => [
    'hello' => 'Hallo!',
    'whoops' => 'Auweh!',
    'salutation' => 'Beste Grüße,',
    'action_text' => "Falls du Probleme damit hast, den \":actionText\" Button zu klicken, kopierst Du bitte die folgende URL in deinen Web Browser: [:actionURL](:actionURL)",    
  ],
  'chat' => [
    'text' => ":name hat eine neue Nachricht geschrieben:\n\n\":message\"",
    'subject' => 'Neue Nachricht im :chatTitle',
    'button' => 'Zum Chat',
  ],
  'newsletter_confirmation' => [
    'text' => "Bitte bestätige deine Anmeldung zum Newsletter.",
    'subject' => 'Anmeldung zum Newsletter bestätigen',
    'button' => 'Anmeldung bestätigen',
  ],
  'all_rights_reserved' => 'Alle Rechte vorbehalten',
];
