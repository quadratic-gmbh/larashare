<?php
return [
    "footer" => "The project \"KlimaEntLaster go Smart Cities\" is funded by the Austrian Climate and Energy Fund",
    "survey" => [
        "subject" => "Thank you for borrowing",
        "text_top" => "*Thank you for using a cargo bike from KlimaEntLaster!<br><br>We hope you had a good trip and a lot of fun with the KlimaEntLaster cargo bike. In order to improve our offer, we would like to ask you a few questions.",
        "text_bottom" => "The survey will take about 2 minutes to complete. Your data will be used **only for research and analysis purposes** and **will not be shared with any third party**. For more information, please see the **Privacy Policy**.<br><br>It is **recommended** to complete the questionnaire on a **PC or tablet** as it has been optimized for these devices.<br><br>**If you have any questions** about the survey, please send an email to <info@klimaentlaster.at><br><br> *Thank you for your support! The<br>KlimaEntLaster project team</br>",
        "button" => "To the survey"
    ],
    "password_reset" => [
        "subject" => "Password-reset notification",
        "line_1" => "You are receiving this email because we have a password-reset request for your account.",
        "line_2" => "This link is invalid after :count minutes.",
        "line_3" => "If you did not request a password-reset, you can ignore this email.",
        "button" => "Reset password"
    ],
    "rental_period_reminder" => [
        "text" => "The rental times for the cargo bike :name expire within the next 30 days. Please enter new rental times.",
        "button" => "To the rental times",
        "subject" => "Procedure rental times"
    ],
    "rental_place" => [
        "new_reservation" => [
            "inquiry" => [
                "text" => [
                    "new" => "There is a new request from the cargo bike **:b_name** for the time period **:timeframe**.\n\n **borrower:**\n\n:u_name\n\n:u_email:u_phone",
                    "update" => "There is a edited request from the transport wheel **:b_name** for the time period **:timeframe**.\n\n **borrower:**\n\n:u_name\n\n:u_email:u_phone"
                ],
                "subject" => [
                    "new" => "New request for :name",
                    "update" => "Edited request for :name"
                ],
                "button" => "To the request"
            ],
            "instant" => [
                "text" => [
                    "new" => "There is a new reservation of the cargo bike **:b_name** for the period **:timeframe**.\n\n**borrower:**\n\n:u_name\n\n:u_email:u_phone",
                    "update" => "There is an edited reservation of the cargo bike **:b_name** for the period **:timeframe**.\n\n**borrower:**\n\n:u_name\n\n:u_email:u_phone"
                ],
                "subject" => [
                    "new" => "New reservation for :name",
                    "update" => "Edited reservation for :name"
                ],
                "button" => "To the reservation"
            ]
        ],
        "cancelled_reservation" => [
            "confirmed" => [
                "text" => "The reservation for **:b_name** in the period **:timeframe** was cancelled by **:u_name**.",
                "subject" => "Canceled reservation for :name"
            ],
            "unconfirmed" => [
                "text" => "The request for **:b_name** in period **:timeframe** was cancelled by **:u_name**.",
                "subject" => "Canceled request for :name"
            ]
        ]
    ],
    "bike" => [
        "cancelled_reservation" => [
            "info" => "You are receiving this email because the cargo bike is no longer available.",
            "confirmed" => [
                "text" => "The reservation for **:b_name** in the period **:timeframe** was cancelled by **:u_name**.",
                "subject" => "Canceled reservation for :name"
            ],
            "unconfirmed" => [
                "text" => "The request for **:b_name** in period **:timeframe** was cancelled by **:u_name**.",
                "subject" => "Canceled request for :name"
            ]
        ]
    ],
    "user" => [
        "new_reservation" => [
            "text" => [
                "new" => "Thank you for your reservation of the cargo bike **:b_name** request for period **:timeframe**.\n\n If there are any uncertainties about pick up or return, please contact the renter. [contact us](:url_chat)",
                "update" => "Thank you for your edited reservation of the cargo bike **:b_name** request for period **:timeframe**.\n\n If there are any uncertainties about pickup or return, please contact the renter. [contact us](:url_chat)"
            ],
            "subject" => [
                "new" => "Your cargo bike reservation",
                "update" => "Your edited cargo bike reservation"
            ],
            "button" => "To the reservation"
        ],
        "cancelled_reservation" => [
            "confirmed" => [
                "text" => "Your reservation for **:b_name** in the period **:timeframe** has been cancelled.",
                "subject" => "Canceled reservation for :name"
            ],
            "unconfirmed" => [
                "text" => "The reservation of the **:b_name** in the period **:timeframe** is **fortunately not possible.**",
                "subject" => "Rejection of the request for :name"
            ]
        ],
        "confirmed_reservation" => [
            "text" => "Your request for **:b_name** in the period **:timeframe** has been confirmed. If there are any uncertainties regarding pick up or return, please contact the renter. [contact us](:url_chat)",
            "subject" => "Reservation confirmation for :name",
            "button" => "To the reservation"
        ]
    ],
    "notification" => [
        "hello" => "Hello!",
        "whoops" => "Ouch!",
        "salutation" => "Best regards,",
        "action_text" => "If you have problems clicking the \":actionText\" button, please copy the following URL into your web browser: [:actionURL](:actionURL)"
    ],
    "chat" => [
        "text" => ":name wrote a new message:\n\n\":message\"",
        "subject" => "New message in :chatTitle",
        "button" => "To the chat"
    ],
    "newsletter_confirmation" => [
        "text" => "Please confirm your subscription to the newsletter.",
        "subject" => "Confirm newsletter subscription",
        "button" => "Confirm registration"
    ],
    "all_rights_reserved" => "All rights reserved"
];
