{{-- Colour swatch radios. Params: $field, $palette (array of hex), $selected.
     $sid makes element ids unique so multiple swatch sets on one page don't
     collide (a duplicate id would make <label for> target the wrong radio). --}}
@php $sid = 'sw-' . \Illuminate\Support\Str::random(6); @endphp
<div class="swatches">
    @foreach($palette as $i => $hex)
        <input type="radio" name="{{ $field }}" id="{{ $sid }}-{{ $i }}" value="{{ $hex }}" @checked($hex === $selected)>
        <label for="{{ $sid }}-{{ $i }}" style="background: {{ $hex }}"></label>
    @endforeach
</div>
