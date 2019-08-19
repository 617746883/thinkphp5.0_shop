define(['jquery', 'bootstrap'], function ($, bs) {

    var mbiz = {

    };

    mbiz.url = function(routes,params) {

        var url = '/' + routes.replace(/\//ig,'/');
        if(params ){
            if(typeof(params)=='object') {
                url+="&" + $.toQueryString(params);
            }  else if(typeof(params)=='string'){
                url+="&" + params
            }
        }
        return url;
    };

    $(document).on('click', '[data-toggle="selectUrlMerch"]', function () {
        $("#selectUrl").remove();
        var _input = $(this).data('input');
        var _full = $(this).data('full');
        var _callback = $(this).data('callback') || false;
        var _cbfunction = !_callback ? false : eval("(" + _callback + ")");
        if (!_input && !_callback) {
            return;
        }
        var url = mbiz.url('merch/util/selecturl');
        if (_full) {
            url = url + "&full=1";
        }
        $.ajax(url, {
            type: "get",
            dataType: "html",
            cache: false
        }).done(function (html) {
            modal = $('<div class="modal fade" id="selectUrl"></div>');
            $(document.body).append(modal), modal.modal('show');
            modal.append2(html, function () {
                $(document).off("click", '#selectUrl nav').on("click", '#selectUrl nav', function () {
                    var _href = $.trim($(this).data("href"));
                    if (_input) {
                        $(_input).val(_href);
                    } else if (_cbfunction) {
                        _cbfunction(_href);
                    }
                    modal.find(".close").click();
                });
            });
        });
    });
});