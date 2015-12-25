<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package 		Phpfox
 * @version 		$Id: form.html.php 3826 2011-12-16 12:30:19Z Raymond_Benc $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
		<div class="custom_block_form">
            {if isset($aSetting)}
			{if $aSetting.var_type == 'textarea'}
                <input type="text" name="custom[{$aSetting.field_id}]" value="{if isset($aSetting.value)}{$aSetting.value|clean}{/if}" placeholder="{phrase var=$aSetting.phrase_var_name}"/>
			{elseif $aSetting.var_type == 'text'}
                <input type="text" name="custom[{$aSetting.field_id}]" value="{if isset($aSetting.value)}{$aSetting.value|clean}{/if}" placeholder="{phrase var=$aSetting.phrase_var_name}"/>

			{elseif $aSetting.var_type == 'select'}
				<select name="custom[{$aSetting.field_id}]" id="custom_field_{$aSetting.field_id}">
                    <option value="">{phrase var=$aSetting.phrase_var_name}</option>
					
					{foreach from=$aSetting.options key=iKey item=sOption}
						<option value="{$iKey}"{if isset($sOption.selected) && ($sOption.selected == true || $sOption.selected == 1)} selected="selected"{/if}>{$sOption.value}</option>
					{/foreach}
				</select>
			{elseif $aSetting.var_type == 'multiselect'}
				<select name="custom[{$aSetting.field_id}][]" multiple="multiple" id="custom_field_{$aSetting.field_id}">
                    <option value="">{phrase var=$aSetting.phrase_var_name}</option>
					{foreach from=$aSetting.options key=iKey item=aOption}
						<option value="{$iKey}"{if isset($aOption.value) && isset($aOption.selected) && $aOption.selected == true} selected="selected"{/if}>{$aOption.value}</option>
					{/foreach}
				</select>
			{elseif $aSetting.var_type == 'radio'}
				{if !$aSetting.is_required}
					<div class="custom_block_form_radio">
						<input id="radio_no_answer" type="radio" name="custom[{$aSetting.field_id}]" value="0" checked="checked" />
						<label for="radio_no_answer"> {phrase var='custom.no_answer'} </label>
					</div> 
				{/if}
				{foreach from=$aSetting.options key=iKey item=aOption}
					<div class="custom_block_form_radio">
						<input id="radio_{$aSetting.field_id}_{$iKey}" type="radio" name="custom[{$aSetting.field_id}]" value="{$iKey}" {if isset($aOption.selected) && $aOption.selected == true}checked="checked"{/if}>
						<label for="radio_{$aSetting.field_id}_{$iKey}"> {$aOption.value} </label>
					</div> 
				{/foreach}
			{elseif $aSetting.var_type == 'checkbox'}
            <div class="custom_block_form_checkbox">
                <label >{phrase var=$aSetting.phrase_var_name}</label>
                </div>
				{foreach from=$aSetting.options key=iKey item=aOption name=customCheck}
					<div class="custom_block_form_checkbox">
						<input id="checkbox_{$aSetting.field_id}_{$iKey}" type="checkbox" name="custom[{$aSetting.field_id}][]" value="{$iKey}" {if isset($aOption.selected) && $aOption.selected == true}checked="checked"{/if}>
						<label for="checkbox_{$aSetting.field_id}_{$iKey}">{$aOption.value} </label>
					</div>
				{/foreach}
                <div class="clear"></div>
			{/if}
            {/if}
		</div>