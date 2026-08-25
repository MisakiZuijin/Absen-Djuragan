@forelse($handRaises as $index => $handRaise)
    <tr class="raise-hand-row group hover:bg-gray-50"
        data-name="{{ strtolower($handRaise->user->profile->full_name ?? $handRaise->user->name ?? '') }}"
        data-school="{{ strtolower($handRaise->user->intern->school->name ?? '') }}"
        data-phone="{{ $handRaise->user->profile->phone ?? '' }}"
        data-status="menunggu">
        <td class="py-4 px-3">
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-semibold text-gray-600">{{ $index + 1 }}</div>
        </td>
        <td class="py-4 px-3">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="w-12 h-12 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold text-lg">
                        {{ strtoupper(substr($handRaise->user->profile->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-500 rounded-full animate-pulse border-2 border-white"></div>
                </div>
                <div>
                    <div class="font-semibold text-gray-900 user-name">{{ $handRaise->user->profile->full_name ?? '-' }}</div>
                    <div class="text-sm text-gray-500 user-school">{{ $handRaise->user->intern->school->name ?? 'Sekolah tidak diketahui' }}</div>
                    <div class="text-sm text-gray-500 mt-1 user-phone flex items-center gap-2">
                        <i class="fa-solid fa-phone fa-xs"></i>
                        <span>{{ $handRaise->user->profile->phone ?? 'No HP tidak ada' }}</span>
                    </div>
                    <div class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-clock"></i>
                        <span>{{ $handRaise->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </td>
        <td class="py-4 px-3 status-cell">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-yellow-100 text-yellow-800 font-medium">
                <i class="fa-solid fa-hand-paper animate-bounce"></i> Menunggu Bantuan
            </span>
        </td>
        <td class="py-4 px-3">
            <form method="POST" action="{{ route('admin.raiseHand.confirm', $handRaise->id) }}" class="confirm-form">
                @csrf
                @method('DELETE')
                <button type="button" class="confirm-btn px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    Selesai
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center py-16 text-gray-500">
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-solid fa-check-circle text-3xl text-green-500"></i>
                </div>
                <p class="font-semibold text-lg text-gray-700">Semua permintaan sudah diselesaikan!</p>
                <p class="text-sm text-gray-500 mt-1">Tidak ada yang membutuhkan bantuan saat ini</p>
            </div>
        </td>
    </tr>
@endforelse