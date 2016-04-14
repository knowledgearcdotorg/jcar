(function ($) {
    $(document).ready(function () {
        // $(".jcar-load-more").click(function () {
           (function(){
                $.ajax({
                type: 'GET',
                url: "index.php/dspace-more-items-example?token=oai_dc///com_10049_25/100&amp;start=100&amp;format=json",
                // data: data,
                dataType: 'json',
                success: function (response) {
                   
                        //console.log(response);
                        var i;
                        for (i = 0; i < response.items.length; i++) {
                            var jcarDataListMarkup = '<h2><a href="dspace-more-items-example/item/' + response.items[i].id + '">' + response.items[i].name + '</a></h2>';
                             $("#jcar-lists-test").append(jcarDataListMarkup);    
                    }
                      $(".jcar-load-more").data("url", response.pagination.pagesNext);  
                      var myURL = $('.jcar-load-more').data("url");
                      console.log(myURL);
                      
                      $(".jcar-load-more").click(function (e) { 
                        $.ajax({
                            type: 'GET',
                            url: myURL,
                            dataType: 'json',
                            success: function (data){
                                console.log("Genuise");
                                 var i;
                        for (i = 0; i < data.items.length; i++) {
                            var jcarDataListMarkup = '<h2><a href="dspace-more-items-example/item/' + data.items[i].id + '">' + response.items[i].name + '</a></h2>';
                             $("#jcar-lists-test").append(jcarDataListMarkup);    
                    }
                            }
                        });
                      });

                    //   $(".jcar-load-more").click(function (e) { 
                    //      e.preventDefault();
                    //      var btn = $(".jcar-load-more").data("url");
                    //       console.log(btn);
                    //   }); 
                     //   $(".jcar-load-more").click(function (e) { 
                    //      e.preventDefault();
                    //      var btn = $(".jcar-load-more").data("url");
                    //       console.log(btn);
                    //   }); 
                    
                    
                   // console.log("pagination value" + response.pagination.pagesNext);

                   
                }
            });
           }());
           
        // });
    });

})(jQuery);
