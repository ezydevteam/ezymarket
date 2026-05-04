<?php

namespace App\Http\Controllers\Theme;

use App\Http\Controllers\Controller;

use App\Models\{User, Follower, Product\Product, Product\ProductReview};
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display the user's profile home tab.
     */
    public function index(Request $request, string|int $id): View
    {
        $user = $this->getUserById($id);

        $followers = Follower::where('following_id', $user->id)
            ->with('follower')
            ->orderbyDesc('id')
            ->paginate(12);

        return $this->renderProfileView($user, 'profile', [
            'followers' => $followers,
        ]);
    }

    /**
     * Display the user's store tab.
     */
    public function store(Request $request, string|int $id): View
    {
        $user = $this->getUserById($id, true);

        $query = Product::where('seller_id', $user->id)
            ->approved();

        $this->applyStoreFilters($query, $request);

        $products = $query->orderByDesc('id')->paginate(12);
        $products->appends($request->only(['search']));

        return $this->renderProfileView($user, 'store', [
            'products' => $products,
        ]);
    }

    /**
     * Display the user's followers list.
     */
    public function followers(Request $request, string|int $id): View
    {
        $user = $this->getUserById($id);

        $followers = Follower::where('following_id', $user->id)
            ->with('follower')
            ->orderbyDesc('id')
            ->paginate(12);

        return $this->renderProfileView($user, 'followers', [
            'followers' => $followers,
        ]);
    }

    /**
     * Display the user's following list.
     */
    public function following(Request $request, string|int $id): View
    {
        $user = $this->getUserById($id);

        $followings = Follower::where('follower_id', $user->id)
            ->with('following')
            ->orderbyDesc('id')
            ->paginate(12);

        return $this->renderProfileView($user, 'following', [
            'followings' => $followings,
        ]);
    }

    /**
     * Display the user's reviews list.
     */
    public function reviews(Request $request, string|int $id): View
    {
        $user = $this->getUserById($id, true);

        $reviews = ProductReview::where('seller_id', $user->id)
            ->with('user')
            ->orderByDesc('id')
            ->paginate(12);

        return $this->renderProfileView($user, 'reviews', [
            'reviews' => $reviews,
        ]);
    }

    /**
     * Helper to render the profile view with common data.
     */
    private function renderProfileView(User $user, string $activeTab, array $data = []): View
    {
        return theme_view('profile.index', array_merge([
            'user' => $user,
            'activeTab' => $activeTab,
        ], $data));
    }

    /**
     * Fetch user and ensure basic data requirements are met.
     */
    protected function getUserById(string|int $id, bool $sellerCheck = false): User
    {
        $query = User::whereKey($id)
            ->whereDataCompleted()
            ->active()
            ->with(['badges' => function ($query) {
                $query->with('badge');
            }])
            ->with('level');

        if ($sellerCheck) {
            $query->seller();
        }

        return $query->firstOrFail();
    }

    /**
     * Apply filters to the store query.
     */
    private function applyStoreFilters($query, Request $request): void
    {
        $searchTerm = $request->input('search');
        if ($searchTerm) {
            $query->where(function (Builder $query) use ($searchTerm) {
                $query->where('id', 'like', '%' . $searchTerm . '%')
                    ->orWhere('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('slug', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%')
                    ->orWhere('options', 'like', '%' . $searchTerm . '%')
                    ->orWhere('demo_link', 'like', '%' . $searchTerm . '%')
                    ->orWhere('tags', 'like', '%' . $searchTerm . '%');
            });
        }
    }
}
