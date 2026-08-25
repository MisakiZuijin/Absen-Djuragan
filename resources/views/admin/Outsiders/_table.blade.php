<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" id="table-wrapper">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nama
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipe
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Terkait
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Notifikasi
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($outsiders as $outsider)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-sm">
                                    <span class="text-white font-medium text-sm">
                                        {{ strtoupper(substr($outsider->profile->full_name ?? $outsider->username, 0, 2)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                                    {{ $outsider->profile->full_name ?? 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $outsider->email }}
                                </div>
                            </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                        <span class="type-badge type-{{ $outsider->outsider->type }}">
                            {{ ucfirst($outsider->outsider->type) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                        <span class="status-badge {{ $outsider->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $outsider->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($outsider->outsider->type == 'guru')
                            @if($outsider->outsider->interns->isNotEmpty())
                                <span class="text-blue-600 font-medium">{{ $outsider->outsider->interns->first()->school->name ?? 'N/A' }}</span>
                            @else
                                <span class="text-gray-500 italic">Belum terkait</span>
                            @endif
                        @else
                            @if($outsider->outsider->interns->isNotEmpty())
                                <span class="text-green-600 font-medium">{{ $outsider->outsider->interns->first()->user->profile->full_name ?? 'N/A' }}</span>
                            @else
                                <span class="text-gray-500 italic">Belum terkait</span>
                            @endif
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                        @if($outsider->outsider->type == 'ortu')
                            <span class="notification-badge {{ $outsider->outsider->notif_enabled ? 'notification-enabled' : 'notification-disabled' }}">
                                <i class="fa fa-bell mr-1"></i>
                                {{ $outsider->outsider->notif_enabled ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            @else
                            <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <!-- Edit Button -->
                            <a href="{{ route('admin.outsiders.edit', $outsider->id) }}"
                                class="action-button inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors duration-200"
                                title="Edit Outsider">
                                <i class="fa fa-edit mr-1"></i>
                                <span>Edit</span>
                            </a>

                            <!-- Delete Button -->
                            <form action="{{ route('admin.outsiders.destroy', $outsider->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="action-button inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition-colors duration-200"
                                    onclick="return confirm('Yakin ingin menghapus outsider ini? Tindakan ini tidak dapat dibatalkan.')"
                                    title="Hapus Outsider">
                                    <i class="fa fa-trash mr-1"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                                <i class="fa fa-users text-3xl text-gray-400"></i>
                            </div>
                            <p class="text-xl font-medium text-gray-900 mb-2">Tidak ada data outsider</p>
                            <p class="text-sm text-gray-500 mb-4">Mulai dengan menambahkan outsider baru</p>
                            <a href="{{ route('admin.outsiders.create') }}"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                                <i class="fa fa-plus mr-2"></i>Tambah Outsider Pertama
                            </a>
                        </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($outsiders->hasPages())
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $outsiders->appends(request()->query())->links() }}
        </div>
    @endif
</div>
