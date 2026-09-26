        @extends('layouts.main')

        @section('title', 'Detail Divisi')

        @section('contents')
            <!-- Main Content -->
            <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">

                @if (session('success'))
                    <div id="success-message"
                        class="mb-2 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative transition-opacity duration-500"
                        role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="removeMessage()">
                            <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20">
                                <title>Close</title>
                                <path
                                    d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                            </svg>
                        </span>
                    </div>
                @endif

                @if (session('error'))
                    <div id="error-message"
                        class="mb-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative transition-opacity duration-500"
                        role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="removeMessage()">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20">
                                <title>Close</title>
                                <path
                                    d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                            </svg>
                        </span>
                    </div>
                @endif


                <div class="container mx-auto">
                    <!-- Header with Icon and Search -->
                    <!-- Header with Icon and Search -->
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 pb-3 border-b border-gray-200">
                        <!-- Back Icon -->
                        <div class="flex items-center">
                            <a href="{{ route('admin.division') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-black transition">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                                <span>Kembali</span>
                            </a>
                        </div>
                        <!-- Action & Search Box -->
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.interns.create') }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-xs font-bold shadow-xs transition">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Tambah Pemagang</span>
                            </a>
                            <div class="relative w-full sm:w-60">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa fa-search text-gray-400 text-xs"></i>
                                </div>
                                <input type="text" id="searchInput" placeholder="Cari anggota atau NIP..."
                                    class="w-full pl-8 pr-4 py-1.5 text-xs border border-gray-300 rounded-full text-gray-800 focus:outline-none focus:ring-1 focus:ring-gray-500 bg-white">
                            </div>
                        </div>
                    </div>

                    <!-- Filter & Bulk Action Section (Compact & Responsive) -->
                    <div class="mt-4 mb-4 bg-white border border-gray-200 rounded-xl p-3 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <!-- Left: Title & Select All -->
                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    <i class="fa-solid fa-filter text-blue-600"></i>
                                    <span>Filter Data Anggota</span>
                                </div>
                                <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md border border-gray-200 bg-gray-50 hover:bg-gray-100 cursor-pointer text-xs font-medium text-gray-700 transition select-none">
                                    <input type="checkbox" id="bulkAction" class="form-checkbox h-3.5 w-3.5 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                        onclick="toggleSelectAll()">
                                    <span>Select All</span>
                                </label>
                            </div>

                            <!-- Right: Bulk Action, Project, & Apply Button -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <!-- Bulk Action Dropdown -->
                                <select id="bulkActionSelect"
                                    class="text-xs py-1.5 px-2.5 border border-gray-300 rounded-md bg-white text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option value="">Bulk Action</option>
                                    <option value="Tambah">Tambah ke Project</option>
                                    <option value="Hapus">Hapus dari Project</option>
                                </select>

                                <!-- Hidden Form for Bulk Action -->
                                <form id="bulkActionForm" action="{{ route('bulk.action') }}" method="POST" class="hidden">
                                    @csrf
                                    <input type="hidden" id="bulkActionInput" name="action" value="">
                                    <input type="hidden" id="projectIdInput" name="projectId" value="">
                                    <button type="submit" id="bulkActionSubmitButton" class="hidden"></button>
                                </form>

                                <!-- Project Select Dropdown -->
                                <div class="flex items-center gap-1.5">
                                    <label for="projectSelect" class="text-xs font-medium text-gray-600 hidden sm:inline">Project :</label>
                                    <select id="projectSelect" class="text-xs py-1.5 px-2.5 border border-gray-300 rounded-md bg-white text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 max-w-[180px] truncate">
                                        <option value="">Pilih Project</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Apply Bulk Action Button -->
                                <button id="applyButton" onclick="applyBulkAction()"
                                    class="inline-flex items-center gap-1.5 py-1.5 px-3.5 rounded-md text-xs font-semibold bg-gray-800 text-white hover:bg-gray-700 shadow-sm transition">
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                    <span>Apply</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="teamGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-2">
                        @if($teams->isEmpty())
                            <div class="col-span-full text-center text-gray-500">Not Available</div>
                        @else
                            @foreach ($teams as $team)
                                <div class="team-card bg-white border border-gray-300 rounded-lg hover:shadow-lg flex flex-col cursor-pointer">
                                    <div class="flex items-center mb-2 bg-gray-800 p-3 rounded">
                                        <input type="checkbox" id="team-check-{{ $team->id }}" class="team-checkbox form-checkbox h-4 w-4 text-blue-400 mr-2"
                                            value="{{ $team->id }}">
                                        <label for="team-check-{{ $team->id }}"
                                            class="text-white cursor-pointer">{{ $team->user->profile->NIP ?? 'NIP Empty' }}</label>
                                    </div>
                                    <p class="text-gray-700 px-3 pt-3 font-medium">{{ $team->user->profile->full_name }}</p>
                                    @if($team->brand)
                                        <div class="px-3 pb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                                <i class="fa-solid fa-tag mr-1 text-[9px]"></i> {{ $team->brand->name }}
                                            </span>
                                        </div>
                                    @endif
                    
                                    <!-- Sunting dan Hapus di bawah kanan -->
                                    <div class="flex justify-end space-x-1 mt-auto mb-2 mr-2">
                                        <a href="{{ route('admin.division.edit.view', ['userId' => $team->user->id]) }}"
                                            class="text-xs text-white bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded">Sunting</a>
                                        @if(auth()->user()->role_id == 7)
                                            <a href="#" data-id="{{ $team->id }}" onclick="openDeleteModal(event, this.dataset.id)"
                                                class="text-xs text-white bg-red-600 hover:bg-red-700 px-2 py-1 rounded">Hapus</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    

                </div>
            </main>

            <!-- Modal Hapus -->
            <div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-lg font-semibold mb-4">Konfirmasi Hapus</h3>
                    <p class="mb-4">Apakah Anda yakin ingin menghapus item ini?</p>
                    <!-- Form untuk penghapusan -->
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <!-- Input hidden untuk ID -->
                        <input type="hidden" id="deleteTeamId" name="team_id" value="">

                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="closeDeleteModal()"
                                class="bg-gray-500 text-white px-4 py-2 rounded">Batal</button>
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                document.getElementById('searchInput').addEventListener('input', function() {
                    let filter = this.value.toLowerCase();
                    let teamCards = document.querySelectorAll('.team-card');

                    teamCards.forEach(function(card) {
                        let nameElement = card.querySelector('.text-gray-700');
                        let NIPElement = card.querySelector('.text-white');

                        let name = nameElement ? nameElement.innerText.toLowerCase() : '';
                        let NIP = NIPElement ? NIPElement.innerText.toLowerCase() : '';

                        if (name.includes(filter) || NIP.includes(filter)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });

                function openDeleteModal(event, internId) {
                    event.preventDefault(); // Mencegah aksi default dari link atau tombol

                    // Dapatkan elemen modal dan form
                    const modal = document.getElementById('deleteModal');
                    const deleteForm = document.getElementById('deleteForm');

                    // Set action URL untuk form dengan ID yang dipilih
                    deleteForm.action = "{{ route('admin.intern.destroy', ':id') }}".replace(':id', internId);

                    // Set nilai hidden input dengan ID tim
                    const deleteTeamId = document.getElementById('deleteTeamId');
                    deleteTeamId.value = internId;

                    // Tampilkan modal
                    modal.classList.remove('hidden');
                    modal.classList.add('flex'); // Pastikan modal terlihat
                }

                function closeDeleteModal() {
                    const modal = document.getElementById('deleteModal');
                    modal.classList.remove('flex', 'block');
                    modal.classList.add('hidden'); // Sembunyikan modal
                }

                // Close dropdowns and modal when clicking outside
                document.addEventListener('click', function(event) {
                    if (!event.target.closest('.relative') && !event.target.closest('#deleteModal')) {
                        document.querySelectorAll('.dropdown-menu').forEach(function(dropdown) {
                            dropdown.classList.add('hidden');
                            dropdown.classList.remove('show');
                        });
                    }
                });

                document.addEventListener('DOMContentLoaded', () => {
                    const searchInput = document.getElementById('searchInput');
                    const teamCards = document.querySelectorAll('.team-card');

                    searchInput.addEventListener('input', function() {
                        const searchValue = this.value.toLowerCase();

                        teamCards.forEach(card => {
                            const name = card.querySelector('p').textContent.toLowerCase();
                            const nip = card.querySelector('label').textContent.toLowerCase();

                            // Cek apakah nama atau NIP cocok dengan nilai pencarian
                            if (name.includes(searchValue) || nip.includes(searchValue)) {
                                card.style.display = ''; // Tampilkan jika cocok
                            } else {
                                card.style.display = 'none'; // Sembunyikan jika tidak cocok
                            }
                        });
                    });
                });

                // Toggle Select All Function
                function toggleSelectAll() {
                    const checkboxes = document.querySelectorAll('.team-checkbox');
                    const selectAll = document.getElementById('bulkAction').checked;
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = selectAll;
                    });
                }

                // Apply Bulk Action Function
                function applyBulkAction() {
                    const selectedCheckboxes = Array.from(document.querySelectorAll('.team-checkbox:checked'));
                    const selectedIds = selectedCheckboxes.map(checkbox => checkbox.value);
                    const bulkAction = document.getElementById('bulkActionSelect').value;
                    const projectId = document.getElementById('projectSelect').value;

                    if (!bulkAction) {
                        alert('Please select a bulk action.');
                        return;
                    }

                    if (selectedIds.length === 0) {
                        alert('Please select at least one team member.');
                        return;
                    }

                    if (!projectId) {
                        alert('Please select a project.');
                        return;
                    }

                    // Set hidden input values and submit the form
                    document.getElementById('bulkActionInput').value = bulkAction;
                    document.getElementById('projectIdInput').value = projectId;

                    const form = document.getElementById('bulkActionForm');

                    // Clear previous team member inputs
                    document.querySelectorAll('input[name="team[]"]').forEach(input => input.remove());

                    // Add selected IDs as hidden inputs
                    selectedIds.forEach(id => {
                        let input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'team[]';
                        input.value = id;
                        form.appendChild(input);
                    });

                    // Submit the form
                    document.getElementById('bulkActionSubmitButton').click();
                }

                document.addEventListener('DOMContentLoaded', function() {
                    const message = document.getElementById('success-message');
                    if (message) {
                        setTimeout(() => {
                            message.style.opacity = 0;
                            setTimeout(() => message.remove(), 600);
                        }, 3000);
                    }
                });
            </script>
        @endsection
