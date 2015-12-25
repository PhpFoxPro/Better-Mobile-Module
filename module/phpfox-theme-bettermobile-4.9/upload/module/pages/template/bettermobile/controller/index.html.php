{literal}
<style type="text/css">
    .header_bar_menu{
        display: none;
    }
    a.moderate_link, a.moderate_link:hover {
        display: none;
    }
</style>
{/literal}

{if count($aPages)}
{if $sView == 'my' && Phpfox::getUserBy('profile_page_id')}
<div class="message">
	{phrase var='pages.note_that_pages_displayed_here_are_pages_created_by_the_page' global_full_name=$sGlobalUserFullName|clean profile_full_name=$aGlobalProfilePageLogin.full_name|clean}
</div>
{/if}
{foreach from=$aPages name=pages item=aPage}

		<div class="row_title_pages">
			<div class="row_title_image_pages">
				<a href="{$aPage.link}">{img server_id=$aPage.profile_server_id title=$aPage.title path='core.url_user' file=$aPage.profile_user_image suffix='_120_square' max_width='60' max_height='60' is_page_image=true}</a>
				{if Phpfox::getUserParam('pages.can_moderate_pages') || $aPage.user_id == Phpfox::getUserId()}
				<div class="row_edit_bar_parent">
					<div class="row_edit_bar_holder">
						<ul>
							{template file='pages.block.link'}
						</ul>
					</div>
					<div class="row_edit_bar">
							<a href="#" class="row_edit_bar_action"><span>{phrase var='pages.actions'}</span></a>
					</div>
				</div>
				{/if}

				{if Phpfox::getUserParam('pages.can_moderate_pages')}
				<a href="#{$aPage.page_id}" class="moderate_link" rel="pages">{phrase var='pages.moderate'}</a>
				{/if}
			</div>
			<div class="row_title_info_pages">
                <div class="row_title_info_pages_text">
                    <a href="{$aPage.link}" class="link">{$aPage.title|clean|split:18}</a>
                    <div class="extra_info_pages">
                        <ul class="extra_info_middot"><li>{$aPage.category_name|convert|shorten:17:'...'}</li>{if $aPage.page_type == '1'}<li><span>&middot;</span></li><li>{if $aPage.total_like > 1}{phrase var='pages.total_members' total=$aPage.total_like|number_format}{elseif $aPage.total_like == 1}{phrase var='pages.1_member'}{else}{phrase var='pages.no_members'}{/if}</li>{/if}</ul>
                    </div>
                </div>
			</div>

		</div>
<div class="clear"></div>

{/foreach}
{if Phpfox::getUserParam('pages.can_moderate_pages')}
{moderation}
{/if}

{pager}
{else}
<div class="extra_info">
	{phrase var='pages.no_pages_found'}
</div>
{/if}