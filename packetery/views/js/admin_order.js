$(document).ready(function () {
    var widgetOptions = $('.open-packeta-widget').data('widget-options');

    $('.open-packeta-widget').on('click', function (event) {
        event.preventDefault();
        Packeta.Widget.pick(widgetOptions['api_key'], function (pickupPoint) {
            if (pickupPoint != null) {
                $.post(widgetOptions['module_dir'] + 'packetery/ajax_backoffice.php', {
                    action: 'adminOrderChangeBranch',
                    order_id: widgetOptions['order_id'],
                    pickup_point: pickupPoint,
                }, 'json').done(function (data) {
                    $('.picked-delivery-place').text(pickupPoint.name);
                }).fail(function (data) {
                    $('.packetery-error').text(JSON.parse(data.responseText).error).slideDown();
                });
            }
        }, {
            appIdentity: widgetOptions['app_identity'],
            country: widgetOptions['country'],
            language: widgetOptions['lang']
        });
    });
});
