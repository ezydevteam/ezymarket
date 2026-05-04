<form id="createBadgeForm"
    action="{{ route('admin.settings.badges.store') }}"
    method="POST"
    enctype="multipart/form-data">
    @csrf

    <div class="row g-3">
            {{-- Badge Image --}}
            <div class="col-12">
                <label class="form-label">{{ translate('Badge Image') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">
                        <img id="attach-image-preview-create-badge" src="{{ asset('images/badges/default.svg') }}" width="32" height="32" style="object-fit: contain;">
                    </span>
                    <input type="text" class="form-control" value="{{ translate('No file selected') }}" readonly>
                    <button type="button" class="btn bg-text-primary attach-image-button" data-id="create-badge">
                        <i class="bi bi-cloud-upload me-1"></i>{{ translate('Upload') }}
                    </button>
                    <input id="attach-image-targeted-input-create-badge" type="file" name="badge_image" accept=".png, .svg" hidden>
                </div>
                <small class="text-muted">{{ translate('PNG or SVG with transparent background, Min. 80x80px') }}</small>
            </div>

            {{-- Badge Name --}}
            <div class="col-12">
                <label class="form-label">
                    {{ translate('Badge Name') }} <span class="text-danger">*</span>
                </label>
                <input type="text" name="name" class="form-control"
                    placeholder="{{ translate('e.g., Gold Seller') }}" />
                <small class="text-muted">{{ translate('Internal identification') }}</small>
            </div>

            {{-- Badge Title --}}
            <div class="col-12">
                <label class="form-label">
                    {{ translate('Badge Title') }} <span class="text-muted">({{ translate('Optional') }})</span>
                </label>
                <input type="text" name="title" class="form-control"
                    placeholder="{{ translate('e.g., Top-Rated Seller') }}" />
                <small class="text-muted">{{ translate('Public display title') }}</small>
            </div>

            {{-- Badge Type --}}
            <div class="col-12">
                <label class="form-label">{{ translate('Badge Type') }}</label>
                <select id="badgeTypeSelect" name="type" class="form-select selectpicker" title="{{ translate('Choose Badge Type') }}">
                    <option value="countries">{{ translate('Country Badge') }}</option>
                    <option value="seller_levels">{{ translate('Seller Level Badge') }}</option>
                    <option value="membership_years">{{ translate('Membership Duration Badge') }}</option>
                </select>
            </div>

            {{-- Country --}}
            <div id="countryField" class="col-12 d-none">
                <label class="form-label">{{ translate('Country') }} <span class="text-danger">*</span></label>
                <select name="country" class="form-select" disabled>
                    <option value="">{{ translate('-- Choose a Country --') }}</option>
                    @foreach (countries() as $countryCode => $countryName)
                        <option value="{{ $countryCode }}">{{ $countryName }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Seller Level --}}
            <div id="sellerLevelField" class="col-12 d-none">
                <label class="form-label">{{ translate('Seller Level') }} <span class="text-danger">*</span></label>
                <select name="seller_level" class="form-select" disabled>
                    @foreach ($sellerLevels as $sellerLevel)
                        <option value="{{ $sellerLevel->id }}">{{ $sellerLevel->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Membership Years --}}
            <div id="membershipYearsField" class="col-12 d-none">
                <label class="form-label">{{ translate('Membership Duration') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="membership_years" class="form-control" min="1" placeholder="5" disabled>
                    <span class="input-group-text">{{ translate('Years') }}</span>
                </div>
            </div>
        </div>

    <div class="d-flex align-items-center gap-3 border-top mt-4 pt-3">
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
        </button>
        <button type="submit" form="createBadgeForm" id="createBadgeBtn" class="btn btn-primary flex-fill">
            <i class="bi bi-check-circle me-2"></i>{{ translate('Create Badge') }}
        </button>
    </div>
</form>
