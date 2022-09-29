[{$smarty.block.parent}]
[{if $edit->oxorder__oxpaymenttype->value == $oViewConf->getAmazonPaymentId()}]
    <tr>
        <td class="edittext"><b>[{oxmultilang ident="OSC_AMAZONPAY_REMARK" suffix="COLON"}]</b></td>
        <td class="edittext">[{$edit->oxorder__osc_amazon_remark->value}]<br></td>
    </tr>
    [{if $edit->oxorder__oxtransstatus->value == 'OK'}]
        <tr>
            <td>
                <form name="refundpayment" id="refundpayment" action="[{$oViewConf->getSelfLink()}]" >
                    [{$oViewConf->getHiddenSid()}]
                    <input type="button" name="refundButton" value="[{oxmultilang ident="OSC_AMAZONPAY_REFUND"}]" onclick="document.refundpayment.submit()" />
                    <input type="hidden" name="oxid" value="[{$oxid}]">
                    <input type="hidden" name="cl" value="order_overview">
                    <input type="hidden" name="fnc" value="refundpayment">
                </form>
            </td>
        </tr>
    [{/if}]
[{/if}]