{foreach from=$aRecords item=aItem}
    <div class="block">
        <div class="image">
            {img user=$aItem suffix='_50_square' max_width=50 max_height=50}

        </div>
        <div class="title">
            <a href="{permalink module='blog' id=$aItem.blog_id title=$aItem.title}" id="js_blog_edit_inner_title{$aItem.blog_id}" class="link ajax_link">{$aItem.title|clean|shorten:55:'...'|split:20}</a>
        </div>
        <div>{phrase var='blog.by_full_name' full_name=$aItem|user:'':'':50}</div>
        <div class="content">{$aItem.text_parsed|strip_tags|highlight:'search'|split:55|shorten:$iShorten'...'}</div>
        <div>{module name='feed.comment' aFeed=$aItem.aFeed}</div>
    </div>
{foreachelse}
{/foreach}