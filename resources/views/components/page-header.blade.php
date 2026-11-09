<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-800">{{ $title }}</h2>
        @isset($subtitle)<p class="text-sm text-slate-500 mt-0.5">{{ $subtitle }}</p>@endisset
    </div>
    @isset($action)
    <a href="{{ $action['url'] }}"
       class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm shadow-indigo-200">
        <i class="fa fa-plus text-xs"></i> {{ $action['label'] }}
    </a>
    @endisset
</div>
