<div class="user-menu">
    <div
      class="user-menu-btn cursor-pointer ms-3 py-2"
      role="button"
      id="dropdownMenuButton"
      data-bs-toggle="dropdown"
    >
      <div class="user-avatar">
        <img
          src="{{ authAdmin()->avatar_url }}"
          alt="{{ authAdmin()->username }}"
          class="object-fit-cover rounded-50"
          width ="28" height="28"
        />
      </div>
    </div>
    <ul class="dropdown-menu codebay-dropdown-menu px-0" aria-labelledby="dropdownMenuButton">
      <li class="d-flex align-items-center gap-2 px-3">
        <div class="image-fluid image-md rounded">
          <img src="{{ authAdmin()->avatar_url }}" alt="{{ authAdmin()->username }}">
        </div>
        <div class="title">
          <p class="mb-0">{{ authAdmin()->full_name }}</p>
          <small class="text-muted">
            {{ authAdmin()->role_label }}
          </small>
        </div>
      </li>

      <li class="dropdown-divider"></li>

      <li>
        <a class="dropdown-item" href="{{ route('admin.account.index') }}">
          <i class="bi bi-person-check me-2"></i>
          {{ translate("My Profile") }}
        </a>
      </li>
      @if(authAdmin()->isAdmin())
      <li>
        <a class="dropdown-item" href="{{ route('admin.system.custom-style.index') }}">
          <i class="bi bi-palette me-2"></i>
          {{ translate("Custom Style") }}
        </a>
      </li>
      @endif

      <li class="dropdown-divider"></li>

      <li>
        <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
          @csrf
          <button type="submit" class="dropdown-item hover-opacity text-danger w-100 text-start">
            <i class="bi bi-box-arrow-right me-2"></i>
            {{ translate("Logout") }}
          </button>
        </form>
      </li>
    </ul>
</div>
