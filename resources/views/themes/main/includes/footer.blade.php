<footer id="ezymarket-footer" class="theme-footer mt-auto">
    @foreach ($footerSections as $section)
        @php
            $sectionId = $section['id'];
            $sectionClass = $section['class'];
            $sectionBorder = $section['border'] ?? '';
            $sectionPadding = $section['padding'];
            $sectionContainer = $section['container'];
            $sectionColumnGap = $section['columnGap'];
            $sectionDirectionClass = $section['directionClass'];
            $sectionBlockGap = $section['blockGap'];
            $sectionBlockWidthClass = $section['blockWidthClass'];
        @endphp
        <div id="{{ $sectionId }}"
            class="footer-section {{ $sectionClass }} {{ $sectionBorder }} {{ $sectionPadding }}">
            <div class="{{ $sectionContainer }}">
                <div class="footer-inner {{ $sectionColumnGap }}">
                    @foreach ($section['columns'] as $colIndex => $column)
                        @php
                            $colId = "{$sectionId}-col-{$colIndex}";
                            $colAlignClass = $column['alignClass'];
                        @endphp
                        <div id="{{ $colId }}"
                            class="footer-col d-flex {{ $sectionDirectionClass }} {{ $sectionBlockGap }} {{ $colAlignClass }}">
                            @foreach ($column['blocks'] as $block)
                                @php
                                    $blockId = $block['id'];
                                    $blockWrapperClass = $block['wrapperClass'] ?? '';
                                    $titleData = $block['titleData'] ?? false;
                                    $blockView = $block['view'];
                                    $blockOptions = $block['options'];
                                @endphp
                                <div class="footer-block block-{{ $blockId }} {{ $sectionBlockWidthClass }} {{ $blockWrapperClass }}">
                                    @if($titleData)
                                        @php
                                            $titleTag = $titleData['tag'];
                                            $titleId = $titleData['id'];
                                            $titleClasses = $titleData['classes'];
                                            $titleText = $titleData['text'];
                                        @endphp
                                        <{{ $titleTag }} id="{{ $titleId }}" class="{{ $titleClasses }}">
                                            {{ $titleText }}
                                        </{{ $titleTag }}>
                                    @endif

                                    @themeInclude($blockView,
                                        ['data' => $blockOptions,
                                        'footerMenus' => $footerMenus]
                                    )
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</footer>
