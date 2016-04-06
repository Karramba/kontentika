function getPopoverVoters($elem) {
    var content = "";
    $.ajax({
        url: '/vote/votes',
        type: 'POST',
        dataType: 'json',
        data: {
            uniqueId: $elem.data('id'),
            voteType: $elem.data('votetype'),
            contentType: $elem.data('votefor'),
        },
        async: false,
    }).done(function(response) {
        if (response != "") {
            $.each(response, function(index, val) {
                content = "<div>" + content + val + "</div>";
            });
        } else {
            content = "Brak głosów";
        }
    }).fail(function() {
        content = "fail";
    });

    return content;
}
$(document).ready(function() {
    $(".vote-buttons").on('click', '.btn-vote', function(event) {
        var $button = $(this);
        $.ajax({
            url: '/vote/' + $(this).data('votetype') + "/" + $(this).data('votefor') + "/" + $(this).data('id'),
            type: 'GET',
            dataType: 'json',
        })
        .done(function(response) {
            $('#' + response.id).find('.upvotes').text(response.upvotes);
            $('#' + response.id).find('.downvotes').text(response.downvotes);
            if (response.myvote == "up") {
                $('#' + response.id).find('.btn-vote-up').addClass('btn-primary');
                $('#' + response.id).find('.btn-vote-down').removeClass('btn-danger');
            } else if (response.myvote == "down") {
                $('#' + response.id).find('.btn-vote-up').removeClass('btn-primary');
                $('#' + response.id).find('.btn-vote-down').addClass('btn-danger');
            } else {
                $('#' + response.id).find('.btn-vote-up').removeClass('btn-primary');
                $('#' + response.id).find('.btn-vote-down').removeClass('btn-danger');
            }
            $button.attr('data-content', getPopoverVoters($button));
            $button.popover('show');
        });

        event.preventDefault();
        /* Act on the event */
    });

    $(".btn-vote").on('mouseenter', function() {
        var $button = $(this);
        $button.attr('data-content', getPopoverVoters($button));
        $button.popover('show');
    }).on('mouseleave', function() {
        var $button = $(this);
        $button.popover('hide');
    });

    $('.btn-vote').popover({
        trigger: "manual",
        html: true,
        container: "body",
    }); 


});
