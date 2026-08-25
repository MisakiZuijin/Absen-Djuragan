// #UNTUK LOGOUT MODAL

$(document).ready(function() {
    // Show the logout modal when the logout button is clicked
    $('.logoutModal').on('click', function(event) {
        event.preventDefault(); // Prevent default action

        // Show the modal
        $('#logout-modal').removeClass('hidden');
    });

    // Close the logout modal
    $('#closeLogout').on('click', function() {
        $('#logout-modal').addClass('hidden');
    });

    // Close the modal when clicking outside of it
    $(window).on('click', function(event) {
        if ($(event.target).is('#logout-modal')) {
            $('#logout-modal').addClass('hidden');
        }
    });
});