define(['core', 'tpl'], function(core, tpl) {
    var modal = {
        params: {}
    };
    modal.init = function(id, is_ladder, more_spec) {
        modal.params.id = id;
        modal.params.more_spec = more_spec;
        $("a.btn-single").bind("click", function() {
            core.json('mobile/groups/goodsCheck', {
                'id': modal.params.id,
                type: 'single'
            }, function(postjson) {
                if (postjson.status == 1 && postjson.result.specArr.length > 0) {
                    $(".fui-modal").css("display", "block");
                    $(".layer").css("display", "block");
                    var specArr = postjson.result.specArr;
                    var str = '';
                    $.each(specArr, function(index, obj) {
                        str += "<div class='title'>" + obj.title + "</div>", str += "<div class='select'>";
                        $.each(obj.item, function(index, itemobj) {
                            str += "  <div class='btn btn-default btn-sm nav spec-item' data-specs='" + itemobj.id + "'>" + itemobj.title + "</div>"
                        });
                        str += "   </div>"
                    });
                    $(".spec").html(str);
                    var spec_id = new Array();
                    $(".select").on('click', 'div', function() {
                        $(this).addClass('active').siblings('.spec-item').removeClass('active');
                        spec_id = [];
                        $.each($(".option .active"), function(k, v) {
                            spec_id.push($(v).data('specs'))
                        });
                        if (spec_id.length > 0) {
                            core.json('mobilegroups/get_option', {
                                'spec_id': spec_id
                            }, function(spec_json) {
                                $(".subtitle").text(spec_json.result.data.title);
                                $(".price").text(spec_json.result.data.single_price);
                                $(".option_id").text(spec_json.result.data.goods_option_id);
                                $(".stock").text(spec_json.result.data.stock)
                            }, true, true)
                        }
                    });
                    $("div.sure").bind("click", function() {
                        var option_id = $(".option_id").text();
                        var stock = $(".stock").html();
                        if (stock == 0) {
                            FoxUI.toast.show('库存不足')
                        }
                        location.href = core.getUrl('mobile/groups/confirm', {
                            id: modal.params.id,
                            type: 'single',
                            options_id: option_id,
                            more_spec: modal.params.more_spec
                        });
                        return
                    })
                } else if (postjson.status == 1) {
                    location.href = core.getUrl('mobile/groups/confirm', {
                        id: modal.params.id,
                        type: 'single'
                    })
                } else {
                    FoxUI.toast.show(postjson.result.message)
                }
            }, true, true)
        });
        $("a.btn-groups").bind("click", function() {
            core.json('mobile/groups/goodsCheck', {
                'id': modal.params.id,
                type: 'groups',
                is_ladder: modal.params.is_ladder
            }, function(postjson) {
                if (postjson.status == 1 && postjson.result.ladder.length > 0) {
                    $(".chosenum").css("display", "block");
                    $(".layer").css("display", "block");
                    var ladderarr = postjson.result.ladder;
                    var str = '';
                    $.each(ladderarr, function(index, obj) {
                        str += "<div class='' data-ladder='" + obj.id + "'  data-price='" + obj.ladder_price + "'>" + obj.ladder_num + "人团</div>"
                    });
                    $(".num").on('click', 'div', function() {
                        $(this).addClass('active').siblings('div').removeClass('active');
                        $(".laddernum").html('￥' + $("div .active").data('price'))
                    });
                    $(".num").html(str);
                    $("div.btn-jieti").bind("click", function() {
                        var ladder = $(".active").data("ladder");
                        if (ladder == undefined) {
                            FoxUI.toast.show('请选择拼团人数');
                            return
                        }
                        modal.params.is_ladder = is_ladder;
                        location.href = core.getUrl('mobile/groups/confirm', {
                            id: modal.params.id,
                            type: 'groups',
                            is_ladder: modal.params.is_ladder,
                            ladder_id: ladder,
                            heads: 1
                        });
                        return
                    });
                    return
                } else if (postjson.status == 1 && postjson.result.specArr.length > 0) {
                    $(".fui-modal").css("display", "block");
                    $(".layer").css("display", "block");
                    var specArr = postjson.result.specArr;
                    var str = '';
                    $.each(specArr, function(index, obj) {
                        str += "<div class='title'>" + obj.title + "</div>", str += "<div class='select'>";
                        $.each(obj.item, function(index, itemobj) {
                            str += "  <div class='btn btn-default btn-sm nav spec-item' data-specs='" + itemobj.id + "'>" + itemobj.title + "</div>"
                        });
                        str += "   </div>"
                    });
                    $(".spec").html(str);
                    var spec_id = new Array();
                    $(".select").on('click', 'div', function() {
                        $(this).addClass('active').siblings('.spec-item').removeClass('active');
                        spec_id = [];
                        $.each($(".option .active"), function(k, v) {
                            spec_id.push($(v).data('specs'))
                        });
                        if (spec_id.length > 0) {
                            core.json('mobile/groups/get_option', {
                                'spec_id': spec_id
                            }, function(spec_json) {
                                $(".subtitle").text(spec_json.result.data.title);
                                $(".price").text(spec_json.result.data.price);
                                $(".option_id").text(spec_json.result.data.goods_option_id);
                                $(".stock").text(spec_json.result.data.stock)
                            }, true, true)
                        }
                    });
                    $("div.sure").bind("click", function() {
                        var option_id = $(".option_id").html();
                        var stock = $(".stock").text();
                        if (stock == 0) {
                            FoxUI.toast.show('库存不足');
                            return
                        }
                        if (spec_id.length < specArr.length) {
                            FoxUI.toast.show('请选择所有规格');
                            return
                        }
                        location.href = core.getUrl('mobile/groups/confirm', {
                            id: modal.params.id,
                            type: 'groups',
                            options_id: option_id,
                            more_spec: modal.params.more_spec,
                            heads: 1
                        });
                        return
                    })
                } else if (postjson.status == 1) {
                    location.href = core.getUrl('mobile/groups/confirm', {
                        id: modal.params.id,
                        type: 'groups',
                        heads: 1
                    });
                    return
                } else {
                    FoxUI.toast.show(postjson.result.message)
                }
            }, true, true)
        });
        $("a.btn-fightgroups").bind("click", function() {
            core.json('mobile/groups/goodsCheck', {
                'id': modal.params.id,
                type: 'groups',
                fightgroups: 1
            }, function(postjson) {
                if (postjson.status == 1 && postjson.result.ladder.length > 0) {
                    $(".chosenum").css("display", "block");
                    $(".layer").css("display", "block");
                    var ladderarr = postjson.result.ladder;
                    var str = '';
                    $.each(ladderarr, function(index, obj) {
                        if (obj.order_num > 0) {
                            str += "<div class='' data-ladder='" + obj.id + "'  data-price='" + obj.ladder_price + "'>" + obj.ladder_num + "人团</div>"
                        } else {}
                    });
                    $(".num").on('click', 'div', function() {
                        $(this).addClass('active').siblings('div').removeClass('active');
                        $(".laddernum").html($("div .active").data('price') + '元')
                    });
                    $(".num").html(str);
                    var z = $(".num").children().length;
                    if (z == 0) {
                        $(".num").html("暂时无团");
                        $("div.btn-jieti").css("display", "none");
                        $(".laddernum").html("")
                    }
                    $("div.btn-jieti").bind("click", function() {
                        var ladder = $(".active").data("ladder");
                        if (ladder == undefined) {
                            FoxUI.toast.show('请选择拼团人数');
                            return
                        }
                        modal.params.is_ladder = is_ladder;
                        location.href = core.getUrl('mobile/groups/fightGroups', {
                            id: modal.params.id,
                            is_ladder: modal.params.is_ladder,
                            ladder_id: ladder
                        });
                        return
                    })
                } else if (postjson.status == 1 && postjson.result.specArr.length > 0) {
                    $(".fui-modal").css("display", "block");
                    $(".layer").css("display", "block");
                    var specArr = postjson.result.specArr;
                    var str = '';
                    $.each(specArr, function(index, obj) {
                        str += "<div class='title'>" + obj.title + "</div>", str += "<div class='select'>";
                        $.each(obj.item, function(index, itemobj) {
                            str += "  <div class='btn btn-default btn-sm nav spec-item' data-specs='" + itemobj.id + "'>" + itemobj.title + "</div>"
                        });
                        str += "   </div>"
                    });
                    $(".spec").html(str);
                    var spec_id = new Array();
                    $(".select").on('click', 'div', function() {
                        $(this).addClass('active').siblings('.spec-item').removeClass('active');
                        spec_id = [];
                        $.each($(".active"), function(k, v) {
                            spec_id.push($(v).data('specs'))
                        });
                        if (spec_id.length > 0) {
                            core.json('mobile/groups/get_option', {
                                'spec_id': spec_id
                            }, function(spec_json) {
                                $(".subtitle").text(spec_json.result.data.title);
                                $(".price").text(spec_json.result.data.price);
                                $(".option_id").text(spec_json.result.data.goods_option_id)
                            }, true, true)
                        }
                    });
                    $("div.sure").bind("click", function() {
                        if (spec_id.length < specArr.length) {
                            FoxUI.toast.show('请选择所有规格');
                            return
                        }
                        var option_id = $(".option_id").html();
                        location.href = core.getUrl('mobile/groups/fightGroups', {
                            id: modal.params.id,
                            type: 'groups',
                            options_id: option_id,
                            more_spec: modal.params.more_spec
                        });
                        return
                    })
                } else if (postjson.status == 1) {
                    location.href = core.getUrl('mobile/groups/fightGroups', {
                        id: modal.params.id,
                        type: 'groups'
                    });
                    return
                } else {
                    FoxUI.toast.show(postjson.result.message)
                }
            }, true, true)
        });
        $(".icon-guanbi1").bind("click", function() {
            $(".fui-modal").css("display", "none");
            $(".layer").css("display", "none");
            $(".chosenum").css("display", "none");
            $(".laddernum").html("价格");
            $("div.btn-jieti").css("display", "block")
        })
    };
    return modal
});