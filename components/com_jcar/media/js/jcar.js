(function ($) {
    $(document).ready(function () {
        var getURL = $(".jcar-load-more").data("url");

        $(".jcar-load-more").click(function (e) {
            //console.log("before running loop: " + getURL);
            if (getURL === "?format=json") {
                $(".jcar-load-more").attr("disabled", "disabled").html("No more data to display");
            }
            else {
                $.get(getURL, function (data) {
                    //console.log(data);
                    var dataList = data.items;
                    var currentURL = $(location).attr('href');
                    for (var j = 0; j < dataList.length; j++) {
                                //console.log(dataList[j].name);
                                var dataHtml = '<h2><a href="'+  currentURL + '/item/' + dataList[j].id +'">'+ dataList[j].name +'</a></h2>';   
                                 $( "articles#jcar-lists" ).append(dataHtml);                       
                            }
                    var updatedURL = data.pagination.pagesNext;
                    getURL = updatedURL;
                    //console.log("end of loop: " + getURL);
                    //    $(".jcar-load-more").data( "url", updatedURL);
                    //     console.log(getURL);
                }, "json");
            }

        });
    });

})(jQuery);
