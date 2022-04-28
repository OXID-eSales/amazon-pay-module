[{$smarty.block.parent}]
[{if $edit->oxorder__oxpaymenttype->value == 'oxidamazon'}]
    <tr>
        <td class="edittext"><b>[{oxmultilang ident="OXPS_AMAZONPAY_REMARK" suffix="COLON"}]</b></td>
        <td class="edittext">[{$edit->oxorder__oxps_amazon_remark->value}]<br></td>
    </tr>
[{/if}]