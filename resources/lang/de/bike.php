<?php
return [
  'user_warnings' => [
    'no_rental_period' => 'Achtung, Lastenrad :name hat derzeit keine Verleihzeiten eingetragen.',
    'not_public' => 'Achtung, das Lastenrad :name ist noch nicht veröffentlicht.',
    'no_image' => 'Achtung, bei Lastenrad :name hast du noch kein Foto hochgeladen.'
  ],
  'form_tabs_header' => 'Transportrad bearbeiten',
  'form_tabs_header_new' => 'Transportrad anlegen',  
  'form_tabs' => [
    'edit' => 'Allgemeine Angaben',
    'images' => 'Bilder',
    'rental_period' => 'Verleihzeiten',
    'review' => 'Zeiten einzeln bearbeiten',
    'publish' => 'Veröffentlichen',
  ],
  'rental_place' => 'Standort',  
  'rental_mode' => [
    'rental_mode' => 'Verleihmodus',
    'none' => 'Kein Verleih',
    'INQUIRY' => 'Verleih nur nach Freigabe',
    'INSTANT_RESERVATION' => 'Fixbuchung ohne Rückfrage',
  ],
  'pricing_type' => [
    'FREE' => 'Gratis',
    'DONATION' => 'Freie Spende',
    'FREE_OR_DONATION' => 'Gratis / Freie Spende',
    'FIXED' => 'Fixpreis'
  ],
  'index' => [
    'title' => 'Meine Lastenräder',
    'header_editable' => 'Andere Lastenräder',
    'empty_text' => 'Keine Räder vorhanden',
    'embeds_link' => 'Einbindung von Transporträdern in deine Website',
    'link_reservations' => 'Reservierungen',
    'link_editors' => 'Administrator/innen',
    'new' => 'Neues Transportrad anlegen',
  ],
  'editors' => [
    'title' => 'Autorisierte Benutzer/innen',
    'title_editors_remove' => ':name Zugriffsrechte abnehmen?',
    'empty_text' => 'Keine Benutzer vorhanden',
    'cant_add_owner' => 'Ist Besitzer.',
    'cant_add_again' => 'Hat bereits Zugriff.',
  ],
  'destroy_ask' => [
    'header_bike' => 'Transportrad ":name" löschen?',
    'header_rental_place' => 'Verleihstandort ":name" löschen?',
  ],
  'deleted' => '[gelöschtes Bike]',
  'form' => [
    'header_edit' => 'Transportrad bearbeiten',
    'header_new' => 'Transportrad anlegen',
    'subheader_bike' => 'Angaben zum Rad',
    'subheader_buffer_time' => 'Pufferzeiten',
    'subheader_cost' => 'Kosten',
    'subheader_rental_duration' => 'Verleihdauer',
    'subheader_rental_places' => 'Verleihstandort(e)',
    'buffer_time_text' => 'Zeiten vor und nach den Ausleihen - zB für das Laden von E-Bike-Akkus',
    'add_place' => 'Weiteren Verleihstandort hinzufügen',
    'remove_place' => 'Verleihstandort entfernen',
    'add_email' => 'Weitere E-Mail-Adresse zu diesem Verleihstandort hinzufügen',
    'remove_email' => 'E-Mail-Adresse von diesem Verleihstandort entfernen',
    'tos_template' => 'Hier findest du eine Vorlage für Nutzungsbedingungen',
    'errors' => [
      'pricing' => [
        'one_required' => 'Zumindest eine Kosten-Art muss angegeben werden.',
        'not_with_free_or_donation' => 'Kann nicht zusammen mit "Gratis" oder "Freie Spende" gewählt werden.'
      ],
      'terms_of_use' => [
        'not_both' => 'Bitte entweder vorhandene Nutzungsbedingungen löschen oder sie durch neue ersetzen.'
      ],
      'rental_place' => [
        'one_required' => 'Zumindest ein Verleihstandort muss angegeben werden.',
      ],
      'email' => [
        'duplicates' => 'E-Mail-Adressen dürfen pro Verleihstandort nicht mehrfach eingetragen werden.',
        'one_required' => 'Pro Verleihstandort muss zumindest eine E-Mail-Adresse angegeben werden.'
      ],
    ],
  ],
  'rental_periods' => [
    'title' => 'Verleihzeiten',
    'hint' => 'Du kannst nacheinander verschiedene Verleihzeiten anlegen. - Also zB im ersten Schritt die Vormittags-Öffnungszeiten eingeben und speichern und in einem zweiten Schritt unter "Weitere Zeiten hinzufügen" die Nachmittags-Öffnungszeiten. Oder zuerst Mo-Fr und als weitere Zeit Sa.',
    'list_title' => 'Vorhandene Verleihzeiten',
    'rental_place_select' => 'Standortauswahl',
    'weekdays' => 'übernehmen für',
    'dates' => 'Gültigkeit der Verleihzeiten',    
    'times' => 'Verleihzeiten',    
    'link_add' => 'Weitere Zeiten hinzufügen',
    'link_rem' => 'Alle Zeiten löschen',
    'link_proceed' => 'Weiter zum Überprüfen und Bearbeiten einzelner Tage',
  ],
  'rental_periods_exception' => [
    'title' => 'Verleihzeit bearbeiten',
  ],
  'rental_periods_review' => [
    'title' => 'Verleihzeitraum Überprüfen',
    'hint' => 'Hier kannst du im Kalender einzelne Tage und Zeitspannen löschen',
  ],
  'publish' => [
    'title' => 'Veröffentlichen',
    'accepts_tos' => 'Ich habe die <a href=":url" target="_blank">Allgemeinen Nutzungsbedingungen</a> gelesen und stimme ihnen zu.',
    'has_permission' => 'Ich bin berechtigt, dieses Fahrrad zum Verleih anzubieten.',
    'public' => 'Veröffentlichen',
    
  ],
  'wheels' => [
    '2' => 'Zweirädrig',
    '3' => 'Dreirädrig',
    '4' => 'Vierrädrig',
  ],
  'children' => '{0} Kein Kindersitz|{1} Sitz und Gurt für 1 Kind|[2,*] Sitz und Gurte für :i Kinder',
  'child_seat' => 'Kindersitz',
  'electric' => [
    '0' => 'Ohne E-Antrieb',
    '1' => 'Mit E-Antrieb'
  ],
  'box_type' => [
    'NO_BOX' => 'Keine Box',
    'LOCKABLE' => 'Verschließbare Box',
    'NON_LOCKABLE' => 'Nicht verschließbare Box',
  ],
  'rental_duration_in_days' => [
    '0' => 'Stunden',
    '1' => 'Tage',
  ],
  'name' => 'Name deines Transportrads (darfst du dir selbst ausdenken)',
  'model' => 'Hersteller + Typ (zB Christiania light)',
  'cargo_weight' => 'Maximale Zuladung in kg',
  'cargo_length' => 'Länge der Ladefläche in cm',
  'cargo_width' => 'Breite der Ladefläche in cm',
  'misc_equipment' => 'Sonstige Ausstattung (zB Abdeckplane, Zurrgurte etc)',
  'description' => 'Beschreibung und Einschränkungen (zB Wofür eignet sich das Rad besonders gut? Kein Tiertransport, Verleih nur an Personen über 18 etc.)',
  'buffer_time_before' => 'Pufferzeit vor Ausleihen',
  'buffer_time_after' => 'Pufferzeit nach Ausleihen',
  'pricing' => [
    'free' => 'Gratis',
    'donation' => 'Freie Spende',
    'fixed' => 'Fixpreis',
    'eur_per' => 'EUR pro',
    'deposit' => 'Kaution'
  ],
  'pricing_rate' => [
    'HOURLY' => 'Stunde',
    'DAILY' => 'Tag',
    'WEEKLY' => 'Woche',
  ],
  'rental_duration' => 'Maximale durchgehende Verleihdauer',
  'rental_duration_short' => 'Verleihdauer',
  'rental_duration_api' => 'Max. Dauer',
  'no_interrupt_api' => 'Das Rad muss bis :time Uhr wieder zurück sein.',
  'no_interrupt_short' => 'Das Rad muss am Ende dieser Verleihzeit wieder zurück sein.',
  'no_interrupt' => 'Das Transportrad muss am Ende dieser Verleihzeit wieder zurück sein.',
  'terms_of_use_file' => 'Hier kannst du eigene Nutzungsbedingungen im pdf-Format hochladen:',
  'terms_of_use_file_edit' => 'Es sind bereits <a href=":route" target="_blank">:tos</a> vorhanden. Du kannst sie ersetzen oder löschen:',
  'terms_of_use_accept' => 'Ich habe die <a href=":route" target="_blank">:tos</a> gelesen und akzeptiere sie.',
  'terms_of_use' => 'Nutzungsbedingungen',
  'delete_terms_of_use_file' => 'Vorhandene Nutzungsbedingungen löschen',
  'rental_place_name' => 'Bezeichnung des Verleihstandorts',
  'rental_place_street_name' => 'Straße',
  'rental_place_house_number' => 'Hausnummer',
  'rental_place_postal_code' => 'PLZ',
  'rental_place_city' => 'Ort',
  'rental_place_description' => 'Zugangsbeschreibung',
  'rental_place_email' => 'E-Mail-Adresse für Kommunikation und Benachrichtigungen',
  'rental_place_email_notify_on_reservation' => 'Ich möchte bei neuen Reservierungen per E-Mail informiert werden.',
  'show' => [
    'title' => 'Reservierung :name',
    'title_edit' => 'Reservierung :name bearbeiten',
    'children' => 'Kindersitz/-sicherung',
    'cargo_weight' => 'max. Zuladung',
    'cargo_width' => 'Breite der Ladefläche',
    'cargo_length' => 'Länge der Ladefläche',
    'misc_equipment' => 'Sonstige Merkmale',
    'rental_place_description' => 'Abholort|Abholorte',
    'description' => 'Beschreibung und Einschränkungen',
    'calendar_hint' => "Wenn du in den Kalender klickst, wird ein Zeitraum von zwei Stunden markiert. Diesen kannst du mit der Maus nach unten ziehen oder am Smartphone mit kurzem längeren Drücken nach unten verlängern. Du kannst dabei auch einen Zeitraum über mehrere Tage auswählen, wenn dieses Transportrad über Nacht verliehen wird.<br><br>Alternativ zur Eingabe des Abhol- und Rückgabezeitpunkts kannst du deinen gewünschten Zeitraum unten direkt im Kalender eintragen.",
    'overlay_text' => 'Du kannst hier in den Kalender klicken/drücken, um einen Zeitraum zu markieren. Diesen Zeitraum kannst du dann verschieben und/oder am unteren Rand verlängern oder verkürzen.',
    'reserve_from' => 'Abholung',
    'reserve_to' => 'Rückgabe',
    'time' => 'Uhrzeit',
    'date' => 'Datum',
    'purpose_hint' => "Optionale Eingabe des Einsatzzwecks (wird im Kalender ohne Name etc. veröffentlicht)",
    'legend' => [
      'INQUIRY' => 'In diesem Zeitraum kannst du die Abholung und Rückgabe des Rades anfragen, aber die Reservierung ist erst nach Bestätigung durch die/den Verleiher/in gültig',
      'INSTANT_RESERVATION' => 'In diesem Zeitraum kannst du das Rad fix buchen (abholen oder zurückgeben)',
      'reservation' => 'In diesem Zeitraum ist das Rad bereits reserviert',
      'selection' => 'Deine Auswahl',
      'restricted' => 'Dieser Zeitraum ist nur für berechtigte User verfügbar. Wenn du berechtigt bist, logge dich bitte ein, um reservieren zu können.'
    ],
  ],
  'reservations' => [
    'title' => 'Reservierungen für :name',
    'calendar_hint' => 'Klicke auf Reservierungen/Anfragen um auf die Detailansicht zu kommen',
    'legend' => [
      'INQUIRY' => 'In diesem Zeitraum ist die Abholung und Rückgabe des Rades anfragbar, die Reservierung ist aber erst nach Bestätigung durch die/den Verleiher/in gültig',
      'INSTANT_RESERVATION' => 'In diesem Zeitraum kann das Rad fix gebucht (abgeholt oder zurückgegeben) werden',
      'reserved' => 'Fixe Reservierung',
      'pending' => 'Noch unbestätigte Reservierung',
    ]
  ],
  'reservation' => [
    'title_confirmed' => 'Reservierung für :name',
    'title_unconfirmed' => 'Reservierungs-Anfrage für :name',
    'title_cancel' => 'Reservierung für :name stornieren',
    'header_from' => 'Abholung',
    'header_to' => 'Rückgabe',
    'btn_contact' => 'Kontakt aufnehmen',
    'btn_cancel' => 'Stornieren',
    'btn_confirm' => 'Bestätigen',
    'btn_deny' => 'Ablehnen',
    'btn_deny_message' => 'Ablehnen und Nachricht schreiben',
    'btn_deny_no_message' => 'Ablehnen ohne Nachricht',
    'purpose' => 'Angegebener Verwendungszweck',
    'purpose_null' => 'Keine Angabe',
    'hint_old' => 'Kontakt aufnehmen',
    'hint_confirmed' => 'Kontakt aufnehmen oder Reservierung stornieren',
    'hint_unconfirmed' => 'Kontakt aufnehmen, Reservierung bestätigen oder ablehnen',
    'user_name' => 'Ausleiher',
    'place_not_found' => 'Standort konnte nicht bestimmt werden',
    'user_deleted' => 'User was deleted',
    'confirm_inquiry_cancellation' => 'Willst du die Anfrage wirklich ablehnen?'
  ],
  'images' => [
    'title' => 'Bilder',
    'hint' => 'Bitte wähle ein Bild aus und klicke auf "Hochladen". Du kannst mehrere Bilder nacheinander hochladen.',
    'file_text' => 'Bild auswählen',
  ],
  'rentee_limitation' => [
    'label_form' => 'Beschränkung der Ausleihe in dieser Zeit ausschließlich auf Nutzer/innen mit folgender E-Mail-Adresse: (Eine E-Mail-Adresse pro Zeile)',
    'label_show' => 'Beschränkung der Ausleihe in dieser Zeit ausschließlich auf Nutzer/innen mit folgender E-Mail-Adresse:'
  ]
];
