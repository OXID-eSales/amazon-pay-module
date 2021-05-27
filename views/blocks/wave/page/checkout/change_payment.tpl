[{if !$oViewConf->isAmazonSessionActive() || $oViewConf->isAmazonExclude()}]
    [{$smarty.block.parent}]
[{else}]
<form action="[{$oViewConf->getSslSelfLink()}]" id="payment" name="order" method="post">
    <div class="hidden">
        [{$oViewConf->getHiddenSid()}]
        [{$oViewConf->getNavFormParams()}]
        <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
        <input type="hidden" name="fnc" value="validatepayment">
        <input type="hidden" name="paymentid" value="oxidamazon">
    </div>
    <div class="card bg-light cart-buttons">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-6">
                    <a href="[{oxgetseourl ident=$oViewConf->getOrderLink()}]" class="btn btn-outline-dark float-left prevStep submitButton largeButton" id="paymentBackStepBottom"><i class="fa fa-caret-left"></i> [{oxmultilang ident="PREVIOUS_STEP"}]</a>
                </div>
                <div class="col-12 col-md-6 text-right">
                    <button type="submit" name="userform" class="btn btn-primary pull-right submitButton nextStep largeButton" id="paymentNextStepBottom">[{oxmultilang ident="CONTINUE_TO_NEXT_STEP"}] <i class="fa fa-caret-right"></i></button>
                </div>
            </div>
        </div>
    </div>
</form>
[{/if}]
