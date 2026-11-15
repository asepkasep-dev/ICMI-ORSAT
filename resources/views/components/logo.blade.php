@props(['class' => 'h-10 w-10'])

{{-- Bawaan: emblem logo ICMI nasional (icmi.id); Admin dapat menggantinya di Pengaturan Website. --}}
<img src="{{ config('icmi.logo_url') ?? asset('images/icmi-emblem.png') }}" alt="" aria-hidden="true"
     {{ $attributes->merge(['class' => $class.' object-contain']) }}>
