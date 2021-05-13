[{block name="wave_missing_delivery_address"}]
    <div id="missing_delivery_address">
        [{block name="wave_missing_delivery_address_note"}]
            <div class="form-group row">
                <div class="col-12">
                    <p><strong>[{oxmultilang ident="COMPLETE_MARKED_FIELDS"}]</strong></p>
                </div>
            </div>
        [{/block}]
        [{block name="wave_missing_delivery_address_fields"}]
            [{if 'oxaddress__oxsal'|array_key_exists:$missingRequiredDeliveryFields}]
                <div class="form-group row text-danger">
                    <label class="col-3 req" for="missing_amazon_deladr_oxaddress__oxsal">[{oxmultilang ident="TITLE"}]</label>
                    <div class="col-9">
                        [{include file="form/fieldset/salutation.tpl" name="missing_amazon_deladr[oxaddress__oxsal]" class="form-control" id="missing_amazon_deladr_oxaddress__oxsal"}]
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxaddress__oxfname'|array_key_exists:$missingRequiredDeliveryFields}]
                <div class="form-group row text-danger">
                    <label class="col-3 req" for="missing_amazon_deladr_oxaddress__oxfname">[{oxmultilang ident="FIRST_NAME"}]</label>
                    <div class="col-9">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_deladr[oxaddress__oxfname]" id="missing_amazon_deladr_oxaddress__oxfname" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxaddress__oxlname'|array_key_exists:$missingRequiredDeliveryFields}]
                <div class="form-group row text-danger">
                    <label class="col-3 req" for="missing_amazon_deladr_oxaddress__oxlname">[{oxmultilang ident="LAST_NAME"}]</label>
                    <div class="col-9">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_deladr[oxaddress__oxlname]" id="missing_amazon_deladr_oxaddress__oxlname" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxaddress__oxcompany'|array_key_exists:$missingRequiredDeliveryFields}]
                <div class="form-group row text-danger">
                    <label class="col-3 req" for="missing_amazon_deladr_oxaddress__oxcompany">[{oxmultilang ident="COMPANY"}]</label>
                    <div class="col-9">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_deladr[oxaddress__oxcompany]" id="missing_amazon_deladr_oxaddress__oxcompany" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxaddress__oxaddinfo'|array_key_exists:$missingRequiredDeliveryFields}]
                <div class="form-group row text-danger">
                    [{assign var="_address_addinfo_tooltip" value="FORM_FIELDSET_USER_BILLING_ADDITIONALINFO_TOOLTIP"|oxmultilangassign}]
                    <label [{if $_address_addinfo_tooltip}]title="[{$_address_addinfo_tooltip}]"[{/if}] class="col-3 req[{if $_address_addinfo_tooltip}] tooltip[{/if}]" for="missing_amazon_deladr_oxaddress__oxaddinfo">[{oxmultilang ident="ADDITIONAL_INFO"}]</label>
                    <div class="col-9">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_deladr[oxaddress__oxaddinfo]" id="missing_amazon_deladr_oxaddress__oxaddinfo" value="" required="">
                        [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxaddress__oxaddinfo}]
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxaddress__oxstreet'|array_key_exists:$missingRequiredDeliveryFields || 'oxaddress__oxstreetno'|array_key_exists:$missingRequiredDeliveryFields}]
                <div class="form-group row text-danger">
                    <label class="col-12 req" for="missing_amazon_deladr_oxaddress__oxstreet">[{oxmultilang ident="STREET_AND_STREETNO"}]</label>
                    <div class="col-8">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_deladr[oxaddress__oxstreet]" id="missing_amazon_deladr_oxaddress__oxstreet" value="" required="">
                    </div>
                    <div class="col-4">
                        <input class="form-control" type="text" maxlength="16" name="missing_amazon_deladr[oxaddress__oxstreetnr]" id="missing_amazon_deladr_oxaddress__oxstreetnr" value="" required="">
                    </div>
                    <div class="offset-3 col-9">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxaddress__oxzip'|array_key_exists:$missingRequiredDeliveryFields || 'oxaddress__oxcity'|array_key_exists:$missingRequiredDeliveryFields}]
                <div class="form-group row text-danger">
                    <label class="col-3 req" for="missing_amazon_deladr_oxaddress__oxzip">[{oxmultilang ident="POSTAL_CODE_AND_CITY"}]</label>
                    <div class="col-5">
                        <input class="form-control" type="text" maxlength="16" name="missing_amazon_deladr[oxaddress__oxzip]" id="missing_amazon_deladr_oxaddress__oxzip" value="" required="">
                    </div>
                    <div class="col-7">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_deladr[oxaddress__oxcity]" id="missing_amazon_deladr_oxaddress__oxcity" value="" required="">
                    </div>
                    <div class="offset-3 col-9">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxaddress__oxcountryid'|array_key_exists:$missingRequiredDeliveryFields}]
                <div class="form-group row text-danger">
                    <label class="col-3 req" for="missing_amazon_deladr_oxaddress__oxcountryid">[{oxmultilang ident="COUNTRY"}]</label>
                    <div class="col-9">
                        <select class="form-control" id="missing_amazon_deladr_oxaddress__oxcountryid" name="missing_amazon_deladr[oxaddress__oxcountryid]" required="">
                            <option value="">-</option>
                            [{assign var="blCountrySelected" value=false}]
                            [{foreach from=$oViewConf->getCountryList() item=country key=country_id}]
                                <option value="[{$country->oxcountry__oxid->value}]">[{$country->oxcountry__oxtitle->value}]</option>
                            [{/foreach}]
                        </select>
                        <div class="help-block"></div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-3" for="[{$oxcmp_user->oxaddress__oxstateid->value}]">[{oxmultilang ident="DD_USER_LABEL_STATE" suffix="COLON"}]</label>
                    <div class="col-9">
                        [{include file="form/fieldset/state.tpl"
                            countrySelectId="missing_amazon_deladr_oxaddress__oxcountryid"
                            stateSelectname="missing_amazon_deladr[oxaddress__oxstateid]"
                            class="form-control"
                            id="missing_amazon_deladr_oxaddress__oxstateid"
                        }]
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxaddress__oxfon'|array_key_exists:$missingRequiredDeliveryFields}]
                <div class="form-group row text-danger">
                    <label class="col-3 req" for="missing_amazon_deladr_oxaddress__oxfon">[{oxmultilang ident="PHONE"}]</label>
                    <div class="col-9">
                        <input class="form-control" type="text" maxlength="128" name="missing_amazon_deladr[oxaddress__oxfon]" id="missing_amazon_deladr_oxaddress__oxfon" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxaddress__oxfax'|array_key_exists:$missingRequiredDeliveryFields}]
                <div class="form-group row text-danger">
                    <label class="col-3 req" for="missing_amazon_deladr_oxaddress__oxfax">[{oxmultilang ident="FAX"}]</label>
                    <div class="col-9">
                        <input class="form-control" type="text" maxlength="128" name="missing_amazon_deladr[oxaddress__oxfax]" id="missing_amazon_deladr_oxaddress__oxfax" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
        [{/block}]
    </div>
[{/block}]