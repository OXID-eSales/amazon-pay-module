[{if $oView->isLowOrderPrice()}]
    <div class="alert alert-info">
        <b>[{oxmultilang ident="MIN_ORDER_PRICE"}] [{$oView->getMinOrderPrice()}] [{$currency->sign}]</b>
    </div>
[{else}]
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
[{/if}]
