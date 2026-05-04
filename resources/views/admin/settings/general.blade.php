@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('General Settings'))
@section('content')
    <form action="{{ route('admin.settings.general.update') }}" method="POST">
        @csrf
        <div class="row g-4">
            {{-- Basic Information --}}
             <div class="col-xl-6">
                 <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle text-primary me-2"></i> {{ translate('Basic Information') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Site Name') }}</label>
                                <input type="text" name="general[site_name]" class="form-control" value="{{ @$settings->general->site_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Site URL') }}</label>
                                <input type="url" name="general[site_url]" class="form-control" value="{{ @$settings->general->site_url }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ translate('Contact Email') }}</label>
                                <input type="email" name="general[contact_email]" class="form-control" value="{{ @$settings->general->contact_email }}">
                                <div class="form-text">{{ translate('Displayed on Contact Page') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Date Format') }}</label>
                                <select name="general[date_format]" class="form-select selectpicker">
                                    @foreach (\App\Models\Settings::dateFormats() as $formatKey => $formatValue)
                                        <option value="{{ $formatKey }}" @selected($formatKey == @$settings->general->date_format)>
                                            {{ \Carbon\Carbon::now()->format($formatValue) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Timezone') }}</label>
                                <select name="general[timezone]" class="form-select selectpicker" data-live-search="true">
                                    @foreach (\App\Models\Settings::timezones() as $timezoneKey => $timezoneValue)
                                        <option value="{{ $timezoneKey }}" @selected($timezoneKey == @$settings->general->timezone)>
                                            {{ $timezoneValue }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                 </div>
             </div>

             {{-- SEO --}}
             <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-search text-success me-2"></i> {{ translate('SEO Configuration') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label">{{ translate('Homepage Title') }}</label>
                                <input type="text" name="seo[title]" class="form-control" value="{{ @$settings->seo->title }}" maxlength="70">
                                <div class="form-text text-end"><span id="titleCounter">{{ strlen(@$settings->seo->title ?? '') }}</span>/70</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ translate('Meta Keywords') }}</label>
                                <input type="text" name="seo[keywords]" class="form-control" value="{{ @$settings->seo->keywords }}">
                            </div>
                             <div class="col-12">
                                <label class="form-label">{{ translate('Meta Description') }}</label>
                                <textarea name="seo[description]" class="form-control" rows="4" maxlength="160">{{ @$settings->seo->description }}</textarea>
                                <div class="form-text text-end"><span id="descCounter">{{ strlen(@$settings->seo->description ?? '') }}</span>/160</div>
                            </div>
                        </div>
                    </div>
                </div>
             </div>

            {{-- Feature Controls --}}
            <div class="col-12">
                <div class="card">
                     <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-sliders2 text-danger me-2"></i> {{ translate('System Features') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                             @foreach (@$settings->actions ?? [] as $key => $value)
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <div class="d-flex align-items-center justify-content-between p-3 border rounded h-100">
                                        <span class="fw-medium">{{ translate(ucfirst(str_replace('_', ' ', $key))) }}</span>
                                        <x-switch
                                            name="actions[{{ $key }}]"
                                            :checked="$value"
                                            :showLabel="false"
                                            size="lg"
                                            onLabel="{{ translate('Yes') }}"
                                            offLabel="{{ translate('No') }}"
                                        />
                                    </div>
                                </div>
                             @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Social Media --}}
            <div class="col-12">
                 <div class="card">
                     <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-reply-all text-purple me-2"></i> {{ translate('Social Media') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                             @php
                                $socialIcons = [
                                    'facebook' => 'bi bi-facebook',
                                    'x' => 'bi bi-twitter-x',
                                    'instagram' => 'bi bi-instagram',
                                    'linkedin' => 'bi bi-linkedin',
                                    'youtube' => 'bi bi-youtube',
                                    'pinterest' => 'bi bi-pinterest',
                                ];
                            @endphp
                            @foreach (@$settings->social_links ?? [] as $key => $link)
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small">{{ translate(ucfirst(str_replace('_', ' ', $key))) }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="{{ $socialIcons[$key] ?? 'bi bi-link-45deg' }}"></i></span>
                                    <input type="text" name="social_links[{{ $key }}]" class="form-control" value="{{ $link }}" placeholder="https://">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                 </div>
            </div>

             {{-- Important Links --}}
             <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-link-45deg text-orange me-2"></i> {{ translate('Important Links') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                             @foreach (@$settings->links ?? [] as $key => $link)
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ translate(ucfirst(str_replace('_', ' ', $key))) }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                    <input type="text" name="links[{{ $key }}]" class="form-control" value="{{ $link }}" placeholder="https://">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
             </div>

            <div class="col-12 text-end">
                 <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill">
                    <i class="bi bi-save me-2"></i>
                    {{ translate('Save Changes') }}
                </button>
            </div>
        </div>
    </form>
@endsection
