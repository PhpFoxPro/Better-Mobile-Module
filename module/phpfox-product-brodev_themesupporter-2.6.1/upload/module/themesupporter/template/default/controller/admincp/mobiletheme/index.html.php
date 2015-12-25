<div class="table_header">
  {phrase var='themesupporter.mobile_theme'}
</div>
<div class="table">
  <div class="table_left">
    {phrase var='themesupporter.mobile_theme'}
  </div>
<form action="" method='post'>
  <div class="table_right">
    <select name='val[mobile_style]'>
      <option value=''>{phrase var='themesupporter.mobile_theme'}</option>
      {foreach from=$aThemes item='aTheme'}
        <optgroup label='{$aTheme.name}'>
          {foreach from=$aStyles item='aStyle'}
          {if $aStyle.theme_id == $aTheme.theme_id}
            <option value="{$aStyle.style_id}" {if $aStyle.style_id == $aDefaultStyle.style_id} selected="selected" {/if}>{$aStyle.name}</option>
          {/if}
          {/foreach}
        </optgroup>
      {/foreach}
    </select>
  </div>
  <div class="table_clear">
    <input type="submit" value="{phrase var='themesupporter.submit'}" class="button" />
  </div>
</form>
</div>