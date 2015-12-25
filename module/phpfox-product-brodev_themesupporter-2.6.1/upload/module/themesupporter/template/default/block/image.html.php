<div class="new_upload">
	<div class="new_upload_header">
        <h1>{phrase var='photo.recently_uploaded'}</h1>
        <a class="view_more_link_photo" href="{url link='photo'}">{phrase var='core.view_more'}</a>
    </div>
    <div class="photo">
      {foreach from=$aRecords item=aPhoto}
      {img server_id=$aPhoto.server_id path='photo.url_photo' file=$aPhoto.destination suffix='' max_width=150 max_height=150 class="hover_action" title=$aPhoto.title}
      {/foreach}
     </div>
</div>