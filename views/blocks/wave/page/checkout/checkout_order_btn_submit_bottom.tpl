[{if $oViewConf->isAmazonActive()}]
    [{assign var="missingRequiredBillingFields" value=$oView->getMissingRequiredBillingFields()}]
    [{if $missingRequiredBillingFields|@count}]
        [{foreach from=$missingRequiredBillingFields item=missingFieldValue key=missingFieldName}]
            <input type="hidden" name="missing_amazon_invadr[[{$missingFieldName}]]" value="" />
        [{/foreach}]
    [{/if}]
    [{assign var="missingRequiredDeliveryFields" value=$oView->getMissingRequiredDeliveryFields()}]
    [{if $missingRequiredDeliveryFields|@count}]
        [{foreach from=$missingRequiredDeliveryFields item=missingFieldValue key=missingFieldName}]
            <input type="hidden" name="missing_amazon_deladr[[{$missingFieldName}]]" value="" />
        [{/foreach}]
    [{/if}]
    [{capture name="amazonpay_missingfields_script"}]
        $("#orderConfirmAgbBottom").submit(function(event) {
            $('#missing_delivery_address [id^=missing_amazon_deladr]').each(function(index) {
                $('#orderConfirmAgbBottom input[name="' + $(this).attr("name") + '"]').val($(this).val());
            });
            $('#missing_billing_address [id^=missing_amazon_invadr]').each(function(index) {
                $('#orderConfirmAgbBottom input[name="' + $(this).attr("name") + '"]').val($(this).val());
            });
        });
    [{/capture}]
    [{oxscript add=$smarty.capture.amazonpay_missingfields_script}]

    [{if !$oViewConf->isAmazonSessionActive() && !$oViewConf->isAmazonExclude()}]
        <div class="float-right">
            [{include file="amazonbutton.tpl" buttonId="AmazonPayButtonNextCart2"}]
        </div>
        <div class="float-right amazonpay-button-or">
            [{"OR"|oxmultilangassign|oxupper}]
        </div>
    [{/if}]
[{/if}]
[{$smarty.block.parent}]
