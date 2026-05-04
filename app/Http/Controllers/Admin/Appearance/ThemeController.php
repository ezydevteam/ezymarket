<?php

namespace App\Http\Controllers\Admin\Appearance;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Illuminate\Support\Facades\{Artisan, DB, File, Validator};
use Illuminate\Support\Str;
use Illuminate\View\View;
use ZipArchive;
use Exception;

/**
 * Class ThemeController
 * @package App\Http\Controllers\Admin\Appearance
 */
class ThemeController extends Controller
{
    /**
     * Display a listing of themes.
     *
     * @return View
     */
    public function index(): View
    {
        $themes = Theme::all();
        return view('admin.appearance.themes.index', compact('themes'));
    }

    /**
     * Upload and install a new theme.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function upload(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'purchase_code' => ['required', 'string'],
            'theme_files' => ['required', 'mimes:zip'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }

        if (!class_exists('ZipArchive')) {
            toastr()->error(translate('ZipArchive extension is not enabled'));
            return back();
        }

        if (!preg_match("/^([a-f0-9]{8})-(([a-f0-9]{4})-){3}([a-f0-9]{12})$/i", $request->purchase_code)) {
            if (!preg_match("/^([d-u0-9]{10})-(([d-u0-9]{5})-){3}([d-u0-9]{10})$/i", $request->purchase_code)) {
                toastr()->error(translate('Invalid purchase code'));
                return back();
            }
        }

        try {

            $themeZipFile = storageFileUpload($request->file('theme_files'), 'temp/', 'local');
            $themeUploadPath = storage_path("app/{$themeZipFile}");

            $tempFolder = md5(Str::random(10) . time());
            $themeTempPath = storage_path("app/temp/{$tempFolder}");

            if (File::exists($themeTempPath)) {
                removeDirectory($themeTempPath);
            }
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back();
        }

        try {

            $zip = new ZipArchive;
            $res = $zip->open($themeUploadPath);
            if ($res != true) {
                throw new Exception(translate('Could not open the theme zip file'));
            }

            $res = $zip->extractTo($themeTempPath);
            if ($res == true) {
                removeFile($themeUploadPath);
            }

            $zip->close();

            $configFile = "{$themeTempPath}/config.json";
            if (!File::exists($configFile)) {
                throw new Exception(translate('Theme Config is missing'));
            }

            $config = json_decode(File::get($configFile), true);

            if ($config['type'] != "theme") {
                throw new Exception(translate('Invalid theme files'));
            }

            $response = validate_purchase_code($request->purchase_code, $config['alias']);

            if (isset($response->status)) {
                if ($response->status == "error") {
                    throw new Exception($response->message);
                }
            } else {
                throw new Exception(translate('Failed to validate the purchase code'));
            }

            $scriptAlias = $config['script']['alias'];
            $scriptVersion = $config['script']['version'];

            if (strtolower(config('system.product.alias')) != strtolower($scriptAlias)) {
                throw new Exception(translate('Invalid action request'));
            }

            if (config('system.product.version') < $scriptVersion) {
                throw new Exception(str_replace(
                    translate("The {theme_name} theme require {script_alias} version {script_version} or above"),
                    ['{theme_name}', '{script_alias}', '{script_version}'],
                    [$config['name'], $scriptAlias, $scriptVersion]
                ));
            }

            $isUpdate = $config['update'];
            $themeExists = Theme::where('alias', $config['alias'])->first();
            if (!$isUpdate) {
                if ($themeExists) {
                    throw new Exception(translate('The :theme_name theme is already exists.', ['theme_name' => $config['name']]));
                }
            } else {
                if (!$themeExists) {
                    throw new Exception(translate('The {theme_name} is not exists to make the update.', ['theme_name' => $config['name']]));
                }
            }

            if (!empty($config['remove'])) {
                $removeDirectories = $config['remove']['directories'];
                if (!empty($removeDirectories)) {
                    foreach ($removeDirectories as $removeDirectory) {
                        removeDirectory(base_path($removeDirectory));
                    }
                }
                $removeFiles = $config['remove']['files'] ?? [];
                if (!empty($removeFiles)) {
                    foreach ($removeFiles as $rmvFile) {
                        removeFile(base_path($rmvFile));
                    }
                }
            }

            DB::beginTransaction();

            if (!empty($config['create'])) {
                $createDirectories = $config['create']['directories'] ?? [];
                if (!empty($createDirectories)) {
                    foreach ($createDirectories as $createDirectory) {
                        makeDirectory(base_path($createDirectory));
                    }
                }
            }

            if (!empty($config['copy'])) {
                $copyDirectories = $config['copy']['directories'] ?? [];
                if (!empty($copyDirectories)) {
                    foreach ($copyDirectories as $copyDirectory) {
                        File::copyDirectory("{$themeTempPath}/{$copyDirectory}", base_path($copyDirectory));
                    }
                }
                $copyFiles = $config['copy']['files'] ?? [];
                if (!empty($copyFiles)) {
                    foreach ($copyFiles as $copyFile) {
                        File::copy("{$themeTempPath}/{$copyFile}", base_path($copyFile));
                    }
                }
            }

            if (!empty($config['database'])) {
                $databaseFiles = $config['database']['files'] ?? [];
                if (!empty($databaseFiles)) {
                    foreach ($databaseFiles as $databaseFile) {
                        $databaseFile = "{$themeTempPath}/{$databaseFile}";
                        if (File::exists($databaseFile)) {
                            $unprepared = DB::unprepared(File::get($databaseFile));
                            if (!$unprepared) {
                                throw new Exception(translate("Cannot unprepare the database file"));
                            }
                        }
                    }
                }
            }

            Theme::updateOrCreate(['alias' => $config['alias']], [
                'name' => $config['name'],
                'version' => $config['version'],
                'thumbnail' => $config['thumbnail'] ?? $config['preview_image'] ?? null,
                'description' => $config['description'],
            ]);

            DB::commit();

            removeDirectory($themeTempPath);
            toastr()->success(translate('Theme uploaded successfully'));
            return back();
        } catch (Exception $e) {
            DB::rollBack();
            if (isset($themeUploadPath)) {
                removeFile($themeUploadPath);
            }
            if (isset($themeTempPath)) {
                removeDirectory($themeTempPath);
            }
            toastr()->error($e->getMessage());
            return back();
        }
    }

    /**
     * Activate a theme.
     *
     * @param Request $request
     * @param Theme $theme
     * @return RedirectResponse
     */
    public function makeActive(Request $request, Theme $theme): RedirectResponse
    {
        setEnv('DEFAULT_THEME', $theme->alias);
        Artisan::call('optimize:clear');
        toastr()->success(translate('Theme has been changed Successfully'));
        return back();
    }

    /**
     * Show theme settings.
     *
     * @param Request $request
     * @param Theme $theme
     * @param string $group
     * @return View
     */
    public function showSettings(Request $request, Theme $theme, string $group = 'general'): View
    {
        $themeSettingsFile = theme_resource_path("settings.json");
        if (!File::exists($themeSettingsFile)) {
            abort(500, translate('Settings file is missing'));
        }

        $themeSettings = json_decode(File::get($themeSettingsFile));

        abort_if(!isset($themeSettings->$group), 404);

        $themeSettingsGroups = collect($themeSettings)->keys();
        $themeSettingsCollection = collect($themeSettings->$group);

        // Group menus by their 'menu' metadata
        $menus = ['root' => []];
        foreach ($themeSettings as $groupKey => $fields) {
            $parent = null;
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if (isset($field->menu)) {
                        $parent = $field->menu;
                        break;
                    }
                }
            }

            if ($parent) {
                $menus[$parent][] = $groupKey;
            } else {
                $menus['root'][] = $groupKey;
            }
        }

        return view('admin.appearance.themes.settings', [
            'theme' => $theme,
            'activeGroup' => $group,
            'themeSettingsGroups' => $themeSettingsGroups,
            'themeSettingsCollection' => $themeSettingsCollection,
            'menus' => $menus,
        ]);
    }

    /**
     * Update theme settings.
     *
     * @param Request $request
     * @param Theme $theme
     * @param string $group
     * @return JsonResponse
     */
    public function updateSettings(Request $request, Theme $theme, string $group = 'general'): JsonResponse
    {
        $themeSettingsFile = theme_resource_path("settings.json");
        if (!File::exists($themeSettingsFile)) {
            return response()->json(['success' => false, 'message' => translate('Settings file is missing')], 404);
        }

        $themeSettings = json_decode(File::get($themeSettingsFile));
        if (!isset($themeSettings->$group)) {
            return response()->json(['success' => false, 'message' => translate('Settings group not found')], 404);
        }

        try {
            $validationRules = [];
            $settingKeys = [];
            foreach ($themeSettings->$group as $setting) {
                if (isset($setting->key, $setting->rule)) {
                    $validationRules[$setting->key] = $setting->rule;
                    $settingKeys[] = $setting->key;
                }
            }

            $requestData = $request->only($settingKeys);
            $validator = Validator::make($requestData, $validationRules);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            foreach ($themeSettings->$group as $setting) {
                if (in_array($setting->field, ['checkbox', 'toggle'])) {
                    $requestData[$setting->key] = (int) $request->input($setting->key, 0);
                } elseif (in_array($setting->field, ['select', 'bootstrap-select', 'radios'])) {
                    if (!array_key_exists($request->input($setting->key), (array) $setting->options)) {
                        return response()->json(['success' => false, 'message' => translate('Failed to update :label', ['label' => $setting->label])], 422);
                    }
                } elseif ($setting->field === 'icon-radios') {
                    $validValues = collect($setting->options)->pluck('value')->toArray();
                    if (!in_array($request->input($setting->key), $validValues)) {
                        return response()->json(['success' => false, 'message' => translate('Failed to update :label', ['label' => $setting->label])], 422);
                    }
                } elseif ($setting->field == "image") {
                    if ($request->has($setting->key)) {
                        $size = $setting->size ?? null;
                        $name = $setting->name ?? null;
                        $requestData[$setting->key] = imageUpload($request->file($setting->key), "themes/{$theme->alias}/{$setting->path}", $size, $name, $setting->value);
                    }
                }
            }

            foreach ($themeSettings->$group as &$setting) {
                if (isset($setting->key) && array_key_exists($setting->key, $requestData)) {
                    $setting->value = $requestData[$setting->key];
                }
            }

            File::put($themeSettingsFile, json_encode($themeSettings, JSON_PRETTY_PRINT));
            $this->updateThemeColors($theme, $themeSettings->colors);

            return response()->json(['success' => true, 'message' => translate('Theme Settings Updated Successfully')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update theme root colors.
     *
     * @param Theme $theme
     * @param array|object $themeColors
     * @return bool
     */
    public function updateThemeColors(Theme $theme, $themeColors): bool
    {
        $output = ':root {' . PHP_EOL;
        foreach ($themeColors as &$color) {
            $output .= '  --' . $color->key . ': ' . $color->value . ';' . PHP_EOL;
        }
        $output .= '}' . PHP_EOL;
        $colorsPath = config('theme.style.colors');
        $colorsFile = theme_public_path("{$colorsPath}");
        return File::put($colorsFile, $output);
    }

    /**
     * Show custom CSS settings.
     *
     * @param Theme $theme
     * @return View
     */
    public function showCustomCss(Theme $theme): View
    {
        $customCssPath = config('theme.style.custom_css');
        $customCssFile = theme_public_path("{$customCssPath}");
        if (!File::exists($customCssFile)) {
            File::put($customCssFile, '');
        }
        $themeCustomCssFile = File::get($customCssFile);
        return view('admin.appearance.themes.custom-css', [
            'theme' => $theme,
            'themeCustomCssFile' => $themeCustomCssFile,
        ]);
    }

    /**
     * Update custom CSS.
     *
     * @param Request $request
     * @param Theme $theme
     * @return RedirectResponse
     */
    public function updateCustomCss(Request $request, Theme $theme): RedirectResponse
    {
        $customCssPath = config('theme.style.custom_css');
        $customCssFile = theme_public_path("{$customCssPath}");
        if (!File::exists($customCssFile)) {
            File::put($customCssFile, '');
        }
        File::put($customCssFile, $request->custom_css);
        toastr()->success(translate('Updated Successfully'));
        return back();
    }
}
