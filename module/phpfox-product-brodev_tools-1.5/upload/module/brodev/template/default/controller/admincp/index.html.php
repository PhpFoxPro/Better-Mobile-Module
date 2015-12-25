<h2>Update Product files</h2>
<form action="{url link='admincp.brodev'}" method="post">
<input type="hidden" name="val[type]" value="product" />
<div class="table">
    <div class="table_left">
        Product
    </div>
    <div class="table_right">
        <select id="product_id" name="val[product_id]">
            {foreach from=$aProductsList key=sProductId item=sProductTitle}
            <option value="{$sProductId}">{$sProductTitle}</option>
            {/foreach}
        </select>
    </div>
</div>
<div class="table_clear">
    <input type="submit" name="submit" value="Submit">
</div>
</form>

<h2>Update Theme files</h2>
<form action="{url link='admincp.brodev'}" method="post">
<input type="hidden" name="val[type]" value="theme" />
<div class="table">
    <div class="table_left">
        Theme
    </div>
    <div class="table_right">
        <select id="theme_id" name="val[theme_id]">
            {foreach from=$aThemesList key=iThemeId item=sThemeName}
            <option value="{$iThemeId}">{$sThemeName}</option>
            {/foreach}
        </select>
    </div>
</div>
<div class="table_clear">
    <input type="submit" name="submit" value="Submit">
</div>
</form>