@php
    if (auth()->check()) {
        $homeUrl = auth()->user()->hasRole('admin') ? '/admin/dashboard' : '/user/dashboard';
    } else {
        $homeUrl = '/';
    }
@endphp

<a href="{{ $homeUrl }}" {{ $attributes->merge(['class' => '']) }}>
    <div class="flex items-center gap-2">
        <x-icon name="o-sparkles" class="w-8 h-8 text-primary" />
        <span class="font-black text-xl">{{ config('app.name') }}</span>
    </div>
</a>
