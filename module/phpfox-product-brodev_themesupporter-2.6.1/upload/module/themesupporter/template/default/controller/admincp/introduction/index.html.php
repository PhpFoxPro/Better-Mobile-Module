<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package 		Phpfox
 * @version 		$Id: index.html.php 2197 2010-11-22 15:26:08Z Raymond_Benc $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div id="js_menu_drop_down" style="display:none;">
    <div class="link_menu dropContent" style="display:block;">
        <ul>
            <li><a href="#" onclick="return $Themesupporter.action(this, 'edit');">{phrase var='themesupporter.edit'}</a></li>
            <li><a href="#" onclick="return $Themesupporter.action(this, 'delete');">{phrase var='themesupporter.delete'}</a></li>
        </ul>
    </div>
</div>
<div class="table_header">
    {phrase var='themesupporter.add_introduction'}
</div>
<form action="{url link='admincp.themesupporter.introduction'}" method="post" enctype="multipart/form-data">
    <div class="table">
        <div class="table_left">
            {phrase var='themesupporter.title'}
        </div>
        <div class="table_right">
            <input type="text" name="val[title]" value="{value type='input' id='title'}" id="title" size="30" />
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
        <div class="table_right">
            <input type="file" name="image">
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
                <span class="js_item_active item_is_active"><input type="radio" name="val[is_show]" value="1" {value type='radio' id='is_show' default='1' selected='true'}/> {phrase var='admincp.yes'}</span>
                <span class="js_item_active item_is_not_active"><input type="radio" name="val[is_show]" value="0" {value type='radio' id='is_show' default='0'}/> {phrase var='admincp.no'}</span>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="table_clear">
        <input type="submit" value="{phrase var='themesupporter.submit'}" class="button" name="add_category">
    </div>
</form>

<br>
<div class="table_header">
    {phrase var='themesupporter.introduction'}
</div>
<form method="post" action="{url link='admincp.themesupporter.introduction'}">
    <div class="table">
        <div class="sortable">
            {$sIntroduction}
        </div>
    </div>
    <div class="table_clear">
        <input type="submit" value="{phrase var='themesupporter.update_order'}" class="button" />
    </div>
</form>