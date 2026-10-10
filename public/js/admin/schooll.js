// ==========================================
// FITUR MODAL & FORM ANGGOTA SEKOLAH
// ==========================================

// Function to open the modal and set values
function openModal(id, name, division, officeName, shiftId, type, startDate, endDate) {
    const shiftModal = document.getElementById('shiftModal');
    if (!shiftModal) return;

    shiftModal.classList.remove('hidden');

    const internIdInput = document.getElementById('intern-id');
    if (internIdInput) internIdInput.value = id || '';

    const internNameInput = document.getElementById('internName');
    if (internNameInput) internNameInput.value = name || '';

    const divisionInput = document.getElementById('division');
    if (divisionInput) divisionInput.value = division || '';

    const officeDropdown = document.getElementById('office_id');
    if (officeDropdown && officeName) {
        for (let i = 0; i < officeDropdown.options.length; i++) {
            if (officeDropdown.options[i].text.trim() === officeName.trim()) {
                officeDropdown.selectedIndex = i;
                break;
            }
        }
    }

    const typeDropdown = document.getElementById('type');
    if (typeDropdown) {
        typeDropdown.value = type ? String(type) : '1';
        toggleRollingInfo(typeDropdown.value);
    }

    const shiftDropdown = document.getElementById('shift_id');
    if (shiftDropdown && shiftId) {
        shiftDropdown.value = String(shiftId);
    }

    if (startDate) {
        const startInput = document.getElementById('start_period');
        if (startInput) startInput.value = startDate;
    }
    if (endDate) {
        const endInput = document.getElementById('end_period');
        if (endInput) endInput.value = endDate;
    }
}

// Function to toggle rolling info alert
function toggleRollingInfo(typeVal) {
    const infoAlert = document.getElementById('rollingInfoAlert');
    if (infoAlert) {
        if (String(typeVal) === '2') {
            infoAlert.classList.remove('hidden');
        } else {
            infoAlert.classList.add('hidden');
        }
    }
}

// Function to close the modal
function closeModal() {
    const shiftModal = document.getElementById('shiftModal');
    if (shiftModal) {
        shiftModal.classList.add('hidden');
    }
}

// ==========================================
// DOMContentLoaded EVENT LISTENERS
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    // 1. Modal backdrop click handler
    const shiftModal = document.getElementById('shiftModal');
    if (shiftModal) {
        shiftModal.addEventListener('click', function(e) {
            if (!e.target.closest('.bg-white')) {
                closeModal();
            }
        });
    }

    // 2. Shift type dropdown change handler
    const typeDropdown = document.getElementById('type');
    if (typeDropdown) {
        typeDropdown.addEventListener('change', function() {
            toggleRollingInfo(this.value);
        });
    }

    // 3. Unified Search handler (berfungsi untuk halaman kartu sekolah & tabel anggota sekolah)
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase().trim();

            // A. Pencarian Kartu Sekolah di halaman Daftar Sekolah
            const schoolCards = document.querySelectorAll('.division-item');
            if (schoolCards.length > 0) {
                schoolCards.forEach(function(card) {
                    const nameAttr = card.getAttribute('data-school-name') || '';
                    const titleEl = card.querySelector('.school-title');
                    const titleText = titleEl ? titleEl.textContent.toLowerCase() : '';
                    const fullText = card.textContent.toLowerCase();

                    const isMatch = filter === '' ||
                                    nameAttr.includes(filter) ||
                                    titleText.includes(filter) ||
                                    fullText.includes(filter);

                    if (isMatch) {
                        card.style.display = '';
                        card.classList.remove('hidden');
                    } else {
                        card.style.display = 'none';
                        card.classList.add('hidden');
                    }
                });
            }

            // B. Pencarian Baris Tabel di halaman Anggota Sekolah & Penjadwalan
            const tableRows = document.querySelectorAll('#teamTableBody tr');
            if (tableRows.length > 0) {
                tableRows.forEach(function(row) {
                    const rowText = row.textContent.toLowerCase();
                    row.style.display = rowText.includes(filter) ? '' : 'none';
                });
            }
        });
    }

    // 4. Auto-dismiss flash success message
    const message = document.getElementById('success-message');
    if (message) {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 600);
        }, 3000);
    }
});