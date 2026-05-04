@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('Watermark Settings'))
@section('container', 'container-max-lg')
@section('content')
    <form action="{{ route('admin.settings.watermark.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">
                    <i class="bi bi-sliders2 text-primary me-2"></i>
                    {{ translate('Watermark Configuration') }}
                </h4>
                    <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> {{ translate('Save Changes') }}
                    </button>
                    </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    {{-- Status --}}
                    <div class="col-12 px-4">
                         <div class="row border rounded px-2 py-3 bg-light-subtle">
                            <div class="col-md-9 d-flex align-items-center gap-3">
                                <div class="card-icon card-icon-md bg-text-primary">
                                    <i class="bi bi-water"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ translate('Watermark Status') }}</h6>
                                    <p class="text-muted small mb-0">{{ translate('Enable watermark overlay on all product images') }}</p>
                                </div>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <x-switch
                                    name="watermark[status]"
                                    value="1"
                                    :checked="@$settings->watermark->status"
                                    :showLabel="false"
                                    onLabel="{{ translate('Enabled') }}"
                                    offLabel="{{ translate('Disabled') }}"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Image Upload --}}
                    <div class="col-lg-6">
                         <label class="form-label fw-medium">{{ translate('Watermark Image') }}</label>
                         <div class="d-flex align-items-center gap-4 p-3 border rounded">
                               <div class="bg-light border rounded d-flex align-items-center justify-content-center p-2" style="width: 72px; height: 72px;">
                                   <img src="{{ asset(@$settings->watermark->image) }}" id="attach-image-preview-watermark_image" alt="Preview" class="mw-100 mh-100 object-fit-contain">
                               </div>
                               <div class="flex-grow-1">
                                    <div class="input-group mb-2">
                                        <button type="button" class="btn bg-text-primary attach-image-button" data-id="watermark_image">
                                            <i class="bi bi-upload me-2"></i>{{ translate('Choose') }}
                                        </button>
                                        <input type="text" id="attach-image-display-watermark_image" class="form-control" value="{{ basename(@$settings->watermark->image) }}" placeholder="{{ translate('No image Selected') }}" disabled>
                                    </div>
                                   <input type="file" name="watermark[image]" id="attach-image-targeted-input-watermark_image" class="d-none" accept="image/png">
                                   <div class="text-muted small">
                                       {{ translate('PNG format, transparent bg recommended.') }}
                                   </div>
                               </div>
                         </div>
                    </div>

                    {{-- Position --}}
                    <div class="col-lg-6">
                        <label class="form-label fw-medium">{{ translate('Position') }}</label>
                        <div class="p-3 border rounded d-flex flex-column justify-content-center">
                            <select name="watermark[position]" class="form-select form-select-lg selectpicker mb-2">
                                @foreach (\App\Models\Settings::watermarkOptions() as $key => $value)
                                    <option value="{{ $key }}" @selected(@$settings->watermark->position == $key)>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-muted small">
                                {{ translate('Choose where the watermark will appear on the image.') }}
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr class="text-muted my-1 opacity-25">
                    </div>

                    {{-- Dimensions Group --}}
                    <div class="col-12">
                        <h5 class="mb-3">
                            <i class="bi bi-arrows-fullscreen text-purple me-2"></i>
                            {{ translate('Dimensions & Style') }}
                        </h5>
                        <div class="row g-3">
                             <div class="col-md-6">
                                <label class="form-label text-muted small">{{ translate('Width (px)') }}</label>
                                <div class="input-group">
                                    <input type="number" name="watermark[width]" class="form-control" min="25" max="10000" value="{{ @$settings->watermark->width }}" required>
                                    <span class="input-group-text text-muted">px</span>
                                </div>
                             </div>
                             <div class="col-md-6">
                                <label class="form-label text-muted small">{{ translate('Height (px)') }}</label>
                                <div class="input-group">
                                    <input type="number" name="watermark[height]" class="form-control" min="25" max="10000" value="{{ @$settings->watermark->height }}" required>
                                    <span class="input-group-text text-muted">px</span>
                                </div>
                             </div>
                             <div class="col-md-6">
                                <label class="form-label text-muted small">{{ translate('Opacity (%)') }}</label>
                                <div class="input-group">
                                    <input type="number" name="watermark[opacity]" class="form-control" min="0" max="100" value="{{ @$settings->watermark->opacity }}" required>
                                    <span class="input-group-text text-muted">%</span>
                                </div>
                             </div>
                             <div class="col-md-6">
                                <label class="form-label text-muted small">{{ translate('Rotate (deg)') }}</label>
                                <div class="input-group">
                                    <input type="number" name="watermark[rotate]" class="form-control" value="{{ @$settings->watermark->rotate }}" required>
                                    <span class="input-group-text text-muted">°</span>
                                </div>
                             </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
@endsection


















