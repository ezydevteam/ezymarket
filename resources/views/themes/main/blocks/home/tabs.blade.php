@php $data = (object)($data ?? []); @endphp

@if(count($data->tabItems ?? []) > 0)
<div id="{{ $data->uniqueId }}" class="home-tabs {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', ['data' => $data])
    <div class="row align-items-start {{ $data->rowClass }}">
        <div class="{{ $data->colClassTab }}">
            <ul class="nav d-flex {{ $data->tabClass }} {{ $data->tabAlignmentClass }}"
                id="pills-tab-{{ $data->tabsId }}" role="tablist">
                @foreach($data->tabItems as $key => $tab)
                @php $tab = (object)$tab; @endphp
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }} fw-semibold w-100"
                        id="pills-{{ $key }}-{{ $data->tabsId }}-tab"
                        data-bs-toggle="{{ ($data->blockStyle ?? '') === 'underline' ? 'tab' : 'pill' }}"
                        data-bs-target="#pills-{{ $key }}-{{ $data->tabsId }}" type="button" role="tab"
                        aria-controls="pills-{{ $key }}-{{ $data->tabsId }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $tab->title ?? 'Tab ' . ($key + 1) }}
                    </button>
                </li>
                @endforeach
            </ul>
        </div>
        <div class="{{ $data->colClassContent }}">
            <div class="tab-content border rounded-4 p-4 bg-{{ $data->tabBgStyle }} shadow-{{ $data->tabContentShadow ? 'sm' : 'none' }} h-100"
                id="pills-tabContent-{{ $data->tabsId }}">
                @foreach($data->tabItems as $key => $tab)
                @php $tab = (object)$tab; @endphp
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                    id="pills-{{ $key }}-{{ $data->tabsId }}" role="tabpanel"
                    aria-labelledby="pills-{{ $key }}-{{ $data->tabsId }}-tab">
                    <div class="rich-text-content">
                        {!! nl2br($tab->htmlContent ?? '') !!}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
