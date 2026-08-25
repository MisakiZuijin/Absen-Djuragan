document.getElementById('activity').addEventListener('input', function() {
    this.value = this.value.replace(/\n/g, ' ').replace(/\r/g, '');
});


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
       if (currentPage > 1) {
           goToPage(currentPage - 1);
       }
   });

   nextButton.on('click', () => {
       if (currentPage < totalPages) {
           goToPage(currentPage + 1);
       }
   });

   $(document).ready(initializePagination);

function togglePopup(id, activity) {
    const popup = document.getElementById('popup-form');
    if (popup.classList.contains('flex')) {
        popup.classList.remove('flex');
        popup.classList.add('hidden');
    } else {
        if (id && activity) {
            const textArea = document.getElementById('activity');
            const logActId = document.getElementById('id');
            logActId.value = id
            textArea.value = activity;
        }
        popup.classList.remove('hidden');
        popup.classList.add('flex');
    }
}

document.addEventListener('DOMContentLoaded', () => {

    const textarea = document.getElementById('activity');
    const button = document.getElementById('submit-button');

    function updateButtonColor() {
        if (textarea.value.trim() === '') {
            button.classList.remove('bg-red-600');
            button.classList.add('bg-red-200');
            button.disabled = true;
        } else {
            button.classList.remove('bg-red-200');
            button.classList.add('bg-red-600');
            button.disabled = false;
        }
    }

    updateButtonColor();

    textarea.addEventListener('input', updateButtonColor);
})