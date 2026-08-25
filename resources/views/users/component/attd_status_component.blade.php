<div class="relative group container bg-gray-200 rounded-lg text-center {{ $isAdjustable ? 'py-4' : 'py-10' }} flex-1">
    @if (!empty($message))
        <div
            class="absolute hidden group-hover:block bg-gray-200 text-gray-900 text-sm p-2 rounded-lg shadow-lg
-top-12 left-1/2 transform -translate-x-1/2 md:-top-12 break-words w-2/3">
            {{ $message }}
            <div class="absolute bg-gray-200 w-4 h-4 left-1/2 transform -translate-x-1/2 rotate-45 -bottom-2">
            </div>
        </div>
    @endif

    <div class="flex flex-col items-center">
        <div class="flex items-center space-x-2">
            <span class="{{ empty($absence_category) ? 'text-gray-400' : 'text-red-500' }} text-sm">
                <i class="fa-solid fa-circle"></i>
            </span>
            <div class="font-bold">{{ $type }}</div>
        </div>
        <div class="mt-2">{{ $absence_category ?? '---' }}</div>
    </div>
</div>
