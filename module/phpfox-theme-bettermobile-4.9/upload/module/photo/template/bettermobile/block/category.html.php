{if isset($aCategories)}
{template file='bettermobile.block.core.category'}

{else}
{if $bParent}
<ul class="action">
	<li><a href="{if $aCallback === null}{url link='photo'}{else}{url link=$aCallback.url_home}{/if}" class="js_photo_category">{phrase var='photo.all_categories'}</a>{/if}
		{$sCategories}
{if $bParent}</li>
</ul>
{/if}
{/if}