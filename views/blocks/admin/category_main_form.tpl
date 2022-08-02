[{$smarty.block.parent}]
[{if $oViewConf|method_exists:'useExclusion' && $oViewConf->useExclusion()}]
    <tr>
        <td class="edittext" width="120">
            [{oxmultilang ident="OSC_AMAZONPAY_EXCLUDED"}]
        </td>
        <td class="edittext">
            <input type="hidden" name="editval[oxcategories__osc_amazon_exclude]" value="0">
            <input class="edittext" type="checkbox" name="editval[oxcategories__osc_amazon_exclude]" value='1' [{if $edit->oxcategories__osc_amazon_exclude->value == 1}]checked[{/if}] [{$readonly}]>
            [{oxinputhelp ident="HELP_ARTICLE_MAIN_ACTIVE"}]
        </td>
    </tr>
[{/if}]