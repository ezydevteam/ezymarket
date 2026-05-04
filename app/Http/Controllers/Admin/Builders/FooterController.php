<?php

namespace App\Http\Controllers\Admin\Builders;

use App\Http\Controllers\Controller;
use App\Classes\{BuilderBlocks, BootstrapIcons, GoogleFonts};
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\View\View;

class FooterController extends Controller
{
    use HandlesValidation;

    /**
     * Display the footer builder.
     * @return View
     */
    public function index(): View
    {
        $footerBlocks = BuilderBlocks::footer();

        $footerLayout = Settings::where('key', 'theme_footer')->value('value');

        if (is_string($footerLayout)) {
            $footerLayout = json_decode($footerLayout, true);
        }

        return view('admin.builders.footer.index', compact('footerBlocks', 'footerLayout'));
    }

    /**
     * Update the footer layout builder data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateLayout(Request $request): JsonResponse
    {
        try {
            $layout = $request->input('layout');
            Settings::updateOrCreate(
                ['key' => 'theme_footer'],
                ['value' => $layout]
            );
            return $this->successJson('Layout Updated Successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified block.
     *
     * @param Request $request
     * @param string $blockId
     * @return JsonResponse
     */
    public function editBlock(Request $request, string $blockId): JsonResponse
    {
        $block = BuilderBlocks::find($blockId);

        if (!$block) {
            return $this->errorJson('Block not found');
        }

        // Get instance-specific options from request
        $instanceOptions = [];
        if ($request->has('options')) {
            $decoded = json_decode(urldecode($request->input('options')), true);
            if (is_array($decoded)) {
                $instanceOptions = $decoded;
            }
        }

        // Merge default block info with instance options
        $footerBlock = array_merge($block, [
            'options' => $instanceOptions,
            'title' => $instanceOptions['title'] ?? $block['title'],
        ]);

        // Convert to object for blade compatibility
        $footerBlock = (object) $footerBlock;

        $bootstrapIcons = BootstrapIcons::all(true);

        return response()->json([
            'title' => translate(':block', ['block' => $footerBlock->title]),
            'content' => view('admin.builders.footer.partials.edit-block', [
                'footerBlock' => $footerBlock,
                'bootstrapIcons' => $bootstrapIcons
            ])->render()
        ]);
    }

    /**
     * Show the form for editing section settings.
     *
     * @return JsonResponse
     */
    public function sectionSettings(Request $request): JsonResponse
    {
        $sectionName = $request->input('section', 'Section');
        $googleFonts = GoogleFonts::getAll();

        return response()->json([
            'title' => translate(':section Settings', ['section' => $sectionName]),
            'content' => view('admin.builders.footer.partials.section-settings', compact('googleFonts'))->render()
        ]);
    }

    /**
     * Upload an image for the footer builder.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:10240'],
        ]);

        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = imageUpload($file, 'images/site-builder/footer/');
                return response()->json(['success' => true, 'path' => asset($path)]);
            }
            return response()->json(['success' => false, 'message' => translate('No file uploaded')], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
