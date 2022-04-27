[{include file="headitem.tpl" title="amazonpay"}]

<div id="content" class="amazonpay-config">
    <h1>[{oxmultilang ident="amazonpay"}] [{oxmultilang ident="OXPS_AMAZONPAY_CONFIG"}]</h1>
    <div class="alert alert-[{if $Errors.amazonpay_error}]danger[{else}]success[{/if}]" role="alert">
        [{if $Errors.amazonpay_error}]
            [{oxmultilang ident="OXPS_AMAZONPAY_ERR_CONF_INVALID"}]
        [{else}]
            [{oxmultilang ident="OXPS_AMAZONPAY_CONF_VALID"}]
        [{/if}]
    </div>

    <form action="[{$oViewConf->getSelfLink()}]" method="post">
        [{$oViewConf->getHiddenSid()}]
        <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
        <input type="hidden" name="fnc" value="save">

        <div class="form-group jsonform-error-paymentRegion">
            <label for="opmode">[{oxmultilang ident="OXPS_AMAZONPAY_OPMODE"}]</label>
            <div class="controls">
                <select name="conf[blAmazonPaySandboxMode]" id="opmode" class="form-control">
                    <option value="sandbox" [{if $config->isSandbox()}]selected[{/if}]>
                        [{oxmultilang ident="OXPS_AMAZONPAY_OPMODE_SANDBOX"}]
                    </option>
                    <option value="prod" [{if !$config->isSandbox()}]selected[{/if}]>
                        [{oxmultilang ident="OXPS_AMAZONPAY_OPMODE_PROD"}]
                    </option>
                </select>
                <span class="help-block">[{oxmultilang ident="HELP_OXPS_AMAZONPAY_OPMODE"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="privkey">[{oxmultilang ident="OXPS_AMAZONPAY_PRIVKEY" suffix="*"}]</label>
            <div class="controls">
                <textarea id="privkey" name="conf[sAmazonPayPrivKey]">[{$displayPrivateKey}]</textarea>
                <span class="help-block">[{oxmultilang ident="HELP_OXPS_AMAZONPAY_PRIVKEY"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="privkeyid">[{oxmultilang ident="OXPS_AMAZONPAY_PUBKEYID" suffix="*"}]</label>
            <div class="controls">
                <input type="text" class="form-control" name="conf[sAmazonPayPubKeyId]" value="[{$config->getPublicKeyId()}]" id="privkeyid">
                <span class="help-block">[{oxmultilang ident="HELP_OXPS_AMAZONPAY_PUBKEYID"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="merchantId">[{oxmultilang ident="OXPS_AMAZONPAY_MERCHANTID" suffix="*"}]</label>
            <div class="controls">
                <input type="text" class="form-control" name="conf[sAmazonPayMerchantId]" value="[{$config->getMerchantId()}]" id="merchantId">
                <span class="help-block">[{oxmultilang ident="HELP_OXPS_AMAZONPAY_MERCHANTID"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="storeId">[{oxmultilang ident="OXPS_AMAZONPAY_STOREID" suffix="*"}]</label>
            <div class="controls">
                <input type="text" class="form-control" name="conf[sAmazonPayStoreId]" value="[{$config->getStoreId()}]" id="storeId">
                <span class="help-block">[{oxmultilang ident="HELP_OXPS_AMAZONPAY_STOREID"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="payRegion">[{oxmultilang ident="OXPS_AMAZONPAY_PAYREGION"}]</label>
            <div class="controls">
                [{assign var="currenciesAbbr" value=$config->getPossiblePresentmentCurrenciesAbbr()}]
                <span id="payRegion">[{", "|implode:$currenciesAbbr}]</span>
                <span class="help-block">[{oxmultilang ident="HELP_OXPS_AMAZONPAY_PAYREGION"}]</span>
            </div>
        </div>

        <h3>[{oxmultilang ident="OXPS_AMAZONPAY_SELLER"}]</h3>

        <div class="form-group">
            <label for="ipnUrl">[{oxmultilang ident="OXPS_AMAZONPAY_IPN"}]</label>
            <div class="controls">
                <input type="text" class="form-control" value="[{$config->getIPNUrl()}]" id="ipnUrl" readonly="readonly">
                <span class="help-block">[{oxmultilang ident="HELP_OXPS_AMAZONPAY_IPN"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="button-placement">[{oxmultilang ident="OXPS_AMAZONPAY_PLACEMENT"}]</label>
            <div class="controls">
                <div>
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="conf[blAmazonPayPDP]" value="0" />
                            <input id="placementDetailPage" type="checkbox" name="conf[blAmazonPayPDP]" [{if $config->displayInPDP()}]checked[{/if}] value="1" />
                            [{oxmultilang ident="OXPS_AMAZONPAY_PDP"}]
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="conf[blAmazonPayMinicartAndModal]" value="0" />
                            <input id="placementMinicartAndModal" type="checkbox" name="conf[blAmazonPayMinicartAndModal]" [{if $config->displayInMinicart()}]checked[{/if}] value="1" />
                            [{oxmultilang ident="OXPS_AMAZONPAY_MINICART_AND_MODAL"}]
                        </label>
                    </div>
                </div>
                <span class="help-block">[{oxmultilang ident="HELP_OXPS_AMAZONPAY_PLACEMENT"}]</span>
            </div>
        </div>

        <div class="form-group">
            <label for="button-placement">[{oxmultilang ident="OXPS_AMAZONPAY_PERFORMANCE"}]</label>
            <div class="controls">
                <div>
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="conf[blAmazonPayUseExclusion]" value="0" />
                            <input id="useExclusion" type="checkbox" name="conf[blAmazonPayUseExclusion]" [{if $config->useExclusion()}]checked[{/if}] value="1" />
                            [{oxmultilang ident="OXPS_AMAZONPAY_EXCLUSION"}]
                        </label>
                    </div>
                </div>
                <span class="help-block">[{oxmultilang ident="HELP_OXPS_AMAZONPAY_EXCLUSION"}]</span>
            </div>
        </div>

        <div class="form-group jsonform-error-captureType">
            <label for="opmode">[{oxmultilang ident="OXPS_AMAZONPAY_CAPTYPE"}]</label>
            <div class="controls">
                <select name="conf[amazonPayCapType]" id="captype" class="form-control" required>
                    <option value="">
                        [{oxmultilang ident="OXPS_AMAZONPAY_PLEASE_CHOOSE"}]
                    </option>
                    <option value="1" [{if $config->isOneStepCapture()}]selected[{/if}]>
                        [{oxmultilang ident="OXPS_AMAZONPAY_CAPTYPE_ONE_STEP"}]
                    </option>
                    <option value="2" [{if $config->isTwoStepCapture()}]selected[{/if}]>
                        [{oxmultilang ident="OXPS_AMAZONPAY_CAPTYPE_TWO_STEP"}]
                    </option>
                </select>
                <span class="help-block">[{oxmultilang ident="HELP_OXPS_AMAZONPAY_CAPTYPE"}]</span>
            </div>
        </div>

        <div class="form-group jsonform-error-payType">
            <label for="opmode">[{oxmultilang ident="OXPS_AMAZONPAY_PAYTYPE"}]</label>
            <div class="controls">
                <select name="conf[amazonPayType]" id="paytype" class="form-control" required>
                    <option value="">
                        [{oxmultilang ident="OXPS_AMAZONPAY_PLEASE_CHOOSE"}]
                    </option>
                    <option value="PayAndShip" [{if $config->getPayType() == 'PayAndShip'}]selected[{/if}]>
                        PayAndShip
                    </option>
                    <option value="PayOnly" [{if $config->getPayType() == 'PayOnly'}]selected[{/if}]>
                        PayOnly
                    </option>
                </select>
                <span class="help-block">[{oxmultilang ident="HELP_OXPS_AMAZONPAY_PAYTYPE"}]</span>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-default bottom-space">[{oxmultilang ident="OXPS_AMAZONPAY_SAVE"}]</button>
        </div>
    </form>

</div>
[{include file="bottomitem.tpl"}]