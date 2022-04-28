[{$smarty.block.parent}]
[{if $edit->oxorder__oxpaymenttype->value == 'oxidamazon'}]
    <tr>
        <td class="edittext"><b>[{oxmultilang ident="AMAZON_PAY_REMARK"}]:</b></td>
        <td class="edittext">[{$edit->oxorder__oxps_amazon_remark->value}]<br></td>
    </tr>
[{/if}]