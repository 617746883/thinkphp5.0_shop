define(['core', 'tpl'], function(core, tpl) {
    var modal = {
        params: {}
    };
    modal.init = function(params) {
        var defaults = {
            orderid: 0,
            wechat: {
                success: false
            },
            cash: {
                success: false
            },
            alipay: {
                success: false
            },
        };
        modal.params = $.extend(defaults, params || {});
        $('.pay-btn').unbind('click').click(function() {
            var btn = $(this);
            core.json('fission/order/pay/check', {
                id: modal.params.orderid
            }, function(pay_json) {
                if (pay_json.status == 1) {
                    modal.pay(btn)
                } else {
                    FoxUI.toast.show(pay_json.result.message)
                }
            }, false, true)
        });
        if (modal.params.wechat.jie == 1) {
            $('.pay-btn[data-type="wechat"]').click()
        }
    };
    modal.pay = function(btn) {
        var type = btn.data('type') || '';
        if (type == '') {
            return
        }
        if (btn.attr('stop')) {
            return
        }
        btn.attr('stop', 1);
        if (type == 'wechat') {
            if (core.ish5app()) {
                appPay('wechat', null, null, true);
                return
            }
            modal.payWechat(btn)
        } else if (type == 'alipay') {
            if (core.ish5app()) {
                appPay('alipay', null, null, true);
                return
            }
            modal.payAlipay(btn)
        } else if (type == 'credit') {
            FoxUI.confirm('确认要支付吗?', '提醒', function() {
                modal.complete(btn, type)
            }, function() {
                btn.removeAttr('stop')
            })
        } else {
            modal.complete(btn, type)
        }
    };
    modal.payWechat = function(btn) {
        var wechat = modal.params.wechat;
        if (!wechat.success) {
            return
        }
        if (wechat.weixin) {
            function  onBridgeReady() {
                WeixinJSBridge.invoke('getBrandWCPayRequest', {
                    'appId': wechat.appid ? wechat.appid : wechat.appId,
                    'timeStamp': wechat.timeStamp,
                    'nonceStr': wechat.nonceStr,
                    'package': wechat.package,
                    'signType': wechat.signType,
                    'paySign': wechat.paySign
                }, function(res) {
                    if (res.err_msg == 'get_brand_wcpay_request:ok') {
                        modal.complete(btn, 'wechat')
                    } else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
                        FoxUI.toast.show('取消支付')
                    } else {
                        FoxUI.toast.show(res.err_msg)
                    }
                    btn.removeAttr('stop')
                })
            }
            if  (typeof  WeixinJSBridge  ==  "undefined") {
                if ( document.addEventListener ) {
                    document.addEventListener('WeixinJSBridgeReady',  onBridgeReady,  false)
                } else  if  (document.attachEvent) {
                    document.attachEvent('WeixinJSBridgeReady',  onBridgeReady);
                    document.attachEvent('onWeixinJSBridgeReady',  onBridgeReady)
                }
            } else {
                onBridgeReady()
            }
        }
        if (wechat.weixin_jie || wechat.jie == 1) {
            modal.payWechatJie(btn, wechat)
        }
    };
    modal.payWechatJie = function(btn, wechat) {
        var img = core.getUrl('index/qr', {
            url: wechat.code_url
        });
        $('#qrmoney').text(modal.params.money);
        $('.order-weixinpay-hidden').show();
        $('#btnWeixinJieCancel').unbind('click').click(function() {
            btn.removeAttr('stop');
            clearInterval(settime);
            $('.order-weixinpay-hidden').hide()
        });
        var settime = setInterval(function() {
            $.getJSON(core.getUrl('fission/order/pay/orderstatus'), {
                id: modal.params.orderid
            }, function(data) {
                if (data.status >= 1) {
                    clearInterval(settime);
                    location.href = core.getUrl('fission/order/pay/success', {
                        id: modal.params.orderid
                    })
                }
            })
        }, 1000);
        $('.verify-pop').find('.close').unbind('click').click(function() {
            $('.order-weixinpay-hidden').hide();
            btn.removeAttr('stop');
            clearInterval(settime)
        });
        $('.verify-pop').find('.qrimg').attr('src', img).show()
    };
    modal.payAlipay = function(btn) {
        var alipay = modal.params.alipay;
        if (!alipay.success) {
            return
        }
        location.href = core.getUrl('order/pay_alipay', {
            orderid: modal.params.orderid,
            type: 77,
            url: alipay.url
        })
    };
    modal.complete = function(btn, type) {
        var peerpay = $('#peerpay').text();
        var peerpaymessage = $('#peerpaymessage').val();
        FoxUI.loader.show('mini');
        setTimeout(function() {
            core.json('fission/order/pay/complete', {
                id: modal.params.orderid,
                ordersn: modal.params.ordersn,
                type: type,
                peerpay: peerpay,
                peerpaymessage: peerpaymessage
            }, function(pay_json) {
                if (pay_json.status == 1) {
                    location.href = core.getUrl('fission/order/pay/success', {
                        id: modal.params.orderid,
                        result: pay_json.result.result
                    });
                    return
                }
                FoxUI.loader.hide();
                btn.removeAttr('stop');
                FoxUI.toast.show(pay_json.result.message)
            }, false, true)
        }, 1000)
    };
    return modal
});