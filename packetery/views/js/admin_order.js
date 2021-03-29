$(document).ready(function () {
    var changePickupPointData = JSON.parse(decodeURIComponent($('input[name="change_pickup_point_data"]').val()));

    $('.open-packeta-widget').on('click', function () {
        Packeta.Widget.pick(changePickupPointData['api_key'], function (pickupPoint) {
            if (pickupPoint != null) {
                $.ajax({
                    url: changePickupPointData['ajax_url'],
                    data: {
                        action: 'adminOrderChangeBranch',
                        order_id: changePickupPointData['order_id'],
                        pickup_point: pickupPoint,
                    },
                    type: "POST",
                    success() {
                        $('.picked-delivery-place').text(pickupPoint.name);
                    },
                });
            }
        }, {
            appIdentity: 'prestashop-1.6-packeta-' + changePickupPointData['module_version'],
            country: changePickupPointData['country'],
            language: changePickupPointData['lang']
        });
    });
});
