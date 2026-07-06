{{-- Colour swatch radios. Params: $field, $palette (array of hex), $selected --}}
<div class="swatches">
    @foreach($palette as $i => $hex)
        <input type="radio" name="{{ $field }}" id="{{ $field }}-{{ $i }}" value="{{ $hex }}" @checked($hex === $selected)>
        <label for="{{ $field }}-{{ $i }}" style="background: {{ $hex }}"></label>
    @endforeach
</div>
