<div class="card mb-3">
    <div class="card-header p-3">
        <h6 class="mb-0">
            <i class="bi bi-plus-circle me-2 text-primary"></i>{{ translate('Add Menu Items') }}
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="accordion accordion-flush" id="menuBuilderAccordion" style="--bs-accordion-btn-icon-width: 14px;">
            {{-- Pages Section --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pagesCollapse">
                        <i class="bi bi-file-text me-2 text-blue"></i>{{ translate('Pages') }}
                    </button>
                </h2>
                <div id="pagesCollapse" class="accordion-collapse collapse" data-bs-parent="#menuBuilderAccordion">
                    <div class="accordion-body p-2">
                        @if($pages->count() > 0)
                            <div class="menu-items-list" style="max-height: 250px; overflow-y: auto;">
                                <div class="form-check mb-2 pb-2 border-bottom">
                                    <input class="form-check-input select-all-checkbox" type="checkbox" id="selectAllPages" data-target="page-item">
                                    <label class="form-check-label fw-semibold" for="selectAllPages">{{ translate('Select All') }}</label>
                                </div>
                                @foreach($pages as $page)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input menu-item-checkbox page-item"
                                               type="checkbox"
                                               id="page_{{ $page->id }}"
                                               data-name="{{ $page->title }}"
                                               data-slug="/{{ $page->slug }}"
                                               data-type="page">
                                        <label class="form-check-label" for="page_{{ $page->id }}">
                                            {{ $page->title }}
                                            <small class="text-muted d-block">/{{ $page->slug }}</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm bg-text-primary hover-opacity w-100 mt-2 add-selected-btn" data-type="page">
                                <i class="bi bi-plus-circle me-2"></i>{{ translate('Add Selected') }}
                            </button>
                        @else
                            <p class="text-muted mb-0 text-center py-3">
                                <i class="bi bi-info-circle me-2"></i>{{ translate('No pages available') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Categories Section --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#categoriesCollapse">
                        <i class="bi bi-folder-plus me-2 text-purple"></i>{{ translate('Categories') }}
                    </button>
                </h2>
                <div id="categoriesCollapse" class="accordion-collapse collapse" data-bs-parent="#menuBuilderAccordion">
                    <div class="accordion-body p-2">
                        @if($categories->count() > 0)
                            <div class="menu-items-list" style="max-height: 250px; overflow-y: auto;">
                                <div class="form-check mb-2 pb-2 border-bottom">
                                    <input class="form-check-input select-all-checkbox" type="checkbox" id="selectAllCategories" data-target="category-item">
                                    <label class="form-check-label fw-semibold" for="selectAllCategories">{{ translate('Select All') }}</label>
                                </div>
                                @foreach($categories as $category)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input menu-item-checkbox category-item"
                                               type="checkbox"
                                               id="category_{{ $category->id }}"
                                               data-name="{{ $category->name }}"
                                               data-slug="/categories/{{ $category->slug }}"
                                               data-type="category">
                                        <label class="form-check-label" for="category_{{ $category->id }}">
                                            {{ $category->name }}
                                            <small class="text-muted d-block">/categories/{{ $category->slug }}</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm bg-text-primary hover-opacity w-100 mt-2 add-selected-btn" data-type="category">
                                <i class="bi bi-plus-circle me-2"></i>{{ translate('Add Selected') }}
                            </button>
                        @else
                            <p class="text-muted mb-0 text-center py-3">
                                <i class="bi bi-info-circle me-2"></i>{{ translate('No categories available') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sub-Categories Section --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#subCategoriesCollapse">
                        <i class="bi bi-folder2-open me-2 text-success"></i>{{ translate('Sub-Categories') }}
                    </button>
                </h2>
                <div id="subCategoriesCollapse" class="accordion-collapse collapse" data-bs-parent="#menuBuilderAccordion">
                    <div class="accordion-body p-2">
                        @if($subCategories->count() > 0)
                            <div class="menu-items-list" style="max-height: 300px; overflow-y: auto;">
                                <div class="form-check mb-2 pb-2 border-bottom">
                                    <input class="form-check-input select-all-checkbox" type="checkbox" id="selectAllSubCategories" data-target="subcategory-item">
                                    <label class="form-check-label fw-semibold" for="selectAllSubCategories">{{ translate('Select All') }}</label>
                                </div>
                                @php
                                    $groupedSubCategories = $subCategories->groupBy('category_id');
                                @endphp
                                @foreach($groupedSubCategories as $categoryId => $subs)
                                    @php
                                        $parentCategory = $subs->first()->category;
                                    @endphp
                                    <div class="mb-2">
                                        <div class="fw-semibold text-muted small mb-1">
                                            <i class="bi bi-folder-plus me-1"></i>{{ $parentCategory->name ?? 'Unknown' }}
                                        </div>
                                        @foreach($subs as $subCategory)
                                            <div class="form-check mb-1 ms-3">
                                                <input class="form-check-input menu-item-checkbox subcategory-item"
                                                       type="checkbox"
                                                       id="subcategory_{{ $subCategory->id }}"
                                                       data-name="{{ $subCategory->name }}"
                                                       data-slug="/categories/{{ $parentCategory->slug ?? 'unknown' }}/{{ $subCategory->slug }}"
                                                       data-type="subcategory">
                                                <label class="form-check-label" for="subcategory_{{ $subCategory->id }}">
                                                    {{ $subCategory->name }}
                                                    <small class="text-muted d-block">/categories/{{ $parentCategory->slug ?? '' }}/{{ $subCategory->slug }}</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm bg-text-primary hover-opacity w-100 mt-2 add-selected-btn" data-type="subcategory">
                                <i class="bi bi-plus-circle me-2"></i>{{ translate('Add Selected') }}
                            </button>
                        @else
                            <p class="text-muted mb-0 text-center py-3">
                                <i class="bi bi-info-circle me-2"></i>{{ translate('No sub-categories available') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Custom Links Section --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#customLinkCollapse">
                        <i class="bi bi-link-45deg me-2 text-danger"></i>{{ translate('Custom Link') }}
                    </button>
                </h2>
                <div id="customLinkCollapse" class="accordion-collapse collapse" data-bs-parent="#menuBuilderAccordion">
                    <div class="accordion-body p-2">
                        <div class="mb-2">
                            <label class="form-label small mb-1">{{ translate('Link Text') }}</label>
                            <input type="text" class="form-control form-control-sm" id="customLinkName" placeholder="{{ translate('Menu item name') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">{{ translate('URL') }}</label>
                            <input type="text" class="form-control form-control-sm" id="customLinkSlug" placeholder="https://example.com or /page-slug">
                        </div>
                        <button type="button" class="btn btn-sm bg-text-primary hover-opacity w-100" id="addCustomLinkBtn">
                            <i class="bi bi-plus-circle me-2"></i>{{ translate('Add to Menu') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
