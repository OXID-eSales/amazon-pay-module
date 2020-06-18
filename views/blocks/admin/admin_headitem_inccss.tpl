[{if $oViewConf->getTopActiveClassName()|lower=="amazonconfig"}]
    <link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="[{$oViewConf->getModuleUrl('oxps/amazonpay', 'out/src/css/amazonpay_backend.min.css')}]" />
[{else}]
    [{$smarty.block.parent}]
[{/if}]

