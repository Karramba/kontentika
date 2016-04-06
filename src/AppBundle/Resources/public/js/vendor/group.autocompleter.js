jQuery(document).ready(function($) {
    $(".group-autocomplete").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "/g-search",
                dataType: "json",
                type: 'POST',
                data: {
                    group: request.term
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 0,
        select: function(event, ui) {

            // log(ui.item ?
            //     "Selected: " + ui.item.label :
            //     "Nothing selected, input was " + this.value);
        },
        open: function() {
            $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
        },
        close: function() {
            $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
        }
    })
});
    