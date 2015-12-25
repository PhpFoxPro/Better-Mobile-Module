{if Phpfox::isMobile()}
<div class="header_bar_menu">
    <div class="header_bar_search">
        <form method="post" action="{url link='user.browse' view=$sView}">
            <div><input type="hidden" name="search[submit]" value="1" /></div>
            <div class="header_bar_search_holder">
                {filter key='keyword'}
                <div class="header_bar_search_input"></div>
            </div>
        </form>
    </div>
</div>
{/if}