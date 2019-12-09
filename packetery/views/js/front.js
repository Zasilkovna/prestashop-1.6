window.initializePacketaWidget = function () {
    $('.zas-box').remove();
    var processCarrier = $('button[name=processCarrier]');
    $("input.delivery_option_radio:checked").each(function (i, e) {
        v = $(e).val().replace(/^\,+|\,+$/g, '');

        // if packetery carrier
        if (carrier_data.hasOwnProperty(v)) {

            var countries = country.split(',');
            var validCarrier = false;

            /* Check if carrier is available for countries */
            $.each(countries, function (k, val)
            {
                if (carrier_data[v]['country'].indexOf(val) != -1)
                {
                    validCarrier = true;
                }
            });

            if (!validCarrier)
            {
                c = $(e).closest('tr').find('td:nth-child(3)');
                c.find('.invalid_country_msg').remove();
                c.append('<p class="invalid_country_msg" style="color: red">' + invalid_country_text + '</p>');
            }
            else {
                /* Display button and inputs */
                c = $(e).closest('tr').find('td:nth-child(3)');
                c.append('<div class="zas-box"><h3><button class="btn btn-success btn-md" id="open-packeta-widget">' + select_text + '</h3>' +
                    '<div id="selected-pickup-point">' +
                    '<input type="hidden" name="packeta-branch-id" id="packeta-branch-id">' +
                    '<input type="hidden" name="packeta-branch-name" id="packeta-branch-name">' +
                    '<b>' + selected_text + '</b>: <span id="picked-delivery-place"></span>' +
                    '</div>' +
                    '</div>');
            }

            if (processCarrier.length > 0) {
                $('button[name=processCarrier]').attr('disabled', true);
            }

            /* disable cgv checkbox - cannot continue without selecting a branch */
            $('#cgv').attr('disabled', true);

            /* unbind click events from payment links and disable them - cannot continue without selecting a branch */
            $('p.payment_module a').unbind('click').click(function (e) {
                alert(must_select_text);
                e.preventDefault();
            });
        }
        else {
            /* Reenable disabled elements if carrier is not packetery */
            $('#cgv').attr('disabled', false);
            $('#cgv').parent().parent().removeClass('disabled');
            $('p.payment_module a').unbind('click');

            if (processCarrier.length > 0) {
                $('button[name=processCarrier]').attr('disabled', false);
            }
        }
    });

    if (document.getElementById('open-packeta-widget') != null) {
        document.getElementById('open-packeta-widget').addEventListener('click', function (e) {
            e.preventDefault();

            /* Open packeta widget */
            Packeta.Widget.pick(api_key, function (pickupPoint) {
                if (pickupPoint != null) {
                    document.getElementById('packeta-branch-id').value = pickupPoint.id;
                    document.getElementById('packeta-branch-name').value = pickupPoint.name;

                    // we let the customer know, which branch they picked by filling html inputs
                    document.getElementById('picked-delivery-place').innerHTML = pickupPoint.name;
                    $("#selected-pickup-point").show();

                    /* Enable button */
                    $('button[name=processCarrier]').attr('disabled', false);

                    // The pick up point must be updated immediately after being selected
                    $.ajax({
                        url: module_dir + "packetery/ajax.php",
                        data: {
                            id_branch: pickupPoint.id,
                            name_branch: pickupPoint.name,
                            currency_branch: pickupPoint.currency
                        },
                        type: "POST",
                        complete: function () {
                            last_ajax = null;
                        }
                    });

                    /* Enable disabled elements */
                    $('p.payment_module a').unbind('click');
                    $('#cgv').attr('disabled', false);
                    $('#cgv').parent().parent().removeClass('disabled');
                }
                else {
                    /* Disable all "continue" elements again */
                    $('#cgv').attr('disabled', true);
                    $('p.payment_module a').unbind('click').click(function (e) {
                        alert(must_select_text);
                        e.preventDefault();
                    });
                }
            }, {appIdentity: 'prestashop-1.6-packeta-' + module_version, country: country, language: lang});
        });
    }
};