{literal}
<style type="text/css">
    .extra_info{
        display: none;
    }
    #breadcrumb_holder{
        display: none;
    }
    .header_bar_menu{
        display: none;
    }
    #mobile_h1_main{
        display: none;
    }
</style>
{/literal}
{if !PHPFOX_IS_AJAX}
<div class="main_search_bar">
	<form method="post" action="{url link='search'}">
        <a href="#" onclick='$("#header_search_form").submit(); return false;' id="main_search_button">{phrase var='core.search'}</a>
		<input type="text" name="q" value="{if isset($sQuery)}{$sQuery|clean}{/if}" class="main_search_bar_input" />
        <a href="#" onclick='oSearch.clear(); return false;' id="main_clear_button"></a>
	</form>
</div>
{/if}

{if isset($aSearchResults) && count($aSearchResults)}
{if PHPFOX_IS_AJAX}
<div class="search_result_new"></div>
{/if}
{foreach from=$aSearchResults item=aSearchResult}
<div class="search_result">
	<div class="search_result_image" style="margin: 0px !important;">
        <div class="search_result_image_inside">
		{if isset($aSearchResult.profile_image)}
		{img user=$aSearchResult.profile_image suffix='_120_square' max_width=62 max_height=62}
		{else}
		{img user=$aSearchResult suffix='_120_square' max_width=62 max_height=62}
		{/if}
        </div>
	</div>
	<div class="search_result_info">
		<div class="search_result_title">
			<a href="{$aSearchResult.item_link}" title="{$aSearchResult.item_title|clean}">{$aSearchResult.item_title|clean|shorten:'30':'...'}</a>
		</div>
	</div>
</div>
{/foreach}
<div class="clear"></div>
<div id="feed_view_more" style="height: 20px;">
		<a href="#" onclick="$(this).html($.ajaxProcess('{phrase var='feed.loading'}')); $.ajaxCall('search.viewMore', '{$sNextPage}', 'GET'); return false;" class="global_view_more no_ajax_link">{phrase var='bettermobile.show_more_results'} &rsaquo;</a>
</div>
{else}
{if PHPFOX_IS_AJAX}
{phrase var='search.no_more_search_results_to_show'}
{else}
{phrase var='search.no_search_results_found'}
{/if}
{/if}
{if !PHPFOX_IS_AJAX}
{literal}
<style type="text/css">
    .extra_info{
        display: none;
    }
    #js_feed_content .search_result .search_result_image a img{
        margin-left: 2px;
        margin-top: 2px;
    }
</style>
{/literal}
<div id="js_feed_content" style="background: #fff"></div>
<div class="clear"></div>
{/if}