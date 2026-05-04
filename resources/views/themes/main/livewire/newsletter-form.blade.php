<div>
    @php
        $btnContent = '';
        if($buttonDisplay === 'icon_only') {
            $btnContent = '<i class="bi '.$buttonIcon.'"></i>';
        } elseif($buttonDisplay === 'both') {
            $btnContent = '<i class="bi '.$buttonIcon.' me-1"></i> ' . $buttonText;
        } else {
            $btnContent = $buttonText;
        }
        $btnClass = 'btn-' . $buttonStyle . ' px-3';
    @endphp

    @if ($style === 'footer')
        {{-- Original Footer Style --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="flex-shrink-0 d-flex align-items-center gap-3">
                <span>
                    <i class="bi bi-envelope-check fs-1 text-primary"></i>
                </span>
                <div>
                    <h4 class="fw-bold mb-1">{{ $heading }}</h4>
                    <p class="mb-0">{{ $subHeading }}</p>
                </div>
            </div>
            <div class="flex-grow-1">
                <form wire:submit.prevent="subscribe">
                    <div class="input-group">
                        <input type="email" wire:model="email" class="form-control form-control-md"
                            placeholder="{{ $placeholder }}" required>
                        <button class="btn {{ $btnClass }} btn-md" type="submit">
                            {!! $btnContent !!}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    @elseif ($style === 'card')
        {{-- Card Style (for widget) --}}
        <form wire:submit.prevent="subscribe">
            @if($showName)
                <div class="mb-2">
                    <input type="text" wire:model="name" class="form-control" placeholder="{{ $namePlaceholder }}">
                </div>
            @endif
            <div class="input-group">
                <input type="email" wire:model="email" class="form-control"
                    placeholder="{{ $placeholder }}" required>
                <button class="btn {{ $btnClass }}" type="submit">
                    {!! $btnContent !!}
                </button>
            </div>
        </form>

    @elseif ($style === 'inline')
        {{-- Inline Style (for widget) --}}
        <form wire:submit.prevent="subscribe">
            <div class="input-group input-group-sm">
                @if($showName)
                    <input type="text" wire:model="name" class="form-control" placeholder="{{ $namePlaceholder }}">
                @endif
                <input type="email" wire:model="email" class="form-control"
                    placeholder="{{ $placeholder }}" required>
                <button class="btn {{ $btnClass }}" type="submit">
                     {!! $btnContent !!}
                </button>
            </div>
        </form>

    @elseif ($style === 'minimal')
        {{-- Minimal Style (Underline) --}}
        <form wire:submit.prevent="subscribe" class="position-relative">
            @if($showName)
                 <input type="text" wire:model="name" class="form-control border-0 border-bottom rounded-0 px-0 shadow-none bg-transparent mb-2"
                    placeholder="{{ $namePlaceholder }}" style="color: inherit;">
            @endif
            <div class="position-relative">
                <input type="email" wire:model="email" class="form-control border-0 border-bottom rounded-0 px-0 shadow-none bg-transparent"
                    placeholder="{{ $placeholder }}" required style="padding-right: 40px; color: inherit;">
                <button class="btn {{ $btnClass }} position-absolute top-0 end-0 p-0 text-decoration-none" type="submit" aria-label="{{ $buttonText }}">
                    {!! $btnContent !!}
                </button>
            </div>
        </form>

    @elseif ($style === 'boxed')
        {{-- Boxed Style --}}
        <form wire:submit.prevent="subscribe" class="p-1 bg-body rounded border">
            @if($showName)
                 <input type="text" wire:model="name" class="form-control border-0 shadow-none mb-1 opacity-75" placeholder="{{ $namePlaceholder }}">
                 <hr class="m-0 text-muted opacity-25">
            @endif
            <div class="input-group">
                 <input type="email" wire:model="email" class="form-control border-0 shadow-none bg-transparent" placeholder="{{ $placeholder }}" required>
                 <button class="btn {{ $btnClass }} rounded" type="submit">{!! $btnContent !!}</button>
            </div>
        </form>

    @elseif ($style === 'pill')
        {{-- Rounded Pill Style --}}
        <form wire:submit.prevent="subscribe">
            @if($showName)
                <input type="text" wire:model="name" class="form-control rounded-pill mb-2 ps-3" placeholder="{{ $namePlaceholder }}">
            @endif
            <div class="input-group">
                 <input type="email" wire:model="email" class="form-control rounded-pill rounded-end-0 ps-3" placeholder="{{ $placeholder }}" required>
                 <button class="btn {{ $btnClass }} rounded-pill rounded-start-0 pe-4" type="submit">{!! $btnContent !!}</button>
            </div>
        </form>

    @elseif ($style === 'modern')
        {{-- Modern Floating Label --}}
         <form wire:submit.prevent="subscribe">
            @if($showName)
            <div class="form-floating text-dark mb-2">
                <input type="text" wire:model="name" class="form-control" id="floatingName-{{ $this->getId() }}" placeholder="{{ $namePlaceholder }}">
                <label for="floatingName-{{ $this->getId() }}">{{ $namePlaceholder }}</label>
            </div>
            @endif
            <div class="form-floating text-dark mb-2">
                <input type="email" wire:model="email" class="form-control" id="floatingNewsletterInput-{{ $this->getId() }}" placeholder="{{ $placeholder }}" required>
                <label for="floatingNewsletterInput-{{ $this->getId() }}">{{ $placeholder }}</label>
            </div>
            <button class="btn {{ $btnClass }} btn-md w-100" type="submit">{!! $btnContent !!}</button>
        </form>

    @else
        {{-- Default/Generic Style --}}
        <form wire:submit.prevent="subscribe" class="d-flex flex-column gap-2">
            @if($showName)
                 <input type="text" wire:model="name" class="form-control" placeholder="{{ $namePlaceholder }}">
            @endif
            <div class="d-flex gap-2 w-100">
                <input type="email" wire:model="email" class="form-control"
                    placeholder="{{ $placeholder }}" required>
                <button class="btn {{ $btnClass }}" type="submit">
                    {!! $btnContent !!}
                </button>
            </div>
        </form>
    @endif
</div>
