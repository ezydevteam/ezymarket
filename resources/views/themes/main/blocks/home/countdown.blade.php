@php $data = (object)($data ?? []); @endphp

@if($data->countdownDate)
<div id="{{ $data->uniqueId }}"
    class="home-countdown {{ $isFullWidth ? $data->containerClass : '' }} countdown-style-{{ $data->countdownStyle ?? 'default' }}"
    data-countdown="{{ $data->countdownDate }}">

    <div
        class="countdown-block d-flex flex-column align-items-center justify-content-center text-{{ $data->blockAlign ?? 'center' }} p-4 position-relative rounded-4">

        {{-- Background Overlay for Default Style if BG Image is used --}}
        @if(($data->countdownStyle === 'default') && ($data->useBgImage ?? false))
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50 rounded z-0"></div>
        @endif

        <div class="position-relative z-1 w-100">
            @themeInclude('blocks.home.partials.block-title', ['data' => $data])

            @if($data->countdownStyle === 'circle')
            {{-- Circle Ring Style --}}
            <div class="row justify-content-{{ $data->blockAlign ?? 'center' }}">
                @if($data->showDays ?? true)
                <div class="col-auto mb-3">
                    <div class="countdown-item">
                        <span class="h2 fw-bold mb-0 countdown-number" data-days>00</span>
                        <div class="small text-uppercase countdown-label">{{ translate('Days') }}</div>
                    </div>
                </div>
                @endif
                @if($data->showHours ?? true)
                <div class="col-auto mb-3">
                    <div class="countdown-item">
                        <span class="h2 fw-bold mb-0 countdown-number" data-hours>00</span>
                        <div class="small text-uppercase countdown-label">{{ translate('Hrs') }}</div>
                    </div>
                </div>
                @endif
                @if($data->showMinutes ?? true)
                <div class="col-auto mb-3">
                    <div class="countdown-item">
                        <span class="h2 fw-bold mb-0 countdown-number" data-minutes>00</span>
                        <div class="small text-uppercase countdown-label">{{ translate('Min') }}</div>
                    </div>
                </div>
                @endif
                @if($data->showSeconds ?? true)
                <div class="col-auto mb-3">
                    <div class="countdown-item">
                        <span class="h2 fw-bold mb-0 countdown-number" data-seconds>00</span>
                        <div class="small text-uppercase countdown-label">{{ translate('Sec') }}</div>
                    </div>
                </div>
                @endif
            </div>

            @elseif($data->countdownStyle === 'digital')
            {{-- Digital Box Style --}}
            <div class="row justify-content-{{ $data->blockAlign ?? 'center' }} g-3">
                @if($data->showDays ?? true)
                <div class="col-auto">
                    <div class="countdown-item text-center">
                        <span class="display-4 fw-bold d-block countdown-number" data-days>00</span>
                        <div class="small text-uppercase countdown-label">{{ translate('Days') }}</div>
                    </div>
                </div>
                @endif
                @if($data->showHours ?? true)
                <div class="col-auto">
                    <div class="countdown-item text-center">
                        <span class="display-4 fw-bold d-block countdown-number" data-hours>00</span>
                        <div class="small text-uppercase countdown-label">{{ translate('Hours') }}</div>
                    </div>
                </div>
                @endif
                @if($data->showMinutes ?? true)
                <div class="col-auto">
                    <div class="countdown-item text-center">
                        <span class="display-4 fw-bold d-block countdown-number" data-minutes>00</span>
                        <div class="small text-uppercase countdown-label">{{ translate('Minutes') }}</div>
                    </div>
                </div>
                @endif
                @if($data->showSeconds ?? true)
                <div class="col-auto">
                    <div class="countdown-item text-center">
                        <span class="display-4 fw-bold d-block countdown-number" data-seconds>00</span>
                        <div class="small text-uppercase countdown-label">{{ translate('Seconds') }}</div>
                    </div>
                </div>
                @endif
            </div>

            @elseif($data->countdownStyle === 'minimal')
            {{-- Minimal Text Style --}}
            <div class="row justify-content-{{ $data->blockAlign ?? 'center' }}">
                @if($data->showDays ?? true)
                <div class="col-auto">
                    <div class="countdown-item">
                        <span class="display-2 fw-light countdown-number" data-days>00</span>
                        <div class="small text-uppercase text-muted">{{ translate('Days') }}</div>
                    </div>
                </div>
                @endif
                @if(($data->showDays ?? true) && ($data->showHours ?? true)) <div class="col-auto align-self-start">
                    <span class="display-4 text-muted">:</span>
                </div> @endif

                @if($data->showHours ?? true)
                <div class="col-auto">
                    <div class="countdown-item">
                        <span class="display-2 fw-light countdown-number" data-hours>00</span>
                        <div class="small text-uppercase text-muted">{{ translate('Hours') }}</div>
                    </div>
                </div>
                @endif
                @if(($data->showHours ?? true) && ($data->showMinutes ?? true)) <div class="col-auto align-self-start">
                    <span class="display-4 text-muted">:</span>
                </div> @endif

                @if($data->showMinutes ?? true)
                <div class="col-auto">
                    <div class="countdown-item">
                        <span class="display-2 fw-light countdown-number" data-minutes>00</span>
                        <div class="small text-uppercase text-muted">{{ translate('Minutes') }}</div>
                    </div>
                </div>
                @endif
                @if(($data->showMinutes ?? true) && ($data->showSeconds ?? true)) <div
                    class="col-auto align-self-start"><span class="display-4 text-muted">:</span></div> @endif

                @if($data->showSeconds ?? true)
                <div class="col-auto">
                    <div class="countdown-item">
                        <span class="display-2 fw-light countdown-number" data-seconds>00</span>
                        <div class="small text-uppercase text-muted">{{ translate('Seconds') }}</div>
                    </div>
                </div>
                @endif
            </div>

            @else
            {{-- Default Style --}}
            <div class="row justify-content-{{ $data->blockAlign ?? 'center' }} align-self-center mb-4">
                @if($data->showDays ?? true)
                <div class="col-auto">
                    <span class="display-4 fw-bold countdown-number" data-days>00</span>
                    <div class="small text-uppercase countdown-label">{{ translate('Days') }}</div>
                </div>
                @endif
                @if(($data->showDays ?? true) && ($data->showHours ?? true)) <div class="col-auto">
                    <span class="display-4">:</span>
                </div> @endif

                @if($data->showHours ?? true)
                <div class="col-auto">
                    <span class="display-4 fw-bold countdown-number" data-hours>00</span>
                    <div class="small text-uppercase countdown-label">{{ translate('Hours') }}</div>
                </div>
                @endif
                @if(($data->showHours ?? true) && ($data->showMinutes ?? true)) <div class="col-auto">
                    <span class="display-4">:</span>
                </div> @endif

                @if($data->showMinutes ?? true)
                <div class="col-auto">
                    <span class="display-4 fw-bold countdown-number" data-minutes>00</span>
                    <div class="small text-uppercase countdown-label">{{ translate('Minutes') }}</div>
                </div>
                @endif
                @if(($data->showMinutes ?? true) && ($data->showSeconds ?? true)) <div
                    class="col-auto"><span class="display-4">:</span></div> @endif

                @if($data->showSeconds ?? true)
                <div class="col-auto">
                    <span class="display-4 fw-bold countdown-number" data-seconds>00</span>
                    <div class="small text-uppercase countdown-label">{{ translate('Seconds') }}</div>
                </div>
                @endif
            </div
            @endif

            @if(!empty($data->btnText) && $data->btnStyle !== 'no-button')
            <div class="countdown-action mt-4">
                <a href="{{ $data->btnUrl ?? '#' }}"
                    class="btn {{ $data->btnStyle ?? 'btn-primary' }} btn-md rounded-pill" data-aos="fade-up"
                    data-aos-delay="400">
                    @if(!empty($data->btnIcon))
                    <i class="bi {{ $data->btnIcon }} me-2"></i>
                    @endif
                    {{ $data->btnText }}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
