jQuery(document).ready(function ($) {
    $('#ccf-contact-form').on('submit', function (e) {
        e.preventDefault();

        var formData = {
            'action': 'ccf_handle_form_submission',
            'ccf_nonce': $('#ccf_nonce').val(),
            'name': $('#ccf-name').val(),
            'email': $('#ccf-email').val(),
            'message': $('#ccf-message').val()
        };

        $.post(ccf_ajax_object.ajax_url, formData, function (response) {
            if (response.success) {
                $('#ccf-response').html('<p>' + response.data + '</p>');
                $('#ccf-contact-form')[0].reset();
            } else {
                $('#ccf-response').html('<p>' + response.data + '</p>');
            }
        });
    });
});
