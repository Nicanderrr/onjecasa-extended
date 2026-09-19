@props(['for' => null])  {{-- Default value of null --}}

@if ($for)
    @error($for)
        <p {{ $attributes->merge(['class' => 'text-sm text-red-600']) }}>{{ $message }}</p>
    @enderror
@endif
