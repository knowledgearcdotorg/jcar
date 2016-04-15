(function ($) {
    $(document).ajaxStart(function () {
        //$( ".log" ).text( "Triggered ajaxStart handler." );
        //console.log("request started");
        $(".jcar-load-more").attr("disabled", "disabled").html("<span class=\"loader-gif\"></span> " + "Loading Records..");
        // $(".loader-gif").css("display", "block");
    });
    $(document).ajaxStop(function () {
        // $( ".log" ).text( "Triggered ajaxStop handler." );
        //console.log("request completed");
        $(".jcar-load-more").removeAttr("disabled").html("Load more");
        // $(".loader-gif").css("display", "none");
    });
    $(document).ready(function () {

        var getURL = $(".jcar-load-more").data("url");
        // console.log("before running loop: " + getURL);

        $(".jcar-load-more").click(function (e) {
            e.preventDefault();
            // console.log("before running inside loop: " + getURL);
            // if (getURL === "?format=json") {
            //     $(".jcar-load-more").hide();
            // }
            // else {
            $.get(getURL, function (data) {
                //console.log(data);
                var dataList = data.items;

                for (var j = 0; j < dataList.length; j++) {
                    //console.log(dataList[j].name);
                    var dataHtml = '<h2><a href="' + dataList[j].link + '">' + dataList[j].name + '</a></h2>';
                    $("articles#jcar-lists").append(dataHtml);
                }
                $("section#jcarCategory > header").html("<h1></h1><div></div>Results 1 - " + (data.pagination.limitstart + dataList.length) + " of " + data.pagination.total);
                var updatedURL = data.pagination.pagesNext;
                getURL = updatedURL;
                // console.log("end of loop: " + getURL);
                //    $(".jcar-load-more").data( "url", updatedURL);
                //     console.log(getURL);
                if (getURL === "?format=json") {
                    $(".jcar-load-more").hide();
                }
            }, "json");
            // }


        });
    });

})(jQuery);
