<?php
return [
    "index" => [
        "empty_text" => "No implementation available",
        "title" => "My implementations",
        "btn_show" => "Implement",
        "btn_new" => "Add new implementation"
    ],
    "form" => [
        "title" => "General data",
        "tabs_header_edit" => "Edit implementation",
        "tabs_header_new" => "New implementation",
        "defaults" => "Start values",
        "search" => [
            "header" => "Search",
            "location" => "Place"
        ]
    ],
    "edit_bikes" => [
        "title" => "Bikes",
        "col_cb" => "Selected",
        "hint_all" => "Here you can select the bikes that should be available during the implementation. If no bikes are selected, all are automatically allowed.",
        "btn_all" => "Allow all bikes"
    ],
    "edit_styling" => [
        "title" => "Styling",
        "tab_simple" => "Simple",
        "tab_advanced" => "Enhanced",
        "color_body" => "Text",
        "color_primary" => "Primary",
        "colors" => "Colours",
        "font" => "Writing",
        "font_size" => "Font size",
        "font_family" => "Font",
        "saved_success" => "Successfully saved. It may take up to 5 minutes for the styling to become available.",
        "advanced_hint" => "Here you can define styling yourself. This is then used as an alternative to the simple styling. You can change the variables of Bootstrap 4.3.1 directly with SCSS.",
        "advanced_example_used" => "As an example, the simple styling is used here for the variables.",
        "advanced_links" => "Documentation:",
        "advanced_link_bootstrap" => "Bootstrap",
        "advanced_link_sass" => "Sass",
        "advanced_empty" => "(empty)",
        "advanced_styling" => "Advanced styling and variables",
        "advanced_variables" => "Variables: here you can define variables and overwrite existing variables.",
        "advanced_text" => "Styling: will be included last",
        "advanced_error_failed" => "Error saving styling."
    ],
    "show" => [
        "title" => "Implement",
        "btn_index" => "To the overview",
        "hint" => "The code for an implementation consists of several parts: Javascript, CSS styling and widget container",
        "widget" => "Widget",
        "js_title" => "Javascript",
        "js_hint" => "Script Tag with the URL to the Javascript of the implementation. The optional URL parameters \"id\" and \"widget\" are used to load the correct implementation respectively the correct widget.",
        "css_title" => "Styling",
        "css_hint" => "Default styling for the implementation. This should be inserted in the head of your page.",
        "div_title" => "Widget container",
        "div_hint" => "The widget is automatically loaded into the following widget container. It is important that the ID remains the same, otherwise the widget cannot be rendered.",
        "options" => [
            "search" => "Search",
            "browse" => "Card"
        ]
    ],
    "destroy_ask" => [
        "header" => "Delete implementation \":name\"?"
    ]
];
