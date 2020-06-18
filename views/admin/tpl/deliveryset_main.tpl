[{$smarty.block.parent}]
<tr>
    <td class="edittext">
        [{oxmultilang ident="OXPS_AMAZONPAY_CARRIER_CODE"}]
    </td>
    <td class="edittext">
        <select name="editAmazonCarrier" id="editAmazonCarrier" [{$custreadonly}]>
            <option value="" [{if $selectedAmazonCarrier|is_null}]SELECTED[{/if}]>---</option>
            [{foreach from=$amazonCarriers key=code item=name}]
                <option value="[{$code}]"[{if $code == $selectedAmazonCarrier}]SELECTED[{/if}]>[{$name}]</option>
            [{/foreach}]
        </select>
    </td>
</tr>
