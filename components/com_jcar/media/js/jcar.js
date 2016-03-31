(function ($) {
    $(".jcar-load-more").click(function() {
        $.ajax({
            type : 'GET',
            url: "index.php?option=com_jcar&token=token",
            success: function (response) {
                if (response.data) {

                }
            }
        });
    });
})(jQuery)
