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
<div class="table_header">
    {phrase var='bettermobile.edit_image'}
</div>
<form action="{url link='admincp.bettermobile.background.edit'}" method="post" enctype="multipart/form-data">
    <div class="table">
        <div class="table_left">
            {phrase var='bettermobile.title'}
        </div>
        <div class="table_right">
            <input type="text" name="val[title]" value="{value type='input' id='title'}" id="title" size="30" />
            <input type="hidden" name="val[id]" value="{value type='input' id='id'}" id="id" size="30" />
        </div>
    </div>
    <div class="table">
        <div class="table_left">
            {phrase var='bettermobile.image'}
        </div>
        <div class="table_right">
            <input type="file" name="image">
        </div>
    </div>
    <div class="table">
        <div class="table_left">
            {phrase var='bettermobile.active'}
        </div>
        <div class="table_right">
            <div class="item_is_active_holder">
                <span class="js_item_active item_is_active"><input type="radio" name="val[active]" value="1" {value type='radio' id='active' default='1' selected='true'}/> {phrase var='admincp.yes'}</span>
                <span class="js_item_active item_is_not_active"><input type="radio" name="val[active]" value="0" {value type='radio' id='active' default='0'}/> {phrase var='admincp.no'}</span>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="table">
        {img path='bettermobile.image_url' file=$aForms.image max_width=600 max_height=600 title=$aForms.title server_id=$aForms.server_id}
    </div>
    <div class="table_clear">
        <input type="submit" value="{phrase var='bettermobile.submit'}" class="button" name="add_category">
    </div>
</form>
