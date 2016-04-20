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
                //Set mustache tempalte path
                var templatePath = './../templates/knowledgearchive/template-html/template.html';
                //Retrieve the tempalte Data
                $.get(templatePath, function (templates) {
                    // filter the tempalte data with correspondent id. 
                    // tempalte.html can have multiple template defined with unique id for each tempalte                    
                    var template = $(templates).filter('#jcarListTemplate').html();
                    //Render template with returned json data from Ajax
                    var html = Mustache.to_html(template, data);
                    //Insert data into mustache template
                    $('#jcarListWrapper').append(html);
                });

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
