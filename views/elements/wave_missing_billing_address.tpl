[{if 'oxuser__oxsal'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-3 req" for="invadr_oxuser__oxsal">[{oxmultilang ident="TITLE"}]</label>
        <div class="col-9">
            [{include file="form/fieldset/salutation.tpl" name="invadr[oxuser__oxsal]" class="form-control" id="invadr_oxuser__oxsal"}]
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxfname'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-3 req" for="invadr_oxuser__oxfname">[{oxmultilang ident="FIRST_NAME"}]</label>
        <div class="col-9">
            <input class="form-control" type="text" maxlength="255" name="invadr[oxuser__oxfname]" id="invadr_oxuser__oxfname" value="" required="">
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxlname'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-3 req" for="invadr_oxuser__oxlname">[{oxmultilang ident="LAST_NAME"}]</label>
        <div class="col-9">
            <input class="form-control" type="text" maxlength="255" name="invadr[oxuser__oxlname]" id="invadr_oxuser__oxlname" value="" required="">
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxcompany'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-3 req" for="invadr_oxuser__oxcompany">[{oxmultilang ident="COMPANY"}]</label>
        <div class="col-9">
            <input class="form-control" type="text" maxlength="255" name="invadr[oxuser__oxcompany]" id="invadr_oxuser__oxcompany" value="" required="">
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxaddinfo'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        [{assign var="_address_addinfo_tooltip" value="FORM_FIELDSET_USER_BILLING_ADDITIONALINFO_TOOLTIP"|oxmultilangassign}]
        <label [{if $_address_addinfo_tooltip}]title="[{$_address_addinfo_tooltip}]"[{/if}] class="col-3 req[{if $_address_addinfo_tooltip}] tooltip[{/if}]" for="invadr_oxuser__oxaddinfo">[{oxmultilang ident="ADDITIONAL_INFO"}]</label>
        <div class="col-9">
            <input class="form-control" type="text" maxlength="255" name="invadr[oxuser__oxaddinfo]" id="invadr_oxuser__oxaddinfo" value="" required="">
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxaddinfo}]
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxstreet'|array_key_exists:$missingRequiredBillingFields || 'oxuser__oxstreetno'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-12 req" for="invadr_oxuser__oxstreet">[{oxmultilang ident="STREET_AND_STREETNO"}]</label>
        <div class="col-8">
            <input class="form-control" type="text" maxlength="255" name="invadr[oxuser__oxstreet]" id="invadr_oxuser__oxstreet" value="" required="">
        </div>
        <div class="col-4">
            <input class="form-control" type="text" maxlength="16" name="invadr[oxuser__oxstreetnr]" id="invadr_oxuser__oxstreetnr" value="" required="">
        </div>
        <div class="offset-3 col-9">
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxzip'|array_key_exists:$missingRequiredBillingFields || 'oxuser__oxcity'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-3 req" for="invadr_oxuser__oxzip">[{oxmultilang ident="POSTAL_CODE_AND_CITY"}]</label>
        <div class="col-5">
            <input class="form-control" type="text" maxlength="16" name="invadr[oxuser__oxzip]" id="invadr_oxuser__oxzip" value="" required="">
        </div>
        <div class="col-7">
            <input class="form-control" type="text" maxlength="255" name="invadr[oxuser__oxcity]" id="invadr_oxuser__oxcity" value="" required="">
        </div>
        <div class="offset-3 col-9">
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxustid'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-3 req" for="invadr_oxuser__oxustid">[{oxmultilang ident="VAT_ID_NUMBER"}]</label>
        <div class="col-9">
            <input class="form-control" type="text" maxlength="255" name="invadr[oxuser__oxustid]" id="invadr_oxuser__oxustid" value="" required="">
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxcountryid'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-3 req" for="invCountrySelect">[{oxmultilang ident="COUNTRY"}]</label>
        <div class="col-9">
            <select class="form-control" id="invCountrySelect" name="invadr[oxuser__oxcountryid]" required=""[{/if}]>
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
        <label class="col-3" for="[{$oxcmp_user->oxuser__oxstateid->value}]">[{oxmultilang ident="DD_USER_LABEL_STATE" suffix="COLON"}]</label>
        <div class="col-9">
            [{include file="form/fieldset/state.tpl"
                countrySelectId="invCountrySelect"
                stateSelectName="invadr[oxuser__oxstateid]"
                class="form-control"
                id="invadr_oxuser__oxstateid"
            }]
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxfon'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-3 req" for="invadr_oxuser__oxfon">[{oxmultilang ident="PHONE"}]</label>
        <div class="col-9">
            <input class="form-control" type="text" maxlength="128" name="invadr[oxuser__oxfon]" id="invadr_oxuser__oxfon" value="" required="">
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxfax'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-3 req" for="invadr_oxuser__oxfax">[{oxmultilang ident="FAX"}]</label>
        <div class="col-9">
            <input class="form-control" type="text" maxlength="128" name="invadr[oxuser__oxfax]" id="invadr_oxuser__oxfax" value="" required="">
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxmobfon'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-3 req" for="invadr_oxuser__oxmobfon">[{oxmultilang ident="CELLUAR_PHONE"}]</label>
        <div class="col-9">
            <input class="form-control" type="text" maxlength="128" name="invadr[oxuser__oxmobfon]" id="invadr_oxuser__oxmobfon" value="" required="">
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxprivfon'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row text-danger">
        <label class="col-3 req" for="invadr_oxuser__oxprivfon">[{oxmultilang ident="PERSONAL_PHONE"}]</label>
        <div class="col-9">
            <input class="form-control" type="text" maxlength="128" name="invadr[oxuser__oxprivfon]" id="invadr_oxuser__oxprivfon" value="" required="">
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
[{if 'oxuser__oxbirthdate'|array_key_exists:$missingRequiredBillingFields}]
    <div class="form-group row oxDate text-danger">
        <label class="col-3 req" for="oxDay">[{oxmultilang ident="BIRTHDATE"}]</label>
        <div class="col-3">
            <input id="oxDay" class="oxDay form-control" name="invadr[oxuser__oxbirthdate][day]" id="invadr_oxuser__oxbirthdate_day" type="text" maxlength="2" value="" placeholder="[{oxmultilang ident="DAY"}]" required="">
        </div>
        <div class="col-3">
            <select class="oxMonth form-control" name="invadr[oxuser__oxbirthdate][month]" required="">
                <option value="" label="-">-</option>
                [{section name="month" start=1 loop=13}]
                    <option value="[{$smarty.section.month.index}]" label="[{$smarty.section.month.index}]">
                        [{oxmultilang ident="MONTH_NAME_"|cat:$smarty.section.month.index}]
                    </option>
                [{/section}]
            </select>
        </div>
        <div class="col-3">
            <input id="oxYear" class="oxYear form-control" name="invadr[oxuser__oxbirthdate][year]" type="text" maxlength="4" value="" placeholder="[{oxmultilang ident="YEAR"}]" required="">
        </div>
        <div class="offset-3">
            <div class="help-block"></div>
        </div>
    </div>
[{/if}]
<div class="form-group row">
    <div class="offset-3 col-9">
        <p class="alert alert-info">[{oxmultilang ident="COMPLETE_MARKED_FIELDS"}]</p>
    </div>
</div>
