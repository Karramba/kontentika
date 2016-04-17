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

    function entryAdd($form) {
        console.log($form);
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

});

$(window).load(function() {
    $(".entry-content").each(function() {
        if ($(this).find('.content').height() >= 200) {
            $btnMore = $('<div class="more"><button class="btn btn-default btn-xs btn-show" data-contentid="' + $(this).attr('id') + '">Pokaż całość</button></div>');
            $btnMore.insertBefore($(this).find('.options'));
            $btnMore.find(".btn-show").on('click', function(event) {
                var contentid = $(this).data('contentid');
                $("#" + contentid).find('.content').toggleClass('full');
                $(this).remove();
            });
        }
    });
    $('video').on('click', function(event) {
        $(this).get(0).play();
        event.preventDefault();
        /* Act on the event */
    });
})