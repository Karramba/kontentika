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