<div class="table_header">
    {phrase var='themesupporter.edit_introduction'}
</div>
<form action="{url link='admincp.themesupporter.introduction.edit'}" method="post" enctype="multipart/form-data">
    <div class="table">
        <div class="table_left">
            {phrase var='themesupporter.title'}
        </div>
        <div class="table_right">
            <input type="text" name="val[title]" value="{value type='input' id='title'}" id="title" size="30" />
            <input type="hidden" name="val[id]" value="{value type='input' id='id'}" id="id">
        </div>
    </div>
    <div class="table">
        <div class="table_left">
            {phrase var='themesupporter.button'}
        </div>
        <div class="table_right">
            <input type="text" name="val[button]" value="{value type='input' id='button'}" id="button" size="30" />
        </div>
    </div>
    <div class="table">
        <div class="table_left">
            {phrase var='themesupporter.link'}
        </div>
        <div class="table_right">
            <input type="text" name="val[link]" value="{value type='input' id='link'}" id="link" size="30" />
        </div>
    </div>
    <div class="table">
        <div class="table_left">
            {phrase var='themesupporter.image'}
        </div>
        <div class="table_right image_value">
            {if $aForms.image == ""}
            <input type="file" name="image">
            {else}
            {img  path='themesupporter.image_url' file=$aForms.image max_width='100' max_height='100' is_page_image=true  suffix='_100_square'}<br>
            {img theme='misc/delete_hover.gif' style='vertical-align:middle;'}<a href="#" onclick="$.ajaxCall('themesupporter.deleteImage','id={$aForms.id}'); return false;">{phrase var='themesupporter.delete_this_image'}</a>
            {/if}
        </div>
    </div>
    <div class="table">
        <div class="table_left">
            {phrase var='themesupporter.detail'}
        </div>
        <div class="table_right">
            {editor id='detail' rows='6'}
            {phrase var='themesupporter.html_supported'}
        </div>
    </div>
    <div class="table">
        <div class="table_left">
            {phrase var='themesupporter.show'}
        </div>
        <div class="table_right">
            <div class="item_is_active_holder">
                <span class="js_item_active item_is_active"><input type="radio" name="val[is_show]" value="1" {value type='radio' id='is_show' default='1'}/> {phrase var='admincp.yes'}</span>
                <span class="js_item_active item_is_not_active"><input type="radio" name="val[is_show]" value="0" {value type='radio' id='is_show' default='0' selected='true'}/> {phrase var='admincp.no'}</span>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="table_clear">
        <input type="submit" value="{phrase var='themesupporter.submit'}" class="button" name="add_category">
    </div>
</form>
