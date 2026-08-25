        @extends('layouts.main')

        @section('title', 'Detail Divisi')

        @section('contents')
            @include('layouts.sidebar')

            @include('layouts.navbar')

            <!-- Main Content -->
            <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64">

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
                    <div class="flex justify-between items-center border-b border-gray-300">
                        <!-- Back Icon -->
                        <div class="flex space-x-6">
                            <a href="{{ route('admin.division') }}" class="text-2xl"><i
                                    class="fa-solid fa-chevron-left"></i>
                                Kembali</a>
                        </div>
                        <!-- Search Box -->
                        <div class="flex flex-col space-y-2 mb-6">
                            <div class="flex items-center border border-gray-300 rounded-full">
                                <div class="bg-white p-2 rounded-l-full">
                                    <i class="ml-2 fa fa-search text-gray-500"></i>
                                </div>

                                <input type="text" id="searchInput" placeholder="Cari anggota divisi"
                                    class="w-full py-2 pl-3 pr-4 rounded-r-full text-gray-800 focus:outline-none">
                            </div>
                        </div>

                    </div>

                    <div class="mt-2 rounded-lg shadow-md">
                        <!-- Filter Data Anggota -->
                        <div class="mb-4 bg-gray-700 p-2 rounded">
                            <h1 class="text-1xl font-semibold text-white">Filter data anggota</h1>
                        </div>
                        <!-- Bulk Action and Project Section -->
                        <div class="inline-block w-full ml-2">
                            <!-- Bulk Action Section -->
                            <div class="flex items-center space-x-4 mb-4">
                                <!-- Bulk Select All Checkbox -->
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" id="bulkAction" class="form-checkbox h-4 w-4 text-blue-400"
                                        onclick="toggleSelectAll()">
                                    <label for="bulkAction" class="text-black">Select All</label>
                                </div>

                                <!-- Bulk Action Dropdown -->
                                <select id="bulkActionSelect"
                                    class="form-select py-2 px-3 border border-gray-300 rounded-lg">
                                    <option value="">Bulk Action</option>
                                    <option value="Tambah">Tambah</option>
                                    <option value="Hapus">Hapus</option>
                                </select>

                                <form id="bulkActionForm" action="{{ route('bulk.action') }}" method="POST">
                                    @csrf
                                    <input type="hidden" id="bulkActionInput" name="action" value="">
                                    <input type="hidden" id="projectIdInput" name="projectId" value="">
                                    <button type="submit" id="bulkActionSubmitButton" class="hidden"></button>
                                </form>

                                <!-- Project Select Dropdown -->
                                <label for="projectSelect" class="text-black">Project :</label>
                                <select id="projectSelect" class="form-select py-2 px-3 border border-gray-300 rounded-lg">
                                    <option value="">Pilih Project</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">- {{ $project->name }}</option>
                                    @endforeach
                                </select>

                                <!-- Apply Bulk Action Button -->
                                <button id="applyButton" onclick="applyBulkAction()"
                                    class="py-2 px-4 rounded-lg border border-blue-600 text-blue-600 hover:bg-gray-700 hover:text-white hover:border-gray-700">
                                    Apply
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
                                        <input type="checkbox" class="team-checkbox form-checkbox h-4 w-4 text-blue-400 mr-2"
                                            value="{{ $team->user->intern->id }}">
                                        <label for="card1"
                                            class="text-white">{{ $team->user->profile->NIP ?? 'NIP Empty' }}</label>
                                    </div>
                                    <p class="text-gray-700 p-3 mb-4">{{ $team->user->profile->full_name }}</p>
                    
                                    <!-- Sunting dan Hapus di bawah kanan -->
                                    <div class="flex justify-end space-x-1 mt-auto mb-1 mr-1">
                                        <a href="{{ route('admin.division.edit.view', ['userId' => $team->user->id]) }}"
                                            class="text-xs text-white bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded">Sunting</a>
                                        <a onclick="openDeleteModal(event, {{ $team->id }})" href="#"
                                            class="text-xs text-white bg-red-600 hover:bg-red-700 px-2 py-1 rounded">Hapus</a>
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
                    deleteForm.action = `{{ route('admin.intern.destroy', ':id') }}`.replace(':id', internId);

                    // Set nilai hidden input dengan ID tim
                    const deleteTeamId = document.getElementById('deleteTeamId');
                    deleteTeamId.value = internId;

                    // Tampilkan modal
                    modal.classList.remove('hidden');
                    modal.classList.add('flex'); // Pastikan modal terlihat
                }

                function closeDeleteModal() {
                    const modal = document.getElementById('deleteModal');
                    modal.classList.remove('flex');
                    modal.classList.add('hidden'); // Sembunyikan modal
                }


                function closeDeleteModal() {
                    const modal = document.getElementById('deleteModal');
                    modal.classList.add('hidden');
                    modal.classList.remove('block'); // Ensure modal is hidden
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
