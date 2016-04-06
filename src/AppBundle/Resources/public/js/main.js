$(document).ready(function() {
    $(".settings-menu-toggle").click(function() {
        $(".settings-menu-dropdown").toggle();
        return false;
    });


    $(".delete-confirm").click(function(event) {
        var $btn = $(this);
        bootbox.confirm("Czy na pewno chcesz usunąć?", function(result) {
            if (result) {
                document.location.href=$btn.attr('href');
            }
        });
        event.preventDefault();
    });

    // var $elem = $("#comment-" + window.location.hash);
    // console.log($elem);
    // $.scrollTo($elem, 500);

    $("li.dropdown.notifications .mark-as-read").on({
        "click":function(e) {
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

