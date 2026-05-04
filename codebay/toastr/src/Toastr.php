<?php

namespace Toastr;

use Illuminate\Support\Facades\Session;

class Toastr
{
    /**
     * Display an info toast message.
     *
     * @param  string  $message
     * @param  string|null  $title
     * @return void
     */
    public static function smartInfo(string $message, string $title = null)
    {
        self::message('info', $message, $title);
    }

    /**
     * Display a warning toast message.
     *
     * @param  string  $message
     * @param  string|null  $title
     * @return void
     */
    public static function smartWarning(string $message, string $title = null)
    {
        self::message('warning', $message, $title);
    }

    /**
     * Display a success toast message.
     *
     * @param  string  $message
     * @param  string|null  $title
     * @return void
     */
    public static function smartSuccess(string $message, string $title = null)
    {
        self::message('success', $message, $title);
    }

    /**
     * Display an error toast message.
     *
     * @param  string  $message
     * @param  string|null  $title
     * @return void
     */
    public static function smartError(string $message, string $title = null)
    {
        self::message('error', $message, $title);
    }

    /**
     * Get the Toastr options as a JavaScript string.
     *
     * @return string
     */
    public function smartOptions()
    {
        return 'toastr.options=' . json_encode(config('toastr.options')) . ';';
    }

    /**
     * Get the Toastr notifications as a JavaScript string.
     *
     * @return string|null
     */
    public function smartNotificationsAsString()
    {
        $toast = null;
        if (Session::has('toastr')) {
            $toastrs = Session::get('toastr');
            foreach ($toastrs as $toastr) {
                if ($toastr['title']) {
                    $toast .= "toastr.{$toastr['type']}('{$toastr['message']}', '{$toastr['title']}');";
                } else {
                    $toast .= "toastr.{$toastr['type']}('{$toastr['message']}');";
                }
            }
        }
        return $toast;
    }

    /**
     * Get the HTML script tag to render Toastr options and notifications.
     *
     * @return string
     */
    public function smartRender()
    {
        $output = '<script type="text/javascript">' . $this->smartOptions() . $this->smartNotificationsAsString() . '</script>';

        // Clear toastr messages from session after rendering
        Session::forget('toastr');

        return $output;
    }

    /**
     * Get the HTML script tag to render Laravel's default errors and status as Toastr notifications.
     *
     * @return string
     */
    public function smartRenderFlush()
    {
        $toast = '';

        // Safely check for Laravel validation errors in session
        $errors = session('errors');
        if ($errors && method_exists($errors, 'any') && $errors->any()) {
            foreach ($errors->all() as $error) {
                $toast .= "toastr.error(" . json_encode($error) . ");";
            }
        }

        // Handle Laravel default status messages (e.g., password reset confirmation)
        if (session('status')) {
            $toast .= "toastr.success(" . json_encode(session('status')) . ");";
        }

        // Handle Laravel email verification resent status
        if (session('resent')) {
            $msg = function_exists('translate')
                ? translate('Verification code has been resent Successfully!')
                : 'Verification code has been resent Successfully!';
            $toast .= "toastr.success(" . json_encode($msg) . ");";
        }

        return $toast ? '<script type="text/javascript">' . $toast . '</script>' : '';
    }

    /**
     * Get the HTML link tag to include Toastr CSS styles.
     *
     * @return string
     */
    public function smartStyles($version = 'latest')
    {
        return '<link rel="stylesheet" href="' . asset('vendor/libs/codebay/toastr/css/toastr.min.css') . '">';
    }

    /**
     * Get the HTML script tag to include Toastr JavaScript.
     *
     * @return string
     */
    public function smartScripts($version = 'latest')
    {
        return '<script src="' . asset('vendor/libs/codebay/toastr/js/toastr.min.js') . '"></script>';
    }

    /**
     * Display an info toast message (legacy method - use smartInfo instead).
     *
     * @param  string  $message
     * @param  string|null  $title
     * @return void
     */
    public static function info(string $message, string $title = null)
    {
        self::smartInfo($message, $title);
    }

    /**
     * Display a warning toast message (legacy method - use smartWarning instead).
     *
     * @param  string  $message
     * @param  string|null  $title
     * @return void
     */
    public static function warning(string $message, string $title = null)
    {
        self::smartWarning($message, $title);
    }

    /**
     * Display a success toast message (legacy method - use smartSuccess instead).
     *
     * @param  string  $message
     * @param  string|null  $title
     * @return void
     */
    public static function success(string $message, string $title = null)
    {
        self::smartSuccess($message, $title);
    }

    /**
     * Display an error toast message (legacy method - use smartError instead).
     *
     * @param  string  $message
     * @param  string|null  $title
     * @return void
     */
    public static function error(string $message, string $title = null)
    {
        self::smartError($message, $title);
    }

    /**
     * Get the Toastr options as a JavaScript string (legacy method - use smartOptions instead).
     *
     * @return string
     */
    public function options()
    {
        return $this->smartOptions();
    }

    /**
     * Get the Toastr notifications as a JavaScript string (legacy method - use smartNotificationsAsString instead).
     *
     * @return string|null
     */
    public function notificationsAsString()
    {
        return $this->smartNotificationsAsString();
    }

    /**
     * Get the HTML script tag to render Toastr options and notifications (legacy method - use smartRender instead).
     *
     * @return string
     */
    public function render()
    {
        return $this->smartRender();
    }

    /**
     * Get the HTML link tag to include Toastr CSS styles (legacy method - use smartStyles instead).
     *
     * @return string
     */
    public function styles($version = 'latest')
    {
        return $this->smartStyles($version);
    }

    /**
     * Get the HTML script tag to include Toastr JavaScript (legacy method - use smartScripts instead).
     *
     * @return string
     */
    public function scripts($version = 'latest')
    {
        return $this->smartScripts($version);
    }

    /**
     * Flash a Toastr message to the session.
     *
     * @param  string  $type
     * @param  string  $message
     * @param  string|null  $title
     * @return void
     */

    private static function message(string $type, string $message, string $title = null)
    {
        $toastr = Session::get('toastr', []);

        $toastr[] = [
            'type' => $type,
            'title' => $title,
            'message' => addslashes($message),
        ];

        Session::put('toastr', $toastr);
        Session::save(); // Force immediate save to ensure persistence through redirects
    }
}










