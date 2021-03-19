<?php
return [
    "firstname" => "First name",
    "lastname" => "Last name",
    "date_of_birth" => "Date of birth",
    "gender" => [
        "gender" => "Gender",
        "" => "not specified",
        "MALE" => "male",
        "FEMALE" => "female"
    ],
    "reservations" => [
        "title" => "My reservations",
        "category_new" => "Current reservations",
        "category_new_empty" => "No current reservations available",
        "category_old" => "Past reservations",
        "category_old_empty" => "No past reservations available",
        "btn_contact" => "Contact us",
        "btn_details" => "Details",
        "btn_cancel" => "Cancel reservation",
        "btn_edit" => "Edit reservation",
        "not_yet_confirmed" => "The reservation has not yet been confirmed."
    ],
    "reservation" => [
        "title" => "Reservation: :name",
        "header_from" => "Pick up",
        "header_to" => "Return",
        "btn_bike" => "Cargo bike details",
        "place_not_found" => "Location could not be determined. Please contact.",
        "messages" => [
            "confirmed" => "Thank you for your reservation! You can pick up the transport bike on the desired day.",
            "unconfirmed" => "Thank you for your request. Please wait for the acceptance or rejection by the renter. - We will of course inform you by e-mail.",
            "unconfirmed_contact_prompt" => "If you do not receive a prompt acceptance or rejection, please contact the renter."
        ]
    ],
    "reservation_cancel" => [
        "title" => "Cancel reservation",
        "hint" => "Do you really want to cancel the reservation?"
    ],
    "edit" => [
        "title" => "My profile",
        "email_old" => "Current email address",
        "password_new" => "New password",
        "password_new_confirmation" => "Repeat New Password"
    ],
    "delete" => [
        "header" => "Delete account",
        "hint" => "Do you really want to delete your account?",
        "hint_not_possible" => "Account cannot be deleted.",
        "hint_bikes" => "You still have active bikes",
        "hint_reservations" => "You have outstanding reservations"
    ],
    "deleted" => "[deleted user]",
    "newsletter_confirmation" => [
        "title" => "Newsletter subscription",
        "text" => "You have successfully subscribed to the newsletter."
    ]
];
