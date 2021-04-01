<div class="panel">
    <div class="panel-heading">
        <i class="icon-dropbox"></i> {l s='Packeta' mod='packetery'}
    </div>
    <div>
        {if $isCarrier}
            <p>{l s='Carrier' mod='packetery'}: <strong class="picked-delivery-place">{$branchName}</strong></p>
        {else}
            <p>{l s='Pickup point' mod='packetery'}: <strong class="picked-delivery-place">{$branchName}</strong></p>
            <p>
                <a href="" class="open-packeta-widget"
                   data-widget-options="{$widgetOptions|@json_encode|escape}">{l s='Change pickup point' mod='packetery'}</a>
            </p>
            <div class="alert alert-danger packetery-error"></div>
        {/if}
    </div>
</div>
