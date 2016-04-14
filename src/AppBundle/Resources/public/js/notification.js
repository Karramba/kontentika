$(document).ready(function() {
    $("li.dropdown.notifications .mark-as-read").on({
        "click": function(e) {
            $.ajax({
                url: '/n/markasread',
                type: 'GET',
            })
                .done(function() {
                    $(".notification.unread").each(function() {
                        $(this).removeClass('unread');
                    })
                    $(".notifications-number").html("0");
                    if (!$(".notifications-number").hasClass('zero')) {
                        $(".notifications-number").toggleClass('zero');
                    }

                });

            e.stopPropagation();
        }
    });
});