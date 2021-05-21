[{if !$oViewConf->isAmazonExclude()}]
<div class="card-deck">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">AMAZON PAY</h3>
        </div>
        <div class="card-body oxEqualized">
            [{if !$oViewConf->isAmazonSessionActive()}]
                <div class="text-left">
                    [{include file='amazonbutton.tpl' buttonId='AmazonPayButtonCheckOutUser' display='inline-block' oViewConf=$oViewConf}]
                </div><br />
                [{oxmultilang ident="AMAZON_PAY_ADVANTAGES"}]
            [{else}]
                <div class="text-left">
                    [{oxmultilang ident="AMAZON_PAY_PROCESSED" args="index.php?cl=amazoncheckout&fnc=cancelAmazonPayment"}]
                </div>
            [{/if}]
        </div>
    </div>
</div>
[{/if}]