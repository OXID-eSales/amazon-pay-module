<tr>
[{if $isOneStepCapture === false}]
    [{if $isCaptured === true}]
    <td colspan="2">[{oxmultilang ident="OXPS_AMAZONPAY_PAYMENT_WAS_SHIPPING"}]</td>
    [{else}]
    <td colspan="2">[{oxmultilang ident="OXPS_AMAZONPAY_PAYMENT_WHEN_SHIPPING"}]</td>
    [{/if}]
[{else}]
    [{if $isCaptured === true}]
    <td colspan="2">[{oxmultilang ident="OXPS_AMAZONPAY_PAYMENT_DURING_CHECKOUT"}]</td>
    [{/if}]
[{/if}]
</tr>
