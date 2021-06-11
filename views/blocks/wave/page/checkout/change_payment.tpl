[{if 'oxidamazon'|array_key_exists:$oView->getPaymentList() && !$oViewConf->isAmazonExclude()}]
    <div class="card-deck">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">[{oxmultilang ident="AMAZON_PAY"}]</h3>
            </div>
            <div class="card-body oxEqualized">
                [{if !$oViewConf->isAmazonSessionActive()}]
                    <div class="text-left">
                        [{include file='amazonbutton.tpl' buttonId='AmazonPayButtonCheckOutUser' display='inline-block'}]
                    </div><br />
                    [{oxmultilang ident="AMAZON_PAY_ADVANTAGES"}]
                [{else}]
                    <div class="row">
                        <div class="col-12 col-md-6">
                            [{oxmultilang ident="AMAZON_PAY_PROCESSED"}]
                        </div>
                        <div class="col-12 col-md-6 text-right">
                            <a class="btn btn-outline-dark" href="[{$oViewConf->getCancelAmazonPaymentUrl()}]">[{oxmultilang ident="AMAZON_PAY_UNLINK"}]</a>
                        </div>
                    </div>
                [{/if}]
            </div>
        </div>
    </div>
[{/if}]
[{if !$oViewConf->isAmazonSessionActive() || $oViewConf->isAmazonExclude()}]
    [{$smarty.block.parent}]
[{elseif 'oxidamazon'|array_key_exists:$oView->getPaymentList()}]
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
