define(['core', 'tpl'], function (core, tpl) {
    var modal = {page: 1, keyword: '',cateid: 0};
    modal.init = function (params) {
        modal.keyword = params.keyword ? params.keyword : '' ;
        modal.cateid = params.cateid ? params.cateid : 0 ;
        modal.page = 1;
        modal.lat = '';
        modal.lng = '';
        modal.range = 2000;
        modal.sorttype = 0;

        if (modal.cateid > 0) {
            $('.sortmenu_cate ul li').each(function(){
                if ($(this).attr('cateid') == modal.cateid) {
                    $('#sortmenu_cate_text').html($(this).attr('text'));
                }
            });
        }


        $(".sortMenu > li").off("click").on("click",function(){
            var menuclass = $(this).attr("data-class");
            if($("."+menuclass+"").css("display")=="none"){
                $(".sortMenu > div").hide();
                $("."+menuclass+"").show();
                $(".sort-mask").show();
            }else{
                $("."+menuclass+"").hide();
                $(".sort-mask").hide();
            }

        });

        $(".sort-mask").off("click").on("click",function(){
            $(this).hide();
            $(".sortMenu > div").hide();
        });

        $('.sortmenu_rule ul li').click(function () {
            modal.range = $(this).attr('range');
            var text = $(this).attr('text');
            $('#sortmenu_rule_text').html(text);
            $('.sortmenu_rule').hide();
            modal.page = 1;
            $(".container").empty();
            $(".sort-mask").hide();
            $(".sortMenu > div").hide();
            modal.getList()
        });

        $('.sortmenu_cate ul li').click(function () {
            modal.cateid = $(this).attr('cateid');
            var text = $(this).attr('text');
            $('#sortmenu_cate_text').html(text);
            $('.sortmenu_cate').hide();
            modal.page = 1;
            $(".container").empty();
            $(".sort-mask").hide();
            $(".sortMenu > div").hide();
            modal.getList()
        });

        $('.sortmenu_sort ul li').click(function () {
            modal.sorttype = $(this).attr('sorttype');
            var text = $(this).attr('text');
            $('#sortmenu_sort_text').html(text);
            $('.sortmenu_sort').hide();
            modal.page = 1;
            $(".container").empty();
            $(".sort-mask").hide();
            $(".sortMenu > div").hide();
            modal.getList()
        });


        $('.fui-content').infinite({
            onLoading: function () {
                modal.getList()
            }
        });
        if (modal.page == 1) {
            modal.getList()
        }
    };
    modal.getList = function () {
        var geolocation = new BMap.Geolocation();

        geolocation.getCurrentPosition(function (r) {
            var _this = this;
            if (this.getStatus() == BMAP_STATUS_SUCCESS) {
                modal.lat = r.point.lat;
                modal.lng = r.point.lng;
            }

            core.json('merch/list/ajaxmerchuser', {page: modal.page, keyword: modal.keyword, cateid: modal.cateid, lat: modal.lat, lng: modal.lng, range: modal.range, sorttype: modal.sorttype}, function (ret) {
                var result = ret.result;
                if (result.total <= 0) {
                    $('.content-empty').show();
                    $('.fui-content').infinite('stop')
                } else {
                    $('.content-empty').hide();
                    $('.container').show();
                    $('.fui-content').infinite('init');
                    if (result.list.length <= 0 || result.list.length < result.pagesize) {
                        $('.fui-content').infinite('stop')
                    }
                }
                modal.page++;
                core.tpl('.container', 'tpl_merch_list_user', result, modal.page > 2);
            }, {enableHighAccuracy: true})
        })
    };

    return modal
});