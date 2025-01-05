var mychart;
var gets = {}; window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str,key,value) { gets[key] = (isset(value) ? value : true); });
var hash = window.location.hash.replace('#', '');
var paths = {};
var _ = {"temp": {}};
refresh_paths();
var directurl = false;
$(function() {
    if(isset(paths[2])) directurl = true;
    var owl = $('.owl-carousel');     owl.owlCarousel({
        loop: true,
        margin: 10,
        nav: false,
        dots: false, 
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: false,
        items: 5,
        autoHeight: true,
        margin: 0,
        touchDrag: false,
        mouseDrag: false,
        lazyLoad: false,
        responsive: {
            0: {
              items: 1
            },
            600: {
              items: 2
            },
            1000: {
              items: 3
            },
            1200: {
              items: 4
            },
            1400: {
              items: 5
            }
        }
    });
    
    $('[data-temp]').each(function() {
		_.temp[$(this).attr('data-temp')] = $(this); 		_.temp[$(this).attr('data-temp')].removeAttr('data-temp');
		$(this).remove();
	});
    $(document).on('click', '[data-func]:not(input)', function(e) {
		data_func($(this),e);
	}).on('keyup', 'input[data-func]', function(e) {
		data_func($(this),e);
    }).on('click', '.overlay', function() {
        $('.modal').remove();
        $(this).addClass("hidden");
    }).on('change', 'select[name="brand"]', function() {
        let t = $(this);
        $(".car-switch-type").find("option").remove();
        let brand_id = t.val();
        "get.brand-type".backend({brand: brand_id})             .done(function(r) {
                r = $.parseJSON(r);
                $('select[name="subtype"]').find("option").remove();
                $('select[name="type"]').append($('<option />').attr("value", "-1").html("- Válaszd ki a típust -"));
                $('select[name="subtype"]').append($('<option />').attr("value", "-1").html("- Válaszd ki a altípust -"));
                $.each(r, function(k, v) {
                    if($('.car-switch-type').find('select').find('[data-name="'+v.category_name+'"]').length < 1) {                         $('.car-switch-type').find('select').append($('<option />').attr({"data-name": v.category_name, "value": v.virtuemart_category_id}).html(v.category_name.replace(t.find("option:selected").html()+" ", "")));
                    }                 });
                                if(isset(paths[2])) {
                    $('select[name="type"]').find("option").each(function() {
                        if($(this).html() == paths[2].replace(/-/g, " ")) {
                            $('select[name="type"]').find("option[value='"+$(this).attr("value")+"']").prop("selected", true);
                            $('select[name="type"]').trigger("change");
                        }
                    });
                }
                if(!directurl) {
                    window.history.pushState('', '', '/'+t.find("option:selected").html());
                    refresh_paths();
                }
                if(directurl && isset(paths[2])) directurl = false;
            });
    }).on('change', 'select[name="type"]', function() {
        let t = $(this);
        $('select[name="subtype"]').removeAttr("disabled");
        "get.brand-subtype".backend({categId: t.val(), categName: t.find("option:selected").attr("data-name")})             .done(function(r) {
                r = $.parseJSON(r);
                if(r.msg == "hasnt_subtype") {
                    $("body").attr("data-productid", r.virtuemart_product_id);                     $('select[name="subtype"]').empty();
                    $('select[name="subtype"]').attr("disabled", true).attr("value", "-2").append($('<option />').html("- Nincsenek altípusok -"));
                    $.each(r, function(k, v) {
                        if(k == "price") {
                            $('.product-details').find('[data-field="'+k+'"]').html(formatMoney(parseInt(v)));
                        } else if(k == "img") {
                            $('.product-details').find('[data-field="'+k+'"]').html(v);
                        }
                    })
                    $('.product').removeClass('hidden');
                } else {
                    $.each(r.subtypes, function(k, v) {
                        v.category_name = v.category_name.replace(t.find("option:selected").attr("data-name")+" ", "");                         
                        $('select[name="subtype"]').append($('<option />').attr("value", v.virtuemart_category_id).html(v.category_name));                     
                    });
                } 
                if(isset(paths[3])) {
                    $('select[name="subtype"]').find("option").each(function() {
                        if($(this).html() == paths[3].replace(/-/g, " ")) {
                            $('select[name="subtype"]').find("option[value='"+$(this).attr("value")+"']").prop("selected", true);
                            $('select[name="subtype"]').trigger("change");
                        }
                    });
                }
                if(!directurl) {
                    window.history.pushState('', '', '/'+paths[1]+'/'+t.find("option:selected").html().replace(/ /g, "-"));
                    refresh_paths();
                }
                if(directurl && isset(paths[3])) directurl = false;
            })
    }).on('change', 'select[name="subtype"]', function() {
        let t = $(this);
        "get.product".backend({category: t.val()})
            .done(function(r) {
                r = $.parseJSON(r); 
                $("body").attr("data-productid", r.virtuemart_product_id);                 $.each(r, function(k, v) {
                    $('.product-details').find('[data-field="'+k+'"]').html(v);
                    if(k == "price") {
                        $('.product-details').find('[data-field="'+k+'"]').html(formatMoney(parseInt(v)));
                    }
                })
                window.history.pushState('', '', '/'+paths[1]+'/'+paths[2]+'/'+t.find("option:selected").html().replace(/ /g, "-"));
                refresh_paths();
            });
            $('.product').removeClass('hidden');
    }).on('keyup', 'input[name="zipcode"]', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if($(this).val().startsWith("3")) {
            curr_page().find('[data-func="select-shipment"]').parent().removeClass("col-md-6");
            curr_page().find('[data-func="select-shipment"]').parent().addClass("col-md-4");
            curr_page().find('.express').removeClass("hidden");
            curr_page().find('.extra-info').removeClass('hidden');
        } else {
            curr_page().find('[data-func="select-shipment"]').parent().addClass("col-md-6");
            curr_page().find('[data-func="select-shipment"]').parent().removeClass("col-md-4");
            curr_page().find('.express').addClass("hidden");
            curr_page().find('.extra-info').addClass('hidden');
        }
    }).on('change', 'input[name="same-delivery-info"]', function() {
        $(".inputs-dif-delivery-info").toggleClass("show");
    }).on('change', 'input[name="contract-request"]', function() {
        if($(this).is(":checked"))             $('.take-order').removeAttr("disabled");
        else
            $('.take-order').attr("disabled", true);
    });
        let page;
    if(!isset(paths[1]) || paths[1] == '' || paths[1] == "index.html") page = 'main';
    if(paths[1] !== "main" && paths[1] !== "cart"  && paths[1] !== "payment-success") {
        page = "main";
    }
    if(paths[1] == "cart") page = "cart";
    if(paths[1] == "payment-success") page = "payment-success";
    if(paths[1] == "payment-fail") page = "payment-fail"
    $('[data-page="'+page+'"]').removeClass("hidden");
    $('[data-page].hidden').remove();
    if(page == "main") {
        "get.brand".backend()
            .done(function(r) {                 let brands = []
                r = $.parseJSON(r);
                $.each(r, function(k, v) {
                    $('select[name="brand"]').append($('<option />').attr('value', v.virtuemart_category_id).html(v.category_name));
                });
                setTimeout(function() {
                    if(paths[1] != page) {
                        $('select[name="brand"]').find("option").each(function() {
                            if($(this).html() == paths[1]) {
                                $('select[name="brand"]').find("option[value='"+$(this).attr("value")+"']").prop("selected", true);
                                $('select[name="brand"]').trigger("change");
                            }
                        });
                    }
                }, 100);
            });
            } else if(page == "cart") { 
        "get.shipment-cost".backend()
            .done(function(r) {
                r= $.parseJSON(r);
                $.each(r, function(k, v) {
                    curr_page().find('[data-shipment-id="'+v.virtuemart_shipmentmethod_id+'"]').attr("data-cost", v.cost);
                    curr_page().find('[data-shipment-id="'+v.virtuemart_shipmentmethod_id+'"]').find("span").html(formatMoney(v.cost));                 })
            });
        "get.payment-cost".backend()
            .done(function(r) {
                r= $.parseJSON(r);
                $.each(r, function(k, v) {
                    curr_page().find('[data-payment-id="'+v.virtuemart_paymentmethod_id+'"]').attr("data-cost", v.cost);
                    curr_page().find('[data-payment-id="'+v.virtuemart_paymentmethod_id+'"]').find("span").html(v.cost == "0" ? "Ingyenes" : formatMoney(v.cost));                 })
            });
        getItems();
        $('input[name="phone"]').inputmask({
            mask: "+36 99 999 99 99",
            placeholrder: "+36 99 9999 99 99"
        });
                        } else if (page == "payment-success") {
        localStorage.removeItem("cart");
    }
});

function curr_page() {
        return $('[data-page]:not(.hidden)');
}
function data_func(t,e) {
	var func = t.attr('data-func');
	let datas = {};
	let next = true;
	if(func == "close-modal") {
        $('.modal').remove();
        $(".overlay").addClass("hidden");
        $('html').removeClass('overflow-h');
    } else if(func == "take-cart") {
        let cart = (localStorage.getItem('cart') !== null ? $.parseJSON(localStorage.getItem('cart')) : {});
        cart[$("body").attr("data-productid")] = (typeof cart[$("body").attr("data-productid")] === 'undefined' ? 1 : cart[$("body").attr("data-productid")]+1);         localStorage.setItem("cart", JSON.stringify(cart));
        console.log(localStorage.getItem("cart"));
        "in_cart".modal(function(_m) {
             $('html').addClass('overflow-h');
        });
    } else if(func == "remove.from-cart") { 		if(confirm("Biztos, hogy törölni szeretné ezt a tételt a kosárból?")) {
			let cart = $.parseJSON(localStorage.getItem("cart"));
            delete cart[t.closest(".item-card").attr("data-id")];
            localStorage.setItem("cart", JSON.stringify(cart));
			t.closest(".item-card").remove();
            getItems();
		}
	} else if(func == "select-shipment" || func == "select-payment") {
        t.parent().parent().find('.options-card').removeClass('active');
        t.addClass("active");
        getItems();
    } else if(func == "send.order") {         let invoiceData = {};
        let shippingData = {};
                $(".inputs-invoice-info").find("input").each(function() {
            if($(this).closest(".required").length > 0 && $(this).val().length < 1) {
                next = false;
                $(this).addClass("error_border");
            }
            invoiceData[$(this).attr("name")] = $(this).val();
        });
        let otherInfo = $('.other-info').find('textarea[name="other-comment"]').val();  
        if($('input[name="same-delivery-info"]').is(':checked')) {             shippingData = invoiceData;
            $(".inputs-dif-delivery-info").attr('disabled', true);
        } else {
            $(".inputs-dif-delivery-info").removeAttr('disabled');
            $(".inputs-dif-delivery-info").find("input").each(function() {
                if($(this).closest(".required").length > 0 && $(this).val().length < 1) {
                    next = false;
                    $(this).closest(".input-group").find("label").addClass("error_msg");                 }
                shippingData[$(this).attr("name")] = $(this).val();
            });
        }
              
        if(!next) alert('Minden piros csillagos mező kitöltése kötelező!'); else {
            let emailRegex = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;             if(!emailRegex.test(invoiceData.email)) {
                $('.input-group').find('label[for="email"]').html('Hibás Email Formátum! *').addClass('error_msg');
                $(this).addClass('error_border');
            }
        }
                     let phoneShip = shippingData.phone.trim();
            let phoneInv = invoiceData.phone.trim();
            if (phoneInv.startsWith("06") || phoneInv.startsWith("+36") || phoneShip.startsWith("06") || phoneShip.startsWith("+36")) {
                invoiceData.phone = phoneInv.replace(/[^+\d]/g, '');   
                shippingData.phone = phoneShip.replace(/[^+\d]/g, '');
                console.log(invoiceData.phone);
            }                         if ($('.shipping-method').find('.active').length < 1 || $('.payment-method').find('.active').length < 1) {                         alert("Nincs kiválasztva szállítási vagy fizetési mód!");             next = false;
        } 
        if(next) {
            shippingData.shipmethodid = $('.shipping-method').find('.active').attr('data-shipment-id');
            shippingData.paymethodid = $('.payment-method').find('.active').attr('data-payment-id');
            console.log(invoiceData);
            console.log(shippingData);
                        "send.order".backend({shippingData: shippingData, invoiceData: invoiceData, cart: $.parseJSON(localStorage.getItem("cart"))})
                .done(function(r) {
                    r = $.parseJSON(r);
                    console.log(r);
                    if(r.msg == "email_error") {
                        console.log('rossz mail');
                        $('input[type="email"]').addClass("error_border");
                        $(".mail-l").addClass('error_msg');
                        $(".mail-l").html("Érvénytelen E-mail cím!");
                    } else {
                        window.location.replace(r.stripe_url);
                    }
                })
        }
                                                                                                                                                                                            }
    }
$('.header').find('.cart-icon').find('.cart-item-count').html(localStorage.getItem("cart") == null ? '0' : Object.keys($.parseJSON(localStorage.getItem("cart"))).length);
function isset(_var) {
	if(typeof _var !== 'undefined') return true;
	else return false;
}
function formatMoney(amount, thousands = " ", _tofixed = 0) {
	if(typeof amount == 'undefined' || amount == '' || amount == 0 || amount == '0') {
		return '0 Ft';
	} else {
		let minus = (parseFloat(amount).toFixed(_tofixed).replace('.', ',') < 0 ? true : false);
		var i = parseFloat(amount = Math.abs(Number(amount) || 0)).toFixed(_tofixed).toString().replace('.', ',');
		var j = (i.length > 3) ? i.length % 3 : 0;
		let _ret = (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands);
		_ret = _ret.replace(',00', '').replace(/(.*?)\,([0-9]{1})0/g, '$1,$2');
		if(_ret.indexOf('.,') != -1) {
			_ret = _ret.replace(/.,/, ',');
		}
		return (minus ? '-' : '')+_ret+' Ft';
	}
}
$.fn.hasAttr = function(name) { 	return this.attr(name) !== undefined;
};
function refresh_paths() { paths = window.location.pathname.split('/'); }
console.log(paths[1]);
String.prototype.backend = function(data) {
	data = (isset(data) ? data : {});
	data['do'] = this;
	if(localStorage.getItem('token') !== null && localStorage.getItem('token') != '')
		data['token'] = localStorage.getItem('token');
	var a = $.ajax({
		type: "POST",
		url: "/app/backend/ajax.php",
		data: data
	});
	console.groupCollapsed("-> Sent POST data to %c"+this+" T="+new Date().getSeconds(), "color:#82b;");
	console.log("\r\n Data sent: ", data );
	console.groupEnd();
	(function(t){
		a.done(function(r){
			console.groupCollapsed("%c" + t + "%c's <- Raw response T="+new Date().getSeconds(), "color:#82b;", "color:#000;");
			console.log(r);
			console.groupEnd();
			if(r.match(/warning:|error:|notice:|hiba:/gi)) {
				console.error("----PHP ERROR----");
			}
		});
	})(this);
	return a;
};
String.prototype.modal = function(callback) {
	var modal = this;
	$.get("/view/modals/"+modal+".html?v="+new Date().getTime(), {do: 'load'}, function(r) {
        $(".overlay").removeClass("hidden"); 		$("body").append(r); 		$("body").find(".modal").attr("data-modal", modal); 		if(typeof callback !== 'undefined') {
			callback($(".modal[data-modal='"+modal+"']"));
		}
	});
};
function getItems() {
    let total = 0;
    if(localStorage.getItem("cart") == null || Object.keys($.parseJSON(localStorage.getItem("cart"))).length < 1) {
        curr_page().find(".content-bx").html("<p>Nincsenek termékek a kosaradban!</p>").addClass('no-items-cart');
        curr_page().find(".content-bx").append('<a href="/">Vissza a főoldalra</a>');
    } else {
        "get.items".backend({items: localStorage.getItem("cart")})
            .done(function(r) {
                r = $.parseJSON(r);
                $.each(r, function(k,v) {
                    if(curr_page().find(".items").find('[data-id="'+v.virtuemart_product_id+'"]').length < 1) {
                        let clone = _.temp['cart-item'].attr('data-id', v.virtuemart_product_id).clone();
                        clone.find('.item-data').find('[data-field="product_name"]').html(v.product_name);
                        clone.find('.item-data').find('[data-field="price"]').html(formatMoney(v.price));
                        clone.find('img').attr("src", "https://".curr_page().find('.items').append(clone));
                    }
                    total = total + v.price;
                })
                if($('.shipping-method').find('.options-card.active').length > 0) {
                    total = total + parseInt($('.shipping-method').find('.options-card.active').attr('data-cost'));
                }
                if($('.payment-method').find('.options-card.active').length > 0) {
                    total = total + parseInt($('.payment-method').find('.options-card.active').attr('data-cost'));
                }
                $('.all-price').find('span').html(formatMoney(total));
            })
    }
    }