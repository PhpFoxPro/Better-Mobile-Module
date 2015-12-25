<div class="table_header">
    {phrase var='accountapi.certificate_manager'}
</div>

<form action="{url link='admincp.accountapi.certificate'}" method="post" enctype="multipart/form-data"">
    <input type="hidden" name="type" value="file" />
    <div class="table">
        <div class="table_left">
            {phrase var='accountapi.certificate_file'}:
        </div>
        <div class="table_right">
            <input type="file" name="file">
            <div class="extra_info">{phrase var='accountapi.you_can_only_update_a_pem_file'}</div>
            {if $sCertificate}
                {$sCertificate}
            {else}
                {phrase var='accountapi.no_pem_file'}
            {/if}
        </div>
        <div class="clear"></div>
    </div>
    <div class="table_clear">
        <input type="submit" value="{phrase var='core.submit'}" class="button" name="submit">
    </div>
    <div class="clear"></div>

</form>