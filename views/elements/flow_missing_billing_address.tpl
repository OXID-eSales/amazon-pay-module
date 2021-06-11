[{block name="flow_missing_billing_address"}]
    <div id="missing_billing_address">
        [{block name="flow_missing_billing_address_note"}]
            <div class="form-group row">
                <div class="col-xs-12">
                    <p><strong>[{oxmultilang ident="COMPLETE_MARKED_FIELDS"}]</strong></p>
                </div>
            </div>
        [{/block}]
        [{block name="flow_missing_billing_address_fields"}]
            [{if 'oxuser__oxsal'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-3 req" for="missing_amazon_invadr_oxuser__oxsal">[{oxmultilang ident="TITLE"}]</label>
                    <div class="col-xs-9">
                        [{include file="form/fieldset/salutation.tpl" name="missing_amazon_invadr[oxuser__oxsal]" class="form-control" id="missing_amazon_invadr_oxuser__oxsal"}]
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxfname'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-3 req" for="missing_amazon_invadr_oxuser__oxfname">[{oxmultilang ident="FIRST_NAME"}]</label>
                    <div class="col-xs-9">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_invadr[oxuser__oxfname]" id="missing_amazon_invadr_oxuser__oxfname" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxlname'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-3 req" for="missing_amazon_invadr_oxuser__oxlname">[{oxmultilang ident="LAST_NAME"}]</label>
                    <div class="col-xs-9">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_invadr[oxuser__oxlname]" id="missing_amazon_invadr_oxuser__oxlname" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxcompany'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-3 req" for="missing_amazon_invadr_oxuser__oxcompany">[{oxmultilang ident="COMPANY"}]</label>
                    <div class="col-xs-9">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_invadr[oxuser__oxcompany]" id="missing_amazon_invadr_oxuser__oxcompany" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxaddinfo'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    [{assign var="_address_addinfo_tooltip" value="FORM_FIELDSET_USER_BILLING_ADDITIONALINFO_TOOLTIP"|oxmultilangassign}]
                    <label [{if $_address_addinfo_tooltip}]title="[{$_address_addinfo_tooltip}]"[{/if}] class="col-xs-3 req[{if $_address_addinfo_tooltip}] tooltip[{/if}]" for="missing_amazon_invadr_oxuser__oxaddinfo">[{oxmultilang ident="ADDITIONAL_INFO"}]</label>
                    <div class="col-xs-9">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_invadr[oxuser__oxaddinfo]" id="missing_amazon_invadr_oxuser__oxaddinfo" value="" required="">
                        [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxaddinfo}]
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxstreet'|array_key_exists:$missingRequiredBillingFields || 'oxuser__oxstreetnr'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-12 req" for="missing_amazon_invadr_oxuser__oxstreet">[{oxmultilang ident="STREET_AND_STREETNO"}]</label>
                    <div class="col-xs-8">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_invadr[oxuser__oxstreet]" id="missing_amazon_invadr_oxuser__oxstreet" value="" required="">
                    </div>
                    <div class="col-xs-4">
                        <input class="form-control" type="text" maxlength="16" name="missing_amazon_invadr[oxuser__oxstreetnr]" id="missing_amazon_invadr_oxuser__oxstreetnr" value="" required="">
                    </div>
                    <div class="offset-xs-3 col-xs-9">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxzip'|array_key_exists:$missingRequiredBillingFields || 'oxuser__oxcity'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-3 req" for="missing_amazon_invadr_oxuser__oxzip">[{oxmultilang ident="POSTAL_CODE_AND_CITY"}]</label>
                    <div class="col-xs-5">
                        <input class="form-control" type="text" maxlength="16" name="missing_amazon_invadr[oxuser__oxzip]" id="missing_amazon_invadr_oxuser__oxzip" value="" required="">
                    </div>
                    <div class="col-xs-7">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_invadr[oxuser__oxcity]" id="missing_amazon_invadr_oxuser__oxcity" value="" required="">
                    </div>
                    <div class="offset-xs-3 col-xs-9">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxustid'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-3 req" for="missing_amazon_invadr_oxuser__oxustid">[{oxmultilang ident="VAT_ID_NUMBER"}]</label>
                    <div class="col-xs-9">
                        <input class="form-control" type="text" maxlength="255" name="missing_amazon_invadr[oxuser__oxustid]" id="missing_amazon_invadr_oxuser__oxustid" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxcountryid'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-3 req" for="missing_amazon_invadr_oxaddress__oxcountryid">[{oxmultilang ident="COUNTRY"}]</label>
                    <div class="col-xs-9">
                        <select class="form-control" id="missing_amazon_invadr_oxaddress__oxcountryid" name="missing_amazon_invadr[oxuser__oxcountryid]" required="">
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
                    <label class="col-xs-3" for="[{$oxcmp_user->oxuser__oxstateid->value}]">[{oxmultilang ident="DD_USER_LABEL_STATE" suffix="COLON"}]</label>
                    <div class="col-xs-9">
                        [{include file="form/fieldset/state.tpl"
                            countrySelectId="missing_amazon_invadr_oxaddress__oxcountryid"
                            stateSelectname="missing_amazon_invadr[oxuser__oxstateid]"
                            class="form-control"
                            id="missing_amazon_invadr_oxuser__oxstateid"
                        }]
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxfon'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-3 req" for="missing_amazon_invadr_oxuser__oxfon">[{oxmultilang ident="PHONE"}]</label>
                    <div class="col-xs-9">
                        <input class="form-control" type="text" maxlength="128" name="missing_amazon_invadr[oxuser__oxfon]" id="missing_amazon_invadr_oxuser__oxfon" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxfax'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-3 req" for="missing_amazon_invadr_oxuser__oxfax">[{oxmultilang ident="FAX"}]</label>
                    <div class="col-xs-9">
                        <input class="form-control" type="text" maxlength="128" name="missing_amazon_invadr[oxuser__oxfax]" id="missing_amazon_invadr_oxuser__oxfax" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxmobfon'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-3 req" for="missing_amazon_invadr_oxuser__oxmobfon">[{oxmultilang ident="CELLUAR_PHONE"}]</label>
                    <div class="col-xs-9">
                        <input class="form-control" type="text" maxlength="128" name="missing_amazon_invadr[oxuser__oxmobfon]" id="missing_amazon_invadr_oxuser__oxmobfon" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxprivfon'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row text-danger">
                    <label class="col-xs-3 req" for="missing_amazon_invadr_oxuser__oxprivfon">[{oxmultilang ident="PERSONAL_PHONE"}]</label>
                    <div class="col-xs-9">
                        <input class="form-control" type="text" maxlength="128" name="missing_amazon_invadr[oxuser__oxprivfon]" id="missing_amazon_invadr_oxuser__oxprivfon" value="" required="">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
            [{if 'oxuser__oxbirthdate'|array_key_exists:$missingRequiredBillingFields}]
                <div class="form-group row oxDate text-danger">
                    <label class="col-xs-3 req" for="oxDay">[{oxmultilang ident="BIRTHDATE"}]</label>
                    <div class="col-xs-3">
                        <input id="oxDay" class="oxDay form-control" name="missing_amazon_invadr[oxuser__oxbirthdate][day]" id="missing_amazon_invadr_oxuser__oxbirthdate_day" type="text" maxlength="2" value="" placeholder="[{oxmultilang ident="DAY"}]" required="">
                    </div>
                    <div class="col-xs-3">
                        <select class="oxMonth form-control" name="missing_amazon_invadr[oxuser__oxbirthdate][month]" required="">
                            <option value="" label="-">-</option>
                            [{section name="month" start=1 loop=13}]
                                <option value="[{$smarty.section.month.index}]" label="[{$smarty.section.month.index}]">
                                    [{oxmultilang ident="MONTH_NAME_"|cat:$smarty.section.month.index}]
                                </option>
                            [{/section}]
                        </select>
                    </div>
                    <div class="col-xs-3">
                        <input id="oxYear" class="oxYear form-control" name="missing_amazon_invadr[oxuser__oxbirthdate][year]" type="text" maxlength="4" value="" placeholder="[{oxmultilang ident="YEAR"}]" required="">
                    </div>
                    <div class="offset-xs-3">
                        <div class="help-block"></div>
                    </div>
                </div>
            [{/if}]
        [{/block}]
    </div>
[{/block}]
