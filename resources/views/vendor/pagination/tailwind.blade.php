@if ($paginator->hasPages())
<nav class="flex items-center justify-between">
    <p class="text-sm text-slate-500">
        Показано {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} з {{ $paginator->total() }}
    </p>
    <div class="flex items-center gap-1">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
        <span class="px-3 py-1.5 rounded-lg text-sm text-slate-300 cursor-not-allowed"><i class="fa fa-chevron-left text-xs"></i></span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-sm text-slate-600 hover:bg-slate-100 transition"><i class="fa fa-chevron-left text-xs"></i></a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
            <span class="px-2 py-1.5 text-sm text-slate-400">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold bg-indigo-500 text-white">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-sm text-slate-600 hover:bg-slate-100 transition">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-sm text-slate-600 hover:bg-slate-100 transition"><i class="fa fa-chevron-right text-xs"></i></a>
        @else
        <span class="px-3 py-1.5 rounded-lg text-sm text-slate-300 cursor-not-allowed"><i class="fa fa-chevron-right text-xs"></i></span>
        @endif
    </div>
</nav>
@endif
