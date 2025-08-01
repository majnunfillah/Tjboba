(function ($) {
    "use strict";
    window.onpopstate = function (e) {
        if (e.state) {
            // document.getElementById("content").innerHTML = e.state.html;
            // document.title = e.state.pageTitle;
            console.log(e.state);
        }
    };

    $(".nav-link").on("click", function (e) {
        if ($(this).attr("href") !== "#") {
            e.preventDefault();
            let url = $(this).attr("href");
            console.log(url);
            $(this).addClass("active");
            baseAjax({
                url,
                type: "GET",
                successCallback: function (html) {
                    console.log(html);
                    // $("#content").empty().append(html);
                },
            });
        }
    });

    function processAjaxData(response, urlPath) {
        document.getElementById("content").innerHTML = response.html;
        document.title = response.pageTitle;
        window.history.pushState(
            { html: response.html, pageTitle: response.pageTitle },
            "",
            urlPath
        );
    }
})(jQuery);
