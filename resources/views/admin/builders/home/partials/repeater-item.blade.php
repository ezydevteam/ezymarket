<div class="accordion-item bg-white mb-2 border rounded shadow-sm">
    <h2 class="accordion-header d-flex align-items-center bg-light" id="heading{{ $key }}">
        <div class="sortable-handle py-2 ps-3 pe-1" style="cursor: move;">
            <i class="bi bi-grip-vertical text-muted fs-5"></i>
        </div>
        <button class="accordion-button collapsed p-3 shadow-none bg-light border-0" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse{{ $key }}" aria-expanded="false"
            aria-controls="collapse{{ $key }}">
            <span class="item-title fw-medium">{{ $item['title'] ?? $item['name'] ?? $item['question'] ?? translate('New
                Item') }}</span>
        </button>
    </h2>
    <div id="collapse{{ $key }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $key }}">
        <div class="accordion-body p-3">
            {{-- Fields based on TYPE --}}

            @if($type === 'home_categories')
            <div class="mb-3">
                <label class="form-label">{{ translate('Title') }}</label>
                <input type="text" name="content[{{ $key }}][title]" class="form-control item-title-input"
                    value="{{ $item['title'] ?? '' }}" placeholder="{{ translate('Category Title') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Icon') }}</label>
                <div class="input-group">
                    <span class="input-group-text p-1 bg-white">
                        <img id="preview-icon-{{ $key }}"
                            src="{{ !empty($item['image']) ? asset($item['image']) : asset('images/placeholders/default.png') }}"
                            width="32" height="32" style="object-fit: contain;">
                    </span>
                    <input type="text" class="form-control"
                        value="{{ !empty($item['image']) ? basename($item['image']) : translate('Choose Image') }}"
                        readonly style="cursor: pointer;" onclick="$('#file-icon-{{ $key }}').click()">
                    <button type="button" class="btn bg-text-primary" onclick="$('#file-icon-{{ $key }}').click()">
                        <i class="bi bi-upload me-1"></i>{{ translate('Upload') }}
                    </button>
                    <input id="file-icon-{{ $key }}" type="file" name="content[{{ $key }}][image]"
                        class="d-none repeater-image-input" data-preview="#preview-icon-{{ $key }}" accept="image/*">
                    @if(!empty($item['image']))
                    <input type="hidden" name="content[{{ $key }}][old_image]" value="{{ $item['image'] }}">
                    @endif
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Link') }}</label>
                <input type="text" name="content[{{ $key }}][link]" class="form-control"
                    value="{{ $item['link'] ?? '' }}" placeholder="https://...">
            </div>

            @elseif($type === 'home_faqs')
            <div class="mb-3">
                <label class="form-label">{{ translate('Question') }}</label>
                <input type="text" name="content[{{ $key }}][question]" class="form-control item-title-input"
                    value="{{ $item['question'] ?? '' }}" placeholder="{{ translate('Question') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Answer') }}</label>
                <textarea name="content[{{ $key }}][answer]" class="form-control" rows="3"
                    placeholder="{{ translate('Answer') }}">{{ $item['answer'] ?? '' }}</textarea>
            </div>

            @elseif($type === 'home_testimonials')
            <div class="row g-3 mb-3 {{ !empty($item['show_image']) ? 'd-none' : '' }}" id="testmonialsInfo-{{ $key }}">
                <div class="col-6">
                    <label class="form-label">{{ translate('Name') }}</label>
                    <input type="text" name="content[{{ $key }}][name]" class="form-control item-title-input"
                        value="{{ $item['name'] ?? '' }}">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Designation') }}</label>
                    <input type="text" name="content[{{ $key }}][designation]" class="form-control"
                        value="{{ $item['designation'] ?? '' }}">
                </div>
                <div class="col-12">
                    <label class="form-label">{{ translate('Comment') }}</label>
                    <textarea name="content[{{ $key }}][comment]" class="form-control"
                        rows="3">{{ $item['comment'] ?? '' }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ translate('Avatar') }}</label>
                    <div class="input-group">
                        <span class="input-group-text p-1 bg-white">
                            <img id="preview-avatar-{{ $key }}"
                                src="{{ !empty($item['image']) ? asset($item['image']) : asset('images/placeholders/default.png') }}"
                                width="32" height="32" style="object-fit: contain;">
                        </span>
                        <input type="text" class="form-control"
                            value="{{ !empty($item['image']) ? basename($item['image']) : translate('Choose Avatar') }}"
                            readonly style="cursor: pointer;" onclick="$('#file-avatar-{{ $key }}').click()">
                        <button type="button" class="btn bg-text-primary"
                            onclick="$('#file-avatar-{{ $key }}').click()">
                            <i class="bi bi-upload me-1"></i>{{ translate('Upload') }}
                        </button>
                        <input id="file-avatar-{{ $key }}" type="file" name="content[{{ $key }}][image]"
                            class="d-none repeater-image-input" data-preview="#preview-avatar-{{ $key }}"
                            accept="image/*">
                        @if(!empty($item['image']))
                        <input type="hidden" name="content[{{ $key }}][old_image]" value="{{ $item['image'] }}">
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ translate('Rating') }}</label>
                    <select name="content[{{ $key }}][rating]" class="form-select selectpicker">
                        @foreach(range(1, 5) as $i)
                        <option value="{{ $i }}" @selected(($item['rating'] ?? 5)==$i)>{{ $i }} {{ translate('Stars') }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="content[{{ $key }}][show_image]"
                    id="show_image_{{ $key }}" value="1" @checked(!empty($item['show_image']))
                    data-slide-toggle="#show-image-upload-{{ $key }}" data-hide-toggle="#testmonialsInfo-{{ $key }}">
                <label class="form-check-label" for="show_image_{{ $key }}">{{ translate('Show Image Instead') }}
                </label>
            </div>

            <div class="show-image-upload d-none mb-3" id="show-image-upload-{{ $key }}">
                <label class="form-label">{{ translate('Testimonial Image') }}</label>
                <div class="input-group">
                    <span class="input-group-text p-1 bg-white">
                        <img id="preview-testimonial-img-{{ $key }}"
                            src="{{ !empty($item['testimonial_image']) ? asset($item['testimonial_image']) : asset('images/placeholders/default.png') }}"
                            width="60" height="40" style="object-fit: contain;">
                    </span>
                    <input type="text" class="form-control"
                        value="{{ !empty($item['testimonial_image']) ? basename($item['testimonial_image']) : translate('Choose Image') }}"
                        readonly style="cursor: pointer;" onclick="$('#file-testimonial-img-{{ $key }}').click()">
                    <button type="button" class="btn bg-text-primary"
                        onclick="$('#file-testimonial-img-{{ $key }}').click()">
                        <i class="bi bi-upload me-1"></i>{{ translate('Upload') }}
                    </button>
                    <input id="file-testimonial-img-{{ $key }}" type="file"
                        name="content[{{ $key }}][testimonial_image]" class="d-none repeater-image-input"
                        data-preview="#preview-testimonial-img-{{ $key }}" accept="image/*">
                    @if(!empty($item['testimonial_image']))
                    <input type="hidden" name="content[{{ $key }}][old_testimonial_image]"
                        value="{{ $item['testimonial_image'] }}">
                    @endif
                </div>
            </div>

            @elseif($type === 'home_slider')
            <div class="mb-3">
                <label class="form-label">{{ translate('Caption / Title') }}</label>
                <input type="text" name="content[{{ $key }}][caption]" class="form-control item-title-input"
                    value="{{ $item['caption'] ?? '' }}" placeholder="{{ translate('Slide Caption') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Link') }}</label>
                <input type="text" name="content[{{ $key }}][link]" class="form-control"
                    value="{{ $item['link'] ?? '' }}" placeholder="https://...">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Image') }}</label>
                <div class="input-group">
                    <span class="input-group-text p-1 bg-white">
                        <img id="preview-slider-{{ $key }}"
                            src="{{ !empty($item['image']) ? asset($item['image']) : asset('images/placeholders/default.png') }}"
                            width="32" height="32" style="object-fit: contain;">
                    </span>
                    <input type="text" class="form-control"
                        value="{{ !empty($item['image']) ? basename($item['image']) : translate('Choose Image') }}"
                        readonly style="cursor: pointer;" onclick="$('#file-slider-{{ $key }}').click()">
                    <button type="button" class="btn bg-text-primary" onclick="$('#file-slider-{{ $key }}').click()">
                        <i class="bi bi-upload me-1"></i>{{ translate('Upload') }}
                    </button>
                    <input id="file-slider-{{ $key }}" type="file" name="content[{{ $key }}][image]"
                        class="d-none repeater-image-input" data-preview="#preview-slider-{{ $key }}" accept="image/*">
                    @if(!empty($item['image']))
                    <input type="hidden" name="content[{{ $key }}][old_image]" value="{{ $item['image'] }}">
                    @endif
                </div>
            </div>
            @elseif($type === 'slider')
            <div class="mb-3">
                <label class="form-label">{{ translate('Caption') }}</label>
                <input type="text" name="content[{{ $key }}][caption]" class="form-control item-title-input"
                    value="{{ $item['caption'] ?? '' }}" placeholder="{{ translate('Slide Caption') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Image') }}</label>
                <div class="input-group">
                    <span class="input-group-text p-1 bg-white">
                        <img id="preview-slide-{{ $key }}"
                            src="{{ !empty($item['image']) ? asset($item['image']) : asset('images/placeholders/default.png') }}"
                            width="60" height="40" style="object-fit: contain;">
                    </span>
                    <input type="text" class="form-control"
                        value="{{ !empty($item['image']) ? basename($item['image']) : translate('Choose Image') }}"
                        readonly style="cursor: pointer;" onclick="$('#file-slide-{{ $key }}').click()">
                    <button type="button" class="btn bg-text-primary" onclick="$('#file-slide-{{ $key }}').click()">
                        <i class="bi bi-upload me-1"></i>{{ translate('Upload') }}
                    </button>
                    <input id="file-slide-{{ $key }}" type="file" name="content[{{ $key }}][image]"
                        class="d-none repeater-image-input" data-preview="#preview-slide-{{ $key }}" accept="image/*">
                    @if(!empty($item['image']))
                    <input type="hidden" name="content[{{ $key }}][old_image]" value="{{ $item['image'] }}">
                    @endif
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Link URL') }}</label>
                <input type="text" name="content[{{ $key }}][link]" class="form-control"
                    value="{{ $item['link'] ?? '' }}" placeholder="https://...">
            </div>
            @elseif($type === 'home_tabs')
            <div class="mb-3">
                <label class="form-label">{{ translate('Tab Title') }}</label>
                <input type="text" name="content[{{ $key }}][title]" class="form-control item-title-input"
                    value="{{ $item['title'] ?? '' }}" placeholder="{{ translate('Tab Title') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Tab Content') }} <small class="text-muted">({{
                    translate('HTML supported') }})</small></label>
                <textarea name="content[{{ $key }}][html]" class="form-control" rows="5">{{ $item['html'] ?? ''
                    }}</textarea>
            </div>

            @elseif($type === 'home_button')
            <div class="mb-3">
                <label class="form-label">{{ translate('Label') }}</label>
                <input type="text" name="content[{{ $key }}][label]" class="form-control item-title-input"
                    value="{{ $item['label'] ?? '' }}" placeholder="Click Me">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Link') }}</label>
                <input type="text" name="content[{{ $key }}][link]" class="form-control"
                    value="{{ $item['link'] ?? '#' }}">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Style') }}</label>
                <select name="content[{{ $key }}][style]" class="form-select selectpicker">
                    <option value="primary" @selected(($item['style']??'')=='primary' )>Primary</option>
                    <option value="secondary" @selected(($item['style']??'')=='secondary' )>Secondary</option>
                    <option value="success" @selected(($item['style']??'')=='success' )>Success</option>
                    <option value="danger" @selected(($item['style']??'')=='danger' )>Danger</option>
                    <option value="warning" @selected(($item['style']??'')=='warning' )>Warning</option>
                    <option value="info" @selected(($item['style']??'')=='info' )>Info</option>
                    <option value="light" @selected(($item['style']??'')=='light' )>Light</option>
                    <option value="dark" @selected(($item['style']??'')=='dark' )>Dark</option>
                    <option value="link" @selected(($item['style']??'')=='link' )>Link</option>
                </select>
            </div>

            @elseif($type === 'home_social_icons')
            <div class="mb-3">
                <label class="form-label">{{ translate('Select Platform') }}</label>
                <div class="input-group">
                    @php
                        $socialPlatforms = ['Facebook','Twitter','Instagram','LinkedIn','YouTube',
                            'Pinterest','TikTok','WhatsApp','Telegram','Snapchat','Reddit','Discord',
                            'Github','Behance','Dribbble','Website','Email','Phone'];
                    @endphp
                    <select name="content[{{ $key }}][name]" class="form-select selectpicker" data-live-search="true">
                        @foreach($socialPlatforms as $platform)
                        <option value="{{ $platform }}" @selected(($item['name'] ?? '') == $platform)>{{ $platform }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="content[{{ $key }}][link]" class="form-control"
                        value="{{ $item['link'] ?? '' }}" placeholder="https://...">
                </div>
            </div>
            @endif

            <div class="text-end mt-2 border-top pt-2">
                <button type="button" class="btn btn-sm btn-outline-danger remove-repeater-item">
                    <i class="bi bi-trash me-1"></i>{{ translate('Remove') }}
                </button>
            </div>
        </div>
    </div>
</div>
