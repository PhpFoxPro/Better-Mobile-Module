{literal}
<style type="text/css">
    #main_content_holder{
        background: none;
    }
</style>
{/literal}
{$sCreateJs}

<div id="welcome_logo">

</div>
<div class="login_box">
    <div class="main_break">
        <form method="post" action="{url link="user.login"}" id="js_login_form" onsubmit="{$sGetJsForm}">
        <div class="table">
            <div class="table_left">
                <label for="login">{if Phpfox::getParam('user.login_type') == 'user_name'}{phrase var='user.user_name'}{elseif Phpfox::getParam('user.login_type') == 'email'}{phrase var='user.email'}{else}{phrase var='user.login'}{/if}:</label>
            </div>
            <div class="table_right">
                <input type="text" name="val[login]" id="login" value="{$sDefaultEmailInfo}" size="40" placeholder="{phrase var='user.email'}"/>
            </div>
            <div class="clear"></div>
        </div>

        <div class="table">
            <div class="table_left">
                <label for="password">{phrase var='user.password'}:</label>
            </div>
            <div class="table_right">
                <input type="password" name="val[password]" id="password" value="" size="40" placeholder="{phrase var='user.password'}"/>
            </div>
            <div class="clear"></div>
        </div>

        <div class="table_clear">
            <input type="submit" value="{phrase var='user.login_button'}" class="button" />
            <a href="{url link='user.register'}" class="btnsignup">{phrase var='user.sign_up'}</a>
            {plugin call='user.template.login_header_set_var'}

        </div>

        <div class="table_clear">
            <label><input type="checkbox" class="checkbox" name="val[remember_me]" value="" /> {phrase var='user.remember'}</label>
            <a href="{url link='user.password.request'}">{phrase var='user.forgot_your_password'}</a>
        </div>

        {plugin call='user.template_controller_login_end'}



        </form>
    </div>
</div>