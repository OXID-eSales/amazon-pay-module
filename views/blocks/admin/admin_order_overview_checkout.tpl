[{$smarty.block.parent}]
[{if $oViewConf->isAmazonPaymentId($edit->oxorder__oxpaymenttype->value)}]
    <tr>
        <td class="edittext"><br>[{oxmultilang ident="OSC_AMAZONPAY_REMARK" suffix="COLON"}]</td>
        <td class="edittext"><br><b>[{$edit->oxorder__osc_amazon_remark->value}] [{$edit->oxorder__oxcurrency->value}]</b><br></td>
    </tr>
    [{assign var="amazonAPIOrderStatus" value=$oView->getAmazonAPIOrderStatus()}]
    [{if $amazonAPIOrderStatus && $withLiveStatus}]
        <tr>
            <td class="edittext">[{oxmultilang ident="OSC_AMAZONPAY_LIVESTATUS" suffix="COLON"}]</b></td>
            <td class="edittext"><b>[{$amazonAPIOrderStatus}]</b></td>
        </tr>
    [{/if}]
    [{assign var="amazonCaptureAmount" value=$oView->getAmazonMaximalCaptureAmount()}]
    [{if $oViewConf->isAmazonActive() && $oView->isAuthorized()}]
        <tr>
            <td class="edittext">
                [{oxmultilang ident="OSC_AMAZONPAY_CAPTURE_ANNOTATION"}]
                [{$amazonCaptureAmount}]
                [{$edit->oxorder__oxcurrency->value}]:
            </td>
            <td>
                <form name="makecharge" id="makecharge" action="[{$oViewConf->getSelfLink()}]" >
                    [{$oViewConf->getHiddenSid()}]
                    <input type="text" name="captureAmount" value="[{$amazonCaptureAmount}]" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" />
                    <input type="button" name="captureButton" value="[{oxmultilang ident="OSC_AMAZONPAY_CAPTURE"}]" onclick="document.makecharge.submit()" />
                    <input type="hidden" name="oxid" value="[{$oxid}]">
                    <input type="hidden" name="cl" value="order_overview">
                    <input type="hidden" name="fnc" value="makeCharge">
                </form>
            </td>
        </tr>
    [{/if}]
    [{assign var="amazonRefundAmountMax" value=$oView->getAmazonMaximalRefundAmount()}]
    [{assign var="amazonRefundAmount" value=$amazonRefundAmountMax}]
    [{if $oViewConf->isAmazonActive()  && $oView->isCaptured()}]
        [{if $edit->oxorder__osc_amazon_remark->value}]
            [{assign var="amazonRefundAmount" value=$amazonCaptureAmount}]
        [{/if}]
        <tr>
            <td class="edittext">
                [{oxmultilang ident="OSC_AMAZONPAY_REFUND_ANNOTATION"}]
                [{$amazonRefundAmountMax}]
                [{$edit->oxorder__oxcurrency->value}]:
            </td>
            <td>
                <form name="refundpayment" id="refundpayment" action="[{$oViewConf->getSelfLink()}]" >
                    [{$oViewConf->getHiddenSid()}]
                    [{if ($amazonServiceErrorMessage)}]
                        <div style="color: darkred">[{$amazonServiceErrorMessage}]</div>
                    [{/if}]
                    <input type="text" name="refundAmount" value="[{$amazonRefundAmount}]" oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/(,.*?),.*/g, '$1')" />
                    <input type="button" name="refundButton" value="[{oxmultilang ident="OSC_AMAZONPAY_REFUND"}]" onclick="document.refundpayment.submit()" />
                    <input type="hidden" name="oxid" value="[{$oxid}]">
                    <input type="hidden" name="cl" value="order_overview">
                    <input type="hidden" name="fnc" value="refundpayment">
                </form>
            </td>
        </tr>
    [{/if}]
[{/if}]
