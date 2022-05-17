[{$smarty.block.parent}]
[{if $edit->oxorder__oxpaymenttype->value == $oViewConf->getAmazonPaymentId()}]
    </table>
    <br>
    <p><b>[{oxmultilang ident="OXPS_AMAZONPAY_TRANSACTION_HISTORY"}]</b></p>
    <table style="width: 100%">

    <tr>
        <td style="width: 20%"><b>[{oxmultilang ident="OXPS_AMAZONPAY_DATE" sufix="COLON"}]</b></td>
        <td style="width: 30%"><b>[{oxmultilang ident="OXPS_AMAZONPAY_REFERENCE" sufix="COLON"}]</b></td>
        <td style="width: 50%"><b>[{oxmultilang ident="OXPS_AMAZONPAY_RESULT" sufix="COLON"}]</b></td>
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
    <p><b>[{oxmultilang ident="OXPS_AMAZONPAY_IPN_HISTORY"}]</b></p>
    <table style="width: 100%">

    <tr>
        <td style="width: 20%"><b>[{oxmultilang ident="OXPS_AMAZONPAY_DATE" sufix="COLON"}]</b></td>
        <td style="width: 30%"><b>[{oxmultilang ident="OXPS_AMAZONPAY_REFERENCE" sufix="COLON"}]</b></td>
        <td style="width: 50%"><b>[{oxmultilang ident="OXPS_AMAZONPAY_RESULT" sufix="COLON"}]</b></td>
    </tr>

    [{foreach from=$ipnLogs item=logItem name=logItemName}]
    <tr>
        <td class="edittext">[{$logItem.time}]</td>
        <td class="edittext">[{$logItem.identifier}]</td>
        <td class="edittext">[{$logItem.requestType}]</td>
    </tr>
    [{/foreach}]
[{/if}]