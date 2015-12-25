{foreach from=$aQuestions item=aQuestion}
<div class="signup_input">
    {if isset($aQuestion.image_path) && !empty($aQuestion.image_path)}
    <img src="{$aQuestion.image_path}">
    {/if}
    <input type="text" name="val[spam][{$aQuestion.question_id}]" value="" placeholder="{$aQuestion.question_phrase}">
</div>
{/foreach}