<?php

namespace Codebay\Installer\App\Http\Controllers;

use App\Models\Admin;
use App\Models\Settings;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InstallationController extends Controller
{
    public function redirectToInstaller()
    {
        return redirect()->route('installer.requirements');
    }

    public function showRequirements()
    {
        if (config('system.install.requirements')) {
            return redirect()->route('installer.permissions');
        }

        $error = false;
        if (in_array(false, $this->extensionsArray())) {
            $error = true;
        }

        return view('installer::requirements', [
            'extensions' => get_required_extensions(),
            'error' => $error,
        ]);
    }

    public function validateRequirements(Request $request)
    {
        if (in_array(false, $this->extensionsArray())) {
            return redirect()->route('installer.requirements');
        }

        if (!defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }

        Artisan::call('key:generate');
        setEnv('APP_ENV', 'production');
        setEnv('INSTALL_REQUIREMENTS', '1');
        config(['system.install.requirements' => true]);

        return redirect()->route('installer.permissions');
    }

    public function showPermissions()
    {
        if (config('system.install.file_permissions')) {
            return redirect()->route('installer.license');
        }

        if (!config('system.install.requirements')) {
            return redirect()->route('installer.requirements');
        }

        $error = false;
        if (in_array(false, $this->permissionsArray())) {
            $error = true;
        }

        return view('installer::permissions', ['permissions' => get_required_permissions(), 'error' => $error]);
    }

    public function validatePermissions(Request $request)
    {
        if (in_array(false, $this->permissionsArray())) {
            return redirect()->route('installer.permissions');
        }

        setEnv('INSTALL_FILE_PERMISSIONS', '1');
        config(['system.install.file_permissions' => true]);
        return redirect()->route('installer.license');
    }

    public function showLicense()
    {
        if (config('system.install.license')) {
            return redirect()->route('installer.database');
        }

        if (!config('system.install.file_permissions')) {
            return redirect()->route('installer.requirements');
        }

        return view('installer::license');
    }

    public function validateLicense(Request $request)
    {
        setEnv('INSTALL_LICENSE', '1');
        config(['system.install.license' => true]);
        return redirect()->route('installer.database');
    }

    public function showDatabaseForm()
    {
        if (config('system.install.database_info')) {
            return redirect()->route('installer.database.import');
        }

        if (!config('system.install.license')) {
            return redirect()->route('installer.license');
        }

        return view('installer::database.details');
    }

    public function validateDatabaseConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db_host' => ['required', 'string'],
            'db_name' => ['required', 'string', 'regex:/^[a-zA-Z0-9_]+$/'],
            'db_user' => ['required', 'string'],
            'db_pass' => ['nullable', 'string'],
        ], [
            'db_name.regex' => translate_text('Database name can only contain letters, numbers, and underscores'),
        ]);

        if ($validator->fails()) {
            return redirect()->route('installer.database')->withErrors($validator->errors());
        }

        // Store original database connection
        $originalConfig = config('database.connections.mysql');

        // Configure new connection
        config([
            'database.connections.mysql.host' => $request->db_host,
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.database' => $request->db_name,
            'database.connections.mysql.username' => $request->db_user,
            'database.connections.mysql.password' => $request->db_pass,
        ]);

        DB::purge('mysql');

        try {
            // Test the connection
            $pdo = DB::connection()->getPdo();

            // Save database credentials and installation state to .env
            setEnv('DB_HOST', $request->db_host);
            setEnv('DB_PORT', '3306');
            setEnv('DB_DATABASE', $request->db_name);
            setEnv('DB_USERNAME', $request->db_user);
            setEnv('DB_PASSWORD', $request->db_pass);
            setEnv('INSTALL_DATABASE_INFO', '1');

            // Update the system config for current request
            config(['system.install.database_info' => true]);

            // Clear config cache and reconnect database
            if (!defined('STDIN')) {
                define('STDIN', fopen('php://stdin', 'r'));
            }
            Artisan::call('config:clear');
            DB::reconnect('mysql');

            // Redirect to import page
            return redirect()->route('installer.database.import');
        } catch (Exception $e) {
            // Restore original connection
            config(['database.connections.mysql' => $originalConfig]);
            DB::purge('mysql');

            return redirect()->route('installer.database')->withErrors([translate_text('Could not connect to the database. Please check your credentials and ensure the database exists. Error: ') . $e->getMessage()]);
        }
    }

    public function showDatabaseImport()
    {
        if (config('system.install.database_import')) {
            return redirect()->route('installer.complete');
        }

        if (!config('system.install.database_info')) {
            return redirect()->route('installer.database');
        }

        return view('installer::database.import');
    }

    public function importDatabase()
    {
        try {
            $sqlFile = base_path('codebay/demo/main.sql');
            if (!file_exists($sqlFile)) {
                throw new Exception('Database SQL file not found');
            }

            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 0);

            // Disable checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            $pdo->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
            $pdo->exec('SET AUTOCOMMIT=0');
            $pdo->exec('START TRANSACTION');

            // Execute SQL file
            $sql = file_get_contents($sqlFile);
            $pdo->exec($sql);

            // Commit and restore
            $pdo->exec('COMMIT');
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            $pdo->exec('SET AUTOCOMMIT=1');

            setEnv('INSTALL_DATABASE_IMPORT', '1');
            config(['system.install.database_import' => true]);

            return response()->json(['success' => true, 'redirect' => route('installer.complete')]);
        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->exec('ROLLBACK');
                $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            }
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    public function downloadDatabase()
    {
        try {
            $sqlFile = base_path('codebay/demo/main.sql');

            if (!file_exists($sqlFile)) {
                throw new Exception('Database SQL file not found');
            }

            return response()->download($sqlFile, 'main-demo.sql');
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    public function skipDatabaseImport()
    {
        setEnv('INSTALL_DATABASE_IMPORT', 1);
        config(['system.install.database_import' => true]);

        return redirect()->route('installer.complete');
    }

    public function showComplete()
    {
        if (!config('system.install.database_import')) {
            return redirect()->route('installer.database');
        }

        return view('installer::complete');
    }

    public function finishInstallation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'website_name' => ['required', 'string', 'max:200'],
            'website_url' => ['required', 'url'],
            'admin_path' => ['required', 'string', 'alpha_num'],
            'editor_path' => ['required', 'string', 'alpha_num'],
            'username' => ['required', 'string', 'min:5', 'max:50', 'alpha_dash', 'unique:admins'],
            'email' => ['required', 'string', 'email', 'unique:admins'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (str_contains($request->website_url, '#')) {
            return redirect()->back()->withErrors([translate_text('Website URL cannot contain a hashtag #')])->withInput();
        }

        $admin = Admin::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'admin', // Set as main admin
        ]);

        if ($admin) {
            $generalSettings = Settings::selectSettings('general');
            $generalSettings->site_name = $request->website_name;
            $generalSettings->site_url = $request->website_url;
            $update = Settings::updateSettings('general', $generalSettings);
            if ($update) {
                setEnv('APP_NAME', Str::slug($request->website_name, '_'));
                setEnv('APP_URL', $request->website_url);
                setEnv('SYSTEM_ADMIN_PATH', $request->admin_path, true);
                setEnv('SYSTEM_editor_path', $request->editor_path, true);
                setEnv('INSTALL_COMPLETE', 1);
                return redirect()->route('admin.login');
            } else {
                return redirect()->back()->withErrors([translate_text('Failed to update general settings')])->withInput();
            }
        }
    }

    public function returnToImport()
    {
        setEnv('INSTALL_DATABASE_IMPORT', '');
        return redirect()->route('installer.database.import');
    }

    private function extensionsArray()
    {
        return check_extension_availability();
    }

    private function permissionsArray()
    {
        return validate_file_permission();
    }
}


















