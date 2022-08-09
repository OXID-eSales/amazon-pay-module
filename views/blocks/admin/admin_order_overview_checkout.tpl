[{$smarty.block.parent}]
[{if $edit->oxorder__oxpaymenttype->value == $oViewConf->getAmazonPaymentId()}]
    <tr>
        <td class="edittext"><b>[{oxmultilang ident="OSC_AMAZONPAY_REMARK" suffix="COLON"}]</b></td>
        <td class="edittext">[{$edit->oxorder__osc_amazon_remark->value}]<br></td>
    </tr>
[{/if}]