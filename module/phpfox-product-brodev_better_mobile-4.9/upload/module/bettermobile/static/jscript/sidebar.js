bCheckPhoto = false;
oFeedOthers = {
    bShow:false,
    bUser:false,
    bIsPhoto: false,
    init:function () {
        bShow = false;
        bUser = false;
        bIsPhoto = false;
    },
    show:function () {
        bShow = (!bShow) ? true : false;
        bCheckPhoto = (!bCheckPhoto) ? true : false;
        if (bShow) {
            $('.abc').css({display:'block'});
        } else {
            $('.abc').css({display:'none'});
        }
    },
    /*show photo share*/
    show1:function () {
        bCheckPhoto = true;
        $('#js_feed_content').css({display:'none'});
        $('.feed_sort_order').css({display:'none'});
        $('ul.activity_feed_form_attach li').css({display:'none'});
        $('.button_cancel').css({display:'block'});
        $('.activity_feed_form_holder').css('padding-top', 11);
        $('.activity_feed_form_holder').css('padding-bottom', 11);
        $('.activity_feed_form_holder').css('padding-left', 8);
        $('.activity_feed_form_holder').css('padding-right', 8);
        $('.activity_feed_form').css({display:'block'});
        $('#mobile_header_home').css({display:'none'});
        $('#mobile_profile_header').css({display:'none'});
        $('.cover_photo_link').css({display:'none'});
        $('.item_bar').css({display:'none'});
        $('#breadcrumb_holder').css({display:'none'});
        $('#mobile_h1_main').css({display:'none'});
        $('.info_holder').css({display:'none'});
        $('#mobile_header').css('z-index', 1);
        $('.mobile_search_button').css({display:'none'});
        $('.mobile_profile_header_menu').css({display:'none'});
        $('.mobile_main_sub_menu').hide();
        $('#holder').css('padding-top', 0);
        $('#mobile_header').css('position', 'static');
        $('.js_ad_space_parent').css({display:'none'});
        // focus
        $("[name='val[user_status]']").focus();
        $('.other_share').hide();

    },
    setTrue:function () {
        bUser = true;
    }
}
$Behavior.initFeedOthers = function () {
    oFeedOthers.init();
};

oSearch = {
    clear:function () {
        $('#header_sub_menu_search_input').val("");
        $('#header_clear_button').css({display:'none'});
    },
    click:function () {
        $.ajaxCall('bettermobile.showSideBar', 'sShow=false&sFocus=true');
        $('#header_sub_menu_search_input').focus();
    },
    clickClear:function () {
        $('#header_clear_button').css({display:'block'});
    },
    hideClear:function () {
        var v = $('#header_sub_menu_search_input').val();
        if (v == "") {
            $('#header_clear_button').css({display:'none'});
        }
    }
}
var oOtherShare = {
    sShow:false,
    show:function () {
        this.sShow = !this.sShow;
        if (this.sShow) {
            $('.other_share').show();
        } else {
            $('.other_share').hide();
        }
    }
}
var oShowSidebar = {
    sShow:false,
    checkShow: function() {
        return this.sShow;
    },
    show:function (sFocus) {
        this.sShow = !this.sShow;

        if (this.sShow) {

            if ($Core.exists('.full_site')) {
                $('#mobile_holder').css({minHeight:$(window).height() + 70});
            }
            if (bRtl) {
                $('#mobile_holder').css('right', 270);
            } else {
                $('#mobile_holder').css('left', 270);
            }
            $('#mobile_holder').css({position:'fixed'});
            $('#showsidebar').css({display:'block'});

            // mobile header
            $('#mobile_header').css({position:'relative'});
            $('#holder').css('padding-top', 0);
            $('.mobile_main_sub_menu').css('z-index', 1);
            $('.mobile_search_button').css('z-index', 1);

            if (sFocus) {
                $('#header_sub_menu_search_input').focus();
            }
        } else {
            if ($Core.exists('.full_site')) {
                if (iOs) {
                    $('#mobile_holder').css({minHeight:$(window).height() + 70});
                } else {
                    $('#mobile_holder').css({minHeight:$(window).height()});
                }

            }
            if (bRtl) {
                $('#mobile_holder').css('right', 0);
            } else {
                $('#mobile_holder').css('left', 0);
            }

            $('#mobile_holder').css({position:'relative'});
            $('#showsidebar').css({display:'none'});
            $('.mobile_main_sub_menu').css('z-index', 2);
            $('.mobile_search_button').css('z-index', 2);
            // mobile header
            $('#mobile_header').css({position:'fixed'});
            $('#holder').css('padding-top', 45);
        }
    }

}
var oShowSidebarMessenger = {
    bMessenger:false,
    sShow:false,
    checkShow: function() {
        return this.sShow;
    },
    setMessenger: function(bIsMessenger) {
        this.bMessenger = bIsMessenger;
    },
    show:function (sFocus) {
        if (this.bMessenger){
            this.sShow = !this.sShow;

            if (this.sShow) {

                if ($Core.exists('.full_site')) {
                    $('#mobile_holder').css({minHeight:$(window).height() + 70});
                }
                if (bRtl) {
                    $('#mobile_holder').css('left', 270);
                    $('#mobile_holder').css('right', 'auto');
                } else {
                    $('#mobile_holder').css('right', 270);
                    $('#mobile_holder').css('left', 'auto');
                }
                $('#mobile_holder').css({position:'fixed'});
                $('#better_messenger').css({display:'block'});

                $('#mobile_header').css({position:'relative'});
                $('#holder').css('padding-top', 0);
                $('.mobile_main_sub_menu').css('z-index', 1);
                $.ajaxCall('bettermobile.getFriendChat', 'auto=1&size=' + $Core.betterMobileMessengerHandle.total);
                clearInterval($Core.betterMobileMessengerHandle.timer);
                $Core.betterMobileMessengerHandle.timer = setInterval(function() {
                    $.ajaxCall('bettermobile.getFriendChat', 'auto=1&size=' + $Core.betterMobileMessengerHandle.total);
                }, $Core.betterMobileMessengerHandle.interval);

            } else {
                if ($Core.exists('.full_site')) {
                    if (iOs) {
                        $('#mobile_holder').css({minHeight:$(window).height() + 70});
                    } else {
                        $('#mobile_holder').css({minHeight:$(window).height()});
                    }

                }
                clearInterval($Core.betterMobileMessengerHandle.timer);
                $('#mobile_holder').css('right', 0);
                $('#mobile_holder').css('left', 0);
                $('#mobile_holder').css({position:'relative'});
                $('#better_messenger').css({display:'none'});

                $('.mobile_main_sub_menu').css('z-index', 2);
                // mobile header
                $('#mobile_header').css({position:'fixed'});
                $('#holder').css('padding-top', 45);
            }
        }

    }

}
$Behavior.initShowSidebar = function () {
    $('.privacy_setting_holder ul li a').hover(function () {
        //$('.privacy_setting_holder').css({display:'block'});
    });
    if ($Core.exists('.full_site')) {
        if (iOs) {
            $('#mobile_holder').css({minHeight:$(window).height() + 70});
        } else {
            $('#mobile_holder').css({minHeight:$(window).height()});
        }

    }
    if ($Core.exists('#mobile_profile_photo_name #message_friend')) {
        $('#mobile_profile_photo').css({minHeight: 90});
    }
    $('.mobile_sidebar').click(function () {
        oShowSidebar.show();
    });
    $('.sidebar_header_username a.user_name_link').click(function () {
        oShowSidebar.show();
    });
    $('.clone').html($('.source').html());


};
var oBetterMobile = {
    showButton: function() {
        $('.clone').html($('.source').html());
    },
    removeFriend: function(userId) {
        if (confirm(oTranslations['core.are_you_sure'])){
            $.ajaxCall('bettermobile.removeFriend', 'id=' + userId);
            $('#js_remove_friend_on_profile').hide();
            $('#js_add_friend_on_profile').show();
        }
        return false;
    }
};
$Behavior.initShowBottomMenu = function () {
    if ($Core.exists('.profile_bottom')) {
        $('#holder').css({paddingBottom:'30px'});
        $('#cometchat_base').css({bottom:'30px'});
        $('.cometchat_ccmobiletab_redirect').css({bottom:'32px'});

    }
}

$Behavior.initBasicInfo = function () {
    $('#js_basic_info_data .info').last().addClass('last');
}

$Behavior.initSignUp = function () {
    $('#js_signup_block .signup_input').last().addClass('last');
}

$Behavior.oShowPhoto = function () {
    $('#photo_button1').click(function () {
        $.ajaxCall('bettermobile.showPhoto', '');
    });
    $('.photo_next_previous').css('margin-bottom', 10);
};

oLikeCount = {
    add:function (FeedId) {
        var likeCountHolder = $('#js_like_body_' + FeedId + ' .like_icon');
        var count = parseInt(likeCountHolder.text());

        likeCountHolder.html(count + 1);
    },
    remove:function (FeedId) {
        var likeCountHolder = $('#js_like_body_' + FeedId + ' .like_icon');
        var count = parseInt(likeCountHolder.text());
        var newCount = count;
        if (newCount > 0) {
            newCount--;
        }

        likeCountHolder.html(newCount);
    },
    addDislike:function(FeedId){
        var dislikeCountHolder = $('#js_like_body_' + FeedId + ' .dislike_icon');
        var count = parseInt(dislikeCountHolder.text());

        dislikeCountHolder.html(count + 1);
    },
    removeDislike:function (FeedId) {
        var dislikeCountHolder = $('#js_like_body_' + FeedId + ' .dislike_icon');
        var count = parseInt(dislikeCountHolder.text());
        var newCount = count;
        if (newCount > 0) {
            newCount--;
        }

        dislikeCountHolder.html(newCount);
    }
}

/**
 * Handle show share status feature
 * @type {Object}
 */
var oStatus = {
    show:function () {
        if (oShowSidebar.checkShow()) {
            return;
        }
        $('#js_feed_content').css({display:'none'});
        $('.feed_sort_order').css({display:'none'});
        $('ul.activity_feed_form_attach li').css({display:'none'});
        $('.button_cancel').css({display:'block'});
        $('.activity_feed_form_holder').css('padding-top', 11);
        $('.activity_feed_form_holder').css('padding-bottom', 11);
        $('.activity_feed_form_holder').css('padding-left', 8);
        $('.activity_feed_form_holder').css('padding-right', 8);
        $('.activity_feed_form').css({display:'block'});
        $('#mobile_header_home').css({display:'none'});
        $('#mobile_profile_header').css({display:'none'});
        $('.cover_photo_link').css({display:'none'});
        $('.item_bar').css({display:'none'});
        $('#breadcrumb_holder').css({display:'none'});
        $('#mobile_h1_main').css({display:'none'});
        $('.info_holder').css({display:'none'});
        $('.mobile_profile_header_menu').css({display:'none'});
        $('#mobile_header').css('z-index', 1);
        $('.mobile_search_button').css({display:'none'});
        $('.mobile_main_sub_menu').css({display:'none'});
        $('.js_ad_space_parent').css({display:'none'});
        $('#holder').css('padding-top', 0);
        $('#mobile_header').css('position', 'static');

        // focus
        $("[name='val[user_status]']").focus();
    },

    hide:function () {
        $('#js_feed_content').css({display:'block'});
        $('.feed_sort_order').css({display:'block'});
        $('ul.activity_feed_form_attach li').css({display:'block'});
        $('.button_cancel').css({display:'none'});
        $('.activity_feed_form').css({display:'none'});
        $('.drop').css({display:'none'});
        $('#mobile_header_home').css({display:'block'});
        $('#mobile_profile_header').css({display:'block'});
        $('.cover_photo_link').css({display:'block'});
        $('.item_bar').css({display:'block'});
        $('#mobile_h1_main').css({display:'none'});
        $('#breadcrumb_holder').css({display:'block'});
        $('.mobile_main_sub_menu').css({display:'block'});
        $('.info_holder').css({display:'block'});
        $('.mobile_profile_header_menu').css({display:'block'});
        $('#mobile_header').css('z-index', 2);
        $('.mobile_search_button').css({display:'block'});
        $('#holder').css('padding-top', 45);
        $('.js_ad_space_parent').css({display:'block'});
        $('#mobile_header').css('position', 'fixed');
        $('.status_button_show ').removeClass('active');
        $('.share_photo_button').removeClass('active')
        bCheckPhoto = false;
    },
    check:function()
    {
        if(!bCheckPhoto){
            oStatus.hide();
        }
    }

}


/* Fix iOS view more */

$Core.isInView = function (elem) {
    if (!$Core.exists(elem)) {
        return false;
    }

    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + window.innerHeight;
    if ($(elem).offset() == null) {
        return false;
    }
    var elemTop = $(elem).offset().top;
    var elemBottom = elemTop + $(elem).height();

    return ((docViewTop <= elemTop) && (docViewBottom >= elemBottom));
}
