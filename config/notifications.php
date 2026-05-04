<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Preference Groups
    |--------------------------------------------------------------------------
    |
    | Defines the groups of notifications that users can toggle. 
    | By consolidating the events, we give users a clean UX with just
    | a single toggle per category, while mapping it directly to the 
    | backend events seamlessly.
    |
    */

    'groups' => [
        'account_security' => [
            'label' => 'Account & Security',
            'desc'  => 'Security alerts, login attempts, ID verification updates, and password changes',
            'events' => [
                'password_reset', 'password_update', 'email_update', 'email_verification', 
                'login_success', 'login_attempt', 'id_verification_status_update', 'become_seller'
            ]
        ],
        
        'purchases_transactions' => [
            'label' => 'Purchases & Transactions',
            'desc'  => 'Order confirmations, payments, download readiness, and transaction cancellations',
            'events' => [
                'payment_confirmation', 'purchase_confirmation', 'transaction_cancelled', 'download_ready'
            ]
        ],

        'products' => [
            'label' => 'Products',
            'desc'  => 'Updates on products, approvals, rejections, lifecycle changes, and discounts',
            'events' => [
                'product_sold', 'product_approved', 'product_rejection', 
                'product_update_approved', 'product_update_rejected', 'new_product', 
                'product_update', 'product_discount', 'product_changelog', 'product_favorite'
            ]
        ],

        'earnings_payouts' => [
            'label' => 'Earnings & Payouts',
            'desc'  => 'Sales earnings, withdrawal submissions, and payout status updates',
            'events' => [
                'sales_earning', 'withdrawal_method_update', 'withdrawal_submitted', 
                'withdrawal_returned', 'withdrawal_completed', 'withdrawal_cancelled', 'withdrawal_approved'
            ]
        ],

        'interactions_community' => [
            'label' => 'Interactions & Community',
            'desc'  => 'New followers, earned badges, comments, and reviews',
            'events' => [
                'new_follower', 'new_badge', 'product_comment', 'product_comment_reply', 
                'product_review', 'product_review_reply'
            ]
        ],

        'support_refunds' => [
            'label' => 'Support & Refunds',
            'desc'  => 'Support ticket updates, responses, and refund requests',
            'events' => [
                'new_support_ticket', 'support_ticket_response', 'support_ticket_status', 
                'refund_request', 'refund_reply', 'refund_status'
            ]
        ],

        'premium_features' => [
            'label' => 'Premium Features',
            'desc'  => 'Premium plan expirations and renewals',
            'events' => [
                'premium_about_to_expire', 'premium_expired'
            ]
        ]
    ]
];
