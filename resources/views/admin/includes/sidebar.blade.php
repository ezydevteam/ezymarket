<aside class="ezydev-sidebar {{ lcfirst(authAdmin()->role->value) }}">
  <div class="overlay"></div>
  <div class="ezydev-sidebar-header position-relative">
    @php
    $siteLogo = themeSettings()->general->logo_light;
    $siteFevicon = themeSettings()->general->favicon ?? 'themes/main/images/favicon.png';
    $siteName = $settings->general->site_name ?? 'EzyMarket';
    @endphp
    <a href="{{ route('admin.dashboard') }}" class="ezydev-sidebar-logo">
      @if($siteLogo)
      <img class="logo-full" src="{{ asset($siteLogo) }}" alt="{{ $siteName }}" />
      @else
      <h1 class="site-name logo-full mb-0 fs-2">{{ $siteName }}</h1>
      @endif
      <img class="logo-small" src="{{ asset($siteFevicon) }}" alt="{{ $siteName }}" />
    </a>
    <button id="sidebarToggleBtn" class="p-0" aria-label="Toggle Sidebar">
      <i class="bi bi-record-circle"></i>
    </button>
  </div>
  <div class="ezydev-sidebar-menu" data-simplebar>
    <div class="ezydev-sidebar-group">
      @if(authAdmin()->canManageSystem())
      <div class="ezydev-sidebar-items">
        <a href="{{ route('admin.dashboard') }}"
          class="ezydev-sidebar-item {{ request()->segment(2) == 'dashboard' ? 'current' : '' }}">
          <p class="ezydev-sidebar-link">
            <span><i class="bi bi-speedometer2 icon-primary"></i>{{ translate("Dashboard") }}</span>
          </p>
        </a>
        @if (isPremiumAvailable())
        <div class="ezydev-sidebar-item {{ request()->segment(2) == 'premium' ? 'active' : '' }}" data-dropdown>
          <p class="ezydev-sidebar-link">
            <span><i class="bi bi-gem icon-warning"></i>{{ translate("Premium") }}</span>
            <span class="arrow"><i class="bi bi-chevron-right"></i></span>
          </p>
          <div class="ezydev-sidebar-submenu">
            <a href="{{ route('admin.premium.plans.index') }}"
              class="ezydev-sidebar-item {{ request()->segment(3) == 'plans' ? 'current' : '' }}">
              <p class="ezydev-sidebar-link">
                <span><i class="bi bi-list-check icon-info"></i>
                  {{ translate("Plans") }}</span>
              </p>
            </a>
            <a href="{{ route('admin.premium.members.index') }}"
              class="ezydev-sidebar-item {{ request()->segment(3) == 'members' ? 'current' : '' }}">
              <p class="ezydev-sidebar-link">
                <span><i class="bi bi-credit-card icon-purple"></i>
                  {{ translate("Members") }}</span>
              </p>
            </a>
            <a href="{{ route('admin.premium.settings.index') }}"
              class="ezydev-sidebar-item {{ request()->segment(3) == 'settings' ? 'current' : '' }}">
              <p class="ezydev-sidebar-link">
                <span><i class="bi bi-gear icon-warning"></i>
                  {{ translate("Settings") }}
                </span>
              </p>
            </a>
          </div>
          @endif
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'roles' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-person-plus icon-success"></i>{{ translate("Roles") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.roles.users.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'users' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-people icon-success"></i>{{ translate("User") }}</span>
                </p>
              </a>
              @if(authAdmin()->isAdmin())
              <a href="{{ route('admin.roles.staff.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'staff' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-shield-check icon-danger"></i>{{ translate("Staff") }}</span>
                </p>
              </a>
              @endif
            </div>
          </div>
        </div>

        <div class="ezydev-sidebar-items">
          <div
            class="ezydev-sidebar-item {{ (request()->segment(2) == 'products' || request()->segment(2) == 'product') && request()->segment(3) != 'categories' ? 'active' : '' }}"
            data-dropdown>
            <p class="ezydev-sidebar-link text-capitalize">
              <span class="w-100"><i class="bi bi-box-seam icon-purple"></i>{{ translate("Products") }}</span>
              @if (($sidebar_counters['products_pending'] ?? 0) ||
              ($sidebar_counters['products_resubmitted'] ?? 0) ||
              ($sidebar_counters['products_updated'] ?? 0))
              <span class="counter me-2">
                {{
                limitCounter(
                ($sidebar_counters["products_pending"] ?? 0) +
                ($sidebar_counters["products_resubmitted"] ?? 0) +
                ($sidebar_counters["products_updated"] ?? 0)
                )
                }}
              </span>
              @endif
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.products.index') }}"
                class="ezydev-sidebar-item {{ (request()->segment(2) == 'products' || request()->segment(4) == 'show') ? 'current' : '' }}">
                <p class="ezydev-sidebar-link text-capitalize">
                  <span><i class="bi bi-box-seam icon-purple"></i>{{ translate("All Products") }}</span>
                  @if (($sidebar_counters['products_pending'] ?? 0) ||
                  ($sidebar_counters['products_resubmitted'] ?? 0))
                  <span class="counter">
                    {{ limitCounter(
                    ($sidebar_counters["products_pending"] ?? 0) +
                    ($sidebar_counters["products_resubmitted"] ?? 0)
                    ) }}
                  </span>
                  @endif
                </p>
              </a>
              <a href="{{ route('admin.products.updated.index') }}"
                class="ezydev-sidebar-item {{ (request()->segment(3) == 'updated' || request()->segment(4) == 'updated') ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-clock-history icon-info"></i>{{ translate("Updated") }}</span>
                  @if ($sidebar_counters['products_updated'] ?? 0)
                  <span class="counter">
                    {{ limitCounter($sidebar_counters["products_updated"]) }}
                  </span>
                  @endif
                </p>
              </a>
            </div>
          </div>
          <div
            class="ezydev-sidebar-item {{ request()->segment(2) == 'products' && request()->segment(3) == 'categories' ? 'active' : '' }}"
            data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-tags icon-success"></i>{{ translate("Categories") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.products.categories.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'categories' && !request()->routeIs('admin.products.categories.sub-categories.*') ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-diagram-3 icon-success"></i>{{ translate("Main Categories") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.products.categories.sub-categories.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(4) == 'sub-categories' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-folder icon-warning"></i>{{ translate("Sub Categories") }}</span>
                </p>
              </a>
            </div>
          </div>

          <!-- Manage Pages -->
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'pages' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-file-text icon-primary"></i>{{ translate("Pages") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <!-- Create Page -->
              <a href="{{ route('admin.pages.create') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'create' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-plus-circle icon-success"></i>{{ translate("New Page") }}</span>
                </p>
              </a>
              <!-- All Pages -->
              <a href="{{ route('admin.pages.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == '' && request()->segment(2) == 'pages' && !request()->is('*/create') && !request()->is('*/edit') ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-list-ul icon-primary"></i>{{ translate("All Pages") }}</span>
                </p>
              </a>
            </div>
          </div>
        </div>
        <div class="ezydev-sidebar-items">
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'financial' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-bank icon-primary"></i>{{ translate("Financial") }}</span>
              @if (($sidebar_counters['payouts'] ?? 0) + ($sidebar_counters['transactions'] ?? 0) > 0)
              <span class="counter me-2">{{
                limitCounter(($sidebar_counters["payouts"] ?? 0) + ($sidebar_counters["transactions"] ?? 0))
                }}</span>
              @endif
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.financial.transactions.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'transactions' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-receipt icon-pink"></i>{{ translate("Transactions") }}</span>
                  @if ($sidebar_counters['transactions'] ?? 0)
                  <span class="counter">{{
                    limitCounter($sidebar_counters["transactions"])
                    }}</span>
                  @endif
                </p>
              </a>
              <a href="{{ route('admin.financial.payouts.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'payouts' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span class="text-truncate">
                    <i class="bi bi-send icon-purple"></i>{{ translate("Payout Request") }}
                  </span>
                  @if ($sidebar_counters['payouts'] ?? 0)
                  <span class="counter">{{
                    limitCounter($sidebar_counters["payouts"])
                    }}
                  </span>
                  @endif
                </p>
              </a>
              <a href="{{ route('admin.financial.payout-methods.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'payout-methods' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <i class="bi bi-cash-stack icon-pink me-1"></i>{{ translate("Payout Methods") }}
                </p>
              </a>
              <a href="{{ route('admin.financial.payment-gateways.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'payment-gateways' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link text-truncate">
                  <i class="bi bi-credit-card icon-purple me-1"></i>{{ translate("Payment Gateways") }}
                </p>
              </a>
              <a href="{{ route('admin.financial.buyer-taxes.index') }}"
                class="ezydev-sidebar-item {{ in_array(request()->segment(3), ['buyer-taxes', 'seller-taxes']) ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-calculator icon-warning"></i>{{ translate("Platform Taxes") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.financial.currencies.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'currencies' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-currency-dollar icon-success"></i>{{ translate("Currencies") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.financial.settings') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'financial' && request()->segment(3) == 'settings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-gear icon-primary"></i>{{ translate("Settings") }}</span>
                </p>
              </a>
              <hr class="my-1 opacity-25">
              <a href="{{ route('admin.financial.transactions.trash.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'transactions' && request()->segment(4) == 'trash' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-trash icon-danger"></i>{{ translate("Trashed Transactions") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.financial.payouts.trash.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'payouts' && request()->segment(4) == 'trash' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-trash icon-danger"></i>{{ translate("Trashed Payouts") }}</span>
                </p>
              </a>
            </div>
          </div>
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'records' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-bar-chart icon-info"></i>{{ translate("Records") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.records.sales.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'sales' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-graph-up icon-success"></i>{{ translate("Sales") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.records.purchases.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'purchases' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-cart icon-warning"></i>{{ translate("Purchases") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.records.support-earnings.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'support-earnings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-headset icon-info"></i>{{ translate("Support Earnings") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.records.referral-earnings.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'referral-earnings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-person-plus icon-purple"></i>{{ translate("Referral Earnings") }}</span>
                </p>
              </a>
              @if (get_license_type(2) && @$settings->premium->status)
              <a href="{{ route('admin.records.premium-earnings.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'premium-earnings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span title="{{ translate('Premium Earnings') }}">
                    <i class="bi bi-award icon-pink"></i>
                    {{ translate("Premium Earnings") }}</span>
                </p>
              </a>
              @endif
              @if (@$settings->actions->refunds)
              <a href="{{ route('admin.records.refunds.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'refunds' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-arrow-counterclockwise icon-indigo"></i>{{ translate("Refunds") }}</span>
                </p>
              </a>
              @endif
              <a href="{{ route('admin.records.statements.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'statements' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-receipt icon-teal"></i>{{ translate("Statements") }}</span>
                </p>
              </a>
              <hr class="my-1 opacity-25">
              <a href="{{ route('admin.records.refunds.trash.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'refunds' && request()->segment(4) == 'trash' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-trash icon-danger"></i>{{ translate("Trashed Refunds") }}</span>
                </p>
              </a>
            </div>
          </div>
        </div>
        <div class="ezydev-sidebar-items">
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'chatbox' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span class="w-100"><i class="bi bi-chat-dots icon-primary"></i>{{ translate("Chatbox") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.chatbox.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'settings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-gear icon-primary"></i>{{ translate("Settings") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.chatbox.history') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'history' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-clock-history icon-success"></i>{{ translate("Chat History") }}</span>
                </p>
              </a>
            </div>
          </div>

          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'reports' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span class="w-100"><i class="bi bi-flag icon-danger"></i>{{ translate("Reports") }}</span>
              @if (($sidebar_counters['reports.product_comments'] ?? 0) +
              ($sidebar_counters['reports.product-reports'] ?? 0) +
              ($sidebar_counters['reports.feedback'] ?? 0) > 0)
              <span class="counter me-2">{{
                limitCounter(
                ($sidebar_counters["reports.product_comments"] ?? 0) +
                ($sidebar_counters["reports.product-reports"] ?? 0) +
                ($sidebar_counters["reports.feedback"] ?? 0)
                )
                }}</span>
              @endif
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.reports.product-reports.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'product-reports' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-flag icon-danger"></i>{{ translate("Product Reports") }}</span>
                  @if ($sidebar_counters['reports.product-reports'] ?? 0)
                  <span class="counter">{{
                    limitCounter($sidebar_counters["reports.product-reports"])
                    }}</span>
                  @endif
                </p>
              </a>
              <a href="{{ route('admin.reports.comment-reports.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'comment-reports' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-chat-left-text icon-warning"></i>{{ translate("Comment Reports") }}</span>
                  @if ($sidebar_counters['reports.product_comments'] ?? 0)
                  <span class="counter">{{
                    limitCounter($sidebar_counters["reports.product_comments"])
                    }}</span>
                  @endif
                </p>
              </a>
              <a href="{{ route('admin.reports.feedback.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'feedback' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-star-half icon-info"></i>{{ translate("Feedback") }}</span>
                  @if ($sidebar_counters['reports.feedback'] ?? 0)
                  <span class="counter">{{
                    limitCounter($sidebar_counters["reports.feedback"])
                    }}</span>
                  @endif
                </p>
              </a>
            </div>
          </div>
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'tickets' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-inbox icon-warning"></i>{{ translate("Tickets") }}</span>
              @if ($sidebar_counters['tickets'] ?? 0)
              <span class="counter me-2">
                {{ limitCounter($sidebar_counters["tickets"]) }}
              </span>
              @endif
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.tickets.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'tickets' && request()->segment(3) != 'categories' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-ticket-perforated icon-warning"></i>{{ translate("Tickets") }}</span>
                  @if ($sidebar_counters['tickets'] ?? 0)
                  <span class="counter">
                    {{ limitCounter($sidebar_counters["tickets"]) }}
                  </span>
                  @endif
                </p>
              </a>
              <a href="{{ route('admin.tickets.categories.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'tickets' && request()->segment(3) == 'categories' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-tags icon-purple"></i>{{ translate("Categories") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.tickets.settings.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'tickets' && request()->segment(3) == 'settings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span title="{{ translate('Ticket Settings') }}"><i
                       class="bi bi-ticket-perforated icon-primary"></i>{{ translate("Settings") }}</span>
                </p>
              </a>
              <hr class="my-1 opacity-25">
              <a href="{{ route('admin.tickets.trash.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'tickets' && request()->segment(3) == 'trash' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-trash icon-danger"></i>{{ translate("Trashed Tickets") }}</span>
                </p>
              </a>
            </div>
          </div>
          @if (isAddonActive('help_center'))
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'help' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-question-circle icon-info"></i>{{ translate("Help Center") }}</span>
              {!! addonBadge('help_center') !!}
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.help.articles.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'help' && request()->segment(3) != 'categories' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-file-text icon-info"></i>{{ translate("Articles") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.help.categories.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'help' && request()->segment(3) == 'categories' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-tags icon-pink"></i>{{ translate("Categories") }}</span>
                </p>
              </a>
            </div>
          </div>
          @endif
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'appearance' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-brush icon-indigo"></i>{{ translate("Appearance") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.appearance.themes.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'themes' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-palette icon-indigo"></i>{{ translate("Themes") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.appearance.addons.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'addons' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-puzzle icon-success"></i>{{ translate("Addons") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.appearance.menus.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'menus' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-list-nested icon-primary"></i>{{ translate("Menus") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.appearance.widgets.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'widgets' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-puzzle icon-info"></i>{{ translate("Widgets") }}</span>
                </p>
              </a>
            </div>
          </div>
          @if (isAddonActive('faker'))
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'faker' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-magic icon-teal"></i>{{ translate("Faker") }}</span>
              {!! addonBadge('faker') !!}
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.faker.settings') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'faker' && request()->segment(3) == 'settings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-gear icon-teal"></i>{{ translate("Settings") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.faker.tools.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'faker' && request()->segment(3) == 'tools' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-wrench icon-primary"></i>{{ translate("Tools") }}</span>
                </p>
              </a>
            </div>
          </div>
          @endif
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'settings' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-gear icon-success"></i>{{ translate("Settings") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.settings.general.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'general' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-gear-fill icon-success"></i>{{ translate("General") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.settings.product.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'product' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link text-capitalize">
                  <span title="{{ translate('Product Settings') }}"><i class="bi bi-box icon-warning"></i>{{
                    translate("Product") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.settings.watermark.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'watermark' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-image icon-info"></i>{{ translate("Watermark") }}</span>
                  {!! addonBadge('watermark') !!}
                </p>
              </a>
              <a href="{{ route('admin.settings.newsletter.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'newsletter' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-envelope icon-purple"></i>{{ translate("Newsletter") }}</span>
                  {!! addonBadge('newsletter') !!}
                </p>
              </a>
              <a href="{{ route('admin.settings.referral.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'referral' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span title="{{ translate('Referral Settings') }}"><i class="bi bi-person-plus icon-pink"></i>{{
                    translate("Referral") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.settings.profile.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'profile' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span title="{{ translate('Profile Settings') }}"><i class="bi bi-person icon-indigo"></i>{{
                    translate("Profile Settings") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.settings.storage-drivers.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'storage-drivers' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span title="{{ translate('Storage Drivers') }}"><i class="bi bi-hdd icon-success"></i>{{
                    translate("Storage Drivers") }}</span>
                </p>
              </a>
              @if (@$settings->product->support_status)
              <a href="{{ route('admin.settings.support-packages.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'support-packages' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span title="{{ translate('Support Packages') }}"><i class="bi bi-clock icon-warning"></i>{{
                    translate("Support Packages") }}</span>
                </p>
              </a>
              @endif
              <a href="{{ route('admin.settings.seller-levels.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'seller-levels' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-star icon-info"></i>{{ translate("Seller Levels") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.settings.badges.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'badges' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-award icon-purple"></i>{{ translate("User Badges") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.settings.social-auth.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'social-auth' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-key icon-pink"></i>{{ translate("Social Auth") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.settings.captcha.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'captcha' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-shield-check icon-indigo"></i>{{ translate("Captcha") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.settings.extensions.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'extensions' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-puzzle icon-success"></i>{{ translate("Extensions") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.settings.translation.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'translation' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-translate icon-warning"></i>{{ translate("Translation") }}</span>
                </p>
              </a>
            </div>
          </div>
        </div>
        <div class="ezydev-sidebar-items">
          <!-- Mail Templates -->
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'mail' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-envelope-fill icon-info"></i>{{ translate("Mail") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <!-- Mail Templates -->
              <a href="{{ route('admin.mail.templates.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'mail' && request()->segment(3) == 'templates' && !request()->segment(4) ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-envelope icon-teal"></i>{{ translate("Templates") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.mail.settings.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'mail' && request()->segment(3) == 'settings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-list-ul icon-primary"></i>{{ translate("Settings") }}</span>
                </p>
              </a>
            </div>
          </div>
        </div>
        <div class="ezydev-sidebar-items">
          <div
            class="ezydev-sidebar-item {{ request()->segment(2) == 'sections' && in_array(request()->segment(3), ['announcement']) ? 'active' : '' }}"
            data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-layers icon-warning"></i>{{ translate("Homepage") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.sections.announcement.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'announcement' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-megaphone icon-warning"></i>{{ translate("Announcement") }}</span>
                </p>
              </a>
            </div>
          </div>
          <div
            class="ezydev-sidebar-item {{ request()->segment(2) == 'builders' && in_array(request()->segment(3), ['home', 'header', 'footer']) ? 'active' : '' }}"
            data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-grid-1x2 icon-info"></i>{{ translate("Site Builder") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.builders.home.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'home' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-house icon-primary"></i>{{ translate("Home") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.builders.header.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'header' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-menu-up icon-success"></i>{{ translate("Header") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.builders.footer.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'footer' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-menu-down icon-purple"></i>{{ translate("Footer") }}</span>
                </p>
              </a>
            </div>
          </div>
          @if (@$settings->actions->blog)
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'blog' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span class="w-100"><i class="bi bi-rss icon-pink"></i>{{ translate("Blog") }}</span>
              @if ($sidebar_counters['blog_comments'] ?? 0)
              <span class="counter me-2">{{
                limitCounter($sidebar_counters["blog_comments"])
                }}</span>
              @endif
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.blog.articles.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'articles' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-newspaper icon-pink"></i>{{ translate("Articles") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.blog.categories.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'blog' && request()->segment(3) == 'categories' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-tags icon-primary"></i>{{ translate("Categories") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.blog.comments.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'comments' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-chat-dots icon-success"></i>{{ translate("Comments") }}</span>
                  @if ($sidebar_counters['blog_comments'] ?? 0)
                  <span class="counter">{{
                    limitCounter($sidebar_counters["blog_comments"])
                    }}</span>
                  @endif
                </p>
              </a>
            </div>
          </div>
          @endif
          @if (@$settings->actions->id_verification)
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'id-verification' ? 'active' : '' }}"
            data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-card-checklist icon-indigo"></i>{{ translate("ID Verification") }}</span>
              @if ($sidebar_counters['id_verifications'] ?? 0)
              <span class="counter">{{
                limitCounter($sidebar_counters["id_verifications"])
                }}</span>
              @endif
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.id-verification.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(2) == 'id-verification' && request()->segment(3) != 'settings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span title="{{ translate('All Verifications') }}"><i class="bi bi-list-ul icon-primary"></i>{{
                    translate("Verifications") }}</span>
                  @if ($sidebar_counters['id_verifications'] ?? 0)
                  <span class="counter">{{
                    limitCounter($sidebar_counters["id_verifications"])
                    }}</span>
                  @endif
                </p>
              </a>
              <a href="{{ route('admin.id-verification.settings.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'settings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-gear icon-teal"></i>{{ translate("Settings") }}</span>
                </p>
              </a>
            </div>
          </div>
          @endif
          <a href="{{ route('admin.ads.index') }}"
            class="ezydev-sidebar-item {{ request()->segment(2) == 'ads' ? 'current' : '' }}">
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-badge-ad icon-teal"></i>{{ translate("Advertisements") }}</span>
            </p>
          </a>
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'system' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-server icon-danger"></i>{{ translate("System") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.system.info.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'info' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-info-circle icon-info"></i>{{ translate("Information") }}</span>
                </p>
              </a>
              @if(authAdmin()->isAdmin())
              <a href="{{ route('admin.system.maintenance') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'maintenance' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-tools icon-warning"></i>{{ translate("Maintenance") }}</span>
                </p>
              </a>
              @endif
              <a href="{{ route('admin.system.rich-text-images.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'rich-text-images' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-images icon-teal"></i>{{ translate("Rich Text Images") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.system.cronjob.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'cronjob' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-clock icon-indigo"></i>{{ translate("Cron Job") }}</span>
                </p>
              </a>
              @if(authAdmin()->isAdmin())
              <a href="{{ route('admin.system.custom-style.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'custom-style' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-palette icon-purple"></i>{{ translate("Custom Styles") }}</span>
                </p>
              </a>
              @endif
            </div>
          </div>
        </div>
        @elseif (authAdmin()->isAccountant())
        <div class="ezydev-sidebar-items">
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'records' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-bar-chart icon-info"></i>{{ translate("Records") }}</span>
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.records.sales.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'sales' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-graph-up icon-success"></i>{{ translate("Sales") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.records.purchases.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'purchases' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-cart icon-warning"></i>{{ translate("Purchases") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.records.support-earnings.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'support-earnings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-headset icon-info"></i>{{ translate("Support Earnings") }}</span>
                </p>
              </a>
              <a href="{{ route('admin.records.referral-earnings.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'referral-earnings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-person-plus icon-purple"></i>{{ translate("Referral Earnings") }}</span>
                </p>
              </a>
              @if (get_license_type(2) && @$settings->premium->status)
              <a href="{{ route('admin.records.premium-earnings.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'premium-earnings' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span title="{{ translate('Premium Earnings') }}">
                    <i class="bi bi-award icon-pink"></i>
                    {{ translate("Premium Earnings") }}</span>
                </p>
              </a>
              @endif
              @if (@$settings->actions->refunds)
              <a href="{{ route('admin.records.refunds.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'refunds' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-arrow-counterclockwise icon-indigo"></i>{{ translate("Refunds") }}</span>
                </p>
              </a>
              @endif
              <a href="{{ route('admin.records.statements.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'statements' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-receipt icon-teal"></i>{{ translate("Statements") }}</span>
                </p>
              </a>
            </div>
          </div>
          <div class="ezydev-sidebar-item {{ request()->segment(2) == 'financial' ? 'active' : '' }}" data-dropdown>
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-bank icon-primary"></i>{{ translate("Financial") }}</span>
              @if (($sidebar_counters['payouts'] ?? 0) + ($sidebar_counters['transactions'] ?? 0) > 0)
              <span class="counter me-2">{{
                limitCounter(($sidebar_counters["payouts"] ?? 0) + ($sidebar_counters["transactions"] ?? 0))
                }}</span>
              @endif
              <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </p>
            <div class="ezydev-sidebar-submenu">
              <a href="{{ route('admin.financial.transactions.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'transactions' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span><i class="bi bi-receipt icon-pink"></i>{{ translate("Transactions") }}</span>
                  @if ($sidebar_counters['transactions'] ?? 0)
                  <span class="counter">{{
                    limitCounter($sidebar_counters["transactions"])
                    }}</span>
                  @endif
                </p>
              </a>
              <a href="{{ route('admin.financial.payouts.index') }}"
                class="ezydev-sidebar-item {{ request()->segment(3) == 'payouts' ? 'current' : '' }}">
                <p class="ezydev-sidebar-link">
                  <span class="text-truncate">
                    <i class="bi bi-send icon-purple"></i>{{ translate("Payout Request") }}
                  </span>
                  @if ($sidebar_counters['payouts'] ?? 0)
                  <span class="counter">{{
                    limitCounter($sidebar_counters["payouts"])
                    }}
                  </span>
                  @endif
                </p>
              </a>
            </div>
          </div>
        </div>
        @elseif (authAdmin()->isReviewer())
        <div class="ezydev-sidebar-item">
          <a href="{{ route('admin.products.index') }}"
            class="ezydev-sidebar-item {{ request()->segment(2) == 'products' && request()->segment(3) != 'updated' ? 'current' : '' }}">
            <p class="ezydev-sidebar-link text-capitalize">
              <span><i class="bi bi-box-seam icon-danger"></i>{{ translate("All Products") }}</span>
              @if ($sidebar_counters['products_all'] ?? 0)
              <span class="counter">
                {{ limitCounter($sidebar_counters["products_all"]) }}
              </span>
              @endif
            </p>
          </a>
          <a href="{{ route('admin.products.updated.index') }}"
            class="ezydev-sidebar-item {{ request()->segment(3) == 'updated' ? 'current' : '' }}">
            <p class="ezydev-sidebar-link">
              <span><i class="bi bi-arrow-repeat icon-info"></i>{{ translate("Updated Products") }}</span>
              @if ($sidebar_counters['products_updated'] ?? 0)
              <span class="counter">{{
                limitCounter($sidebar_counters["products_updated"])
                }}</span>
              @endif
            </p>
          </a>
        </div>
        @endif
      </div>
    </div>
</aside>
