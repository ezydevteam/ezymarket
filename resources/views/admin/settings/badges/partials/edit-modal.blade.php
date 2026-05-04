<form id="editBadgeForm" action="{{ route('admin.settings.badges.update', $badge->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">
        {{-- Badge Image --}}
        <div class="col-12">
            <label class="form-label">{{ translate('Badge Image') }}</label>
            <div class="input-group">
                <span class="input-group-text">
                    <img id="attach-image-preview-user-badge" src="{{ $badge->image_url }}" width="32" height="32" style="object-fit: contain;">
                </span>
                <input type="text" class="form-control" value="{{ $badge->name }}" readonly>
                <button type="button" class="btn bg-text-primary attach-image-button" data-id="user-badge">
                    <i class="bi bi-cloud-upload me-1"></i>{{ translate('Upload') }}
                </button>
                <input id="attach-image-targeted-input-user-badge" type="file" name="badge_image" accept=".png, .svg" hidden>
            </div>
            <small class="text-muted">{{ translate('PNG or SVG with transparent background, min. 80x80px') }}</small>
        </div>

        {{-- Badge Name --}}
        <div class="col-12">
            <label class="form-label">
                {{ translate('Badge Name') }} <span class="text-danger">*</span>
            </label>
            <input type="text" name="name" class="form-control"
                value="{{ $badge->name }}"
                placeholder="{{ translate('e.g., Gold Seller') }}"
                required />
            <small class="text-muted">{{ translate('Internal identification') }}</small>
        </div>

        {{-- Badge Title --}}
        <div class="col-12">
            <label class="form-label">
                {{ translate('Badge Title') }} <span class="text-muted">({{ translate('Optional') }})</span>
            </label>
            <input type="text" name="title" class="form-control"
                value="{{ $badge->title }}"
                placeholder="{{ translate('e.g., Top-Rated Seller') }}" />
            <small class="text-muted">{{ translate('Public display title') }}</small>
        </div>

        {{-- Badge Type --}}
        <div class="{{ $badge->isDefaultBadge() ? 'col-12' : 'col-md-6'  }}">
            <label class="form-label">
                {{ translate('Badge Type') }}
            </label>
            <div class="form-control" readonly>
                @if ($badge->isCountryBadge())
                    {{ translate('Country Badge') }}
                @elseif ($badge->IsSellerLevelBadge())
                    {{ translate('Seller Level Badge') }}
                @elseif ($badge->isMembershipYearsBadge())
                    {{ translate('Membership Duration Badge') }}
                @else
                    {{ translate('Default Badge') }}
                @endif
            </div>
        </div>

        @if (!$badge->isDefaultBadge())
            <div class="col-md-6">
                <label class="form-label">
                     {{ translate(':label', ['label' => $badge->isCountryBadge() ? translate('Country') : ($badge->isSellerLevelBadge() ? translate('Seller Level') : translate('Membership Duration'))]) }}
                </label>
                <div class="form-control" readonly>
                    @if($badge->isCountryBadge())
                        {{ countries()[$badge->country] ?? translate('Unknown Country') }}
                    @elseif($badge->isSellerLevelBadge())
                        {{ $badge->level ? $badge->level->name : translate('Unknown Level') }}
                    @elseif($badge->isMembershipYearsBadge())
                        {{ $badge->membership_years }} {{ translate('Years') }}
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="d-flex align-items-center gap-3 border-top mt-4 pt-3">
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
        </button>
        <button type="submit" form="editBadgeForm" id="editBadgeBtn" class="btn btn-primary flex-fill">
            <i class="bi bi-check-circle me-2"></i>{{ translate('Update Badge') }}
        </button>
    </div>
</form>

