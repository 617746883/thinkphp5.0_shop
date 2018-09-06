define(['core', 'tpl'], function(core, tpl) {
    var modal = {};
    modal.initList = function() {
        if (typeof(window.editAddressData) !== 'undefined') {
            var item = $(".address-item[data-addressid='" + window.editAddressData.id + "']");
            if (item.length <= '0') {
                var first = $(".address-item");
                if (first.length > '0') {
                    var html = tpl('tpl_address_item', {
                        address: window.editAddressData
                    });
                    $(first).first().before(html)
                } else {
                    window.editAddressData.isdefault = 1;
                    var html = tpl('tpl_address_item', {
                        address: window.editAddressData
                    });
                    $('.content-empty').hide();
                    $('.fui-content').html(html)
                }
            } else {
                var address = window.editAddressData;
                item.find('.realname').html(address.realname);
                item.find('.mobile').html(address.mobile);
                item.find('.address').html(address.areas.replace(/ /ig, '') + ' ' + address.address)
            }
            delete window.editAddressData
        }
        $('*[data-toggle=delete]').unbind('click').click(function() {
            var item = $(this).closest('.address-item');
            var id = item.data('addressid');
            if (!id) {
                id = $(this).data("addressid");
                var i = id
            }
            FoxUI.confirm('删除后无法恢复, 确认要删除吗 ?', function() {
                core.json('member/address/delete', {
                    id: id
                }, function(ret) {
                    if (ret.status == 1) {
                        if (ret.result.defaultid) {
                            $("[data-addressid='" + ret.result.defaultid + "']").find(':radio').prop('checked', true)
                        }
                        item.remove();
                        setTimeout(function() {
                            if ($(".address-item").length <= 0) {
                                $('.content-empty').show()
                            }
                        }, 100);
                        return
                    }
                    FoxUI.toast.show(ret.result.message)
                }, true, true);
                if (id == i) {
                    window.history.back()
                }
            })
        });
        $(document).on('click', '[data-toggle=setdefault]', function() {
            var item = $(this).closest('.address-item');
            var id = item.data('addressid');
            if (!id) {
                id = $(this).data("addressid")
            }
            core.json('member/address/setdefault', {
                id: id
            }, function(ret) {
                if (ret.status == 1) {
                    $('.fui-content').prepend(item);
                    FoxUI.toast.show("设置默认地址成功");
                    return
                }
                FoxUI.toast.show(ret.result.message)
            }, true, true)
        })
    };
    modal.initPost = function(params) {
        var reqParams = ['foxui.picker'];
        if (params.new_area) {
            reqParams = ['foxui.picker', 'foxui.citydatanew']
        }
        require(reqParams, function() {
            $('#areas').cityPicker({
                title: '请选择所在城市',
                new_area: params.new_area,
                address_street: params.address_street,
                onClose: function(self) {
                    var datavalue = $('#areas').attr('data-value');
                    var codes = datavalue.split(' ');
                    if (params.new_area && params.address_street) {
                        var city_code = codes[1];
                        var area_code = codes[2];
                        city_code = city_code + '';
                        area_code = area_code + '';
                        var data = loadStreetData(city_code, area_code);
                        var street = $('<input type="text" id="street"  name="street" data-value="" value="" placeholder="所在街道"  class="fui-input" readonly=""/>');
                        var parents = $('#street').closest('.fui-cell-info');
                        $('#street').remove();
                        parents.append(street);
                        street.cityPicker({
                            title: '请选择所在街道',
                            street: 1,
                            data: data
                        })
                    }
                }
            });
            if (params.new_area && params.address_street) {
                var datavalue = $('#areas').attr('data-value');
                if (datavalue) {
                    var codes = datavalue.split(' ');
                    var city_code = codes[1];
                    var area_code = codes[2];
                    var data = loadStreetData(city_code, area_code);
                    $('#street').cityPicker({
                        title: '请选择所在街道',
                        street: 1,
                        data: data
                    })
                }
            }
        });
        $(document).on('click', '#btn-address', function() {
            wx.openAddress({
                success: function(res) {
                    $("#realname").val(res.userName);
                    $('#mobile').val(res.telNumber);
                    $('#address').val(res.detailInfo);
                    $('#areas').val(res.provinceName + " " + res.cityName + " " + res.countryName)
                }
            })
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
            if ($('#address').isEmpty()) {
                FoxUI.toast.show("请填写详细地址");
                return
            }
            $('#btn-submit').html('正在处理...').attr('submit', 1);
            window.editAddressData = {
                realname: $('#realname').val(),
                mobile: $('#mobile').val(),
                address: $('#address').val(),
                areas: $('#areas').val(),
                street: $('#street').val(),
                streetdatavalue: $('#street').attr('data-value'),
                datavalue: $('#areas').attr('data-value'),
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
                    history.back()
                } else {
                    FoxUI.toast.show(json.result.message)
                }
            }, true, true)
        })
    };
    modal.initSelector = function() {
        if (typeof(window.editAddressData) !== 'undefined') {
            var address = window.editAddressData;
            var item = $(".address-item[data-addressid='" + address.id + "']", $('#page-address-selector'));
            if (item.length > 0) {
                item.find('.realname').html(address.realname);
                item.find('.mobile').html(address.mobile);
                item.find('.address').html(address.areas.replace(/ /ig, '') + ' ' + address.address)
            } else {
                var html = tpl('tpl_address_item', {
                    address: window.editAddressData
                });
                $('.fui-list-group').prepend(html)
            }
            delete window.editAddressData
        }
        var selectedAddressID = false;
        if (typeof(window.selectedAddressData) !== 'undefined') {
            selectedAddressID = window.selectedAddressData.id;
            delete window.selectedAddressData
        } else if (typeof(window.orderSelectedAddressID) !== 'undefined') {
            selectedAddressID = window.orderSelectedAddressID
        }
        if (selectedAddressID) {
            $(".address-item[data-addressid='" + selectedAddressID + "'] .fui-radio", $('#page-address-selector')).prop('checked', true)
        }
        $('.address-item .fui-list-media,.address-item .fui-list-inner', $('#page-address-selector')).click(function() {
            var $this = $(this).closest('.address-item');
            window.selectedAddressData = {
                'realname': $this.find('.realname').html(),
                'address': $this.find('.address').html(),
                'mobile': $this.find('.mobile').html(),
                'id': $this.data('addressid')
            };
            history.back()
        })
    };
    modal.loadSelectorData = function() {
        core.json('member/address/selector/get_list', {}, function() {})
    };
    window.loadXmlFile = function(xmlFile) {
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
    window.loadStreetData = function(city_code, area_code) {
        var left = city_code.substring(0, 2);
        var xmlUrl = '/public/static/js/dist/area/list/' + left + '/' + city_code + '.xml';
        var xmlCityDoc = loadXmlFile(xmlUrl);
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
    return modal
});