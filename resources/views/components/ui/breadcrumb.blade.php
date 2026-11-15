@props(['items' => []])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => '']) }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-xs font-medium text-slate-500">
        <li>
            <a href="{{ route('home') }}" class="transition hover:text-gold-600">Beranda</a>
        </li>
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                <svg class="h-3 w-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                @if (!$loop->last && isset($item['url']))
                    <a href="{{ $item['url'] }}" class="transition hover:text-gold-600">{{ $item['label'] }}</a>
                @else
                    <span class="text-forest-800" @if($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>