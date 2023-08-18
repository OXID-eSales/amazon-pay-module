[{include file="headitem.tpl" title="amazonpay"}]

<div id="content" class="amazonpay-config">
    <h1>[{oxmultilang ident="amazonpay"}] [{oxmultilang ident="OSC_AMAZONPAY_CONFIG"}]</h1>
    <div class="alert alert-[{if $Errors.amazonpay_error}]danger[{else}]success[{/if}]" role="alert">
        [{if $Errors.amazonpay_error}]
            [{oxmultilang ident="OSC_AMAZONPAY_ERR_CONF_INVALID"}]
        [{else}]
            [{oxmultilang ident="OSC_AMAZONPAY_CONF_VALID"}]
        [{/if}]
    </div>

    <form action="[{$oViewConf->getSelfLink()}]" method="post">
        [{$oViewConf->getHiddenSid()}]
        <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
        <input type="hidden" name="fnc" value="save">

        <div class="form-group jsonform-error-paymentRegion">
            <label for="opmode">[{oxmultilang ident="OSC_AMAZONPAY_OPMODE"}]</label>
            <div class="controls">
                <select name="conf[blAmazonPaySandboxMode]" id="opmode" class="form-control">
                    <option value="sandbox" [{if $config->isSandbox()}]selected[{/if}]>
                        [{oxmultilang ident="OSC_AMAZONPAY_OPMODE_SANDBOX"}]
                    </option>
                    <option value="prod" [{if !$config->isSandbox()}]selected[{/if}]>
                        [{oxmultilang ident="OSC_AMAZONPAY_OPMODE_PROD"}]
                    </option>
                </select>
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_OPMODE"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="privkey">[{oxmultilang ident="OSC_AMAZONPAY_PRIVKEY" suffix="*"}]</label>
            <div class="controls">
                <textarea id="privkey" name="conf[sAmazonPayPrivKey]">[{$displayPrivateKey}]</textarea>
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_PRIVKEY"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="privkeyid">[{oxmultilang ident="OSC_AMAZONPAY_PUBKEYID" suffix="*"}]</label>
            <div class="controls">
                <input type="text" class="form-control" name="conf[sAmazonPayPubKeyId]" value="[{$config->getPublicKeyId()}]" id="privkeyid">
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_PUBKEYID"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="merchantId">[{oxmultilang ident="OSC_AMAZONPAY_MERCHANTID" suffix="*"}]</label>
            <div class="controls">
                <input type="text" class="form-control" name="conf[sAmazonPayMerchantId]" value="[{$config->getMerchantId()}]" id="merchantId">
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_MERCHANTID"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="storeId">[{oxmultilang ident="OSC_AMAZONPAY_STOREID" suffix="*"}]</label>
            <div class="controls">
                <input type="text" class="form-control" name="conf[sAmazonPayStoreId]" value="[{$config->getStoreId()}]" id="storeId">
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_STOREID"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="payRegion">[{oxmultilang ident="OSC_AMAZONPAY_PAYREGION"}]</label>
            <div class="controls">
                [{assign var="currenciesAbbr" value=$config->getPossiblePresentmentCurrenciesAbbr()}]
                <span id="payRegion">[{", "|implode:$currenciesAbbr}]</span>
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_PAYREGION"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="payRegion">[{oxmultilang ident="OSC_AMAZONPAY_DELREGION"}]</label>
            <div class="controls">
                [{assign var="deliveryAbbr" value=$config->getPossibleEUAddressesAbbr()}]
                <span id="delRegion">[{", "|implode:$deliveryAbbr}]</span>
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_DELREGION"}]</span>
            </div>
        </div>

        <h3>[{oxmultilang ident="OSC_AMAZONPAY_SELLER"}]</h3>

        <div class="form-group">
            <label for="ipnUrl">[{oxmultilang ident="OSC_AMAZONPAY_IPN"}]</label>
            <div class="controls">
                <input type="text" class="form-control" value="[{$config->getIPNUrl()}]" id="ipnUrl" readonly="readonly">
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_IPN"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="button-placement">[{oxmultilang ident="OSC_AMAZONPAYEXPRESS_PLACEMENT"}]</label>
            <div class="controls">
                <div>
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="conf[blAmazonPayExpressPDP]" value="0" />
                            <input id="placementDetailPage" type="checkbox" name="conf[blAmazonPayExpressPDP]" [{if $config->displayExpressInPDP()}]checked[{/if}] value="1" />
                            [{oxmultilang ident="OSC_AMAZONPAY_PDP"}]
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="conf[blAmazonPayExpressMinicartAndModal]" value="0" />
                            <input id="placementMinicartAndModal" type="checkbox" name="conf[blAmazonPayExpressMinicartAndModal]" [{if $config->displayExpressInMiniCartAndModal()}]checked[{/if}] value="1" />
                            [{oxmultilang ident="OSC_AMAZONPAY_MINICART_AND_MODAL"}]
                        </label>
                    </div>
                </div>
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAYEXPRESS_PLACEMENT"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="button-placement">[{oxmultilang ident="OSC_AMAZONPAY_PERFORMANCE"}]</label>
            <div class="controls">
                <div>
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="conf[blAmazonPayUseExclusion]" value="0" />
                            <input id="useExclusion" type="checkbox" name="conf[blAmazonPayUseExclusion]" [{if $config->useExclusion()}]checked[{/if}] value="1" />
                            [{oxmultilang ident="OSC_AMAZONPAY_EXCLUSION"}]
                        </label>
                    </div>
                </div>
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_EXCLUSION"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="button-placement">[{oxmultilang ident="OSC_AMAZONPAY_SOCIAL_LOGIN"}]</label>
            <div class="controls">
                <div>
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="conf[blAmazonSocialLoginDeactivated]" value="0" />
                            <input id="amazonSocialLoginDeactivated" type="checkbox" name="conf[blAmazonSocialLoginDeactivated]" [{if $config->socialLoginDeactivated()}]checked[{/if}] value="1" />
                            [{oxmultilang ident="OSC_AMAZONPAY_SOCIAL_LOGIN_DEACTIVATED"}]
                        </label>
                    </div>
                </div>
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_SOCIAL_LOGIN_DEACTIVATED"}]</span>
            </div>
        </div>

        <div class="form-group jsonform-error-captureType">
            <label for="captype">[{oxmultilang ident="OSC_AMAZONPAY_CAPTYPE"}]</label>
            <div class="controls">
                <select name="conf[amazonPayCapType]" id="captype" class="form-control" required>
                    <option value="1" [{if $config->isOneStepCapture()}]selected[{/if}]>
                        [{oxmultilang ident="OSC_AMAZONPAY_CAPTYPE_ONE_STEP"}]
                    </option>
                    <option value="2" [{if $config->isTwoStepCapture()}]selected[{/if}]>
                        [{oxmultilang ident="OSC_AMAZONPAY_CAPTYPE_TWO_STEP"}]
                    </option>
                </select>
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_CAPTYPE"}]</span>
            </div>
        </div>


        <div class="form-group">
            <label for="button-placement">[{oxmultilang ident="OSC_AMAZONPAY_AUTOMATED_REFUND"}]</label>
            <div class="controls">
                <div>
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="conf[blAmazonAutomatedRefundActivated]" value="0" />
                            <input id="amazonSocialLoginDeactivated" type="checkbox" name="conf[blAmazonAutomatedRefundActivated]" [{if $config->automatedRefundActivated()}]checked[{/if}] value="1" />
                            [{oxmultilang ident="OSC_AMAZONPAY_AUTOMATED_REFUND_ACTIVATED"}]
                        </label>
                    </div>
                </div>
                <div>
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="conf[blAmazonAutomatedCancelActivated]" value="0" />
                            <input id="amazonSocialLoginDeactivated" type="checkbox" name="conf[blAmazonAutomatedCancelActivated]" [{if $config->automatedCancelActivated()}]checked[{/if}] value="1" />
                            [{oxmultilang ident="OSC_AMAZONPAY_AUTOMATED_CANCEL_ACTIVATED"}]
                        </label>
                    </div>
                </div>
                <span class="help-block">[{oxmultilang ident="HELP_OSC_AMAZONPAY_AUTOMATED_REFUND"}]</span>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-default bottom-space">[{oxmultilang ident="OSC_AMAZONPAY_SAVE"}]</button>
        </div>
    </form>

</div>
[{include file="bottomitem.tpl"}]