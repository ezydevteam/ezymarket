<?php

if (!function_exists('stepNumber')) {
    function stepNumber($number)
    {
        $steps = [
            'requirements' => 1,
            'permissions' => 2,
            'license' => 3,
            'database' => 4,
            'import' => 5,
            'complete' => 6,
        ];

        $step = $steps[request()->segment(2)];
        if ($step == $number) {
            return 'current';
        } elseif ($step > $number) {
            return 'active';
        }
    }
}

if (!function_exists('validate_purchase_code')) {
    function validate_purchase_code($purchaseCode, $alias)
    {
        try {
            $client = new Client();
            $url = config('system.license.api');
            $queryParams = http_build_query([
                'purchase_code' => $purchaseCode,
                'alias' => strtolower($alias),
                'website' => url('/'),
            ]);
            $res = $client->get($url . '?' . $queryParams);
            if ($res->getStatusCode() == 200) {
                return json_decode($res->getBody());
            }
            return false;
        } catch (RequestException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('licenseType')) {
    function licenseType($type = null)
    {
        if (!is_null($type)) {
            return config('system.license.type') == $type;
        }
        return config('system.license.type');
    }
}


if (!function_exists('get_license_type')) {
    function get_license_type($type = null)
    {
        if (!is_null($type)) {
            return config('system.license.type') == $type;
        }
        return config('system.license.type');
    }
}

if (!function_exists('translate_text')) {
    function translate_text($text)
    {
        return $text;
    }
}

if (!function_exists('check_extension_availability')) {
    function check_extension_availability()
    {
        $extensions = get_required_extensions();
        return array_map(function ($extension) {
            return extension_loaded($extension);
        }, array_column($extensions, 'name'));
    }
}

if (!function_exists('validate_file_permission')) {
    function validate_file_permission()
    {
        $permissions = get_required_permissions();
        return array_map(function ($permission) {
            return is_writable($permission['path']);
        }, $permissions);
    }
}

if (!function_exists('get_required_extensions')) {
    function get_required_extensions()
    {
        return [
            ['name' => 'bcmath'],
            ['name' => 'ctype'],
            ['name' => 'curl'],
            ['name' => 'dom'],
            ['name' => 'fileinfo'],
            ['name' => 'json'],
            ['name' => 'mbstring'],
            ['name' => 'openssl'],
            ['name' => 'pcre'],
            ['name' => 'PDO'],
            ['name' => 'pdo_mysql'],
            ['name' => 'tokenizer'],
            ['name' => 'xml'],
            ['name' => 'zip'],
        ];
    }
}

if (!function_exists('get_required_permissions')) {
    function get_required_permissions()
    {
        return [
            [
                'path' => base_path('storage/framework/'),
                'permission' => '0775',
            ],
            [
                'path' => base_path('storage/logs/'),
                'permission' => '0775',
            ],
            [
                'path' => base_path('bootstrap/cache/'),
                'permission' => '0775',
            ],
            [
                'path' => base_path('.env'),
                'permission' => '0775',
            ],
        ];
    }
}

if (!function_exists('setEnv')) {
    function setEnv($key, $value)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $env = file_get_contents($path);

            // If the key exists, replace its value
            if (preg_match("/^{$key}=.*/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
            } else {
                // If the key doesn't exist, append it
                $env .= PHP_EOL . "{$key}={$value}";
            }

            file_put_contents($path, $env);
        }
    }
}


















