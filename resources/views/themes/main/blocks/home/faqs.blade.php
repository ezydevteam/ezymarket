@php $data = (object)($data ?? []); @endphp
@if (!empty($data->faqs) && $data->faqs->count() > 0)
<div id="{{ $data->uniqueId ?? 'faqs' }}" class="home-faqs {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', ['data' => $data])
    <div class="section-body">
        <div class="accordion-custom">
            <div class="accordion" id="accordion-{{ $data->uniqueId ?? 'faqs' }}">
                <div class="row g-3 justify-content-{{ $data->blockAlignment ?? 'left' }}">
                    @foreach ($data->faqs as $faq)
                    @php $isFirst = $loop->first && ($data->collapseFirst ?? false); @endphp
                    <div class="col-12 col-md-{{ $data->colClass ?? '12' }}" data-aos="fade-up"
                        data-aos-duration="{{ min(($loop->index + 1) * 100, 800) }}">
                        <div class="accordion-item {{ $data->itemClass ?? '' }}">
                            <h2 class="accordion-header"
                                id="heading-{{ $data->uniqueId ?? 'faqs' }}-{{ $loop->index }}">
                                <button
                                    class="accordion-button {{ $isFirst ? '' : 'collapsed' }} {{ $data->btnClass ?? '' }}"
                                    type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse-{{ $data->uniqueId ?? 'faqs' }}-{{ $loop->index }}"
                                    aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                                    aria-controls="collapse-{{ $data->uniqueId ?? 'faqs' }}-{{ $loop->index }}">
                                    <div class="accordion-button-icon">
                                        @if(($data->faqIcon ?? 'plus_minus') === 'chevron')
                                        <i class="bi bi-chevron-down fs-6"></i>
                                        @else
                                        <i class="bi bi-plus-lg fs-6"></i>
                                        <i class="bi bi-dash-lg fs-6"></i>
                                        @endif
                                    </div>
                                    {{ $faq->question }}
                                </button>
                            </h2>
                            <div id="collapse-{{ $data->uniqueId ?? 'faqs' }}-{{ $loop->index }}"
                                class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}"
                                aria-labelledby="heading-{{ $data->uniqueId ?? 'faqs' }}-{{ $loop->index }}"
                                data-bs-parent="#accordion-{{ $data->uniqueId ?? 'faqs' }}">
                                <div class="accordion-body">
                                    {!! $faq->answer !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
