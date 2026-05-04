<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\Product\ProductStatus;
use App\Models\Admin;
use Illuminate\Http\{Request, JsonResponse};

class AdminSearchController extends Controller
{
    /**
     * Search admin menu items based on query.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = strtolower(trim($request->input('query', '')));

            if (empty($query)) {
                return response()->json([]);
            }

            $admin = authAdmin();
            $menus = $this->getMenuStructure();
            $results = [];

            foreach ($menus as $menu) {
                if (isset($menu['permission']) && !$this->checkPermission($admin, $menu['permission'])) {
                    continue;
                }

                // Search in parent menu
                if (str_contains(strtolower($menu['title']), $query)) {
                    $results[] = [
                        'title' => $menu['title'],
                        'url' => $menu['url'],
                        'breadcrumb' => null,
                    ];
                }

                // Search in submenus
                if (isset($menu['children'])) {
                    foreach ($menu['children'] as $child) {
                        if (isset($child['permission']) && !$this->checkPermission($admin, $child['permission'])) {
                            continue;
                        }

                        if (str_contains(strtolower($child['title']), $query)) {
                            $results[] = [
                                'title' => $child['title'],
                                'url' => $child['url'],
                                'breadcrumb' => $menu['title'],
                            ];
                        }
                    }
                }
            }

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Search failed'], 500);
        }
    }

    /**
     * Check if the admin has the required permission.
     *
     * @param Admin $admin
     * @param string $permission
     * @return bool
     */
    private function checkPermission(Admin $admin, string $permission): bool
    {
        return match ($permission) {
            'isAdmin' => $admin->isAdmin(),
            'isManager' => $admin->isManager(),
            'canManageSystem' => $admin->canManageSystem(),
            'canManageProducts' => $admin->canManageProducts(),
            'canManageFinancials' => $admin->canManageFinancials(),
            default => true,
        };
    }

    /**
     * Get the complete menu structure.
     *
     * @return array
     */
    private function getMenuStructure(): array
    {
        $menus = [
            [
                'title' => translate('Dashboard'),
                'url' => route('admin.dashboard'),
            ],
        ];

        // Premium
        if (isPremiumAvailable()) {
            $menus[] = [
                'title' => translate('Premium'),
                'url' => '#',
                'children' => [
                    [
                        'title' => translate('Plans'),
                        'url' => route('admin.premium.plans.index'),
                    ],
                    [
                        'title' => translate('Members'),
                        'url' => route('admin.premium.members.index'),
                    ],
                    [
                        'title' => translate('Settings'),
                        'url' => route('admin.premium.settings.index'),
                    ],
                ],
            ];
        }

        // Roles
        $menus[] = [
            'title' => translate('Roles'),
            'url' => '#',
            'permission' => 'canManageSystem',
            'children' => [
                [
                    'title' => translate('Users'),
                    'url' => route('admin.roles.users.index'),
                ],
                [
                    'title' => translate('Staff'),
                    'url' => route('admin.roles.staff.index'),
                    'permission' => 'isAdmin',
                ],
                [
                    'title' => translate('Trashed'),
                    'url' => route('admin.roles.users.trash.index'),
                ],
            ],
        ];

        // Products
        $menus[] = [
            'title' => translate('Products'),
            'url' => '#',
            'permission' => 'canManageProducts',
            'children' => [
                [
                    'title' => translate('All Products'),
                    'url' => route('admin.products.index'),
                ],
                [
                    'title' => translate('Pending Products'),
                    'url' => route('admin.products.index', ['status' => ProductStatus::PENDING->value]),
                ],
                [
                    'title' => translate('Resubmitted Products'),
                    'url' => route('admin.products.index', ['status' => ProductStatus::RESUBMITTED->value]),
                ],
                [
                    'title' => translate('Updated Products'),
                    'url' => route('admin.products.updated.index'),
                ],
                [
                    'title' => translate('Trashed Products'),
                    'url' => route('admin.products.trash.index'),
                ],
            ],
        ];

        // Categories
        $menus[] = [
            'title' => translate('Categories'),
            'url' => '#',
            'permission' => 'canManageProducts',
            'children' => [
                [
                    'title' => translate('Categories'),
                    'url' => route('admin.products.categories.index'),
                ],
                [
                    'title' => translate('Sub Categories'),
                    'url' => route('admin.products.categories.sub-categories.index'),
                ],
            ],
        ];

        // Pages
        $menus[] = [
            'title' => translate('Pages'),
            'url' => '#',
            'children' => [
                [
                    'title' => translate('Create Page'),
                    'url' => route('admin.pages.create'),
                ],
                [
                    'title' => translate('All Pages'),
                    'url' => route('admin.pages.index'),
                ],
            ],
        ];

        // Financial
        $menus[] = [
            'title' => translate('Financial'),
            'url' => '#',
            'permission' => 'canManageFinancials',
            'children' => [
                [
                    'title' => translate('Transactions'),
                    'url' => route('admin.financial.transactions.index'),
                ],
                [
                    'title' => translate('Payouts'),
                    'url' => route('admin.financial.payouts.index'),
                ],
                [
                    'title' => translate('Payout Methods'),
                    'url' => route('admin.financial.payout-methods.index'),
                ],
                [
                    'title' => translate('Payment Gateways'),
                    'url' => route('admin.financial.payment-gateways.index'),
                ],
                [
                    'title' => translate('Buyer Taxes'),
                    'url' => route('admin.financial.buyer-taxes.index'),
                ],
                [
                    'title' => translate('Currencies'),
                    'url' => route('admin.financial.currencies.index'),
                ],
                [
                    'title' => translate('Settings'),
                    'url' => route('admin.financial.settings'),
                ],
            ],
        ];

        // Records
        $menus[] = [
            'title' => translate('Records'),
            'url' => '#',
            'permission' => 'canManageFinancials',
            'children' => [
                [
                    'title' => translate('Sales'),
                    'url' => route('admin.records.sales.index'),
                ],
                [
                    'title' => translate('Purchases'),
                    'url' => route('admin.records.purchases.index'),
                ],
                [
                    'title' => translate('Support Earnings'),
                    'url' => route('admin.records.support-earnings.index'),
                ],
                [
                    'title' => translate('Referral Earnings'),
                    'url' => route('admin.records.referral-earnings.index'),
                ],
                [
                    'title' => translate('Premium Earnings'),
                    'url' => route('admin.records.premium-earnings.index'),
                ],
                [
                    'title' => translate('Refunds'),
                    'url' => route('admin.records.refunds.index'),
                ],
                [
                    'title' => translate('Statements'),
                    'url' => route('admin.records.statements.index'),
                ],
            ],
        ];

        // Chatbox
        $menus[] = [
            'title' => translate('Chatbox'),
            'url' => '#',
            'children' => [
                [
                    'title' => translate('Active Chats'),
                    'url' => route('admin.chatbox.index'),
                ],
                [
                    'title' => translate('Chat History'),
                    'url' => route('admin.chatbox.history'),
                ],
            ],
        ];

        // Reports
        $menus[] = [
            'title' => translate('Reports'),
            'url' => '#',
            'children' => [
                [
                    'title' => translate('Product Reports'),
                    'url' => route('admin.reports.product-reports.index'),
                ],
                [
                    'title' => translate('Comment Reports'),
                    'url' => route('admin.reports.comment-reports.index'),
                ],
                [
                    'title' => translate('Feedback'),
                    'url' => route('admin.reports.feedback.index'),
                ],
            ],
        ];

        // Tickets
        $menus[] = [
            'title' => translate('Tickets'),
            'url' => '#',
            'children' => [
                [
                    'title' => translate('All Tickets'),
                    'url' => route('admin.tickets.index'),
                ],
                [
                    'title' => translate('Categories'),
                    'url' => route('admin.tickets.categories.index'),
                ],
                [
                    'title' => translate('Settings'),
                    'url' => route('admin.tickets.settings.index'),
                ],
            ],
        ];

        // Help Center
        $menus[] = [
            'title' => translate('Help Center'),
            'url' => '#',
            'children' => [
                [
                    'title' => translate('Articles'),
                    'url' => route('admin.help.articles.index'),
                ],
                [
                    'title' => translate('Categories'),
                    'url' => route('admin.help.categories.index'),
                ],
            ],
        ];

        // Appearance
        $menus[] = [
            'title' => translate('Appearance'),
            'url' => '#',
            'permission' => 'canManageSystem',
            'children' => [
                [
                    'title' => translate('Themes'),
                    'url' => route('admin.appearance.themes.index'),
                ],
                [
                    'title' => translate('Addons'),
                    'url' => route('admin.appearance.addons.index'),
                ],
                [
                    'title' => translate('Menus'),
                    'url' => route('admin.appearance.menus.index'),
                ],
                [
                    'title' => translate('Widgets'),
                    'url' => route('admin.appearance.widgets.index'),
                ],
            ],
        ];

        // Faker (Development)
        if (config('app.env') !== 'production') {
            $menus[] = [
                'title' => translate('Faker'),
                'url' => '#',
                'permission' => 'isAdmin',
                'children' => [
                    [
                        'title' => translate('Settings'),
                        'url' => route('admin.faker.settings'),
                    ],
                    [
                        'title' => translate('Tools'),
                        'url' => route('admin.faker.tools.index'),
                    ],
                ],
            ];
        }

        // Settings
        $menus[] = [
            'title' => translate('Settings'),
            'url' => '#',
            'children' => [
                [
                    'title' => translate('General'),
                    'url' => route('admin.settings.general'),
                ],
                [
                    'title' => translate('Product'),
                    'url' => route('admin.settings.product.index'),
                ],
                [
                    'title' => translate('Watermark'),
                    'url' => route('admin.settings.watermark'),
                ],
                [
                    'title' => translate('Newsletter'),
                    'url' => route('admin.settings.newsletter'),
                ],
                [
                    'title' => translate('Referral'),
                    'url' => route('admin.settings.referral'),
                ],
                [
                    'title' => translate('Profile'),
                    'url' => route('admin.settings.profile'),
                ],
                [
                    'title' => translate('Storage Drivers'),
                    'url' => route('admin.settings.storage-drivers.index'),
                ],
                [
                    'title' => translate('Support Packages'),
                    'url' => route('admin.settings.support-packages.index'),
                ],
                [
                    'title' => translate('Seller Levels'),
                    'url' => route('admin.settings.seller-levels.index'),
                ],
                [
                    'title' => translate('Badges'),
                    'url' => route('admin.settings.badges.index'),
                ],
                [
                    'title' => translate('Social Auth'),
                    'url' => route('admin.settings.social-auth.index'),
                ],
                [
                    'title' => translate('Captcha'),
                    'url' => route('admin.settings.captcha.index'),
                ],
                [
                    'title' => translate('Extensions'),
                    'url' => route('admin.settings.extensions.index'),
                ],
                [
                    'title' => translate('Translation'),
                    'url' => route('admin.settings.translation.index'),
                ],
            ],
        ];

        // Mail
        $menus[] = [
            'title' => translate('Mail'),
            'url' => '#',
            'permission' => 'canManageSystem',
            'children' => [
                [
                    'title' => translate('Templates'),
                    'url' => route('admin.mail.templates.index'),
                ],
                [
                    'title' => translate('Settings'),
                    'url' => route('admin.mail.settings.index'),
                ],
            ],
        ];

        // Sections
        $menus[] = [
            'title' => translate('Sections'),
            'url' => '#',
            'children' => [
                [
                    'title' => translate('Announcement'),
                    'url' => route('admin.sections.announcement.index'),
                ],
                [
                    'title' => translate('Home Sections'),
                    'url' => route('admin.sections.home-sections.index'),
                ],
                [
                    'title' => translate('Home Categories'),
                    'url' => route('admin.sections.home-categories.index'),
                ],
                [
                    'title' => translate('FAQs'),
                    'url' => route('admin.sections.faqs.index'),
                ],
                [
                    'title' => translate('Testimonials'),
                    'url' => route('admin.sections.testimonials.index'),
                ],
            ],
        ];

        // Blog
        $menus[] = [
            'title' => translate('Blog'),
            'url' => '#',
            'children' => [
                [
                    'title' => translate('Articles'),
                    'url' => route('admin.blog.articles.index'),
                ],
                [
                    'title' => translate('Categories'),
                    'url' => route('admin.blog.categories.index'),
                ],
                [
                    'title' => translate('Comments'),
                    'url' => route('admin.blog.comments.index'),
                ],
            ],
        ];

        // ID Verification
        $menus[] = [
            'title' => translate('ID Verification'),
            'url' => '#',
            'children' => [
                [
                    'title' => translate('Verification List'),
                    'url' => route('admin.id-verification.list'),
                ],
                [
                    'title' => translate('Settings'),
                    'url' => route('admin.id-verification.settings'),
                ],
            ],
        ];

        // Advertisements
        $menus[] = [
            'title' => translate('Advertisements'),
            'url' => route('admin.ads.index'),
        ];

        // System
        $menus[] = [
            'title' => translate('System'),
            'url' => '#',
            'permission' => 'canManageSystem',
            'children' => [
                [
                    'title' => translate('Info'),
                    'url' => route('admin.system.info.index'),
                ],
                [
                    'title' => translate('Maintenance'),
                    'url' => route('admin.system.maintenance'),
                ],
                [
                    'title' => translate('Rich Text Images'),
                    'url' => route('admin.system.rich-text-images.index'),
                ],
                [
                    'title' => translate('Cron Job'),
                    'url' => route('admin.system.cronjob.index'),
                ],
                [
                    'title' => translate('Custom Style'),
                    'url' => route('admin.system.custom-style.index'),
                ],
            ],
        ];

        return $menus;
    }
}
