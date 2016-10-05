/**
 * Created by cjg on 05/10/2016.
 */

jQuery(document).ready(function () {
    var hovernote;
    hovernote = jQuery("<div id='hovernote' style='display:none'></div>").appendTo('body');

    var ua = navigator.userAgent;
    var mobile = ua.match(/(iPhone|iPod|iPad|BlackBerry|Android)/);
    if (mobile) {
        jQuery('.programme_event').css('border', 'solid 1px green');
        return;
    }
    jQuery('.mm_hover').map(function (i, x) {
        var cell = jQuery(x);

        var shownote_fn = function (event) {
            hovernote.html(this.html());
            var tPosX = event.pageX - 190;
            var tPosY = event.pageY + 5;
            hovernote.css({
                'position': 'absolute',
                'width': '300px',
                'white-space': 'normal',
                'min-height': '20px',
                'top': tPosY + 'px',
                'left': tPosX + 'px',
                'display': 'block'
            });

            var BOTTOM_MARGIN = 15;
            var RIGHT_MARGIN = 15;
            // check to see if box would be off right hand side and if so
            // shunt it back a bit
            if (tPosX + hovernote.width() > jQuery(window).innerWidth() + jQuery(window).scrollLeft() - RIGHT_MARGIN) {
                tPosX = jQuery(window).innerWidth() + jQuery(window).scrollLeft() - hovernote.width() - RIGHT_MARGIN;
                hovernote.css('left', tPosX + 'px');
            }
            // and the left
            if (tPosX < jQuery(window).scrollLeft() + RIGHT_MARGIN) {
                tPosX = jQuery(window).scrollLeft() + RIGHT_MARGIN;
                hovernote.css('left', tPosX + 'px');
            }
            // check to see if box would be off the bottom of the window and if so
            // shunt it up a bit
            if (tPosY + hovernote.height() > jQuery(window).innerHeight() + jQuery(window).scrollTop() - BOTTOM_MARGIN) {
                tPosY = jQuery(window).innerHeight() + jQuery(window).scrollTop() - hovernote.height() - BOTTOM_MARGIN;

                hovernote.css('top', tPosY + 'px');
            }
        };

        cell.find(".mm_hover_target")
            .mousemove(shownote_fn.bind(cell.find(".mm_hover_message")))
            .mouseleave(function () {
                jQuery('#hovernote').hide();
            })
        ;

    });
});