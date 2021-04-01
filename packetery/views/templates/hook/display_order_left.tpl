<div class="panel">
    <div class="panel-heading">
        <i class="icon-dropbox"></i> {l s='Packeta' mod='packetery'}
    </div>
    <div>
        <p>{$service_name}: <strong class="picked-delivery-place">{$branch_name}</strong></p>
        {if $change_pickup_point}
            <a href="javascript:" class="open-packeta-widget">{$change_pickup_point}</a>
            <input type="hidden" name="change_pickup_point_data" value="{$change_pickup_point_data}">
        {/if}
    </div>
</div>
