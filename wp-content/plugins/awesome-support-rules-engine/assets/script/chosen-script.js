jQuery( document ).ready( function() {

    var chosen_client_name = jQuery('select[name="condition_client_name[]"]');
    var chosen_client_email = jQuery('select[name="condition_client_email[]"]');
    var chosen_agent_name = jQuery('select[name="condition_agent_name[]"]');

    chosen_client_name.select2({
        placeholder: "Select names",
        ajax: {
            url: ajaxurl+'?action=get_client_names',
            quietMillis: 250,
            data: function (term, page) {
                return {
                    qn: term, // search term
                };
            },
            processResults: function (data, page) {
                return { results: data };
            }
        }
    });

    chosen_agent_name.select2({
        placeholder: "Select names",
        ajax: {
            url: ajaxurl+'?action=get_agent_names',
            quietMillis: 250,
            data: function (term, page) {
                return {
                    qc: term, // search term
                };
            },
            processResults: function (data3, page) {
                return { results: data3 };
            }
        }
    });

    chosen_client_email.select2({
        placeholder: "Select emails",
        ajax: {
            url: ajaxurl+'?action=get_client_email_list',
            quietMillis: 250,
            data: function (term, page) {
                return {
                    qr: term, // search term
                };
            },
            processResults: function (data2, page) {
                return { results: data2 };
            },
        }
    });
});