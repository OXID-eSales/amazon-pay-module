[{$smarty.block.parent}]
<tr>
    <td class="edittext" width="120">
        [{oxmultilang ident="OXPS_AMAZONPAY_EXCLUDED"}]
    </td>
    <td class="edittext">
        <input type="hidden" name="editval[oxcategories__oxps_amazon_exclude]" value="0">
        <input class="edittext" type="checkbox" name="editval[oxcategories__oxps_amazon_exclude]" value='1' [{if $edit->oxcategories__oxps_amazon_exclude->value == 1}]checked[{/if}] [{$readonly}]>
        [{oxinputhelp ident="HELP_ARTICLE_MAIN_ACTIVE"}]
    </td>
</tr>