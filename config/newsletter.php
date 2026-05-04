<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Newsletter Driver
    |--------------------------------------------------------------------------
    |
    | The driver to use to interact with newsletter service API.
    | You may use "log" or "null" to prevent calling the API directly
    | from your environment (useful for testing).
    |
    | Supported Drivers:
    | - Spatie\Newsletter\Drivers\MailChimpDriver::class (MailChimp)
    | - Spatie\Newsletter\Drivers\MailcoachDriver::class (Mailcoach)
    | - 'log' (writes to logs instead of sending)
    | - 'null' (does nothing)
    |
    | Controlled by: NEWSLETTER_DRIVER in .env file
    | Default: MailChimpDriver
    |
     */

    'driver' => env('NEWSLETTER_DRIVER', Spatie\Newsletter\Drivers\MailChimpDriver::class),

    /*
    |--------------------------------------------------------------------------
    | Driver Arguments
    |--------------------------------------------------------------------------
    |
    | Configuration arguments that will be passed to the newsletter driver.
    |
    | For MailChimp:
    | - api_key: Your MailChimp API key (get it from mailchimp.com/account/api)
    | - endpoint: MailChimp API endpoint (optional, defaults to us1.api.mailchimp.com)
    |
    | For Mailcoach:
    | - api_key: Your Mailcoach API key
    | - endpoint: Your Mailcoach installation URL
    |
    | Controlled by: NEWSLETTER_API_KEY and NEWSLETTER_ENDPOINT in .env file
    |
     */

    'driver_arguments' => [
        'api_key' => env('NEWSLETTER_API_KEY'),
        'endpoint' => env('NEWSLETTER_ENDPOINT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default List Name
    |--------------------------------------------------------------------------
    |
    | The default list name to use when no list name is specified in a method call.
    | This should match one of the keys in the 'lists' array below.
    |
    | Default: 'subscribers'
    |
     */

    'default_list_name' => 'subscribers',

    /*
    |--------------------------------------------------------------------------
    | Newsletter Lists
    |--------------------------------------------------------------------------
    |
    | Configure your newsletter lists/audiences here.
    | You can define multiple lists and reference them by their key name.
    |
    | Each list configuration requires:
    | - Key: A friendly identifier for the list (e.g., 'subscribers', 'customers')
    | - id: The actual list ID from your newsletter service
    |
    | For MailChimp: Use the List ID (find it in your MailChimp account settings)
    | For Mailcoach: Use the Email List UUID (displayed in Mailcoach UI)
    |
    | See: http://kb.mailchimp.com/lists/managing-subscribers/find-your-list-id
    |
    | Controlled by: NEWSLETTER_LIST_ID in .env file
    |
     */

    'lists' => [

        /*
         * The 'subscribers' list - main subscription list
         * You can add more lists by following the same pattern
         */
        'subscribers' => [
            'id' => env('NEWSLETTER_LIST_ID'),
        ],

        // Example of additional list:
        // 'customers' => [
        //     'id' => env('NEWSLETTER_CUSTOMERS_LIST_ID'),
        // ],

    ],

];



















