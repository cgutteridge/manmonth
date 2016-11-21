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
            var showAsRemoved = li.attr('data-mm-remove') == "true";
            // ensure this input is initially what we expect
            actionInput.val(0);
            removeButton.click(mmRemove);
            undoButton.hide().click(mmUndo);

            function mmRemove() {
                removeButton.hide();
                undoButton.show();
                recordStub.addClass('mm-chopping-block');
                actionInput.val(1);
            }

            function mmUndo() {
                removeButton.show();
                undoButton.hide();
                recordStub.removeClass('mm-chopping-block');
                actionInput.val(0);
            }

            if (showAsRemoved) {
                mmRemove();
            }
        });

        block.find("li.mm-link-edit-list-add").each(function () {
            var li = $(this);
            var idPrefix = li.attr("data-mm-idprefix");
            var addButton = li.find('.mm-button-add');
            var select = li.find('select');
            var toAdd = [];
            if (li.attr("data-mm-add")) {
                toAdd = li.attr("data-mm-add").split(",");
            }
            addButton.click(mmAddFromSelect);
            select.change(mmAddFromSelect);

            function mmAddFromSelect() {
                var id = select.val();
                var name = select.find(">option:selected").html();
                mmAddValue(id, name);
            }

            function mmAddValue(id, name) {
                var stubclass = "mm-record-stub mm-record-entity mm-record-" + id;
                var code = idPrefix + 'add_' + id;
                var newRow = $('<li><a class="' + stubclass + '">' + name + '</a> </li>');
                newRow.append($('<input name="' + code + '" style="display:none" value="1" /> '));
                var removeButton = $('<a class="mm-button mm-button-remove"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></a>');
                removeButton.click(function () {
                    newRow.remove()
                });
                newRow.append(removeButton);
                li.before(newRow);
            }

            for (var i = 0; i < toAdd.length; ++i) {
                var id = toAdd[i];
                var name = li.attr("data-mm-add-" + id);
                mmAddValue(id, name);
            }
        });

    });
})
;
