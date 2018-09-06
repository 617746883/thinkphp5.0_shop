define(['core', 'tpl', 'biz/plugin/diyform'], function(core, tpl, diyform) {
    var modal = {
        params: {
            orderid: 0,
            goods: [],
            coupon_goods: [],
            merchs: [],
            iscarry: 0,
            isverify: 0,
            isvirtual: 0,
            isonlyverifygoods: 0,
            addressid: 0,
            contype: 0,
            couponid: 0,
            wxid: 0,
            wxcardid: 0,
            wxcode: "",
            isnodispatch: 0,
            nodispatch: '',
            packageid: 0,
            new_area: '',
            address_street: ''
        }
    };
    modal.init = function(params) {
        modal.params = $.extend(modal.params, params || {});
        modal.params.couponid = 0;
        $('#coupondiv').find('.fui-cell-label').html('优惠券');
        $('#coupondiv').find('.fui-cell-info').html('');
        var discountprice = core.getNumber($(".discountprice").val());
        var isdiscountprice = core.getNumber($(".isdiscountprice").val());
        if (discountprice > 0) {
            $('.discount').show()
        }
        if (isdiscountprice > 0) {
            $('.isdiscount').show()
        }
        var loadAddress = false;
        if (typeof(window.selectedAddressData) !== 'undefined') {
            loadAddress = window.selectedAddressData
        } else if (typeof(window.editAddressData) !== 'undefined') {
            loadAddress = window.editAddressData;
            loadAddress.address = loadAddress.areas.replace(/ /ig, '') + ' ' + loadAddress.address
        } else {
            var id = modal.getCookie('id');
            var mobile = modal.getCookie('mobile');
            var address = modal.getCookie('addressd');
            var realname = modal.getCookie('realname');
            if (id > 0) {
                loadAddress = {
                    id: id,
                    mobile: mobile,
                    address: address,
                    realname: realname
                }
            }
        } if (loadAddress) {
            modal.params.addressid = loadAddress.id;
            $('#addressInfo .has-address').show();
            $('#addressInfo .no-address').hide();
            $('#addressInfo .icon-dingwei').show();
            $('#addressInfo .realname').html(loadAddress.realname);
            $('#addressInfo .mobile').html(loadAddress.mobile);
            $('#addressInfo .address').html(loadAddress.address);
            $('#addressInfo a').attr('href', core.getUrl('member/address/selector'));
            $('#addressInfo a').click(function() {
                window.orderSelectedAddressID = loadAddress.id
            })
        }
        document.cookie = "id=0";
        var loadStore = false;
        if (typeof(window.selectedStoreData) !== 'undefined') {
            loadStore = window.selectedStoreData;
            modal.params.storeid = loadStore.id;
            $('#carrierInfo .storename').html(loadStore.storename);
            $('#carrierInfo .realname').html(loadStore.realname);
            $('#carrierInfo_mobile').html(loadStore.mobile);
            $('#carrierInfo .address').html(loadStore.address);
            $('#carrierInfo').find('.no-address').css("display", "none");
            $('#carrierInfo').find('.has-address').css("display", "block");
            $('#carrierInfo').find('.fui-list-media').css("display", "block");
            $('#carrierInfo').find('.text').css("display", "block");
            $('#carrierInfo').find('.subtitle').css("display", "block")
        }
        FoxUI.tab({
            container: $('#carrierTab'),
            handlers: {
                tab1: function() {
                    modal.params.iscarry = 0;
                    $('#addressInfo').show(), $('#carrierInfo').hide(), $('#memberInfo').hide(), $('#showdispatchprice').show();
                    modal.caculate()
                },
                tab2: function() {
                    modal.params.iscarry = 1;
                    $('#addressInfo').hide(), $('#carrierInfo').show(), $('#memberInfo').show(), $('#showdispatchprice').hide();
                    modal.caculate()
                }
            }
        });
        var number = $('.fui-number');
        if (number.length > 0) {
            var maxbuy = number.data('maxbuy') || 0,
                goodsid = number.data('goodsid'),
                minbuy = number.data('minbuy') || 0,
                unit = number.data('unit') || '件';
            number.numbers({
                max: maxbuy,
                min: minbuy,
                minToast: "{min}" + unit + "起售",
                maxToast: "最多购买{max}" + unit,
                callback: function(num) {
                    $.each(modal.params.goods, function() {
                        if (this.goodsid == goodsid) {
                            this.total = num;
                            return false
                        }
                    });
                    $.each(modal.params.coupon_goods, function() {
                        if (this.goodsid == goodsid) {
                            this.total = num;
                            return false
                        }
                    });
                    modal.params.contype = 0;
                    modal.params.couponid = 0;
                    modal.params.wxid = 0;
                    modal.params.wxcardid = "";
                    modal.params.wxcode = "";
                    modal.params.couponmerchid = 0;
                    $('#coupondiv').find('.fui-cell-label').html('优惠券');
                    $('#coupondiv').find('.fui-cell-info').html('');
                    $('#goodscount').html(num);
                    var marketprice = core.getNumber(number.closest('.goods-item').find('.marketprice').html()) * num;
                    $('.goodsprice').html(core.number_format(marketprice, 2));
                    modal.caculate()
                }
            })
        }
        $('#deductcredit').click(function() {
            modal.calcCouponPrice()
        });
        $('#deductcredit2').click(function() {
            modal.calcCouponPrice()
        });
        modal.bindCoupon();
        $(document).click(function() {
            $('input,select,textarea').each(function() {
                $(this).attr('data-value', $(this).val())
            });
            $(':checkbox,:radio').each(function() {
                $(this).attr('data-checked', $(this).prop('checked'))
            })
        });
        $('input,select,textarea').each(function() {
            var value = $(this).attr('data-value') || '';
            if (value != '') {
                $(this).val(value)
            }
        });
        $(':checkbox,:radio').each(function() {
            var value = $(this).attr('data-checked') === 'true' ? true : false;
            $(this).prop('checked', value)
        });
        $('.buybtn').click(function() {
            modal.submit(this, params.token)
        });
        modal.caculate();
        $(".fui-cell-giftclick").click(function() {
            modal.giftPicker = new FoxUIModal({
                content: $('#gift-picker-modal').html(),
                extraClass: 'picker-modal',
                maskClick: function() {
                    modal.giftPicker.close()
                }
            });
            modal.giftPicker.container.find('.btn-danger').click(function() {
                modal.giftPicker.close()
            });
            modal.giftPicker.show();
            var giftid = $("#giftid").val();
            $(".gift-item").each(function() {
                if ($(this).val() == giftid) {
                    $(this).prop("checked", "true")
                }
            });
            $(".gift-item").on("click", function() {
                $.ajax({
                    url: core.getUrl('goods/detail/querygift', {
                        id: $(this).val()
                    }),
                    cache: true,
                    success: function(data) {
                        data = window.JSON.parse(data);
                        if (data.status > 0) {
                            $("#giftid").val(data.result.id);
                            $("#gifttitle").text(data.result.title)
                        }
                    }
                })
            })
        });
        $('.show-allshop-btn').click(function() {
            $(this).closest('.store-container').addClass('open')
        });
        modal.initaddress()
    };
    modal.getCookie = function(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1);
            if (c.indexOf(name) != -1) {
                return c.substring(name.length, c.length)
            }
        }
        return ""
    };
    modal.giftPicker = function() {
        modal.giftPicker = new FoxUIModal({
            content: $('#option-picker-modal').html(),
            extraClass: 'picker-modal',
            maskClick: function() {
                modal.packagePicker.close()
            }
        })
    };
    modal.bindCoupon = function() {
        $('#coupondiv').unbind('click').click(function() {
            $('#couponloading').show();
            core.json('sale/coupon/util/query', {
                money: 0,
                type: 0,
                merchs: modal.params.merchs,
                goods: modal.params.goods
            }, function(rjson) {
                $('#couponloading').hide();
                if (rjson.result.coupons.length > 0 || rjson.result.wxcards.length > 0) {
                    $('#coupondiv').show().find('.badge').html(rjson.result.coupons.length + rjson.result.wxcards.length).show();
                    $('#coupondiv').find('.text').hide();
                    require(['biz/sale/coupon/picker'], function(picker) {
                        picker.show({
                            couponid: modal.params.couponid,
                            coupons: rjson.result.coupons,
                            wxcards: rjson.result.wxcards,
                            onCancel: function() {
                                modal.params.contype = 0;
                                modal.params.couponid = 0;
                                modal.params.wxid = 0;
                                modal.params.wxcardid = "";
                                modal.params.wxcode = "";
                                modal.params.couponmerchid = 0;
                                $('#coupondiv').find('.fui-cell-label').html('优惠券');
                                $('#coupondiv').find('.fui-cell-info').html('');
                                modal.calcCouponPrice()
                            },
                            onSelected: function(data) {
                                modal.params.contype = data.contype;
                                if (modal.params.contype == 1) {
                                    modal.params.couponid = 0;
                                    modal.params.wxid = data.wxid;
                                    modal.params.wxcardid = data.wxcardid;
                                    modal.params.wxcode = data.wxcode;
                                    modal.params.couponmerchid = data.merchid;
                                    $('#coupondiv').find('.fui-cell-label').html('已选择');
                                    $('#coupondiv').find('.fui-cell-info').html(data.couponname);
                                    $('#coupondiv').data(data);
                                    modal.calcCouponPrice()
                                } else if (modal.params.contype == 2) {
                                    modal.params.couponid = data.couponid;
                                    modal.params.wxid = 0;
                                    modal.params.wxcardid = "";
                                    modal.params.wxcode = "";
                                    modal.params.couponmerchid = data.merchid;
                                    $('#coupondiv').find('.fui-cell-label').html('已选择');
                                    $('#coupondiv').find('.fui-cell-info').html(data.couponname);
                                    $('#coupondiv').data(data);
                                    modal.calcCouponPrice()
                                } else {
                                    modal.params.contype = 0;
                                    modal.params.couponid = 0;
                                    modal.params.wxid = 0;
                                    modal.params.wxcardid = "";
                                    modal.params.wxcode = "";
                                    modal.params.couponmerchid = 0;
                                    $('#coupondiv').find('.fui-cell-label').html('优惠券');
                                    $('#coupondiv').find('.fui-cell-info').html('');
                                    modal.calcCouponPrice()
                                }
                            }
                        })
                    })
                } else {
                    FoxUI.toast.show('未找到优惠券!');
                    modal.hideCoupon()
                }
            }, false, true)
        })
    };
    modal.hideCoupon = function() {
        $('#coupondiv').hide();
        $('#coupondiv').find('.badge').html('0').hide();
        $('#coupondiv').find('.text').show()
    };
    modal.caculate = function() {
        var goodsprice = core.getNumber($('.goodsprice').html());
        var taskdiscountprice = core.getNumber($(".taskdiscountprice").val());
        var lotterydiscountprice = core.getNumber($(".lotterydiscountprice").val());
        var discountprice = core.getNumber($(".discountprice").val());
        var isdiscountprice = core.getNumber($(".isdiscountprice").val());
        var taskcut = core.getNumber($("#taskcut").val());
        var totalprice = goodsprice - taskdiscountprice - lotterydiscountprice - discountprice - isdiscountprice - taskcut;
        if ($('.shownum').length > 0) {
            totalprice = core.getNumber($('.marketprice').html()) * parseInt($('.shownum').val())
        }
        if (modal.params.fromcart == 0) {
            if (modal.params.goods.length == 1) {
                modal.params.goods[0].total = parseInt($('.shownum').val())
            }
        }
        if (typeof(window.selectedAddressData) !== 'undefined') {
            modal.params.addressid = window.selectedAddressData.id
        }
        core.json('order/create/caculate', {
            totalprice: totalprice,
            addressid: modal.params.addressid,
            dispatchid: modal.params.dispatchid,
            dflag: modal.params.iscarry,
            goods: modal.params.goods,
            liveid: modal.params.liveid
        }, function(getjson) {
            if (getjson.status == 1) {
                if (modal.params.iscarry) {
                    $('.dispatchprice').html('0.00')
                } else {
                    $('.dispatchprice').html(core.number_format(getjson.result.price, 2))
                } if (getjson.result.taskdiscountprice) {
                    $('#taskdiscountprice').val(core.number_format(getjson.result.taskdiscountprice, 2))
                }
                if (getjson.result.lotterydiscountprice) {
                    $('#lotterydiscountprice').val(core.number_format(getjson.result.lotterydiscountprice, 2))
                }
                if (getjson.result.discountprice) {
                    $('#discountprice').val(core.number_format(getjson.result.discountprice, 2))
                }
                if (getjson.result.buyagain) {
                    $('#buyagain').val(core.number_format(getjson.result.buyagain, 2));
                    $('#showbuyagainprice').html(core.number_format(getjson.result.buyagain, 2)).parents(".fui-cell").show()
                }
                if (getjson.result.isdiscountprice) {
                    $('#isdiscountprice').val(core.number_format(getjson.result.isdiscountprice, 2))
                }
                if (getjson.result.deductcredit) {
                    $('#deductcredit_money').html(core.number_format(getjson.result.deductmoney, 2));
                    $('#deductcredit_info').html(getjson.result.deductcredit);
                    $("#deductcredit").data('credit', getjson.result.deductcredit);
                    $("#deductcredit").data('money', core.number_format(getjson.result.deductmoney, 2))
                }
                if (getjson.result.deductcredit2) {
                    $('#deductcredit2_money').html(getjson.result.deductcredit2);
                    $("#deductcredit2").data('credit2', getjson.result.deductcredit2)
                }
                if (getjson.result.include_dispath > 0) {
                    $('#include_dispath').show()
                } else {
                    $('#include_dispath').hide()
                } if (getjson.result.seckillprice > 0) {
                    $('#seckillprice').show();
                    $('#seckillprice_money').html(core.number_format(getjson.result.seckillprice, 2))
                } else {
                    $('#seckillprice').hide();
                    $('#seckillprice_money').html(0)
                } if (getjson.result.couponcount > 0) {
                    $('#coupondiv').show().find('.badge').html(getjson.result.couponcount).show();
                    $('#coupondiv').find('.text').hide()
                } else {
                    modal.params.couponid = 0;
                    $('#coupondiv').hide().find('.badge').html(0).hide()
                } if (getjson.result.merch_deductenough_money > 0) {
                    $('#merch_deductenough').show();
                    $('#merch_deductenough_money').html(core.number_format(getjson.result.merch_deductenough_money, 2));
                    $('#merch_deductenough_enough').html(core.number_format(getjson.result.merch_deductenough_enough, 2))
                } else {
                    $('#merch_deductenough').hide();
                    $('#merch_deductenough_money').html('0.00');
                    $('#merch_deductenough_enough').html('0.00')
                } if (getjson.result.deductenough_money > 0) {
                    $('#deductenough').show();
                    $('#deductenough_money').html(core.number_format(getjson.result.deductenough_money, 2));
                    $('#deductenough_enough').html(core.number_format(getjson.result.deductenough_enough, 2))
                } else {
                    $('#deductenough').hide();
                    $('#deductenough_money').html('0.00');
                    $('#deductenough_enough').html('0.00')
                } if (getjson.result.merchs) {
                    modal.params.merchs = getjson.result.merchs
                }
                if (getjson.result.isnodispatch == 1) {
                    modal.isnodispatch = 1;
                    modal.nodispatch = getjson.result.nodispatch;
                    FoxUI.toast.show(modal.nodispatch)
                } else {
                    modal.isnodispatch = 0;
                    modal.nodispatch = ''
                }
                modal.calcCouponPrice()
            }
        }, true, true)
    };
    modal.totalPrice = function(couponprice) {
        var goodsprice = core.getNumber($('.goodsprice').html());
        var couponprice = couponprice;
        var taskdiscountprice = core.getNumber($(".showtaskdiscountprice").html());
        var lotterydiscountprice = core.getNumber($(".showlotterydiscountprice").html());
        var discountprice = core.getNumber($(".showdiscountprice").html());
        var isdiscountprice = core.getNumber($(".showisdiscountprice").html());
        var buyagainprice = core.getNumber($("#buyagain").val());
        var totalprice = goodsprice - taskdiscountprice - lotterydiscountprice - discountprice - isdiscountprice - couponprice - buyagainprice;
        var taskcut = core.getNumber($("#taskcut").val());
        var dispatchprice = core.getNumber($(".dispatchprice").html());
        var merch_enoughprice = 0;
        if ($("#merch_deductenough_money").length > 0 && $("#merch_deductenough_money").html() != '') {
            merch_enoughprice = core.getNumber($("#merch_deductenough_money").html())
        }
        var enoughprice = 0;
        if ($("#deductenough_money").length > 0 && $("#deductenough_money").html() != '') {
            enoughprice = core.getNumber($("#deductenough_money").html())
        }
        totalprice = totalprice - merch_enoughprice - enoughprice + dispatchprice - taskcut;
        var deductprice = 0;
        if ($("#deductcredit").length > 0) {
            if ($("#deductcredit").prop('checked')) {
                deductprice = core.getNumber($("#deductcredit").data('money'));
                if ($("#deductcredit2").length > 0) {
                    var td1 = core.getNumber($("#deductcredit2").data('credit2'));
                    if (totalprice - deductprice >= 0) {
                        var td = totalprice - deductprice;
                        if (td > td1) {
                            td = td1
                        }
                        $("#deductcredit2_money").html(core.number_format(td, 2))
                    }
                }
            } else {
                if ($("#deductcredit2").length > 0) {
                    var td = core.getNumber($("#deductcredit2").data('credit2'));
                    $("#deductcredit2_money").html(core.number_format(td, 2))
                }
            }
        }
        var deductprice2 = 0;
        if ($("#deductcredit2").length > 0) {
            if ($("#deductcredit2").prop('checked')) {
                deductprice2 = core.getNumber($("#deductcredit2_money").html())
            }
        }
        totalprice = totalprice - deductprice - deductprice2;
        if (totalprice <= 0) {
            totalprice = 0
        }
        $('.totalprice').html(core.number_format(totalprice));
        modal.bindCoupon();
        return totalprice
    };
    modal.calcCouponPrice = function() {
        var goodsprice = core.getNumber($('.goodsprice').html());
        $('#coupondeduct_div').hide();
        $('#coupondeduct_text').html('');
        $('#coupondeduct_money').html('0');
        var deductprice = 0;
        var taskdiscountprice = core.getNumber($(".taskdiscountprice").val());
        var taskcut = core.getNumber($("#taskcut").val());
        var lotterydiscountprice = core.getNumber($(".lotterydiscountprice").val());
        var discountprice = core.getNumber($(".discountprice").val());
        var isdiscountprice = core.getNumber($(".isdiscountprice").val());
        if (modal.params.couponid == 0 && modal.params.wxid == 0) {
            if (taskdiscountprice > 0) {
                $(".showtaskdiscountprice").html($(".taskdiscountprice").val());
                $('.istaskdiscount').show()
            } else {
                $('.istaskdiscount').hide()
            } if (taskcut > 0) {
                $(".showtaskcut").html($("#taskcut").val());
                $('.taskcut').show()
            } else {
                $('.istaskdiscount').hide()
            } if (lotterydiscountprice > 0) {
                $(".showlotterydiscountprice").html($(".lotterydiscountprice").val());
                $('.islotterydiscount').show()
            } else {
                $('.islotterydiscount').hide()
            } if (discountprice > 0) {
                $(".showdiscountprice").html($(".discountprice").val());
                $('.discount').show()
            } else {
                $('.discount').hide()
            } if (isdiscountprice > 0) {
                $(".showisdiscountprice").html($(".isdiscountprice").val());
                $('.isdiscount').show()
            } else {
                $('.isdiscount').hide()
            }
            return modal.totalPrice(0)
        }
        core.json('order/create/getcouponprice', {
            goods: modal.params.coupon_goods,
            goodsprice: goodsprice,
            couponid: modal.params.couponid,
            contype: modal.params.contype,
            wxid: modal.params.wxid,
            wxcardid: modal.params.wxcardid,
            wxcode: modal.params.wxcode,
            discountprice: discountprice,
            isdiscountprice: isdiscountprice
        }, function(getjson) {
            if (getjson.status == 1) {
                $('#coupondeduct_text').html(getjson.result.coupondeduct_text);
                deductprice = getjson.result.deductprice;
                var discountpricenew = getjson.result.discountprice;
                var isdiscountpricenew = getjson.result.isdiscountprice;
                if (discountpricenew > 0) {
                    $(".showdiscountprice").html(discountpricenew);
                    $('.discount').show()
                } else {
                    $(".showdiscountprice").html(0);
                    $('.discount').hide()
                } if (isdiscountpricenew > 0) {
                    $(".showisdiscountprice").html(isdiscountpricenew);
                    $('.isdiscount').show()
                } else {
                    $(".showisdiscountprice").html(0);
                    $('.isdiscount').hide()
                } if (deductprice > 0) {
                    $('#coupondeduct_div').show();
                    $('#coupondeduct_money').html(core.number_format(deductprice, 2))
                }
            } else {
                if (discountprice > 0) {
                    $(".showdiscountprice").html($(".discountprice").val());
                    $('.discount').show()
                } else {
                    $('.discount').hide()
                } if (isdiscountprice > 0) {
                    $(".showisdiscountprice").html($(".isdiscountprice").val());
                    $('.isdiscount').show()
                } else {
                    $('.isdiscount').hide()
                }
                deductprice = 0
            }
            return modal.totalPrice(deductprice)
        }, true, true)
    };
    modal.submit = function(obj, token) {
        var $this = $(obj);
        var giftid = parseInt($("#giftid").val());
        if (modal.params.mustbind) {
            FoxUI.alert("请先绑定手机", "提示", function() {
                location.href = core.getUrl('member/bind', {
                    backurl: btoa(location.href)
                })
            });
            return
        }
        if ($this.attr('stop')) {
            return
        }
        if (modal.params.isonlyverifygoods) {} else if (modal.params.iscarry || modal.params.isverify || modal.params.isvirtual) {
            if (modal.params.iscarry && modal.params.storeid == 0) {
                FoxUI.toast.show('请选择自提点');
                return
            }
            if ($(':input[name=carrier_realname]').isEmpty() && $(':input[name=carrier_realname]').attr("data-set") == 0) {
                FoxUI.toast.show('请填写联系人');
                return
            }
            if ($(':input[name=carrier_mobile]').isEmpty() && $(':input[name=carrier_mobile]').attr("data-set") == 0) {
                FoxUI.toast.show('请填写联系电话');
                return
            }
            if (!$(':input[name=carrier_mobile]').isMobile() && $(':input[name=carrier_mobile]').attr("data-set") == 0) {
                FoxUI.toast.show('联系电话格式有误');
                return
            }
        } else {
            if (modal.params.addressid == 0) {
                FoxUI.toast.show('请选择收货地址');
                return
            } else {
                if (modal.isnodispatch == 1) {
                    FoxUI.toast.show(modal.nodispatch);
                    return
                }
            }
        }
        var diyformdata = '';
        if (modal.params.orderdiyformid != '0') {
            var diyformdata = diyform.getData('.diyform-container');
            if (!diyformdata) {
                return
            }
        }
        if (modal.params.fromcart == 0) {
            if (modal.params.goods.length == 1) {
                modal.params.goods[0].total = parseInt($('.shownum').val())
            }
        }
        $this.attr('stop', 1);
        var data = {
            'orderid': modal.params.orderid,
            'id': modal.params.id,
            'goods': modal.params.goods,
            'giftid': giftid,
            'gdid': modal.params.gdid,
            'liveid': modal.params.liveid,
            'diydata': diyformdata,
            'dispatchtype': modal.params.iscarry ? 1 : 0,
            'fromcart': modal.params.fromcart,
            'carrierid': modal.params.iscarry ? modal.params.storeid : 0,
            'addressid': !modal.params.iscarry ? modal.params.addressid : 0,
            'carriers': (modal.params.iscarry || modal.params.isvirtual || modal.params.isverify) ? {
                'carrier_realname': $(':input[name=carrier_realname]').val(),
                'carrier_mobile': $(':input[name=carrier_mobile]').val(),
                'realname': $('#carrierInfo .realname').html(),
                'mobile': $('#carrierInfo_mobile').html(),
                'storename': $('#carrierInfo .storename').html(),
                'address': $('#carrierInfo .address').html()
            } : '',
            'remark': $("#remark").val(),
            'officcode': $("#officcode").val(),
            'deduct': ($("#deductcredit").length > 0 && $("#deductcredit").prop('checked')) ? 1 : 0,
            'deduct2': ($("#deductcredit2").length > 0 && $("#deductcredit2").prop('checked')) ? 1 : 0,
            'contype': modal.params.contype,
            'couponid': modal.params.couponid,
            'wxid': modal.params.wxid,
            'wxcardid': modal.params.wxcardid,
            'wxcode': modal.params.wxcode,
            'invoicename': $('#invoicename').val(),
            'submit': true,
            'token': token,
            'packageid': modal.params.packageid,
            'fromquick': modal.params.fromquick
        };
        FoxUI.loader.show('mini');
        core.json('order/create/submit', data, function(ret) {
            $this.removeAttr('stop', 1);
            if (ret.status == 0) {
                FoxUI.loader.hide();
                FoxUI.toast.show(ret.result.message);
                return
            }
            if (ret.status == -1) {
                FoxUI.loader.hide();
                FoxUI.alert(ret.result.message);
                return
            }
            location.href = core.getUrl('order/pay', {
                id: ret.result.orderid
            })
        }, false, true)
    };
    modal.initaddress = function(params) {
        var reqParams = ['foxui.picker'];
        if (modal.params.new_area) {
            reqParams = ['foxui.picker', 'foxui.citydatanew']
        }
        require(reqParams, function() {
            $('#areas').cityPicker({
                title: '请选择所在城市',
                new_area: modal.params.new_area,
                address_street: modal.params.address_street,
                onClose: function(self) {
                    var datavalue = $('#areas').attr('data-value');
                    var codes = datavalue.split(' ');
                    if (modal.params.new_area && modal.params.address_street) {
                        $('#areas').attr('data-datavalue', datavalue);
                        var city_code = codes[1];
                        var area_code = codes[2];
                        city_code = city_code + '';
                        area_code = area_code + '';
                        var data = modal.loadStreetData(city_code, area_code);
                        var street = $('<input type="text" id="street"  name="street" data-value="" value="" placeholder="所在街道"  class="fui-input" readonly=""/>');
                        var parents = $('#street').closest('.fui-cell-info');
                        $('#street').remove();
                        parents.append(street);
                        street.cityPicker({
                            title: '请选择所在街道',
                            street: 1,
                            data: data,
                            onClose: function(self) {
                                var streetdatavalue = $('#street').attr('data-value');
                                $('#street').attr('data-datavalue', streetdatavalue)
                            }
                        })
                    }
                }
            });
            if (modal.params.new_area && modal.params.address_street) {
                var datavalue = $('#areas').attr('data-value');
                if (datavalue) {
                    var codes = datavalue.split(' ');
                    var city_code = codes[1];
                    var area_code = codes[2];
                    var data = modal.loadStreetData(city_code, area_code);
                    $('#street').cityPicker({
                        title: '请选择所在街道',
                        street: 1,
                        data: data
                    })
                }
            }
        });
        $(document).on('click', '#btn-submit', function() {
            if ($(this).attr('submit')) {
                return
            }
            if ($('#realname').isEmpty()) {
                FoxUI.toast.show("请填写收件人");
                return
            }
            var jingwai = /(境外地区)+/.test($('#areas').val());
            var taiwan = /(台湾)+/.test($('#areas').val());
            var aomen = /(澳门)+/.test($('#areas').val());
            var xianggang = /(香港)+/.test($('#areas').val());
            if (jingwai || taiwan || aomen || xianggang) {
                if ($('#mobile').isEmpty()) {
                    FoxUI.toast.show("请填写手机号码");
                    return
                }
            } else {
                if (!$('#mobile').isMobile()) {
                    FoxUI.toast.show("请填写正确手机号码");
                    return
                }
            } if ($('#areas').isEmpty()) {
                FoxUI.toast.show("请填写所在地区");
                return
            }
            if ($('#addresss').isEmpty()) {
                FoxUI.toast.show("请填写详细地址");
                return
            }
            $('#btn-submit').html('正在处理...').attr('submit', 1);
            window.editAddressData = {
                realname: $('#realname').val(),
                mobile: $('#mobile').val(),
                address: $('#addresss').val(),
                areas: $('#areas').val(),
                street: $('#street').val(),
                streetdatavalue: $('#street').attr('data-datavalue'),
                datavalue: $('#areas').attr('data-datavalue'),
                isdefault: $('#isdefault').is(':checked') ? 1 : 0
            };
            core.json('member/address/submit', {
                id: $('#addressid').val(),
                addressdata: window.editAddressData
            }, function(json) {
                $('#btn-submit').html('保存地址').removeAttr('submit');
                window.editAddressData.id = json.result.addressid;
                if (json.status == 1) {
                    FoxUI.toast.show('保存成功!');
                    $("#addaddress").css("display", "none");
                    location.reload()
                } else {
                    FoxUI.toast.show(json.result.message)
                }
            }, true, true)
        })
    };
    modal.loadStreetData = function(city_code, area_code) {
        var left = city_code.substring(0, 2);
        var xmlUrl = '/public/static/js/dist/area/list/' + left + '/' + city_code + '.xml';
        var xmlCityDoc = modal.loadXmlFile(xmlUrl);
        var CityList = xmlCityDoc.childNodes[0].getElementsByTagName("county");
        var data = [];
        if (CityList.length > 0) {
            for (var i = 0; i < CityList.length; i++) {
                var county = CityList[i];
                var county_code = county.getAttribute("code");
                if (county_code == area_code) {
                    var streetlist = county.getElementsByTagName("street");
                    for (var m = 0; m < streetlist.length; m++) {
                        var street = streetlist[m];
                        data.push({
                            "text": street.getAttribute('name'),
                            "value": street.getAttribute('code'),
                            "children": []
                        })
                    }
                }
            }
        }
        return data
    };
    modal.loadXmlFile = function(xmlFile) {
        var xmlDom = null;
        if (window.ActiveXObject) {
            xmlDom = new ActiveXObject("Microsoft.XMLDOM");
            xmlDom.async = false;
            xmlDom.load(xmlFile) || xmlDom.loadXML(xmlFile)
        } else if (document.implementation && document.implementation.createDocument) {
            var xmlhttp = new window.XMLHttpRequest();
            xmlhttp.open("GET", xmlFile, false);
            xmlhttp.send(null);
            xmlDom = xmlhttp.responseXML
        } else {
            xmlDom = null
        }
        return xmlDom
    };
    return modal
});