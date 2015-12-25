<div class="table_header">
    {phrase var='bettermobile.manager_background_images'}
</div>
<table cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 150px">{phrase var='bettermobile.title'}</th>
        <th style="width: 120px">{phrase var='bettermobile.image'}</th>
        <th class="t_center" style="width:60px;">{phrase var='bettermobile.active'}</th>
        <th class="t_center" style="width:60px;">{phrase var='bettermobile.action'}</th>
    </tr>
    {foreach from=$aImages item=aImage}
    <tr>
        <td>{$aImage.title}</td>
        <td>{img path='bettermobile.image_url' file=$aImage.image suffix='_100_square' server_id=$aImage.server_id}</td>
        <td>{if $aImage.active}  {phrase var='admincp.yes'} {else} {phrase var='admincp.no'} {/if}</td>
        <td>
            <a href="{url link='admincp.bettermobile.background.edit' id=$aImage.id}">{phrase var='bettermobile.edit'}</a>
            <a href="{url link='admincp.bettermobile.background' delete=$aImage.id}" class="sJsConfirm">{phrase var='bettermobile.delete'}</a>
        </td>
    </tr>
    {foreachelse}

    {/foreach}
</table>
