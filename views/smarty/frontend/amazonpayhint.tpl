[{block name="amazonpay_amazonpayhint"}]
    [{if $oViewConf->isAmazonSessionActive()}]
        <div class="alert alert-info [{if $alignLeft}]text-left[{/if}] [{if $alignRight}]float-right[{/if}]">
            [{assign var="sSelfLink" value=$oViewConf->getSslSelfLink()|replace:"&amp;":"&"}]
            [{oxmultilang ident="AMAZONPAY_RUNNING_CHECKOUT_SESSION_HINT"}][{if $withBreak}]<br />[{/if}]
            <a href="[{$sSelfLink|cat:"cl=order"}]">[{oxmultilang ident="AMAZONPAY_RUNNING_CHECKOUT_SESSION_HINT_AFREF"}]</a>
        </div>
    [{/if}]
[{/block}]