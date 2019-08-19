define(['core', 'tpl'], function(core, tpl) {
    var modal = {};
    modal.init = function(fromDetail) {
        if (typeof fromDetail === undefined) {
            fromDetail = true
        }
        modal.fromDetail = fromDetail;
        $('.order-cancel select').unbind('change').change(function() {
            var orderid = $(this).data('orderid');
            var val = $(this).val();
            if (val == '') {
                return
            }
            FoxUI.confirm('确认要取消订单吗?', '提示', function() {
                modal.cancel(orderid, val, true)
            })
        });
        $('.order-delete').unbind('click').click(function() {
            var orderid = $(this).data('orderid');
            FoxUI.confirm('确认要删除订单吗?', '提示', function() {
                modal.delete(orderid)
            })
        });
        $('.order-finish').unbind('click').click(function() {
            var orderid = $(this).data('orderid');
            FoxUI.confirm('确认已收到货了吗?', '提示', function() {
                modal.finish(orderid)
            })
        });
        $('.order-verify').unbind('click').click(function() {
            var orderid = $(this).data('orderid');
            modal.verify(orderid)
        })
    };
    modal.cancel = function(id, remark) {
        core.json('mobile/groups/ordercancel', {
            id: id,
            remark: remark
        }, function(pay_json) {
            if (pay_json.status == 1) {
                if (modal.fromDetail) {
                    location.href = core.getUrl('groups/orders')
                } else {
                    $(".order-item[data-orderid='" + id + "']").remove()
                }
                return
            }
            FoxUI.toast.show(pay_json.result)
        }, true, true)
    };
    modal.delete = function(id) {
        core.json('mobile/groups/orderdelete', {
            id: id
        }, function(pay_json) {
            if (pay_json.status == 1) {
                if (modal.fromDetail) {
                    location.href = core.getUrl('groups/orders')
                } else {
                    $(".order-item[data-orderid='" + id + "']").remove()
                }
                return
            }
            FoxUI.toast.show(pay_json.result)
        }, true, true)
    };
    modal.finish = function(id) {
        core.json('mobile/groups/orderfinish', {
            id: id
        }, function(pay_json) {
            if (pay_json.status == 1) {
                location.reload();
                return
            }
            FoxUI.toast.show(pay_json.result)
        }, true, true)
    };
    modal.verify = function(orderid) {
        container = new FoxUIModal({
            content: $(".order-verify-hidden").html(),
            extraClass: "popup-modal",
            maskClick: function() {
                container.close()
            }
        });
        container.show();
        $('.verify-pop').find('.close').unbind('click').click(function() {
            container.close()
        });
        core.json('mobile/groups/verifyqrcode', {
            id: orderid
        }, function(ret) {
            if (ret.status == 0) {
                FoxUI.alert('生成出错，请刷新重试!');
                return
            }
            var time = +new Date();
            $('.verify-pop').find('.qrimg').attr('src', ret.result.url + "?timestamp=" + time).show()
        }, false, true)
    };
    return modal
});