(function ($) {
    $(document).ready(function () {
        (function () {
            $.get("index.php/dspace-more-items-example?token=oai_dc///com_10049_25/100&amp;start=100&amp;format=json", function (data) {
                console.log(data);
                var totalData = data.pagination.total;
                console.log(totalData); //check the total number of data returned
                // console.log(typeof totalData); //check if the returned value is number

                $(".jcar-load-more").click(function (e) {
                     e.preventDefault();
                    var i;
                    var totalDataCounter;
                    for (i = 100; i < totalData; i = i + 100) {
                        totalDataCounter = i;
                    }

                    var myURL = "index.php/dspace-more-items-example?token=oai_dc///com_10049_25/" +
                        totalDataCounter + "&amp;start=" + totalDataCounter + "&amp;format=json";

                    $.get(myURL, function (response) {
                        var totalDataLoopCount = response.pagination.limitstart + response.items.length;
                        // console.log(totalData);
                        // console.log(totalDataLoopCount);
                        // console.log(response);
                         var dataList = response.items;                        
                         var currentURL = $(location).attr('href');
                            for (var j = 0; j < dataList.length; j++) {
                                //console.log(dataList[j].name);
                                var dataHtml = '<h2><a href="'+  currentURL + '/item/' + dataList[j].id +'">'+ dataList[j].name +'</a></h2>';   
                                 $( "articles#jcar-lists" ).append(dataHtml);                       
                            }
                           
                        if (totalData === totalDataLoopCount) {
                            $(".jcar-load-more").attr("disabled", "disabled").html("No more data to dispaly");
                        } 
                        // else {
                        //     $(".jcar-load-more").show();                          
                        // }
                    });
                });

            }, "json");
        })();


    });

})(jQuery);
