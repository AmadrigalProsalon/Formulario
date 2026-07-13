@if ($paginator->hasPages())
<nav role="navigation" aria-label="Paginación" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="text-sm text-slate-500">
        Mostrando <span class="font-bold text-slate-800">{{ $paginator->firstItem() }}</span>
        a <span class="font-bold text-slate-800">{{ $paginator->lastItem() }}</span>
        de <span class="font-bold text-slate-800">{{ $paginator->total() }}</span> registros
    </div>
    <div class="flex flex-wrap items-center gap-1.5">
        @if ($paginator->onFirstPage())
            <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-slate-100 px-3 text-sm font-semibold text-slate-400">← Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm hover:border-violet-300 hover:text-violet-700">← Anterior</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex h-10 min-w-10 items-center justify-center px-2 text-slate-400">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 px-3 text-sm font-black text-white shadow-md shadow-violet-500/20">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm hover:border-violet-300 hover:text-violet-700">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm hover:border-violet-300 hover:text-violet-700">Siguiente →</a>
        @else
            <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-slate-100 px-3 text-sm font-semibold text-slate-400">Siguiente →</span>
        @endif
    </div>
</nav>
@endif
