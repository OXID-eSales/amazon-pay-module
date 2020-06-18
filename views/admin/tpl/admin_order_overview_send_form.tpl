<tr>
[{if $isOneStepCapture === false}]
    [{if $isCaptured === true}]
    <td colspan="2">This payment was captured by AmazonPay when shipping was processed</td>
    [{else}]
    <td colspan="2">This payment will be captured by AmazonPay when shipping is processed</td>
    [{/if}]
[{else}]
    [{if $isCaptured === true}]
    <td colspan="2">This payment was captured by AmazonPay during checkout</td>
    [{/if}]
[{/if}]
</tr>
