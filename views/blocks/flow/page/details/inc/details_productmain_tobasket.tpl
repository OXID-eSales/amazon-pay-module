[{$smarty.block.parent}]

[{if $oViewConf->isAmazonActive() && $oViewConf->displayInPDP() && !$oViewConf->isAmazonExclude($oDetailsProduct->oxarticles__oxid->value)}]
    <div class="text-center amazonpay-button-or large">[{"OR"|oxmultilangassign|oxupper}]</div>
    [{include file='amazonbutton.tpl' buttonId="AmazonPayButtonProductMain"}]
    <br />
[{/if}]
