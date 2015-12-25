{literal}
<style type="text/css">
    input{
        width: 235px;
    }
    .bottom{
        display: none;
    }
    .menu{
        display: none;
    }
    #mobile_content {
        overflow:hidden;
    }
    #js_error{
        padding-top: 10px;
        margin-left: 20px;
        margin-bottom: -10px;
        color: #ff0000;
        font-size: 13px;
    }
</style>
{/literal}

<div class="login_box">
    {plugin call='user.template_controller_login_block__start'}
    <form method="post" action="{url link="user.login"}">
    <div id="js_error">

    </div>
    <div class="p_top_4" style="padding-top: 19px;">
        <div id="js_email">
            <input type="{if Phpfox::getParam('user.login_type') == 'email'}email{else}text{/if}" name="val[login]" id="email_login" value="" size="30" placeholder="{if Phpfox::getParam('user.login_type') == 'user_name'}{phrase var='user.user_name'}{elseif Phpfox::getParam('user.login_type') == 'email'}{phrase var='user.email'}{else}{phrase var='user.login'}{/if}"  />
        </div>
    </div>

    <div class="p_top_4" style="padding-top: 0px; ">

        <div id="js_password">
            <input type="password" name="val[password]" id="password_login" value="" size="30" placeholder="{phrase var='user.password'}" />

        </div>
    </div>

    <div class="p_top_8">
        <input type="submit" value="{phrase var='user.login_button'}" class="button_login"  />
    </div>
    <a href="{url link='user.register'}"><div class="button_sign_up" >{phrase var='user.sign'}</div></a>

    <div class="clear"></div>

    <div class="header_menu_login_sub">
        <label><input type="checkbox" name="val[remember_me]" value="" checked="checked" tabindex="4" /> <span>{phrase var='bettermobile.remember'}</span></label>
        <a href="{url link='user.password.request'}" class="forgot">{phrase var='bettermobile.forgot_pass'}</a>
    </div>
    <div class="header_menu_login_sub">

    </div>

    </form>
</div>





{literal}
<script type="text/javascript">
    $Behavior.initLogin = function(){
        $('#public_message').hide();
        $('#js_error').text($('#public_message').text());
    }
</script>

{/literal}