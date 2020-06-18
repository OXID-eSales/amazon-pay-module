[{if !$oViewConf->isAmazonExclude()}]
<div class="card-deck">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">AMAZON PAY</h3>
        </div>
        <div class="card-body oxEqualized">
            [{if !$oViewConf->isAmazonSessionActive()}]
            <ul>
                <li>No need to register, use your account to login.</li>
                <li>Skip manually entering shipping address and payment details, simply use the information
                    that is already stored within your amazon account.</li>
                <li>Fully benefit from Amazon's A-z guarantee.</li>
            </ul>
            <div class="text-left">
                [{include file='amazonbutton.tpl' buttonId='AmazonPayButtonCheckOutUser' display='inline-block' oViewConf=$oViewConf}]
            </div>
            [{else}]
            <div class="text-left">
                [{oxmultilang ident="AMAZON_PAY_PROCESSED" args="index.php?cl=amazoncheckout&fnc=cancelAmazonPayment"}]
            </div>
            [{/if}]
        </div>
    </div>
</div>
[{/if}]