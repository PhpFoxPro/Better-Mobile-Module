{*
<form method="post" action="{url link=''}">
	<div class="feed_share_box">
		{phrase var='core.what_s_on_your_mind'}
		<div class="feed_text_box">
			<input type="text" name="val[status]" value="" class="feed_input_bar" />
			<div class="feed_share_button">
				<input type="submit" value="{phrase var='core.share'}" class="button" />
			</div>
		</div>
	</div>
</form>
*}
{foreach from=$aFeeds name=feeds item=aFeed}
{template file='feed.block.entry'}
{/foreach}
{* pager *}