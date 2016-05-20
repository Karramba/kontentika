$(document).ready(function() {
    $("#comments").on('click', '.comment-reply', function(event) {
        var $commentId = $(this).data('id');

        $.ajax({
            url: '/c/' + $commentId + '/reply',
            type: 'GET',
        })
        .done(function(result) {
            $("#comment-" + $commentId).find('.reply-form').html(result);
            var $textarea = $("#comment-" + $commentId).find('.reply-form').find('textarea');
            $textarea.focus();
            $textarea.val($textarea.val() + ": ");
        })
       
        event.preventDefault();
    });
});

$(window).load(function() {
    $(".comment-content").each(function() {
        if ($(this).find('.content').height() >= 200) {
            console.log('found');
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
