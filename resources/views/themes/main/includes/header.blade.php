<header id="ezymarket-header" class="theme-header">
    @foreach ($headerSections as $section)
        @php
            $sectionId = $section['id'];
            $sectionClass = $section['class'];
            $sectionBorder = $section['border'];
            $sectionPadding = $section['padding'];
            $sectionAttributes = $section['attributes'] ?? '';
            $sectionContainer = $section['container'];
            $sectionColumnGap = $section['columnGap'];
            $isMobileBottom = $sectionId === 'mobile-header-bottom';
        @endphp

        {{-- Skip mobile_header_bottom here, render separately below --}}
        @if($isMobileBottom)
            @continue
        @endif

        <div id="{{ $sectionId }}" class="header-section {{ $sectionClass }} {{ $sectionBorder }} {{ $sectionPadding }}" {!! $sectionAttributes !!}>
            <div class="{{ $sectionContainer }}">
                <div class="header-inner {{ $sectionColumnGap }}">
                    @foreach ($section['columns'] as $colIndex => $column)
                        @php
                            $colId = "{$sectionId}-col-{$colIndex}";
                            $directionClass = $column['directionClass'] ?? 'align-items-center';
                            $alignClass = $column['alignClass'];
                            $blockGap = $section['blockGap'];
                        @endphp
                        <div id="{{ $colId }}" class="header-col">
                            <div class="d-flex h-100 {{ $directionClass }} {{ $alignClass }} {{ $blockGap }}">
                                @foreach ($column['blocks'] as $block)
                                    @php
                                        $blockOptions = (array)($block['options'] ?? []);
                                        $blockUniqueId = $blockOptions['uniqueId'] ?? Str::random(8);
                                        $blockId = $block['id'];
                                        $wrapperClass = $block['wrapperClass'] ?? '';
                                        $view = $block['view'];
                                    @endphp
                                    <div id="block-{{ $blockUniqueId }}" class="header-block block-{{ $blockId }} {{ $wrapperClass }}">
                                        @themeInclude($view, ['data' => $blockOptions])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</header>

{{-- Mobile Bottom Header - Fixed at bottom --}}
@foreach ($headerSections as $section)
    @php
        $sectionId = $section['id'];
        $hasColumns = !empty($section['columns']);
    @endphp

    @if($sectionId === 'mobile-header-bottom' && $hasColumns)
        @php
            $sectionClass = $section['class'];
            $sectionBorder = $section['border'];
            $sectionPadding = $section['padding'];
            $sectionAttributes = $section['attributes'] ?? '';
            $sectionContainer = $section['container'];
            $sectionColumnGap = $section['columnGap'];
        @endphp
        <div id="{{ $sectionId }}" class="header-section {{ $sectionClass }} {{ $sectionBorder }} {{ $sectionPadding }}" {!! $sectionAttributes !!}>
            <div class="{{ $sectionContainer }}">
                <div class="header-inner {{ $sectionColumnGap }}">
                    @foreach ($section['columns'] as $colIndex => $column)
                        @php
                            $colId = "{$sectionId}-col-{$colIndex}";
                            $directionClass = $column['directionClass'] ?? 'align-items-center';
                            $alignClass = $column['alignClass'];
                            $blockGap = $section['blockGap'];
                        @endphp
                        <div id="{{ $colId }}" class="header-col">
                            <div class="d-flex h-100 {{ $directionClass }} {{ $alignClass }} {{ $blockGap }}">
                                @foreach ($column['blocks'] as $block)
                                    @php
                                        $blockOptions = (array)($block['options'] ?? []);
                                        $blockUniqueId = $blockOptions['uniqueId'] ?? Str::random(8);
                                        $blockId = $block['id'];
                                        $wrapperClass = $block['wrapperClass'] ?? '';
                                        $view = $block['view'];
                                    @endphp
                                    <div id="block-{{ $blockUniqueId }}" class="header-block block-{{ $blockId }} {{ $wrapperClass }}">
                                        @themeInclude($view, ['data' => $blockOptions])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endforeach
