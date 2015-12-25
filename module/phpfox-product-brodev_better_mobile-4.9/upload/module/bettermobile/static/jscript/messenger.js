/**
 * Created with JetBrains PhpStorm.
 * User: dat
 * Date: 3/9/14
 * Time: 1:28 PM
 * To change this template use File | Settings | File Templates.
 */
var iTimeout;
$Behavior.loadBetterMobileMessenger = function() {
    var $messengerHolder = $('#better_messenger'),
        $listContainer = $('#mobile_chat_friend');
    $('#better_messenger .chat_input').keyup(function() {
        clearTimeout(iTimeout);
        iTimeout = setTimeout(function() {
            var $searchHolder = $('#mobile_search_friend')
                ;
            if ( $('#better_messenger .chat_input').val() != '') {
                $searchHolder.show();
                $listContainer.hide();
            } else {
                $searchHolder.hide();
                $listContainer.show();
            }
            $.ajaxCall('bettermobile.searchFriendChat', 'query=' + $('#better_messenger .chat_input').val() + '');
        }, 800);

    });
    $Core.betterMobileMessengerHandle.init();
    $('#better_messenger .chat_input').keydown(function() {

        clearTimeout(iTimeout);
    });
    $Core.messengerlib.bottomScroll($messengerHolder, function() {
        clearInterval($Core.betterMobileMessengerHandle.timer);
        var page = parseInt($listContainer.attr('data-page'));
        if($listContainer.attr('data-page') != "0" && !$listContainer.hasClass('wait-ajax')) {
            $listContainer.addClass('wait-ajax');
            $.ajaxCall('bettermobile.getFriendChat', 'size='+ $Core.betterMobileMessengerHandle.total +'&p=' + page).done(function() {
                $listContainer.removeClass('wait-ajax');
            });
        }
    });
}


$Core.betterMobileMessengerHandle = {
    timer:null,
    first:true,
    link:null,
    interval:1000,
    total:8,
    setIntervalTime: function(iTime) {
        this.interval = iTime;

    },
    getFriends: function(json) {
        var $listContainer = $('#mobile_chat_friend');
        var template = $Core.handlebar.template('friends', json);
        $listContainer.attr('data-page', json['page_next']);

        if (json['auto']) {
            $listContainer.html(template);
        } else {
            $listContainer.append(template);
        }

        this.friendHolderBinding();
    },
    run: function() {
        this.first = false;
    },
    init: function() {
        iWinHeight = $(window).height();
        this.total = (iWinHeight/60).toFixed(0);
    },
    setLink: function(sLink) {
        this.link = sLink;
    },

    friendHolderBinding: function()
    {
        var $friendHolder = $('.friend_holder');

        $friendHolder.unbind('click').bind( 'click', function(event) {
            event.stopPropagation();

            var uid = $(this).data('user'),
                $friendListHolder = $(this);

            $Core.messengerlib.addColumn(uid, {focus: true, no_save: true}, function() {});
        });
    },

    show: function(data) {
        // Show side bar
    },
    searchFriend: function(json) {
        var $searchHolder = $('#mobile_search_friend');

        if(json['count']) {
            template = $Core.handlebar.template('friends', json);
            $searchHolder.html(template);
        } else {
            $searchHolder.html(json['empty_message']);
        }

        this.friendHolderBinding();
    }
}
