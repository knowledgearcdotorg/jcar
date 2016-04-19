(function ($) {
    $(document).ajaxStart(function () {
        //Disable Load more button when data is being fetched
        $(".jcar-load-more").attr("disabled", "disabled").html("<span class=\"loader-gif\"></span> " + $.translations.COM_JCAR_LOADING_BUTTON);
    });
    
    $(document).ajaxStop(function () {
        //Enable Load more button again when data is completely fetched
        $(".jcar-load-more").removeAttr("disabled").html("Load more");
    });
    
    $(document).ready(function () {
        //Get url from the html button data-url attribute
        var getURL = $(".jcar-load-more").data("url");
        
        $(".jcar-load-more").click(function (e) {
            e.preventDefault();
            
            $.get(getURL, function (data) {
                //create tempalte for mustache js
                var template = $('#jcarListTemplate').html();
                 //Render tempalte
                var html = Mustache.to_html(template, data);
                //Insert data into mustache template
                $('#jcarListWrapper').append(html);
                
                //udpate Ajax url for next iteration
                var updatedURL = data.pagination.pagesNext;
                getURL = updatedURL;
                
                //hide load more button if nextPage value is null
                if (getURL === "?format=json") {
                    $(".jcar-load-more").hide();
                }
            }, "json");
        });
    });

})(jQuery);
