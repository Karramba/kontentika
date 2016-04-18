$(document).ready(function() {
    $(".entries").on('click', '.entry-reply', function(event) {
        $(".entries").find('div.reply-form').empty();

        var $entryId = $(this).data('id');

        $.ajax({
            url: '/e/' + $entryId + '/reply',
            type: 'GET',
        }).done(function(result) {
            $("#entry-" + $entryId).find('.reply-form').html(result);
            var $textarea = $("#entry-" + $entryId).find('.reply-form').find('textarea');
            $textarea.focus();
            $textarea.val($textarea.val() + ": ");
        })
        event.preventDefault();
    });

    $(".entries").on('click', '.entry-reply-cancel', function(event) {
        $(this).closest('div.reply-form').empty();
    });

    $(".entries").on('click', '.entry-edit', function(event) {
        $(".entries").find('div.edit-form').empty();

        var $entryId = $(this).data('id');

        $.ajax({
            url: '/e/' + $entryId + '/edit',
            type: 'GET',
        }).done(function(result) {
            $("#entry-" + $entryId).find('.edit-form').html(result);
            var $textarea = $("#entry-" + $entryId).find('.edit-form').find('textarea');
            $textarea.focus();
        })
        event.preventDefault();
    });

    $(".entries").on('click', '.entry-delete', function(event) {
        var $btn = $(this);
        var $entryId = $(this).data('id');
        bootbox.confirm("Czy na pewno chcesz usunąć?", function(confirmed) {
            if (confirmed) {
                $.ajax({
                    url: '/e/' + $entryId + '/delete',
                    type: 'GET',
                }).fail(function(response) {
                    $(".errors").html("Fail");
                    $(".errors").addClass("alert alert-danger");
                });
            }
        });
        event.preventDefault();
        event.stopPropagation();
    });

    function entryAdd($form) {
        var url = '/e/new';

        if ($form.attr('action') != undefined) {
            url = $form.attr('action');
        }

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: $form.serialize(),
        }).done(function(response) {
            if (response.error == false) {
                $form.trigger('reset');
                $(".errors").html("").removeClass("alert alert-danger");
                if ($form.attr('action') != undefined) {
                    $('.reply-form').remove();
                }
            } else {
                $(".errors").html(response.error);
                if ($(".errors").hasClass('alert') == false) {
                    $(".errors").addClass("alert alert-danger");
                }
            }
        }).fail(function(response) {
            $(".errors").html("Fail");
            $(".errors").addClass("alert alert-danger");
        }).always(function() {
            // console.log("complete");
        });
    }

    $(document).delegate('form[name="entry"]', 'submit', function(e) {
        e.preventDefault();
        entryAdd($(this));
    });

    $(document).delegate('form[name="entry_reply"]', 'submit', function(e) {
        e.preventDefault();
        entryAdd($(this));
    });

    $(document).delegate('form[name="entry_edit"]', 'submit', function(e) {
        e.preventDefault();
        entryAdd($(this));
    });
});

function addButtonsMore() {
    $(".entry-content").each(function() {
        $content = $entryContent.find('.content');
        if ($content.height() >= 200) {
            if (!$entryContent.has($('.more')).length) {
                $btnMore = $('<div class="more"><button class="btn btn-default btn-xs btn-show" data-contentid="' + $entryContent.attr('id') + '">Pokaż całość</button></div>');
                $btnMore.insertBefore($entryContent.find('.options'));
                $btnMore.find(".btn-show").on('click', function(event) {
                    $("#" + $entryContent.attr('id')).find('.content').toggleClass('full');
                    $(this).remove();
                });
            }
        } else if ($entryContent.has($('.more')).length) {
            $('.more').remove();
        }
    });
}

$(window).load(function() {
    addButtonsMore();
})