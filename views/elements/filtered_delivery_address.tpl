[{if $delivadr}]
    <div id="shippingAddress" class="col-lg-9 offset-lg-3">
        [{if $delivadr->oxaddress__oxcompany->value != ''}] <strong>[{$delivadr->oxaddress__oxcompany->value}]</strong><br />[{/if}]
        [{if $delivadr->oxaddress__oxaddinfo->value != ''}] [{$delivadr->oxaddress__oxaddinfo->value}]<br />[{/if}]
        [{if $delivadr->oxaddress__oxfname->value != '' || $delivadr->oxaddress__oxlname->value != ''}]
            [{if $delivadr->oxaddress__oxsal->value  != ''}][{$delivadr->oxaddress__oxsal->value|oxmultilangsal}]&nbsp;[{/if}][{$delivadr->oxaddress__oxfname->value}] [{$delivadr->oxaddress__oxlname->value}]<br />
        [{/if}]
        [{if $delivadr->oxaddress__oxstreet->value  != '' || $delivadr->oxaddress__oxstreetnr->value != ''}][{$delivadr->oxaddress__oxstreet->value}]&nbsp;[{$delivadr->oxaddress__oxstreetnr->value}]<br />[{/if}]
        [{if $delivadr->oxaddress__oxstateid->value != ''}][{$delivadr->oxaddress__oxstateid->value}] [{/if}]
        [{if $delivadr->oxaddress__oxzip->value  != '' || $delivadr->oxaddress__oxcity->value != ''}][{$delivadr->oxaddress__oxzip->value}]&nbsp;[{$delivadr->oxaddress__oxcity->value}]<br />[{/if}]
        [{if $delivadr->oxaddress__oxcountry->value != ''}][{$delivadr->oxaddress__oxcountry->value}]<br /><br />[{/if}]
        [{if $delivadr->oxaddress__oxfon->value != ''}]<strong>[{oxmultilang ident="PHONE"}]</strong> [{$delivadr->oxaddress__oxfon->value}]<br />[{/if}]
        [{if $delivadr->oxaddress__oxfax->value != ''}]<strong>[{oxmultilang ident="FAX"}]</strong> [{$delivadr->oxaddress__oxfax->value}]<br />[{/if}]
    </div>
[{/if}]
