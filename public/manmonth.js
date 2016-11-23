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
        var min = block.attr('data-mm-min');
        var max = block.attr('data-mm-max');
        var choices = {};
        var current = {}; // current never appear in the picklist

        // This function is called any time this field is altered
        // including by the initial data passed in from the form.
        function mmChanged() {
            var size = Object.keys(choices).length;
            if (size < min) {
                block.addClass("mm-below-min");
                block.removeClass("mm-at-min");
            } else if (size == min) {
                block.addClass("mm-at-min");
                block.removeClass("mm-below-min");
            } else {
                block.removeClass("mm-below-min");
                block.removeClass("mm-at-min");
            }
            if (max) {
                if (size > max) {
                    block.addClass("mm-above-max");
                    block.removeClass("mm-at-max");
                } else if (size == max) {
                    block.addClass("mm-at-max");
                    block.removeClass("mm-above-max");
                } else {
                    block.removeClass("mm-above-max");
                    block.removeClass("mm-at-max");
                }
            }

            // remove options from select
            block.find("li.mm-link-edit-list-add select option").each(function () {
                var option = $(this);
                if (choices[option.attr("value")] || current[option.attr("value")]) {
                    option.hide();
                } else {
                    option.show();
                }
            });
        }


        block.find("li.mm-link-edit-list-existing").each(function () {
            var li = $(this);
            var removeButton = li.find('.mm-button-remove');
            var undoButton = li.find('.mm-button-undo');
            var actionInput = li.find('.mm-form-action');
            var showAsRemoved = li.attr('data-mm-remove') == "true";
            var sid = li.attr('data-mm-sid');
            // ensure this input is initially what we expect
            actionInput.val(0);
            removeButton.click(mmRemove);
            undoButton.click(mmUndo);
            choices[sid] = 1;
            current[sid] = 1;

            function mmRemove() {
                li.addClass('mm-chopping-block');
                actionInput.val(1);
                delete( choices[sid] );
                mmChanged();
            }

            function mmUndo() {
                li.removeClass('mm-chopping-block');
                actionInput.val(0);
                choices[sid] = 1;
                mmChanged();
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
                var sid = select.val();
                if (sid == "") {
                    return;
                }
                var name = select.find(">option:selected").html();
                mmAddValue(sid, name);
            }

            function mmAddValue(sid, name) {
                var stubclass = "mm-record-stub mm-record-entity mm-record-" + sid;
                var code = idPrefix + 'add_' + sid;
                var newRow = $('<li><a class="' + stubclass + '">' + name + '</a> </li>');
                newRow.append($('<input name="' + code + '" style="display:none" value="1" /> '));
                var removeButton = $('<a class="mm-button mm-button-remove"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></a>');
                removeButton.click(function () {
                    newRow.remove();
                    delete( choices[sid] );
                    mmChanged();
                });
                newRow.append(removeButton);
                li.before(newRow);
                select.val("");
                choices[sid] = 1;
                mmChanged();
            }

            for (var i = 0; i < toAdd.length; ++i) {
                var id = toAdd[i];
                var name = li.attr("data-mm-add-" + id);
                mmAddValue(id, name);
            }
        });

        // initial setup of what is visible/hidden
        mmChanged();
    });
})
;
