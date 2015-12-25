<div class="info_holder">

	<div class="info">
		<div class="info_left">
			{phrase var='marketplace.posted_on'}:
		</div>
		<div class="info_right" itemprop="releaseDate">
			{$aListing.time_stamp|date:'marketplace.marketplace_view_time_stamp'}
		</div>
	</div>
	
	{if is_array($aListing.categories) && count($aListing.categories)}
	<div class="info">
		<div class="info_left">
			{phrase var='marketplace.category'}:
		</div>
		<div class="info_right">
			{$aListing.categories|category_display}
		</div>
	</div>		
	{/if}	
	
	<div class="info">
		<div class="info_left">
			{phrase var='marketplace.posted_by'}:
		</div>
		<div class="info_right">
			{$aListing|user:'':'':50}
		</div>
	</div>
	<div class="info">
		<div class="info_left">
			{phrase var='marketplace.location'}:
		</div>
		<div class="info_right">
			{$aListing.country_iso|location}
			{if !empty($aListing.country_child_id)}
			<div class="p_2">&raquo; {$aListing.country_child_id|location_child}</div>
			{/if}
			{if !empty($aListing.city)}
			<div class="p_2">&raquo; {$aListing.city|clean|split:50} </div>
			{/if}			
		</div>
	</div>
	
	{if Phpfox::isModule('input')}
		{module name='input.display' action='add-listing' module='marketplace' item_id=$aListing.listing_id}
	{/if}
	
	<div class="item_view_content" itemprop="description">
		{$aListing.description|parse|split:70}
	</div>
</div>
<div id="js_marketplace_click_image_viewer">
    <div id="js_marketplace_click_image_viewer_inner">
        {phrase var='marketplace.loading'}
    </div>
    <div id="js_marketplace_click_image_viewer_close">
        <a href="#">{phrase var='marketplace.close'}</a>
    </div>
</div>
{if count($aImages) > 1}
<div class="js_box_thumbs_holder2">
    {/if}
    <div class="marketplace_image_holder">
        <div class="marketplace_image">
            <a class="js_marketplace_click_image no_ajax_link" href="{img return_url=true server_id=$aListing.server_id title=$aListing.title path='marketplace.url_image' file=$aListing.image_path suffix='_400'}">
                {img server_id=$aListing.server_id title=$aListing.title path='marketplace.url_image' file=$aListing.image_path suffix='_200' max_width='180' max_height='180'}</a>
        </div>
        {if count($aImages) > 1}
        <div class="marketplace_image_extra js_box_image_holder_thumbs">
            <ul>{foreach from=$aImages name=images item=aImage}<li><a class="js_marketplace_click_image no_ajax_link" href="{img return_url=true server_id=$aImage.server_id title=$aListing.title path='marketplace.url_image' file=$aImage.image_path suffix='_400'}">{img server_id=$aImage.server_id title=$aListing.title path='marketplace.url_image' file=$aImage.image_path suffix='_120_square' max_width='65' max_height='65'}</a></li>{/foreach}</ul>
            <div class="clear"></div>
        </div>
        {/if}
    </div>
    {if count($aImages) > 1}
</div>
{/if}