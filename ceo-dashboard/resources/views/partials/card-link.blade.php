{{-- Subtle external-tool link embedded inside a KPI card.
     Usage: @include('partials.card-link', ['tool' => 'staff'])           --}}
@php $l = config("integrations.tool_links." . ($tool ?? '')); @endphp
@if($l)
    <a href="{{ $l['url'] }}" target="_blank" rel="noopener" class="card-tool-link"
       title="Open {{ $l['label'] }} in a new tab" onclick="event.stopPropagation()">
        {{ $text ?? ('Open ' . $l['label']) }} <i class="bi bi-box-arrow-up-right"></i>
    </a>
@endif
