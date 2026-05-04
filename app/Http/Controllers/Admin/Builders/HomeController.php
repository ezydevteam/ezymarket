<?php

namespace App\Http\Controllers\Admin\Builders;

use App\Http\Controllers\Controller;
use App\Classes\{BuilderBlocks, GoogleFonts, BootstrapIcons};
use App\Models\{Settings, Advertisement};
use App\Traits\HandlesValidation;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\View\View;

class HomeController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of the resource.
     * @return View
     */
    public function index(): View
    {
        $homeBlocks = BuilderBlocks::home();

        $homeLayout = Settings::where('key', 'theme_home')->value('value');

        if (is_string($homeLayout)) {
            $homeLayout = json_decode($homeLayout, true);
        }

        return view('admin.builders.home.index', compact('homeBlocks', 'homeLayout'));
    }

    /**
     * Update the home layout builder data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateLayout(Request $request): JsonResponse
    {
        try {
            $layout = $request->input('layout');
            Settings::updateOrCreate(
                ['key' => 'theme_home'],
                ['value' => $layout]
            );
            return $this->successJson('Layout Updated Successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource in a block.
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
        $homeBlock = array_merge($block, [
            'options' => $instanceOptions,
            'title' => $instanceOptions['title'] ?? $block['title'],
            'subtitle' => $instanceOptions['subtitle'] ?? '',
        ]);

        // Convert to object for blade compatibility
        $homeBlock = (object) $homeBlock;

        $bootstrapIcons = BootstrapIcons::all(true);
        $advertisements = $blockId === 'home_advertisement' ? Advertisement::home()->active()->get() : [];

        return response()->json([
            'title' => translate(':block', ['block' => $homeBlock->title]),
            'content' => view('admin.builders.home.partials.edit-block', compact('homeBlock', 'advertisements', 'bootstrapIcons'))->render()
        ]);
    }

    /**
     * Update the specified block options.
     *
     * @param Request $request
     * @param string $blockId
     * @return JsonResponse
     */
    public function updateBlock(Request $request, string $blockId): JsonResponse
    {
        $block = BuilderBlocks::find($blockId);

        if (!$block) {
            return $this->errorJson('Block not found');
        }

        // Collect all form data as options
        $options = $request->except(['_token']);

        // Handle file uploads
        if ($request->hasFile('image')) {
            $options['image'] = imageUpload($request->file('image'), 'images/site-builder/home/');
        } elseif ($request->has('old_image')) {
            $options['image'] = $request->input('old_image');
        }

        // Handle content images for repeaters
        if ($request->has('content') && is_array($request->input('content'))) {
            $content = $request->input('content');
            foreach ($content as $key => $item) {
                if (is_array($item)) {
                    // Check for file uploads within content array
                    if ($request->hasFile("content.{$key}.image")) {
                        $content[$key]['image'] = imageUpload($request->file("content.{$key}.image"), 'images/site-builder/home/');
                    } elseif (isset($item['old_image'])) {
                        $content[$key]['image'] = $item['old_image'];
                        unset($content[$key]['old_image']);
                    }
                }
            }
            $options['content'] = $content;
        }

        // Remove old_ prefixed fields
        $options = array_filter($options, fn($key) => !str_starts_with($key, 'old_'), ARRAY_FILTER_USE_KEY);

        return response()->json([
            'success' => true,
            'message' => translate('Block updated successfully'),
            'options' => $options
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
            'content' => view('admin.builders.home.partials.section-settings', compact('googleFonts'))->render()
        ]);
    }

    /**
     * Upload an image for the home builder row background.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
        ]);

        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = imageUpload($file, 'images/site-builder/home/');
                return response()->json(['success' => true, 'path' => asset($path)]);
            }
            return response()->json(['success' => false, 'message' => translate('No file uploaded')], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
