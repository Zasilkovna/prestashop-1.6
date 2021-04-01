$(document).ready(function () {
    var changePickupPointData = JSON.parse(decodeURIComponent($('input[name="change_pickup_point_data"]').val()));

    $('.open-packeta-widget').on('click', function () {
        Packeta.Widget.pick(changePickupPointData['api_key'], function (pickupPoint) {
            if (pickupPoint != null) {
                $.post(changePickupPointData['module_dir'] + 'packetery/ajax_backoffice.php', {
                    action: 'adminOrderChangeBranch',
                    order_id: changePickupPointData['order_id'],
                    pickup_point: pickupPoint,
                }).done(function (data) {
                    var ajaxResult = JSON.parse(data);
                    if (ajaxResult['error']) {
                        $('<br><br><div class="alert alert-danger">' + ajaxResult['error'] + '</div>').insertAfter('.open-packeta-widget');
                    } else {
                        $('.picked-delivery-place').text(pickupPoint.name);
                    }
                });
            }
        }, {
            appIdentity: changePickupPointData['app_identity'],
            country: changePickupPointData['country'],
            language: changePickupPointData['lang']
        });
    });
});
