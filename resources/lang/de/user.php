<?php
return [
  'firstname' => 'Vorname',
  'lastname' => 'Nachname',
  'date_of_birth' => 'Geburtsdatum',  
  'gender' => [
    'gender' => 'Geschlecht',
    '' => 'keine Angabe',
    'MALE' => 'männlich',
    'FEMALE' => 'weiblich'
  ], 
  'reservations' => [
    'title' => 'Meine Reservierungen',
    'category_new' => 'Aktuelle Reservierungen',
    'category_new_empty' => 'Keine aktuellen Reservierungen vorhanden',
    'category_old' => 'Vergangene Reservierungen',
    'category_old_empty' => 'Keine vergangenen Reservierungen vorhanden',
    'btn_contact' => 'Kontakt aufnehmen',
    'btn_details' => 'Details',
    'btn_cancel' => 'Reservierung stornieren',
    'btn_edit' => 'Reservierung bearbeiten',
    'not_yet_confirmed' => 'Die Reservierung wurde noch nicht bestätigt.',     
  ],
  'reservation' => [
    'title' => 'Reservierung: :name',
    'header_from' => 'Abholung',
    'header_to' => 'Rückgabe',
    'btn_bike' => 'Lastenrad-Details',
    'place_not_found' => 'Standort konnte nicht bestimmt werden. Bitte Kontakt aufnehmen.',
    'messages' => [
      'confirmed' => 'Danke für deine Reservierung! Du kannst das Transportrad am gewünschten Tag abholen.',
      'unconfirmed' => "Danke für deine Anfrage. Bitte warte auf die Annahme oder Ablehnung durch die/den Verleiher/in. - Wir informieren dich darüber natürlich auch per E-Mail.",
      'unconfirmed_contact_prompt' => 'Falls du nicht zeitnah eine Annahme oder Ablehnung erhalten solltest, nimm bitte Kontakt mit der/dem Verleiher/in auf.'
    ]
  ],
  'reservation_cancel' => [
    'title' => 'Reservierung stornieren',
    'hint' => 'Möchtest du die Reservierung wirklich stornieren?',    
  ],
  'edit' => [
    'title' => 'Mein Profil',
    'email_old' => 'Aktuelle E-Mail-Adresse',
    'password_new' => 'Neues Passwort',
    'password_new_confirmation' => 'Wiederholung Neues Passwort',
  ],
  'delete' => [
    'header' => 'Account löschen',
    'hint' => 'Möchtest du deinen Account wirklich löschen?',
    'hint_not_possible' => 'Account kann nicht gelöscht werden.',
    'hint_bikes' => 'Du hast noch aktive Räder',
    'hint_reservations' => 'Du hast noch austehende Reservierungen',    
  ],
  'deleted' => '[gelöschter Benutzer]',
  'newsletter_confirmation' => [
    'title' => 'Newsletter Anmeldung',
    'text' => 'Du hast dich erfolgreich zum Newsletter angemeldet.'
  ],
];
