@extends('layouts.main')

@section('title', 'Divisi')

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">

        <div class="container mx-auto">
            <!-- Header with Tabs and Search -->
            <div class="flex flex-col md:flex-row justify-between md:items-center gap-3 border-b border-gray-300 pb-2 md:pb-0">
                <!-- Tab Navigation -->
                <div class="flex space-x-4 sm:space-x-6">
                    <button id="tab1"
                        class="text-gray-800 py-2 px-3 sm:px-4 border-b-2 border-black font-semibold text-sm sm:text-base">Aktif</button>
                    <button id="tab2" class="text-gray-500 py-2 px-3 sm:px-4 text-sm sm:text-base">Belum Aktif<span class="text-xs sm:text-sm text-gray-400 mb-1">
                            ({{ sizeOf($internWithoutDivision) }})</span></button>
                </div>
                <!-- Action & Search Box -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-1">
                    <a href="{{ route('admin.interns.create') }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-xs font-bold shadow-xs transition">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>Tambah Pemagang</span>
                    </a>
                    <div class="flex items-center border border-gray-300 rounded-full flex-1 sm:flex-initial">
                        <div class="bg-white p-2 rounded-l-full">
                            <i class="ml-2 fa fa-search text-gray-500 text-xs"></i>
                        </div>
                        <input type="text" id="searchInput" placeholder="Cari divisi/nama"
                            class="py-2 pl-2 pr-4 rounded-r-full text-gray-800 focus:outline-none w-full sm:w-64 text-xs sm:text-sm">
                    </div>
                </div>

            </div>

            <!-- Tab Content -->
            <div id="content1" class="py-6">
                <!-- Grid Layout -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <!-- Grid Item -->

                    @foreach ($division as $division)
                        <a href="{{ route('admin.division.team', ['divisionId' => $division->id]) }}" class="division-item">
                            <div
                                class=" flex iteminternWithoutDivisions-center space-x-4 p-4 bg-white rounded-lg shadow hover:shadow-lg">
                                <div class="p-4">
                                    <img src="{{ asset('img/' . $division->icon) }}" alt="{{ $division->name }} Icon"
                                        class="w-6 sm:w-6 md:w-6 lg:w-6">
                                </div>
                                <div>
                                    <div class="text-lg font-semibold">{{ $division->name }}</div>
                                    <div class="text-gray-500">{{ $division->count }} Anggota</div>
                                </div>
                            </div>
                        </a>
                    @endforeach

                </div>
                <!-- Link See All Teams -->
                <div class="mt-6 text-red-500">
                    {{-- i set the query param to 0 because it cant null --}}
                    <a href="{{ route('admin.division.team', ['divisionId' => 0]) }}" class="font-semibold">See
                        all teams...</a>
                </div>
            </div>

            <!-- Tab 2 Content (Initially Hidden) -->
            <div id="content2" class="hidden">
                <!-- List Layout -->
                <div class="space-y-4">
                    @forelse ($internWithoutDivision as $intern)
                        <a href="{{ route('admin.division.edit.view', ['userId' => $intern->user->id]) }}"
                            class="intern-item p-2">
                            <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow hover:shadow-lg">
                                <span class="font-semibold">{{ $intern->user->profile->full_name }}</span>
                                <span class="text-gray-500">{{ $intern->user->getDurationFromCreation() }} yang lalu</span>
                            </div>
                        </a>
                    @empty
                        <div class="p-2">
                            <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow hover:shadow-lg">
                                <span class="font-semibold">Tidak ada</span>
                                <span class="text-gray-500"></span>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>

    <script>
        // #FITUR DIVISI

        document.getElementById('searchInput').addEventListener('input', function() {
            let filter = this.value.toLowerCase();
            let divisionItems = document.querySelectorAll('.division-item');
            let internItems = document.querySelectorAll('.intern-item'); // Menambahkan pencarian untuk nama intern

            // Pencarian untuk divisi
            divisionItems.forEach(function(item) {
                let divisionTextElement = item.querySelector('.text-lg');
                if (divisionTextElement) {
                    let divisionName = divisionTextElement.innerText.toLowerCase();
                    if (divisionName.includes(filter)) {
                        item.style.display = ''; // Menampilkan item jika cocok
                    } else {
                        item.style.display = 'none'; // Menyembunyikan item jika tidak cocok
                    }
                } else {
                    item.style.display = 'none';
                }
            });

            // Pencarian untuk nama intern
            internItems.forEach(function(item) {
                let internNameElement = item.querySelector('.font-semibold');
                if (internNameElement) {
                    let internName = internNameElement.innerText.toLowerCase();
                    if (internName.includes(filter)) {
                        item.style.display = ''; // Menampilkan item jika cocok
                    } else {
                        item.style.display = 'none'; // Menyembunyikan item jika tidak cocok
                    }
                } else {
                    item.style.display = 'none';
                }
            });
        });


        // Get tab buttons and content
        const tab1Button = document.getElementById('tab1');
        const tab2Button = document.getElementById('tab2');
        const content1 = document.getElementById('content1');
        const content2 = document.getElementById('content2');

        // Add event listeners for tab buttons
        tab1Button.addEventListener('click', () => {
            content1.classList.remove('hidden');
            content2.classList.add('hidden');
            tab1Button.classList.add('text-gray-800', 'border-b-2', 'border-black', 'font-semibold');
            tab2Button.classList.remove('text-gray-800', 'border-b-2', 'border-black', 'font-semibold');
            tab2Button.classList.add('text-gray-500');
        });

        tab2Button.addEventListener('click', () => {
            content1.classList.add('hidden');
            content2.classList.remove('hidden');
            tab2Button.classList.add('text-gray-800', 'border-b-2', 'border-black', 'font-semibold');
            tab1Button.classList.remove('text-gray-800', 'border-b-2', 'border-black', 'font-semibold');
            tab1Button.classList.add('text-gray-500');
        });



        // # DETAIL DIVISI

        function toggleDropdown(menuId, event) {
            event.stopPropagation(); // Prevents the click event from bubbling up to the document

            // Hide all menus
            document.querySelectorAll('.dropdown-menu').forEach(function(dropdown) {
                if (dropdown.id !== menuId) {
                    dropdown.classList.add('hidden');
                    dropdown.classList.remove('show');
                }
            });

            // Toggle the visibility of the clicked menu
            const menu = document.getElementById(menuId);
            if (menu.classList.contains('show')) {
                menu.classList.remove('show');
                menu.classList.add('hidden');
            } else {
                menu.classList.remove('hidden');
                menu.classList.add('show');
            }
        }

        function openDeleteModal(event, teamId) {
            event.preventDefault(); // Prevent the default action of the link

            // Hide all dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(function(dropdown) {
                dropdown.classList.add('hidden');
                dropdown.classList.remove('show');
            });

            const modal = document.getElementById('deleteModal');
            const confirmButton = document.getElementById('confirmDelete');

            // Set the action for the confirm button
            confirmButton.onclick = function() {
                fetch(`/admin/divisi/delete/${teamId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            location.reload();
                        } else {
                            console.error('Error:', response.statusText);
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                    })
                    .finally(() => {
                        closeDeleteModal();
                    });
            };


            modal.classList.remove('hidden');
            modal.classList.add('block'); // Ensure modal is visible
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
