{foreach from=$aSections item=aSection key=sKey}
    <h2>{$sKey}</h2>
        <ul style="margin-left: 20px;">
            {foreach from=$aSection item=sLink}
                <li style="padding: 7px 0;">
                    {if $sLink}
                        <a target="_blank" href="{$sLink}">{$sLink}</a></li>
                    {else}
                        <span style="color: red;">Missing!</span>
                    {/if}
                </li>
            {/foreach}
        </ul>
{/foreach}