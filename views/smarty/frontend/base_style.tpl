[{if $oViewConf->isAmazonActive()}]
    [{assign var="sFileMTime" value=$oViewConf->getModulePath('osc_amazonpay','css/amazonpay.min.css')|filemtime}]
    [{oxstyle include=$oViewConf->getModuleUrl('osc_amazonpay', 'css/amazonpay.min.css')|cat:"?"|cat:$sFileMTime}]
[{/if}]