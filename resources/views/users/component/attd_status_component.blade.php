<div class="relative group bg-white border border-slate-200/90 rounded-2xl text-center py-5 sm:py-7 px-3 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-center items-center min-w-0 w-full">
    @if (!empty($message))
        <div class="absolute hidden group-hover:block z-30 bg-slate-900 text-white text-xs p-2.5 rounded-xl shadow-lg -top-12 left-1/2 -translate-x-1/2 break-words max-w-[220px] pointer-events-none">
            {{ $message }}
            <div class="absolute bg-slate-900 w-2.5 h-2.5 left-1/2 -translate-x-1/2 rotate-45 -bottom-1"></div>
        </div>
    @endif

    <div class="flex items-center justify-center gap-2 w-full">
        <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ empty($absence_category) ? 'bg-slate-300' : 'bg-emerald-500 ring-4 ring-emerald-100' }}"></span>
        <span class="text-sm sm:text-base font-bold text-slate-800 tracking-tight truncate">{{ $type }}</span>
    </div>
    <div class="mt-2 text-base sm:text-lg md:text-xl font-mono font-bold tracking-tight {{ empty($absence_category) ? 'text-slate-400 font-normal' : 'text-slate-900' }}">
        {{ $absence_category ?? '--:--:--' }}
    </div>
</div>
