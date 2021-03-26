<link rel="stylesheet" href="{$css_url}"/>
<div class="panel">
    <div class="panel-heading">
        <i class="icon-dropbox"></i> {l s='Packetery'}
    </div>
    <div>
        <p>{$service_name}: <strong class="picked-delivery-place">{$branch_name}</strong></p>
        {if $change_pickup_point}
            <script type="text/javascript" src="https://widget.packeta.com/v6/www/js/library.js"></script>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('.open-packeta-widget').on('click', function () {
                        Packeta.Widget.pick('{$api_key}', function (pickupPoint) {
                            if (pickupPoint != null) {
                                $.ajax({
                                    url: '{$ajax_url}',
                                    data: {
                                        action: 'adminOrderChangeBranch',
                                        order_id: '{$order_id}',
                                        pickup_point: pickupPoint,
                                    },
                                    type: "POST",
                                    success() {
                                        $('.picked-delivery-place').text(pickupPoint.name);
                                    },
                                });
                            }
                        }, {
                            appIdentity: 'prestashop-1.6-packeta-' + '{$module_version}',
                            country: '{$country}',
                            language: '{$lang}'
                        });
                    });
                });
            </script>
            <a href="javascript:" class="open-packeta-widget">{$change_pickup_point}</a>
        {/if}
    </div>
</div>
