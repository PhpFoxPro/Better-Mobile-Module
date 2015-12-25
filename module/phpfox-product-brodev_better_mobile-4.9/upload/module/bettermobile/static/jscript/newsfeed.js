/**
 * Created with JetBrains PhpStorm.
 * User: kiemdv
 * Date: 11/29/13
 * Time: 4:38 PM
 * To change this template use File | Settings | File Templates.
 */

$Core.newfeed = {
    bShow:false,
    show: function(e)
    {
        var aMenuDrop = $('#'+e).parent().find('.feed_delete_drop');
        bShow = (!bShow) ? true : false;
        if (bShow) {
            $(aMenuDrop[0]).css({display:'block'});
        } else {
            $(aMenuDrop[0]).css({display:'none'});
        }
    }
}
$Behavior.initNewFeed = function(){
    $('.feed_delete_drop').hide();
}