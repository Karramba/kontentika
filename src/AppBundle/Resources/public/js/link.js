var update;

$(document).ready(function() {
    $("#link_url").on('input', function(event) {
        clearTimeout(update);
        update = setTimeout(function() {
            $("#link_title").prop('disabled', true);
            $("#link_description").prop('disabled', true)
            $.ajax({
                    url: '/l/generate_title',
                    type: 'POST',
                    dataType: 'json',
                    data: { url: $("#link_url").val() },
                })
                .done(function(response) {
                    $("#link_title").val(response.title);
                    $("#link_description").val(response.description);
                    $("#link_thumbnail").val(response.thumbnail);

                    $("#link_title").prop('disabled', false);
                    $("#link_description").prop('disabled', false)
                });
        }, 100);
        event.preventDefault();
        /* Act on the event */
    });

});

$(window).load(function() {
    $(".comment-content").each(function() {
        if ($(this).find('.content p').height() > 140) {
            $btnMore = $('<div class="more"><button class="btn btn-default btn-xs btn-show" data-contentid="' + $(this).attr('id') + '">Pokaż całość</button></div>');
            $btnMore.insertBefore($(this).find('.options'));
            $btnMore.find(".btn-show").on('click', function(event) {
                var contentid = $(this).data('contentid');
                $("#" + contentid).find('.content').toggleClass('full');
                $(this).remove();
            });
        }
    });
})