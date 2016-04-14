$(document).ready(function() {
    $(".settings-menu-toggle").click(function() {
        $(".settings-menu-dropdown").toggle();
        return false;
    });


    $(".delete-confirm").click(function(event) {
        var $btn = $(this);
        bootbox.confirm("Czy na pewno chcesz usunąć?", function(result) {
            if (result) {
                document.location.href = $btn.attr('href');
            }
        });
        event.preventDefault();
    });

});