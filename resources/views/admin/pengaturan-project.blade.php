@extends('layouts.main')

@section('title', 'Pengaturan Project')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.sidebar-pengaturan')

    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-[32rem] mt-24 p-6">
        <!-- Header -->
        <h1 class="text-2xl font-bold mb-2">Manage Project</h1>
        <p class="mb-6 text-gray-600">Membuat Category Project untuk anak magang</p>

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

        <form action="{{ route('projects.store') }}" method="POST" onsubmit="handleFormSubmit(event)">
            @csrf
            <div class="flex gap-8 bg-gray-100">
                <div class="w-1/3 bg-white p-6 rounded shadow">
                    <div class="mb-4">
                        <label for="project-name" class="block text-sm font-medium text-gray-700">Nama Project<span
                            class="text-red-500">*</span></label>
                        <div class="flex items-center space-x-2">
                            <select id="project-name" name="project_name" id="project_name"
                                class="mt-1 block w-full rounded border border-gray-300 shadow-sm p-2" required>
                                <option value="" disabled selected>Pilih nama project</option>
                                @foreach ($nameProject as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                            <!-- Button Icon + -->
                            <button type="button" class="p-2 bg-blue-500 text-white" onclick="toggleModal()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Team Name -->
                    <div class="mb-4">
                        <label for="team-name" class="block text-sm font-medium text-gray-700">Nama Tim<span
                            class="text-red-500">*</span></label>
                        <input type="text" id="team-name" name="team_name" placeholder="Masukkan nama Tim"
                            class="mt-1 block w-full rounded border border-gray-300 shadow-sm p-2" required>
                    </div>

                    <!-- Members -->
                    <div class="mb-4">
                        <label for="search" class="block text-sm font-medium text-gray-700">Anggota</label>
                        <input id="search" type="text" placeholder="Cari Nama atau Kampus"
                            class="mt-1 block w-full rounded border border-gray-300 shadow-sm p-2"
                            onkeyup="filterTable()" />
                        <div class="mt-2 block w-full rounded border border-gray-300 shadow-sm overflow-auto max-h-60">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-3 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Asal Kampus</th>
                                    </tr>
                                </thead>
                                <tbody id="table-body" class="bg-white divide-y divide-gray-200">
                                    @foreach ($intern as $intern)
                                        <tr>
                                            <td class="px-2 py-1 whitespace-nowrap">
                                                <input type="checkbox" name="members[]" value="{{ $intern->id }}">
                                                {{-- This will show 'Nama Tidak Tersedia' if the full_name cannot be found --}}
                                                <label class="ml-2">{{ $intern->user?->profile?->full_name ?? 'Nama Tidak Tersedia' }}</label>
                                            </td>
                                            <td class="px-2 py-1 whitespace-nowrap">{{ $intern->school->name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Teams is member of your projects</p>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700">Deksripsi Project<span
                            class="text-red-500">*</span></label>
                        <textarea id="description" name="description" rows="3" placeholder="Masukkan deskripsi proyek"
                            class="mt-1 block w-full rounded border border-gray-300 shadow-sm p-2" required></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-2 bg-gray-800 text-white rounded shadow hover:bg-gray-700">
                        Simpan
                    </button>
                </div>

                <!-- Project List Section -->
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold">Daftar Project</h2>
                        <div class="flex items-center">
                            <input type="text" id="searchInput" placeholder="Search Project"
                                class="block w-64 pl-3 pr-3 py-2 ml-2 rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                onkeyup="filterProjects()">
                        </div>
                    </div>
                    <div class="bg-white rounded shadow overflow-hidden">
                        <table class="min-w-full" id="projectsTable">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="p-3 text-left"></th>
                                    <th class="p-3 text-left">Project</th>
                                    <th class="p-3 text-left">Tim</th>
                                    <th class="p-3 text-left">Deskripsi</th>
                                    <th class="p-3 text-left">Anggota</th>
                                    <th class="p-3 text-left">Status</th>
                                    <th class="p-3 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="projectsBody">
                                @foreach ($projects as $index => $project)
                                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} border-t border-gray-200"
                                        data-index="{{ $index }}">
                                        <td class="p-3">
                                            <input type="checkbox" class="form-checkbox"
                                                onchange="updateProjectStatus({{ $project->id }}, this.checked)"
                                                {{ $project->status == 'done' ? 'checked' : '' }}>
                                        </td>
                                        <td class="p-3">{{ $project->nameProject->name ?? 'N/A' }}</td>
                                        <td class="p-3">{{ $project->team }}</td>
                                        <td class="p-3">{{ $project->description }}</td>
                                        <td class="p-3">
                                            @if ($project->members->isNotEmpty())
                                                <ul>
                                                    @foreach ($project->members as $intern)
                                                        @if ($intern)
                                                            <li>- {{ $intern->user->profile->full_name }}</li>
                                                        @else
                                                            <li>-</li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="p-3 text-center">
                                            @if ($project->status == 'done')
                                                <i class="fa-solid fa-check"></i>
                                            @elseif ($project->status == 'progress')
                                                <i class="fa-regular fa-hourglass-half"></i>
                                            @else
                                                {{ $project->status }}
                                            @endif
                                        </td>
                                        <td class="p-3 flex space-x-2">
                                            <button class="text-blue-500 hover:text-blue-700"
                                                onclick="editProject(event, {{ json_encode($project) }})">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button class="text-red-500 hover:text-red-700"
                                                onclick="openDeleteModal({{ $project->id }}, event)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex justify-center items-center space-x-2 border rounded-md p-2">
                        <button id="prev-page" onclick="changePage('prev')"
                            class="cursor-pointer bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100"
                            disabled>
                            Previous
                        </button>

                        <div id="page-numbers" class="flex space-x-2"></div>

                        <button id="next-page" onclick="changePage('next')"
                            class="bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100">
                            Next
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </main>

    <!-- Modal Pop-up -->
    <div id="modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
            <h2 class="text-xl font-bold mb-4">Tambah Proyek Baru</h2>
            <form action="{{ route('projects.nameProject') }}" method="POST">
                @csrf
                <input type="text" name="new_project_name" placeholder="Masukkan nama proyek baru"
                    class="w-full border border-gray-300 rounded p-2 mb-4">
                <div class="flex justify-end space-x-2">
                    <button type="button" class="px-4 py-2 bg-gray-300 rounded" onclick="toggleModal()">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Project -->
    <div id="deleteProjectModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Hapus Project</h2>
            <p>Apakah Anda yakin ingin menghapus project ini?</p>
            <form id="deleteProjectForm" action="#" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="deleteProjectId" name="projectId">
                <div class="flex justify-end mt-4">
                    <button type="button" id="closeDeleteProjectModal"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        const rowsPerPage = 5;
        let currentPage = 1;
        const projectsTable = document.getElementById('projectsTable');
        const projectsBody = document.getElementById('projectsBody');
        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');
        const pageNumbersDiv = document.getElementById('page-numbers');
        const rows = Array.from(projectsBody.getElementsByTagName('tr'));

        const totalPages = Math.ceil(rows.length / rowsPerPage);

        function changePage(direction) {
            if (direction === 'prev' && currentPage > 1) {
                currentPage--;
            } else if (direction === 'next' && currentPage < totalPages) {
                currentPage++;
            }

            renderTable();
        }

        function renderTable() {
            // Hide all rows
            rows.forEach(row => row.style.display = 'none');

            // Show only the rows for the current page
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const currentRows = rows.slice(start, end);

            currentRows.forEach(row => row.style.display = '');

            // Update page numbers
            updatePageNumbers();

            // Enable/disable buttons
            prevButton.disabled = currentPage === 1;
            nextButton.disabled = currentPage === totalPages;
        }

        function updatePageNumbers() {
            pageNumbersDiv.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.innerText = i;
                pageButton.classList.add('px-4', 'py-2', 'border', 'rounded-md');
                if (i === currentPage) {
                    pageButton.classList.add('bg-blue-600', 'text-white');
                } else {
                    pageButton.classList.add('bg-white', 'text-blue-600', 'hover:bg-gray-100');
                }
                pageButton.addEventListener('click', () => {
                    currentPage = i;
                    renderTable();
                });
                pageNumbersDiv.appendChild(pageButton);
            }
        }

        // Initial render
        renderTable();

        function toggleModal() {
            document.getElementById('modal').classList.toggle('hidden');
        }

        function filterTable() {
            const searchInput = document.getElementById('search');
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('#table-body tr');

            rows.forEach(row => {
                const cells = row.getElementsByTagName('td');
                const name = cells[0].textContent.toLowerCase();
                const campus = cells[1].textContent.toLowerCase();
                const isMatch = name.includes(filter) || campus.includes(filter);

                row.style.display = isMatch ? '' : 'none';
            });
        }

        function editProject(event, project) {
            event.preventDefault();
            document.getElementById('project-name').value = project.id;
            document.getElementById('team-name').value = project.team;
            document.getElementById('description').value = project.description;

            const memberCheckboxes = document.querySelectorAll('input[name="members[]"]');
            memberCheckboxes.forEach(checkbox => {
                checkbox.checked = false; // Uncheck all members initially
            });

            project.members.forEach(member => {
                memberCheckboxes.forEach(checkbox => {
                    if (checkbox.value == member
                        .id) { 
                        checkbox.checked = true; 
                    }
                });
            });

            let projectIdInput = document.getElementById('project-id');
            if (!projectIdInput) {
                projectIdInput = document.createElement('input');
                projectIdInput.type = 'hidden';
                projectIdInput.id = 'project-id';
                projectIdInput.name = 'project_id';
                document.querySelector('form').appendChild(projectIdInput);
            }
            projectIdInput.value = project.id;

            document.querySelector('form').scrollIntoView({
                behavior: 'smooth'
            });
        }

        function handleFormSubmit(event) {
            const projectId = document.getElementById('project-id') ? document.getElementById('project-id').value : null;
            if (projectId) {
                document.querySelector('form').action = `./projects/update/${projectId}`;
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                document.querySelector('form').appendChild(methodInput);
            }
        }

        function openDeleteModal(projectId, event) {
            if (event) {
                event.preventDefault(); 
            }
            document.getElementById('deleteProjectModal').classList.remove('hidden');
            document.getElementById('deleteProjectId').value = projectId;
            document.getElementById('deleteProjectForm').action = `./delete-projects/${projectId}`;
        }

        document.getElementById('closeDeleteProjectModal').addEventListener('click', function() {
            document.getElementById('deleteProjectModal').classList.add('hidden');
        });

        function filterProjects() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#projectsTable tbody tr');

            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const team = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const description = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                const members = row.querySelector('td:nth-child(5)').textContent.toLowerCase();

                if (name.includes(query) || team.includes(query) || description.includes(query) || members.includes(
                        query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function updateProjectStatus(projectId, isChecked) {
            let newStatus = isChecked ? 'done' : 'progress';

            fetch(`./projects/${projectId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                });
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
