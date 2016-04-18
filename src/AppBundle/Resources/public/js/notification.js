$(document).ready(function() {
    $("li.dropdown.notifications").on({
        "click": function(e) {
            $.ajax({
                url: '/n/markasread',
                type: 'GET',
            }).done(function() {
                $(".notification.unread").each(function() {
                    $(this).removeClass('unread');
                })
                $(".notifications-number").html("0");
                if (!$(".notifications-number").hasClass('zero')) {
                    $(".notifications-number").toggleClass('zero');
                }

                document.title = document.title.replace(/\([0-9+]\) /g, "");
            });

            e.stopPropagation();
            e.preventDefault();
        }
    }, '.mark-as-read');
});
