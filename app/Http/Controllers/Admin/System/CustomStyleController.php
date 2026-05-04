<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

/**
 * Admin Style Controller
 *
 * Handles custom styling for the admin panel including colors, fonts, and custom CSS.
 */
class CustomStyleController extends Controller
{
    use HandlesValidation;

    /**
     * Display the admin style customization page.
     *
     * @return View
     */
    public function index(): View
    {
        $customCssFile = public_path(config('system.admin.custom_css'));

        if (!File::exists($customCssFile)) {
            File::put($customCssFile, '');
        }

        $customCssFile = File::get($customCssFile);
        $googleFonts = $this->getAvailableGoogleFonts();
        $customFonts = $this->getUploadedCustomFonts();

        $systemAdmin = Settings::where('key', 'system_admin')->first();
        $colors = [];
        $fonts = [];

        if ($systemAdmin?->value) {
            $value = json_decode(json_encode($systemAdmin->value), true);
            $colors = $value['colors'] ?? [];
            $fonts = $value['fonts'] ?? [];
        }

        return view('admin.system.custom-style', compact('colors', 'fonts', 'customCssFile', 'googleFonts', 'customFonts'));
    }

    /**
     * Update admin panel styles.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        // Custom validation for font file
        if ($request->hasFile('custom_font_file')) {
            $file = $request->file('custom_font_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['ttf', 'otf', 'woff', 'woff2'];

            if (!in_array($extension, $allowedExtensions)) {
                return $this->errorBack('The font file must be a file of type: ttf, otf, woff, woff2.');
            }

            if ($file->getSize() > 5120 * 1024) {
                return $this->errorBack('The font file must not be greater than 5MB.');
            }
        }

        $validator = $this->validateRequestWithoutInput($request, [
            'system_admin.colors.*' => 'required|regex:/^#[A-Fa-f0-9]{6}$/',
        ]);

        if ($validator instanceof RedirectResponse) {
            return $validator;
        }

        // Get existing system_admin settings
        $settings = Settings::first();
        $existingSystemAdmin = $settings?->system_admin ?? [];
        $existingSystemAdmin = json_decode(json_encode($existingSystemAdmin), true);

        $requestData = $request->except(['_token', 'custom_css', 'custom_font_file', 'delete_custom_font', 'delete_custom_font_file']);

        // Handle delete custom font file
        if ($request->filled('delete_custom_font_file')) {
            return $this->handleDeleteCustomFont($request->input('delete_custom_font_file'), $existingSystemAdmin);
        }

        // Handle custom font file upload (new font upload to directory)
        if ($request->hasFile('custom_font_file')) {
            $fontFile = $request->file('custom_font_file');
            $originalName = pathinfo($fontFile->getClientOriginalName(), PATHINFO_FILENAME);
            fileUpload($fontFile, 'vendor/admin/fonts/', $originalName);
            return $this->successBack('Font uploaded successfully');
        }

        // Restructure fonts data to match DB structure
        if (isset($requestData['system_admin'])) {
            // Get existing fonts to preserve data
            $existingFonts = $existingSystemAdmin['fonts'] ?? [];
            $fonts = $existingFonts;

            // Parse unified font selection (format: "type:value" or "system")
            $selectedFont = $request->input('system_admin.selected_font');

            if ($selectedFont === 'system' || empty($selectedFont)) {
                // System Default
                $fonts['selected_type'] = 'system';
                $fonts['google_font'] = '';
                $fonts['custom_font'] = [];
            } elseif (str_starts_with($selectedFont, 'google:')) {
                // Google Font selected
                $fonts['selected_type'] = 'google';
                $fonts['google_font'] = str_replace('google:', '', $selectedFont);
                $fonts['custom_font'] = [];
            } elseif (str_starts_with($selectedFont, 'custom:')) {
                // Custom Font selected
                $fonts['selected_type'] = 'custom';
                $customFontFile = str_replace('custom:', '', $selectedFont);
                $fonts['custom_font']['file'] = $customFontFile;
                $fonts['custom_font']['name'] = pathinfo($customFontFile, PATHINFO_FILENAME);
                $fonts['google_font'] = '';
            }

            // Remove old structure
            unset($requestData['system_admin']['selected_font']);

            // Set fonts structure
            $requestData['system_admin']['fonts'] = $fonts;
        }

        // Update settings - no merge needed, Settings model handles direct replacement
        foreach ($requestData as $key => $value) {
            $update = Settings::updateSettings($key, $value);
            if (!$update) {
                return $this->errorBack('Updated Error');
            }
        }

        $this->updateAdminColors($requestData['system_admin']['colors'] ?? []);
        $this->updateAdminCustomCss($request->custom_css);
        $this->updateAdminFonts($requestData['system_admin']['fonts'] ?? []);

        return $this->updatedBack();
    }

    /**
     * Update admin panel colors CSS file.
     *
     * @param array $colors
     * @return void
     */
    private function updateAdminColors(array $colors): void
    {
        $output = ':root {' . PHP_EOL;
        foreach ($colors as $key => $value) {
            $cssVarName = str_replace('_', '-', $key);
            $output .= '  --' . $cssVarName . ': ' . $value . ';' . PHP_EOL;
        }
        $output .= '}' . PHP_EOL;
        $colorsFile = public_path(config('system.admin.colors'));
        if (!File::exists($colorsFile)) {
            File::put($colorsFile, '');
        }
        File::put($colorsFile, $output);
    }

    /**
     * Update admin panel custom CSS file.
     *
     * @param string|null $content
     * @return void
     */
    private function updateAdminCustomCss(?string $content): void
    {
        $customCssFile = public_path(config('system.admin.custom_css'));
        if (!File::exists($customCssFile)) {
            File::put($customCssFile, '');
        }
        File::put($customCssFile, $content ?? '');
    }

    /**
     * Update admin panel fonts CSS file.
     *
     * @param array $fonts
     * @return void
     */
    private function updateAdminFonts(array $fonts): void
    {
        $output = '';

        // Default system font stack
        $systemFontStack = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';

        // Check which font type is selected
        $selectedType = $fonts['selected_type'] ?? 'system';

        if ($selectedType === 'custom' && !empty($fonts['custom_font']['file'])) {
            // Custom Font Selected
            $fontPath = asset($fonts['custom_font']['file']);
            $fontFileName = basename($fonts['custom_font']['file']);
            $fontName = $fonts['custom_font']['name'] ?? pathinfo($fontFileName, PATHINFO_FILENAME);
            $fontExtension = pathinfo($fonts['custom_font']['file'], PATHINFO_EXTENSION);

            // Determine font format
            $fontFormat = match($fontExtension) {
                'ttf' => 'truetype',
                'otf' => 'opentype',
                'woff' => 'woff',
                'woff2' => 'woff2',
                default => 'truetype',
            };

            $output .= '@font-face {' . PHP_EOL;
            $output .= '  font-family: \'' . $fontName . '\';' . PHP_EOL;
            $output .= '  src: url(\'' . $fontPath . '\') format(\'' . $fontFormat . '\');' . PHP_EOL;
            $output .= '  font-weight: normal;' . PHP_EOL;
            $output .= '  font-style: normal;' . PHP_EOL;
            $output .= '  font-display: swap;' . PHP_EOL;
            $output .= '}' . PHP_EOL . PHP_EOL;

            // Apply custom font to body with system fallback
            $output .= 'body {' . PHP_EOL;
            $output .= '  font-family: \'' . $fontName . '\', ' . $systemFontStack . ';' . PHP_EOL;
            $output .= '}' . PHP_EOL;
        } elseif ($selectedType === 'google' && !empty($fonts['google_font'])) {
            // Google Font Selected
            $fontName = $fonts['google_font'];
            $fontUrl = 'https://fonts.googleapis.com/css2?family=' . str_replace(' ', '+', $fontName) . ':wght@400;500;600;700&display=swap';
            $output .= '@import url("' . $fontUrl . '");' . PHP_EOL . PHP_EOL;
            $output .= 'body {' . PHP_EOL;
            $output .= '  font-family: \'' . $fontName . '\', ' . $systemFontStack . ';' . PHP_EOL;
            $output .= '}' . PHP_EOL;
        } else {
            // System Default Font
            $output .= 'body {' . PHP_EOL;
            $output .= '  font-family: ' . $systemFontStack . ';' . PHP_EOL;
            $output .= '}' . PHP_EOL;
        }

        // Write to fonts CSS file
        $fontsFile = public_path(config('system.admin.fonts', 'vendor/admin/css/fonts.css'));

        // Create directory if it doesn't exist
        $directory = dirname($fontsFile);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($fontsFile, $output);
    }

    /**
     * Get list of available Google Fonts.
     *
     * @return array<int, string>
     */
    private function getAvailableGoogleFonts(): array
    {
        return [
            'Roboto',
            'Open Sans',
            'Lato',
            'Montserrat',
            'Poppins',
            'Raleway',
            'Nunito',
            'Playfair Display',
            'Oswald',
            'Merriweather',
            'Ubuntu',
            'Mukta',
            'Inter',
            'Work Sans',
            'Quicksand',
            'Rubik',
            'Karla',
            'Source Sans Pro',
            'Noto Sans',
            'PT Sans'
        ];
    }

    /**
     * Get list of uploaded custom fonts from the fonts directory.
     *
     * @return array<int, array{file: string, name: string, display: string}>
     */
    private function getUploadedCustomFonts(): array
    {
        $fontsPath = public_path('vendor/admin/fonts');
        $customFonts = [];

        if (File::exists($fontsPath)) {
            $files = File::files($fontsPath);
            foreach ($files as $file) {
                $extension = $file->getExtension();
                if (in_array($extension, ['ttf', 'otf', 'woff', 'woff2'])) {
                    $fileName = $file->getFilename();
                    $fontName = pathinfo($fileName, PATHINFO_FILENAME);

                    $displayName = str_replace(['-', '_'], ' ', $fontName);
                    $displayName = ucwords($displayName);

                    $customFonts[] = [
                        'file' => 'vendor/admin/fonts/' . $fileName,
                        'name' => $fontName,
                        'display' => $displayName,
                    ];
                }
            }
        }

        return $customFonts;
    }

    /**
     * Handle deletion of a custom font file.
     *
     * @param string $fontFileToDelete
     * @param array $existingSystemAdmin
     * @return RedirectResponse
     */
    private function handleDeleteCustomFont(string $fontFileToDelete, array $existingSystemAdmin): RedirectResponse
    {
        removeFile(public_path($fontFileToDelete));

        // If the deleted font was currently selected, switch to system default
        if (!empty($existingSystemAdmin['fonts']['custom_font']['file']) &&
            $existingSystemAdmin['fonts']['custom_font']['file'] === $fontFileToDelete) {
            $existingSystemAdmin['fonts']['selected_type'] = 'system';
            $existingSystemAdmin['fonts']['google_font'] = '';
            $existingSystemAdmin['fonts']['custom_font'] = [];

            $setting = Settings::where('key', 'system_admin')->first();
            if ($setting) {
                $setting->value = $existingSystemAdmin;
                $setting->save();
            }
            $this->updateAdminFonts($existingSystemAdmin['fonts'] ?? []);
        }

        return $this->successBack('Custom font deleted successfully');
    }
}
