<?php
return [
    "user_warnings" => [
        "no_rental_period" => "Attention, cargo bike :name currently has no rental times entered.",
        "not_public" => "Attention, the cargo bike :name is not yet published.",
        "no_image" => "Attention, at cargo bike :name you have not uploaded a photo yet."
    ],
    "form_tabs_header" => "Edit cargo bike",
    "form_tabs_header_new" => "Add cargo bike",
    "form_tabs" => [
        "edit" => "General information",
        "images" => "Images",
        "rental_period" => "Rental times",
        "review" => "Edit times individually",
        "publish" => "Publish"
    ],
    "rental_place" => "Location",
    "rental_mode" => [
        "rental_mode" => "Rental mode",
        "none" => "No rental",
        "INQUIRY" => "Rental only after approval",
        "INSTANT_RESERVATION" => "Fixed booking without further inquiry"
    ],
    "pricing_type" => [
        "FREE" => "Free",
        "DONATION" => "Free donation",
        "FREE_OR_DONATION" => "Free / Free donation",
        "FIXED" => "Fixed price"
    ],
    "index" => [
        "title" => "My cargo bikes",
        "header_editable" => "Other cargo bikes",
        "empty_text" => "No bikes available",
        "embeds_link" => "Implementation of cargo bikes into your website",
        "link_reservations" => "Reservations",
        "link_editors" => "Administrators",
        "new" => "Add new cargo bike"
    ],
    "editors" => [
        "title" => "Authorized users",
        "title_editors_remove" => ":name remove access rights?",
        "empty_text" => "No users available",
        "cant_add_owner" => "Is owner.",
        "cant_add_again" => "Already has access."
    ],
    "destroy_ask" => [
        "header_bike" => "Delete cargo bike \":name\"?",
        "header_rental_place" => "Delete rental location \":name\"?"
    ],
    "deleted" => "[deleted bike]",
    "form" => [
        "header_edit" => "Edit cargo bike",
        "header_new" => "Add cargo bike",
        "subheader_bike" => "Bike details",
        "subheader_buffer_time" => "Slack time",
        "subheader_cost" => "Costs",
        "subheader_rental_duration" => "Rental period",
        "subheader_rental_places" => "Rental location(s)",
        "buffer_time_text" => "Times before and after the borrowing - e.g. for charging e-bike batteries",
        "add_place" => "Add another rental location",
        "remove_place" => "Remove rental location",
        "add_email" => "Add another email address to this rental location",
        "remove_email" => "Remove email address from this rental location",
        "tos_template" => "Here you can find a template for terms of use",
        "errors" => [
            "pricing" => [
                "one_required" => "At least one cost type have to be specified.",
                "not_with_free_or_donation" => "Cannot be selected together with \"Free\" or \"Free donation\"."
            ],
            "terms_of_use" => [
                "not_both" => "Please either delete existing terms of use or replace them with new ones."
            ],
            "rental_place" => [
                "one_required" => "At least one rental location have to be indicated."
            ],
            "email" => [
                "duplicates" => "Email addresses may not be entered more than once per rental location.",
                "one_required" => "At least one e-mail address have to be provided for each rental location."
            ]
        ]
    ],
    "rental_periods" => [
        "title" => "Rental times",
        "hint" => "You can add different rental times consecutively. - So e.g. in the first step enter the morning opening times and save and in a second step under \"Add more times\" the afternoon opening times. Or first Mo-Fr and as a further time Sa.",
        "list_title" => "Available rental times",
        "rental_place_select" => "Location selection",
        "weekdays" => "take over for",
        "dates" => "Validity of the rental times",
        "times" => "Rental times",
        "link_add" => "Add more times",
        "link_rem" => "Delete all times",
        "link_proceed" => "Continue to check and edit individual days"
    ],
    "rental_periods_exception" => [
        "title" => "Edit rental time"
    ],
    "rental_periods_review" => [
        "title" => "Check rental period",
        "hint" => "Here you can delete individual days and time periods in the calendar"
    ],
    "publish" => [
        "title" => "Publish",
        "accepts_tos" => "I have read and agree to the <a href=\":url\" target=\"_blank\">general terms of use</a>.",
        "has_permission" => "I am authorized to offer this bicycle for rental.",
        "public" => "Publish"
    ],
    "wheels" => [
        2 => "Two-wheeled",
        3 => "Three-wheeled",
        4 => "Four-wheeled"
    ],
    "children" => "{0} No child seat|{1} Seat and belt for 1 child|[2,*] Seat and belts for :i children",
    "child_seat" => "Child seat",
    "electric" => [
        "Without electric drive",
        "With electric drive"
    ],
    "box_type" => [
        "NO_BOX" => "No box",
        "LOCKABLE" => "Lockable box",
        "NON_LOCKABLE" => "Non-lockable box"
    ],
    "rental_duration_in_days" => [
        "Hours",
        "Days"
    ],
    "name" => "Name of your cargo bike (you can make it up yourself)",
    "model" => "Manufacturer + type (e.g. Christiania light)",
    "cargo_weight" => "Maximum payload in kg",
    "cargo_length" => "Length of the loading area in cm",
    "cargo_width" => "Width of the loading area in cm",
    "misc_equipment" => "Other equipment (e.g. tarpaulin, lashing straps etc)",
    "description" => "Description and restrictions (e.g. What is the bike particularly suitable for? No animal transport, rental only to persons over 18 etc.)",
    "buffer_time_before" => "Slack time before borrowing",
    "buffer_time_after" => "Slack time after borrowing",
    "pricing" => [
        "free" => "Free",
        "donation" => "Free donation",
        "fixed" => "Fixed price",
        "eur_per" => "EUR per",
        "deposit" => "Deposit"
    ],
    "pricing_rate" => [
        "HOURLY" => "Hour",
        "DAILY" => "Day",
        "WEEKLY" => "Week"
    ],
    "rental_duration" => "Maximum continuous rental period",
    "rental_duration_short" => "Rental period",
    "rental_duration_api" => "Max. Duration",
    "no_interrupt_api" => "The bike has to be back by :time.",
    "no_interrupt_short" => "The bike hast to be back at the end of this rental period.",
    "no_interrupt" => "The transport bike has to be back at the end of this rental time.",
    "terms_of_use_file" => "Here you can upload your own terms of use in pdf format:",
    "terms_of_use_file_edit" => "There are already <a href=\":route\" target=\"_blank\">:tos</a>. You can replace or delete them:",
    "terms_of_use_accept" => "I have read the <a href=\":route\" target=\"_blank\">:tos</a> and accept them.",
    "terms_of_use" => "Terms of use",
    "delete_terms_of_use_file" => "Delete existing terms of use",
    "rental_place_name" => "Designation of the rental location",
    "rental_place_street_name" => "Street",
    "rental_place_house_number" => "House No",
    "rental_place_postal_code" => "Postcode",
    "rental_place_city" => "Place",
    "rental_place_description" => "Access description",
    "rental_place_email" => "E-mail address for communication and notifications",
    "rental_place_email_notify_on_reservation" => "I would like to be informed about new reservations by e-mail.",
    "show" => [
        "title" => "Reservation :name",
        "title_edit" => "Edit reservation :name",
        "children" => "Child seat/safety device",
        "cargo_weight" => "max. payload",
        "cargo_width" => "Width of the loading area",
        "cargo_length" => "Length of the loading area",
        "misc_equipment" => "Other characteristics",
        "rental_place_description" => "Pick up location|Pick up locations",
        "description" => "Description and limitations",
        "calendar_hint" => "When you click on the calendar, a period of two hours is marked. You can drag this downwards with the mouse or extend it on your smartphone by briefly pressing and holding down. You can also select a period of several days if this transport bike is lent overnight.<br><br>As an alternative to entering the pick up and drop-off time, you can enter your desired time period directly in the calendar below.",
        "overlay_text" => "You can click/press here in the calendar to mark a period. You can then move this period and/or extend or shorten it at the bottom edge.",
        "reserve_from" => "Pick up",
        "reserve_to" => "Return",
        "time" => "Time",
        "date" => "Date",
        "purpose_hint" => "Optional entry of the purpose of application (will be published in the calendar without name etc.)",
        "legend" => [
            "INQUIRY" => "During this period you can request the pick up and return of the bike, but the reservation is only valid after confirmation by the renter",
            "INSTANT_RESERVATION" => "During this period you can book the bike (pick it up or return it)",
            "reservation" => "During this period the bike is already reserved",
            "selection" => "Your selection"
        ]
    ],
    "reservations" => [
        "title" => "Reservations for :name",
        "calendar_hint" => "Click on reservations/requests to get to the detail view",
        "legend" => [
            "INQUIRY" => "During this period, the pick up and return of the bike can be requested, but the reservation is only valid after confirmation by the renter",
            "INSTANT_RESERVATION" => "During this period the bike can be booked (picked up or returned)",
            "reserved" => "Fixed reservation",
            "pending" => "Reservation not yet confirmed"
        ]
    ],
    "reservation" => [
        "title_confirmed" => "Reservation for :name",
        "title_unconfirmed" => "Reservation request for :name",
        "title_cancel" => "Cancel reservation for :name",
        "header_from" => "Pick up",
        "header_to" => "Return",
        "btn_contact" => "Contact us",
        "btn_cancel" => "Cancel",
        "btn_confirm" => "Confirm",
        "btn_deny" => "Reject",
        "purpose" => "Specified purpose",
        "purpose_null" => "Not specification",
        "hint_old" => "Contact us",
        "hint_confirmed" => "Contact us or cancel your reservation",
        "hint_unconfirmed" => "Contact, confirm or decline reservation",
        "user_name" => "Borrower",
        "place_not_found" => "Location could not be determined",
        "user_deleted" => "User was deleted"
    ],
    "images" => [
        "title" => "Pictures",
        "hint" => "Please select a picture and click on \"Upload\". You can upload several images consecutively.",
        "file_text" => "Choose picture"
    ],
    "rentee_limitation" => [
        "label_form" => "Restrict borrowing during this time to users with the following email address only: (One e-mail address per line)",
        "label_show" => "Restriction of borrowing during this period exclusively to users with the following e-mail address:"
    ]
];
