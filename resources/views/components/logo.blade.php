@props(['class' => 'h-10 w-10'])

{{-- Placeholder emblem geometric — logo resmi organisasi belum tersedia (PRD §12). --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <circle cx="24" cy="24" r="23" fill="#123B32"/>
    <circle cx="24" cy="24" r="22.5" stroke="#D5B46A" stroke-opacity="0.4"/>
    <g fill="#D5B46A">
        <rect x="13" y="13" width="22" height="22" rx="2.5"/>
        <rect x="13" y="13" width="22" height="22" rx="2.5" transform="rotate(45 24 24)"/>
    </g>
    <circle cx="24" cy="24" r="7" fill="#123B32"/>
    <circle cx="24" cy="24" r="3.2" fill="#F7F8F5"/>
</svg>