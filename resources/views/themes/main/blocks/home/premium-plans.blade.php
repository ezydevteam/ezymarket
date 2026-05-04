@php $data = (object)($data ?? []); @endphp

@if(isPremiumAvailable() && ($data->premiumTabs ?? collect())->count() > 0)
<div id="{{ $data->premiumPlansId }}" class="home-premium-plans {{ $isFullWidth ? $data->containerClass : '' }}">
        @themeInclude('blocks.home.partials.block-title', ['data' => $data])

        @if ($data->premiumShowSwitcher)
        <ul class="nav nav-pills justify-content-center bg-light rounded-pill p-1 mx-auto mb-4"
            id="pills-tab-{{ $data->premiumPlansId }}" role="tablist">
            @foreach ($data->premiumTabs as $tab)
            @php $tabTargetId = 'pills-' . $tab['id'] . '-' . $data->premiumPlansId; @endphp
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }} rounded-pill px-4"
                    id="{{ $tabTargetId }}-tab" data-bs-toggle="pill" data-bs-target="#{{ $tabTargetId }}" type="button"
                    role="tab" aria-controls="{{ $tabTargetId }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    {{ $tab['label'] }}
                </button>
            </li>
            @endforeach
        </ul>
        @endif

        <div class="tab-content" id="pills-tabContent-{{ $data->premiumPlansId }}">
            @foreach ($data->premiumTabs as $tab)
            @php $tabTargetId = 'pills-' . $tab['id'] . '-' . $data->premiumPlansId; @endphp
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tabTargetId }}" role="tabpanel"
                aria-labelledby="{{ $tabTargetId }}-tab">

                <div class="row row-cols-1 row-cols-md-3 row-cols-xl-4 g-4 justify-content-center">
                    @foreach ($tab['premiumPlans'] as $plan)
                    <div class="col">
                        @themeInclude('partials.premium-plans', ['plan' => $plan, 'blockStyle' => $data->blockStyle,
                        'buttonPosition' => $data->buttonPosition, 'buttonText' => $data->buttonText])
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
@endif
