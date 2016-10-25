/**
 * Created by cjg on 24/10/2016.
 */

$(document).ready(function () {

    /* submenus */
    $('[data-submenu]').submenupicker();

    /* tooltips */
    $('[data-toggle="tooltip"]').tooltip();

    /* block hover highlight of all matching blocks */
    $(".mm-record-entity").hover(function () {
        var rid = $(this).attr("data-rid");
        $(".mm-record-" + rid).addClass("mm-highlight");
    }, function () {
        var rid = $(this).attr("data-rid");
        $(".mm-record-" + rid).removeClass("mm-highlight");
    });

    /* clever link fields in record edit */
    $("[data-mm-dynamic='inline-link-edit']").each(function () {
        var block = $(this);
        block.find("li.mm-link-edit-list-existing").each(function () {
            var li = $(this);
            var removeButton = li.find('.mm-button-remove');
            var undoButton = li.find('.mm-button-undo');
            var actionInput = li.find('.mm-form-action');
            var recordStub = li.find('.mm-record-stub');

            removeButton.click(function () {
                removeButton.hide();
                undoButton.show();
                recordStub.addClass('mm-chopping-block');
                actionInput.val(1);
            });
            undoButton.hide().click(function () {
                removeButton.show();
                undoButton.hide();
                recordStub.removeClass('mm-chopping-block');
                actionInput.val(0);
            });
            // ensure this is what we expect
            actionInput.val(0);
        });
        block.find("li.mm-link-edit-list-add").each(function () {
            var li = $(this);
            var addButton = li.find('.mm-button-add');
            var select = li.find('select');
            addButton.click(function () {
                var id = select.val();
                var name = select.find(">option:selected").html();
                var stubclass = "mm-record-stub mm-record-entity mm-record-" + id;
                var code = 'bartlefnk_add_' + id;
                var newRow = $('<li><a class="' + stubclass + '">' + name + '</a> </li>');
                newRow.append($('<input name="' + code + '" style="display:none" value="1" /> '));
                var removeButton = $('<a class="mm-button mm-button-remove"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></a>');
                removeButton.click(function () {
                    newRow.remove()
                });
                newRow.append(removeButton);
                li.before(newRow);
            });
        });

    });
});
