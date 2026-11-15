@props(['route', 'label', 'icon'])

@php $active = request()->routeIs($route); @endphp

<a href="{{ route($route) }}"
   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ $active ? 'bg-forest-800/80 text-gold-300' : 'text-forest-100/70 hover:bg-forest-900 hover:text-white' }}"
   @if($active) aria-current="page" @endif>
    <svg class="h-5 w-5 shrink-0 transition-colors {{ $active ? 'text-gold-400' : 'text-forest-300/70 group-hover:text-gold-300' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
    </svg>
    <span class="truncate">{{ $label }}</span>
    @if ($active)
        <span class="ml-auto h-1.5 w-1.5 shrink-0 rounded-full bg-gold-400"></span>
    @endif
</a>