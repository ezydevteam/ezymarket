<?php

namespace App\Http\Controllers\Admin\Builders;

use App\Http\Controllers\Controller;
use App\Classes\{BuilderBlocks, BootstrapIcons};
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\View\View;
use App\Classes\GoogleFonts;

class HeaderController extends Controller
{
    use HandlesValidation;

    /**
     * Display the header builder.
     * @return View
     */
    public function index(): View
    {
        $headerBlocks = BuilderBlocks::header();

        $headerLayout = Settings::where('key', 'theme_header')->value('value');

        if (is_string($headerLayout)) {
            $headerLayout = json_decode($headerLayout, true);
        }

        return view('admin.builders.header.index', compact('headerBlocks', 'headerLayout'));
    }

    /**
     * Update the header layout builder data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateLayout(Request $request): JsonResponse
    {
        try {
            $layout = $request->input('layout');
            Settings::updateOrCreate(
                ['key' => 'theme_header'],
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
        $headerBlock = array_merge($block, [
            'options' => $instanceOptions,
            'title' => $instanceOptions['title'] ?? $block['title'],
        ]);

        // Convert to object for blade compatibility
        $headerBlock = (object) $headerBlock;

        $bootstrapIcons = BootstrapIcons::all(true);

        return response()->json([
            'title' => translate(':block', ['block' => $headerBlock->title]),
            'content' => view('admin.builders.header.partials.edit-block', [
                'headerBlock' => $headerBlock,
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
        $sectionId = $request->input('id');
        $googleFonts = GoogleFonts::getAll();

        return response()->json([
            'title' => translate(':section Settings', ['section' => $sectionName]),
            'content' => view('admin.builders.header.partials.section-settings', compact('sectionId', 'googleFonts'))->render()
        ]);
    }

    /**
     * Upload an image for the header builder.
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
                $path = imageUpload($file, 'images/site-builder/header/');
                return response()->json(['success' => true, 'path' => asset($path)]);
            }
            return response()->json(['success' => false, 'message' => translate('No file uploaded')], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
