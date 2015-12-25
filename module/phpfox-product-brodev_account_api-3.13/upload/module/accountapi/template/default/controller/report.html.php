
{if $bCanReport}
<div id="js_report_body">
    {phrase var='report.you_are_about_to_report_a_violation_of_our_a_href_link_target_blank_terms_of_use_a' link=$sTermsUrl}
    <div class="p_4">
        {phrase var='report.all_reports_are_strictly_confidential'}
        <div class="p_top_8">
            <form action="{url link='accountapi.report'}" method="post" enctype="multipart/form-data">
                <div><input type="hidden" name="val[type]" value="{$sType}" /></div>
                <div><input type="hidden" name="val[item]" value="{$iItemId}" /></div>
                <div class="table">
                    <div class="table_left">
                        {phrase var='report.reason'}:
                    </div>
                    <div class="table_right">
                        <select name="val[reason]" id="js_report">
                            <option value="">{phrase var='report.choose_one'}</option>
                            {foreach from=$aOptions item=aOption}
                            <option value="{$aOption.report_id}">{$aOption.message|convert}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="table_left">
                        {phrase var='report.a_comment_optional'}:
                    </div>
                    <div class="table_right">
                        <textarea name="val[feedback]" id="feedback" cols="19" rows="3"></textarea>
                    </div>
                </div>
                <div class="table">
                    <div class="table_left"></div>
                    <div class="table_right">
                        <input type="submit" value="{phrase var='core.submit'}" class="button" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
{else}
{phrase var='report.you_have_already_reported_this_item'}
{/if}