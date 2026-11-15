@props(['status'])

@php
    $status = $status instanceof \App\Enums\ArticleStatus ? $status : \App\Enums\ArticleStatus::from($status);
    $color = match ($status) {
        \App\Enums\ArticleStatus::Draft => 'gray',
        \App\Enums\ArticleStatus::InReview => 'amber',
        \App\Enums\ArticleStatus::Revision => 'red',
        \App\Enums\ArticleStatus::Published => 'forest',
        \App\Enums\ArticleStatus::Scheduled => 'sky',
        \App\Enums\ArticleStatus::Archived => 'gray',
    };
@endphp

<x-ui.badge :color="$color" {{ $attributes }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $status->label() }}
</x-ui.badge>