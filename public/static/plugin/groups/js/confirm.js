define(['core', 'tpl', 'biz/plugin/diyform'], function(core, tpl, diyform) {
    var modal = {
        params: {
            orderid: 0,
            goods: [],
            iscarry: 0,
            isverify: 0,
            isvirtual: 0,
            addressid: 0,
            couponid: 0
        }
    };
    modal.init = function(params) {
        modal.params = $.extend(modal.params, params || {});
        var loadAddress = false;
        if (typeof(window.selectedAddressData) !== 'undefined') {
            loadAddress = window.selectedAddressData
        } else if (typeof(window.editAddressData) !== 'undefined') {
            loadAddress = window.editAddressData;
            loadAddress.address = loadAddress.areas.replace(/ /ig, '') + ' ' + loadAddress.address
        }
        if (loadAddress) {
            modal.params.addressid = loadAddress.id;
            $('#addressInfo .has-address').show();
            $('#addressInfo .no-address').hide();
            $('#addressInfo .aid').val(loadAddress.id);
            $('#addressInfo .realname').html(loadAddress.realname);
            $('#addressInfo .mobile').html(loadAddress.mobile);
            $('#addressInfo .address').html(loadAddress.address);
            $('#addressInfo a').attr('href', core.getUrl('mobile/member/addressselector'));
            $('#addressInfo a').click(function() {
                window.orderSelectedAddressID = loadAddress.id
            })
        }
        var loadStore = false;
        if (typeof(window.selectedStoreData) !== 'undefined') {
            loadStore = window.selectedStoreData;
            modal.params.storeid = loadStore.id;
            $('#carrierInfo .storename').html(loadStore.storename);
            $('#carrierInfo .realname').html(loadStore.realname);
            $('#carrierInfo_mobile').html(loadStore.mobile);
            $('#carrierInfo .address').html(loadStore.address)
        }
        $('#deductcredit').click(function() {
            if (this.checked) {
                $("#isdeduct").val(1)
            } else {
                $("#isdeduct").val(0)
            }
            modal.totalPrice()
        });
        $("form").submit(function() {
            var diyformdata = false;
            if ($(".diyform-container").length > 0) {
                diyformdata = diyform.getData('.diyform-container');
                if (!diyformdata) {
                    return false
                }
                diyformdata = JSON.stringify(diyformdata);
                $("input[name=groups]").val(diyformdata)
            }
            var isverify = $("#memberInfo").attr("data-type");
            if ($("input[name=mobile]").isMobile() == false && isverify) {
                FoxUI.toast.show('联系电话格式有误');
                return false
            }
            if ($("input[name=realname]") == undefined && isverify) {
                FoxUI.toast.show('联系人信息有误');
                return false
            }
            if ($("input[name=aid]").val() == undefined && !isverify) {
                FoxUI.toast.show('请选择地址');
                return false
            }
        })
    };
    modal.totalPrice = function() {
        var goodsprice = core.getNumber($('.goodsprice').html());
        var dispatchprice = core.getNumber($(".dispatchprice").html());
        var discountprice = 0;
        if ($('.discountprice').length > 0) {
            discountprice = core.getNumber($(".discountprice").html())
        }
        var totalprice = goodsprice - discountprice;
        totalprice = totalprice + dispatchprice;
        var deductprice = 0;
        if ($("#deductcredit").length > 0) {
            if ($("#deductcredit").prop('checked')) {
                deductprice = core.getNumber($("#deductcredit").data('money'))
            }
        }
        totalprice = totalprice - deductprice;
        if (totalprice <= 0) {
            totalprice = 0
        }
        $('.totalprice').html(core.number_format(totalprice));
        return totalprice
    };
    return modal
});