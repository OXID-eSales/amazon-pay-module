[{if !$oViewConf->isAmazonExclude()}]
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">[{oxmultilang ident="AMAZON_PAY"}]</h3>
        </div>
        <div class="panel-body">
            [{if !$oViewConf->isAmazonSessionActive()}]
                <div class="pull-left">
                    [{include file='amazonbutton.tpl' buttonId='AmazonPayButtonCheckOutUser' display='inline-block'}]
                    <br />
                    [{oxmultilang ident="AMAZON_PAY_ADVANTAGES"}]
                </div>
            [{else}]
                <div class="pull-left">
                    [{oxmultilang ident="AMAZON_PAY_PROCESSED"}]
                </div>
                <div class="pull-right">
                    <a class="btn btn-default" href="[{$oViewConf->getCancelAmazonPaymentUrl()}]">[{oxmultilang ident="AMAZON_PAY_UNLINK"}]</a>
                </div>
            [{/if}]
        </div>
    </div>
[{/if}]
[{if !$oViewConf->isAmazonSessionActive() || $oViewConf->isAmazonExclude()}]
    [{$smarty.block.parent}]
[{else}]
    <form action="[{$oViewConf->getSslSelfLink()}]" class="form-horizontal js-oxValidate payment" id="payment" name="order" method="post" novalidate="novalidate">
        <div class="hidden">
            [{$oViewConf->getHiddenSid()}]
            [{$oViewConf->getNavFormParams()}]
            <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
            <input type="hidden" name="fnc" value="validatepayment">
            <input type="hidden" name="paymentid" value="oxidamazon">
        </div>
        <div class="well well-sm cart-buttons">
            <a href="[{oxgetseourl ident=$oViewConf->getOrderLink()}]" class="btn btn-default pull-left prevStep submitButton largeButton" id="paymentBackStepBottom"><i class="fa fa-caret-left"></i> [{oxmultilang ident="PREVIOUS_STEP"}]</a>
            <button type="submit" name="userform" class="btn btn-primary pull-right submitButton nextStep largeButton" id="paymentNextStepBottom">[{oxmultilang ident="CONTINUE_TO_NEXT_STEP"}] <i class="fa fa-caret-right"></i></button>
            <div class="clearfix"></div>
        </div>
    </form>
[{/if}]
