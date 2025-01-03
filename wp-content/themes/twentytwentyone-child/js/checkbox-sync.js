jQuery(document).ready(function ($) {
    // Update Author Status via AJAX (for Authors)
    $('#job-availability').on('change', function () {
        let authorId = $(this).data('author-id');
        let status = $(this).is(':checked') ? '1' : '0';

        // Update Author's status via AJAX (only for the Author)
        $.post(ajax_object.ajax_url, {
            action: 'update_author_status',
            status: status,
        }, function (response) {
            if (response.success) {
                alert('Status updated successfully...');

                // If the status is set to "Available for Jobs" (checked), send email to Admin
                if (status == '1') {
                    alert('Admin has been notified.');
                }
            } else {
                alert('Failed to update status.');
            }
        });
    });

   

    $('.send-email').on('click', function () {
        var button = $(this); // Store the button reference
        var authorId = button.data('author-id');
        var authorEmail = button.data('author-email');

        // Disable the button to prevent multiple clicks
        button.prop('disabled', true);

        // Send email to Author
        $.post(ajax_object.ajax_url, {
            action: 'send_admin_email_to_author',
            author_id: authorId,
            author_email: authorEmail,
        }, function (response) {
            if (response.success) {
                alert('Email sent successfully to the Author.');

                // After email is sent, uncheck the author's status checkbox
                $('input[data-author-id="' + authorId + '"]').prop('checked', false);

                // Update author status to false in the database using AJAX
                $.post(ajax_object.ajax_url, {
                    action: 'update_author_status_gmail',
                    status: '0',
                }, function (response) {
                    // Optionally check if the status update was successful
                    if (response.success) {
                        console.log('Author status updated to false.');
                    } else {
                        console.log('Failed to update author status.');
                    }
                });
            } else {
                alert('Failed to send email. Please try again.');
            }
        });
    });





    $(document).on('click', '.open-email-popup', function () {
        const authorId = $(this).data('author-id');
        const authorEmail = $(this).data('author-email');

        // Populate fields in the popup
        $('#email-author-id').val(authorId);
        $('#email-author-email').val(authorEmail);

        // Show the popup
        $('#email-popup').fadeIn();
    });

    // Close the popup
    $(document).on('click', '.close-email-popup', function () {
        $('#email-popup').fadeOut();
    });



    // Initialize Quill
var quill = new Quill('#email-body', {
    theme: 'snow',
    placeholder: 'Write your email body here...',
    modules: {
        toolbar: [
            [{ header: [1, 2, false] }],
            ['bold', 'italic', 'underline'],
            ['link', 'blockquote', 'code-block'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ align: [] }],
            ['clean'] // Remove formatting
        ]
    }
});

// On email send button click
$('#send-email').on('click', function () {
    var authorId = $('#email-author-id').val();
    var authorEmail = $('#email-author-email').val();
    var subject = $('#email-subject').val(); // Get the subject from the input field
    var message = quill.root.innerHTML; // Get the content from the Quill editor

    // Send email to Author with the subject and body from the popup
    $.post(ajax_object.ajax_url, {
        action: 'send_custom_email_to_author',
        author_id: authorId,
        author_email: authorEmail,
        subject: subject,
        message: message
    }, function (response) {
        if (response.success) {
            alert('Email sent successfully to the Author.');

            // Uncheck the author's status checkbox
            $('input[data-author-id="' + authorId + '"]').prop('checked', false);

            // Optionally update the author status via AJAX
            $.post(ajax_object.ajax_url, {
                action: 'update_author_status_gmail',
                status: '0',
            }, function (response) {
                if (response.success) {
                    console.log('Author status updated to false.');
                } else {
                    console.log('Failed to update author status.');
                }
            });

            // Close the popup after sending the email
            $('#email-popup').fadeOut();
        } else {
            alert('Failed to send the email.');
            console.log(response); // Log the response for debugging
        }
    });
});




});












