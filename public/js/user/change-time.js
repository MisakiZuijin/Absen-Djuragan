// #FITUR CHANGE TIME
const rowsPerPage = 10; 
let currentPage = 1;
const rows = $('tbody tr'); 
const totalRows = rows.length;
const totalPages = Math.ceil(totalRows / rowsPerPage);

const pageNumbersContainer = $('#page-numbers');
const prevButton = $('#prev-page');
const nextButton = $('#next-page');

function initializePagination() {
    pageNumbersContainer.empty();

    for (let i = 1; i <= totalPages; i++) {
        const isActive = i === currentPage ? 'bg-blue-500 text-white' : 'bg-white text-blue-600';
        const pageButton = $(`
            <button class="page-number ${isActive} px-4 py-2 rounded-md border">${i}</button>
        `);

        pageButton.on('click', () => goToPage(i));
        pageNumbersContainer.append(pageButton);
    }

    displayPage(currentPage);
    updateButtons();
}

function displayPage(page) {
    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    rows.each((index, row) => {
        $(row).toggle(index >= start && index < end);
    });
}

function updateButtons() {
    prevButton.prop('disabled', currentPage === 1);
    nextButton.prop('disabled', currentPage === totalPages);
}

function goToPage(page) {
    currentPage = page;
    displayPage(currentPage);
    updateButtons();
    initializePagination(); 
}

prevButton.on('click', () => {
    if (currentPage > 1) goToPage(currentPage - 1);
});

nextButton.on('click', () => {
    if (currentPage < totalPages) goToPage(currentPage + 1);
});

$(document).ready(initializePagination);

function openModal(buktiFoto) {
    document.getElementById('bukti-foto').value = buktiFoto;
    document.getElementById('modal').classList.remove('hidden');
}
document.getElementById('closeModal').addEventListener('click', function() {
    document.getElementById('modal').classList.add('hidden');
});

function copyToClipboard() {
    var copyText = document.getElementById('bukti-foto');
    copyText.select();
    document.execCommand('copy');
    alert('URL copied to clipboard: ' + copyText.value);
}