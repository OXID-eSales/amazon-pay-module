[{$smarty.block.parent}]

</table>
<br>
<p><b>AmazonPay Transaction History</b></p>
<table style="width: 100%">

<tr>
    <td style="width: 20%"><b>Date:</b></td>
    <td style="width: 30%"><b>Reference</b></td>
    <td style="width: 50%"><b>Result</b></td>
</tr>

[{foreach from=$orderLogs item=logItem name=logItemName}]
    <tr>
        <td class="edittext">[{$logItem.time}]</td>
        <td class="edittext">[{$logItem.identifier}]</td>
        <td class="edittext">[{$logItem.requestType}]</td>
    </tr>
[{/foreach}]
</table>
<br />
<p><b>AmazonPay IPN History</b></p>
<table style="width: 100%">

<tr>
    <td style="width: 20%"><b>Date:</b></td>
    <td style="width: 30%"><b>Reference</b></td>
    <td style="width: 50%"><b>Result</b></td>
</tr>

[{foreach from=$ipnLogs item=logItem name=logItemName}]
<tr>
    <td class="edittext">[{$logItem.time}]</td>
    <td class="edittext">[{$logItem.identifier}]</td>
    <td class="edittext">[{$logItem.requestType}]</td>
</tr>
[{/foreach}]