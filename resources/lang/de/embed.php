<?php
return [
  'index' => [
    'empty_text' => 'Keine Einbindungen vorhanden',
    'title' => 'Meine Einbindungen',
    'btn_show' => 'Einbinden',
    'btn_new' => 'Neue Einbindung anlegen',
  ],
  'form' => [
    'title' => 'Allgemeine Daten',
    'tabs_header_edit' => 'Einbindung Bearbeiten',
    'tabs_header_new' => 'Neue Einbindung',
    'defaults' => 'Startwerte',
    'search' => [
      'header' => 'Suche',
      'location' => 'Ort'
    ]
  ],
  'edit_bikes' => [
    'title' => 'Räder',
    'col_cb' => 'Ausgewählt',
    'hint_all' => 'Du kannst hier die Räder, die bei der Einbindung verfügbar sein sollen, auswählen. Wenn keine Räder ausgewählt werden, sind automatisch alle erlaubt.',
    'btn_all' => 'Alle Räder zulassen',
  ],
  'edit_styling' => [
    'title' => 'Styling',
    'tab_simple' => 'Einfach',
    'tab_advanced' => 'Erweitert',
    'color_body' => 'Text',
    'color_primary' => 'Primär',
    'colors' => 'Farben',
    'font' => 'Schrift',
    'font_size' => 'Schriftgröße',
    'font_family' => 'Schrfitart',
    'saved_success' => 'Erfolgreich gespeichert. Es kann bis zu 5 Minuten dauern, bis das Styling verfügbar ist.',
    'advanced_hint' => "Hier kannst du selbst Styling definieren. Dieses wird dann alternativ zum einfachen Styling verwendet. Du kannst hier die Variablen von Bootstrap 4.3.1 direkt mit SCSS verändern.",
    'advanced_example_used' => "Als Beispiel wird hier das einfache Styling bei den Variablen verwendet.",
    'advanced_links' => 'Dokumentation:',
    'advanced_link_bootstrap' => 'Bootstrap',
    'advanced_link_sass' => 'Sass',
    'advanced_empty' => '(leer)',
    'advanced_styling' => 'Erweitertes Styling und Variablen',
    'advanced_variables' => 'Variablen: hier kannst du Variablen definieren und bestehende Variablen überschreiben.',
    'advanced_text' => 'Styling: wird zuletzt eingebunden',
    'advanced_error_failed' => 'Fehler beim Speichern des Stylings.',
  ],
  'show' => [
    'title' => 'Einbinden',
    'btn_index' => 'Zur Übersicht',
    'hint' => 'Der Code für eine Einbindung besteht aus mehreren Teilen: Javascript, CSS-Styling und Widget-Container',
    'widget' => 'Widget',
    'js_title' => 'Javascript',
    'js_hint' => 'Script Tag mit der URL zum Javascript der Einbindung. Die Optionalen URL-Parameter "id" und "widget" werden verwendet, um die richtige Einbindung bzw. das richtige Widget zu laden.',
    'css_title' => 'Styling',
    'css_hint' => 'Default-Styling für die Einbindung. Das sollte im Head deiner Seite eingefügt werden.',
    'div_title' => 'Widget-Container',
    'div_hint' => 'Das Widget wird automatisch in den folgenden Widget-Container geladen. Es ist hierbei wichtig, dass die ID gleich bleibt, da das Widget sonst nicht gerendert werden kann.',
    'options' => [
      'search' => 'Suche',
      'browse' => 'Karte',
    ],    
  ],
  'destroy_ask' => [
    'header' => 'Einbindung ":name" löschen?',
  ],
];