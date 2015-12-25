{if Phpfox::getUserId() != $aUser.user_id}
{literal}
<script type="text/javascript">
    $Behavior.oShowPhoto = function(){
        oFeedOthers.setTrue();
    };
</script>
{/literal}
{/if}