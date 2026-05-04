<header class="ezydev-header">
  <button id="navToggleBtn" class="btn-icon mx-2 d-xl-none">
    <i class="bi bi-list"></i>
  </button>

  <!-- Logo for mobile (<1200px) -->
  <div class="d-xl-none">
    @php
      $siteLogo = themeSettings()->general->logo_light;
      $siteName = $settings->general->site_name ?? 'EzyMarket';
    @endphp
    <a href="{{ route('admin.dashboard') }}" class="navbar-logo">
      @if($siteLogo)
        <img src="{{ asset($siteLogo) }}" alt="{{ $siteName }}">
      @else
        <h1 class="site-name mb-0 fs-2">{{ $siteName }}</h1>
      @endif
    </a>
  </div>

  <!-- Admin Menu Search - Desktop & Mobile Input -->
  <div class="admin-menu-search position-relative">
    <!-- Search Toggle Button for Mobile -->
    <button type="button" class="btn-icon search-toggle-btn d-md-none" id="searchToggleBtn">
      <i class="bi bi-search"></i>
    </button>

    <div class="search-wrapper">
      <input
        type="text"
        id="adminMenuSearch"
        class="form-control form-control-sm"
        placeholder="{{ translate('Search...') }}"
        autocomplete="off"
      >
      <i class="bi bi-search search-icon"></i>
      <button type="button" class="btn-clear d-none" id="clearSearch">
        <i class="bi bi-x-circle-fill"></i>
      </button>
    </div>
    <div class="search-results d-none" id="searchResults">
      <div class="search-results-content">
        <!-- Results will be populated here -->
      </div>
    </div>
  </div>

  <div class="nav-actions-menu d-flex align-items-center me-3">
    @if(authAdmin()->canManageSystem())
    <div class="dropdown">
      <button
        class="dropdown-btn btn-icon header-btn" data-bs-toggle="dropdown" aria-expanded="false"
      >
        <i class="bi bi-three-dots-vertical"></i>
      </button>
      <ul class="dropdown-menu codebay-dropdown-menu" aria-labelledby="actionsDropdown">
        @if (isAddonActive('license_verification_tool'))
        <li>
          <a
            class="dropdown-item"
            href="{{ route('admin.license-verification.index') }}"
          >
            <i class="bi bi-key"></i>
            {{ translate("Verify License") }}
          </a>
        </li>
        @endif
        <li>
          <a
            class="dropdown-item action-confirm text-danger"
            href="{{ route('admin.system.info.cache') }}"
          >
            <i class="bi bi-trash"></i>
            {{ translate("Clear Cache") }}
          </a>
        </li>
        <div class="dropdown-divider"></div>
        <li>
          <a class="dropdown-item text-success" href="{{ url('/') }}" target="_blank">
            <i class="bi bi-eye"></i>
            {{ translate("View Site") }}
          </a>
        </li>
      </ul>
    </div>
    @else
      <a class="text-dark hover-primary me-3" href="{{ url('/') }}" target="_blank">
        <i class="bi bi-eye me-2"></i>{{ translate("View Site") }}
      </a>
    @endif

    @if(authAdmin()->canManageSystem())
      @include('admin.partials.notification-dropdown')
    @endif

    @include('admin.partials.user-menu')
  </div>
</header>
