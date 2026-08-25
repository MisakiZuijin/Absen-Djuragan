// FITUR SEKOLAH/KAMPUS

document.getElementById('searchInput').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let divisionItems = document.querySelectorAll('.division-item');

    divisionItems.forEach(function(item) {
        let divisionTextElement = item.querySelector('.text-lg');
        if (divisionTextElement) {
            let divisionName = divisionTextElement.innerText.toLowerCase();
            if (divisionName.includes(filter)) {
                item.style.display = ''; // Show the item if it matches
            } else {
                item.style.display = 'none'; // Hide the item if it does not match
            }
        } else {
            item.style.display = 'none'; // Hide the item if the text element is not found
        }
    });
});

// FITUR DAFTAR ANGGOTA SEKOLAH

  // Function to open the modal and set values
  function openModal(id, name, division, officeName) {
    const shiftModal = document.getElementById('shiftModal');
    if (shiftModal) {
        shiftModal.classList.remove('hidden');
        document.getElementById('intern-id').value = id;
        document.getElementById('internName').value = name;
        document.getElementById('division').value = division;
        
        let officeDropdown = document.getElementById('office_id');
        for (let i = 0; i < officeDropdown.options.length; i++) {
            if (officeDropdown.options[i].text === officeName) {
                officeDropdown.selectedIndex = i;
                break;
            }
        }

    } else {
        console.error("Shift modal element not found");
    }
}

// Function to close the modal
function closeModal() {
    const shiftModal = document.getElementById('shiftModal');
    if (shiftModal) {
        shiftModal.classList.add('hidden');
    } else {
        console.error("Shift modal element not found");
    }
}

// Close modal when clicking outside the modal content
document.addEventListener('DOMContentLoaded', () => {
    const shiftModal = document.getElementById('shiftModal');
    if (shiftModal) {
        shiftModal.addEventListener('click', function(e) {
            if (!e.target.closest('.bg-white')) {
                closeModal();
            }
        });
    } else {
        console.error("Shift Modal element not found");
    }

    // Handle form submission
    const saveButton = document.getElementById('saveButton');
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            document.getElementById('shiftForm').submit();
        });
    } else {
        console.error("Save Button element not found");
    }
});

// Search functionality for table rows
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('#teamTableBody tr');

    tableRows.forEach(function(row) {
        const nameCell = row.cells[1].textContent.toLowerCase();
        if (nameCell.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Fade-out success message
document.addEventListener('DOMContentLoaded', function() {
    const message = document.getElementById('success-message');
    if (message) {
        setTimeout(() => {
            message.style.opacity = 0;
            setTimeout(() => message.remove(), 600); // Remove after transition
        }, 3000); // Display duration (3 seconds)
    }
});