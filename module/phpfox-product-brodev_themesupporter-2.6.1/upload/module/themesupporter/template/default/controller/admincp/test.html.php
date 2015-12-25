{foreach from=$aBlocks item=sBlock}
    <h2>{$sBlock}</h2>
    <div style="padding: 20px 0;">
    {module name='themesupporter.'$sBlock}
    </div>
{/foreach}

<h2>Feed</h2>
{module name='feed.display'}
