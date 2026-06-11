{{-- Quick "open the integrated tool" buttons. Usage:
     @include('partials.tool-links', ['tools' => ['staff','bgr_portal'], 'class' => 'ms-auto']) --}}
@php $links = collect($tools ?? [])->map(fn ($k) => config("integrations.tool_links.$k"))->filter(); @endphp
@if($links->isNotEmpty())
    <div class="d-flex gap-2 flex-wrap align-items-center {{ $class ?? '' }}">
        @foreach($links as $l)
            <a href="{{ $l['url'] }}" target="_blank" rel="noopener"
               class="btn btn-sm btn-outline-primary" title="Open {{ $l['label'] }} in a new tab">
                <i class="bi {{ $l['icon'] }}"></i> {{ $l['label'] }} <i class="bi bi-box-arrow-up-right small"></i>
            </a>
        @endforeach
    </div>
@endif
