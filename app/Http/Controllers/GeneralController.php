<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Page, Favorite};
use App\Mail\ContactMail;
use App\Traits\HandlesValidation;
use Illuminate\Support\Facades\{Mail, Storage};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

class GeneralController extends Controller
{
    use HandlesValidation;

    /**
     * Display a static page by its slug.
     *
     * @param string $slug
     * @return View
     */
    public function page(string $slug): View
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        trackView($page, 'pages');

        return theme_view('page', ['page' => $page]);
    }

    /**
     * Display the FAQ page.
     *
     * @return View
     */
    public function faq(): View
    {
        return theme_view('faq');
    }

    /**
     * Display the contact-us page.
     *
     * @return View
     */
    public function contact(): View
    {
        return theme_view('contact');
    }

    /**
     * Handle the contact form submission.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function handleContactForm(Request $request): RedirectResponse
    {
        $validator = $this->validateRequestWithInput($request, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'image', 'max:10240'],
        ] + captchaRules());

        if ($validator instanceof RedirectResponse) {
            return $validator;
        }

        try {
            $data = $request->only(['name', 'email', 'subject', 'message', 'url']);
            $data['message'] = sanitizeHtml($data['message'], true);
            $fullImagePath = null;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('uploads', $imageName, 'public');
                $fullImagePath = Storage::disk('public')->path($imagePath);
            }

            // Send contact email using the dedicated Mailable
            Mail::to(@settings('general')->contact_email)
                ->send(new ContactMail($data, $fullImagePath));

            return $this->successBack('Your message has been sent successfully');

        } catch (\Exception $e) {
            return $this->errorBack('Error sending your message. Please try again later.');
        }
    }

    /**
     * Display the user's favorite products list.
     *
     * @return View
     */
    public function favorites(): View
    {
        $favorites = Favorite::query()
            ->where('user_id', authUser()->id)
            ->withWhereHas('product', function($q) {
                $q->approved()->with(['seller', 'category', 'discount']);
            });

        if (request()->filled('query')) {
            $searchTerm = '%' . request('query') . '%';
            $favorites->whereHas('product', function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('slug', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('options', 'like', $searchTerm)
                  ->orWhere('demo_link', 'like', $searchTerm)
                  ->orWhere('tags', 'like', $searchTerm)
                  ->orWhereHas('category', fn($qc) => $qc->where('name', 'like', $searchTerm))
                  ->orWhereHas('subCategory', fn($qs) => $qs->where('name', 'like', $searchTerm));
            });
        }

        $favorites = $favorites->latest()
            ->paginate(10)
            ->appends(request()->only(['query']));

        $favoriteCount = $favorites->total();

        return theme_view('favorites', compact('favorites', 'favoriteCount'));
    }

    /**
     * Display the API documentation.
     *
     * @return View
     */
    public function apiDocs(): View
    {
        return view('api-docs.index');
    }
}
