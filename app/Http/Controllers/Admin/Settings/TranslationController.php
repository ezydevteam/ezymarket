<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Classes\Localization;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\Translate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Exception;

class TranslationController extends Controller
{
    private $langPath;
    private $defaultLanguage;

    public function __construct()
    {
        $this->langPath = base_path("lang/");
        $this->defaultLanguage = env('DEFAULT_LANGUAGE');
    }

    public function index()
    {
        $languages = Localization::all();
        return view('admin.settings.translation.index', ['languages' => $languages]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language.code' => ['required', 'string', 'max:3'],
            'language.direction' => ['required', 'string', 'in:ltr,rtl'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }

        $requestData = $request->except('_token');

        $languageCode = $requestData['language']['code'];

        if (!array_key_exists($languageCode, Localization::all())) {
            toastr()->error(translate('Invalid language name'));
            return back();
        }

        try {
            $update = Settings::updateSettings('language', $requestData['language']);
            if (!$update) {
                toastr()->error(translate('Updated Error'));
                return back();
            }

            if ($languageCode != $this->defaultLanguage) {
                $this->createNewLanguageFiles($languageCode);
            }

            setEnv('DEFAULT_LANGUAGE', @settings('language')->code);
            setEnv('DEFAULT_DIRECTION', @settings('language')->direction);

            Artisan::call('view:clear');

            toastr()->success(translate('Updated Successfully'));
            return back();
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back();
        }
    }

    public function translates()
    {
        $language = Localization::get($this->defaultLanguage);

        $translates = Translate::query();

        if (request()->filled('search')) {
            $searchTerm = '%' . request('search') . '%';
            $translates->where('key', 'like', $searchTerm)
                ->orWhere('value', 'like', $searchTerm);
        }

        $translates = $translates->orderbyDesc('id')->paginate(50)->appends(request()->query());

        return view('admin.settings.translation.translates', [
            'language' => $language,
            'translates' => $translates,
        ]);
    }

    public function translatesUpdate(Request $request)
    {
        // DEVELOPMENT VERSION: Allow editing both keys and values
        foreach ($request->translates as $id => $data) {
            $translate = Translate::where('id', $id)->first();
            if ($translate) {
                // Allow editing both key and value for development
                if (is_array($data)) {
                    if (isset($data['key'])) {
                        $translate->key = $data['key'];
                    }
                    if (isset($data['value'])) {
                        $translate->value = $data['value'];
                    }
                } else {
                    // Backward compatibility: if just string, update value
                    $translate->value = $data;
                }
                $translate->save();
            }
        }

        // PRODUCTION VERSION: Only allow editing values (uncomment to use in production)
        // foreach ($request->translates as $id => $value) {
        //     $translate = Translate::where('id', $id)->first();
        //     if ($translate) {
        //         $translate->value = $value;
        //         $translate->save();
        //     }
        // }

        toastr()->success(translate('Updated Successfully'));
        return back();
    }

    protected function createNewLanguageFiles($newLanguageCode)
    {
        try {
            $newLanguagePath = $this->langPath . $newLanguageCode;

            if (!File::exists($newLanguagePath)) {
                File::makeDirectory($newLanguagePath, 0755, true);

                $sourceLanguagePath = $this->langPath . $this->defaultLanguage;

                if (File::exists($sourceLanguagePath)) {
                    $this->copyLanguageFiles($sourceLanguagePath, $newLanguagePath);
                } else {
                    $this->createBasicLanguageFiles($newLanguagePath);
                }
            }

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function copyLanguageFiles($sourcePath, $destinationPath)
    {
        $files = File::allFiles($sourcePath);
        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $destinationFile = $destinationPath . '/' . $relativePath;

            $destinationDir = dirname($destinationFile);
            if (!File::exists($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true);
            }

            if (!File::exists($destinationFile)) {
                File::copy($file->getPathname(), $destinationFile);
            }
        }
    }

    private function createBasicLanguageFiles($languagePath)
    {
        // Create basic language files if no source exists
        $basicFiles = [
            'messages.php' => "<?php\n\nreturn [\n    // Add your translations here\n];\n",
            'validation.php' => "<?php\n\nreturn [\n    // Add validation translations here\n];\n",
        ];

        foreach ($basicFiles as $filename => $content) {
            $filePath = $languagePath . '/' . $filename;
            if (!File::exists($filePath)) {
                File::put($filePath, $content);
            }
        }
    }
}
