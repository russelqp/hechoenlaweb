jQuery(document).ready(function($) {
        $('#atbdp_ie_download_sample').on('click', function(e) {
                const ie_file = $(this).attr('data-sample-csv');
                if (ie_file) {
                        window.location.href = ie_file;
                        return false;
                }
        });
        const stepTwo = $('#atbdp_csv_step_two');
        let position = 0;
        let failed = 0;
        let imported = 0;
        $(stepTwo).on('submit', function(e) {
                e.preventDefault();

                $('.atbdp-importer-mapping-table-wrapper').fadeOut(300);
                $('.directorist-importer__importing').fadeIn(300);
                $(this)
                        .parent('.csv-fields')
                        .fadeOut(300);
                $('.atbdp-mapping-step')
                        .removeClass('active')
                        .addClass('done');
                $('.atbdp-progress-step').addClass('active');

                let counter = 0;
                var run_import = function() {
                        const form_data = new FormData();
                        // ajax action
                        form_data.append('action', 'atbdp_import_listing');
                        form_data.append('file', $('input[name="file"]').val());
                        form_data.append('delimiter', $('input[name="delimiter"]').val());
                        form_data.append('update_existing', $('input[name="update_existing"]').val());
                        form_data.append('position', position);
                        form_data.append('wpnonce', $('input[name="_wpnonce"]').val());
                        $('select.atbdp_map_to').each(function() {
                                const name = $(this).attr('name');
                                const value = $(this).val();
                                if (value == 'title' || value == 'description' || value == '_listing_prv_img') {
                                        form_data.append(value, name);
                                } else if (value == 'category' || value == 'location' || value == 'tag') {
                                        form_data.append(`tax_input[${value}]`, name);
                                } else {
                                        form_data.append(`meta[${value}]`, name);
                                }
                        });

                        $.ajax({
                                method: 'POST',
                                processData: false,
                                contentType: false,
                                // async: false,
                                url: import_export_data.ajaxurl,
                                data: form_data,
                                success(response) {
                                        imported += response.imported;
                                        failed += response.failed;
                                        $('.importer-details').html(
                                                `Imported ${response.next_position} out of ${response.total}`
                                        );
                                        $('.directorist-importer-progress').val(response.percentage);
                                        if (response.percentage != '100' && counter < 150) {
                                                position = response.next_position;
                                                run_import();
                                                counter++;
                                        } else {
                                                window.location = `${
                                                        response.url
                                                }&listing-imported=${imported}&listing-failed=${failed}`;
                                        }
                                },
                                error(response) {
                                        window.console.log(response);
                                },
                        });

                };
                run_import();
        });
        /* csv upload */
        $('#upload').change(function(e) {
                const filename = e.target.files[0].name;
                $('.csv-upload .file-name').html(filename);
        });
});
