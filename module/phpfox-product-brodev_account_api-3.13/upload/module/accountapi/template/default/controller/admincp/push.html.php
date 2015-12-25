<?php
?>

<div class="table_header">
    {phrase var='accountapi.send_push_notification'}
</div>

<form action="{url link='admincp.accountapi.push'}" method="post" enctype="multipart/form-data">

    <div class="table">
        <div class="table_left">
            {phrase var='accountapi.link_to'}:
        </div>
        <div class="table_right">
            <input type="text" name="val[link]" id="link" size="60" value="http://"/>
        </div>
    </div>

    <div class="clear" />

    <div class="table">
        <div class="table_left">
            {required}{phrase var='accountapi.content'}:
        </div>
        <div class="table_right">
            <textarea name="val[message]" id="message" cols="50" rows="6"></textarea>
        </div>
    </div>

    <div class="table">
        <div class="table_left"></div>
        <div class="table_right">
            <input type="submit" value="{phrase var='core.submit'}" class="button" />{if $bProcess} {img theme='ajax/add.gif'} {phrase var='core.processing'}{/if}
        </div>
    </div>
</form>
