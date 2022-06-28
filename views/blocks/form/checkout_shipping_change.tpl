[{if !$oViewConf->isAmazonSessionActive() && !$oViewConf->isAmazonExclude()}]
    [{$smarty.block.parent}]
[{/if}]