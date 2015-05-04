/**
 * Created by reidsavage on 4/29/15.
 */

$(document).ready(function() {
    //var myLazyLoad = new LazyLoad();
    var menu = '<div data-role="panel" id="menu-left" data-display="push" data-theme="a" data-position="left"> <ul data-role="listview"> <li><a href="#browse" data-rel="close">Browse Items</a></li> <li><a href="#cart" data-rel="close">Shopping cart</a></li> <li><a href="#account" data-rel="close">Account Information</a></li> </ul> </div>';
    var $sign_in_form = $("#sign-in-form");
    var $register_form = $("#register-form");
    //setTimeout(function () {   window.scrollTo(0, 1); }, 1000); // supposed to hide safarty navbar

    $(document).one('pagebeforecreate', function() {
        $.mobile.pageContainer.prepend(menu);
        $("#menu-left").panel().enhanceWithin();
    });

    $( document ).on( "pagechange", function( event, ui ) { // okay
        if(event.currentTarget.URL.split("/").slice(-1) == "#browse") {
            BrowseController.reconstructGrid();
            CartController();
        }

        if(event.currentTarget.URL.split("/").slice(-1) == "#cart") {
        }
    });

    var BrowseController = (function() {
        var _this = this;
        var $grid = $("#item-grid");
        var $date_display = $("#display-date");
        var shopping_date = new Date(Date.now());
        var $change_date_link = $("#change-date");
        var $date_pick_div = $("#pick-date");
        var $detail = $("#item-detail");
        var number_of_columns = 3;
        var products;

        var constructor = function () {
            //console.log(shopping_date.toDateString());
            update_date();
            $("#filter-grid-input").val("").trigger('keyup');
            $date_pick_div.date({
                onSelect: function(dateText) {
                    update_date($date_pick_div.date("getDate"));
                    $date_pick_div.slideUp();
                }
            }).hide();
            $change_date_link.on('click', function(e) {
                e.preventDefault();
                $date_pick_div.slideDown();
            });
            $("#item-detail-add-to-cart").click(function(e){
                e.preventDefault();
                var quantity = $("#quantity").val();
                var product_id = $detail.data('product-id');
                CartController.addToCart(product_id,quantity);
                $.mobile.back();
            });
            $.get('/api/products/categories').success(function(data) {
                data = JSON.parse(data);
                var html = '';
                for (var i = 0; i < data.length; i++) {
                    html += '<li><a class="ui-btn ui-btn-icon-right ui-icon-carat-r" href="#">' + data[i]['category_display_name'] + '</a></li>';
                }
                $("#categories-list").append(html).trigger('create').enhanceWithin().find('a').click(function () {
                    $("#filter-grid-input").val($(this).text()).trigger('keyup');
                });
            });
        };

        var update_date = function(date_string) {
            if(!(date_string == '' || date_string == undefined)) {
                shopping_date = new Date(date_string);
            }
            $date_display.text(shopping_date.toDateString());
        };

        var browseUpdate = function (category) {
                if(products == undefined) {
                    Model.getAllProducts().success(function (data) {
                        products = JSON.parse(data);
                        updateGrid();
                    }).always(function () {

                    });
                }
        };

        var updateGrid = function() {
            $grid.empty();
            var grid_html = "";
            for(var i = 0; i < products.length; i++) {
                var letter = String.fromCharCode(i%number_of_columns + 97); // repeats abc, etc - for the ui-block class
                grid_html += '<div class="ui-block-'+letter+'"><div class="ui-body ui-body-d grid-item">';
                grid_html += '<div id="item-detail-hidden-tags" class="ui-hidden-accessible">'+products[i]['tags'].join(" ")+'</div>';
                grid_html += '<h3>'+products[i]['product_name']+'</h3>';
                grid_html += '<a href="#item-detail" data-item-id="'+i+'" class="ui-shadow ui-btn ui-corner-all ui-btn-inline" data-transition="pop"><img src="'+products[i]['image_thumb_url'] + '"></a></div></div>';

            }

            $grid.append(grid_html).hide().fadeIn().find('a').click(function(evt) {
                var index = this.getAttribute('data-item-id');

                $detail.find('#item-detail-title').html('<h2>'+products[index]['product_name']+'</h2>');
                $detail.find('#item-detail-image').html('<img src="'+products[index]['image_full_url']+'"></img>');
                $detail.find('#item-detail-description').html(products[index]['product_description']);
                $detail.find('#item-detail-price').html('$'+products[index]['product_price'] + ' / ' + products[index]['product_price_per']);
                $detail.data('product-id',products[index]['product_id']);
                $detail.find('#quantity').val(1);
            });
        };

        constructor();

        // Public methods
        return {
            reconstructGrid : function(){browseUpdate();}
        }
    }());

    var CartController = (function() {
        var items = [];
        var $table = $("#cart-table");

        var constructor = function() {
            pullCart();
        };

        var initializeRemoveClickEvents = function() {
            $(".remove-cart-item").one('click',function(e) {
                e.preventDefault();
                var numb = $(this).parent().parent().fadeOut().attr('data-product-id');
                var new_ary = [];
                for(var i = 0; i < items.length; i++) {
                    if(items[i][0] != numb) {
                        new_ary.push(items[i]);
                    }
                }
                //console.log(new_ary);
                items = new_ary;
                pushCart();
            });
        };

        var refreshCart = function() {
            $table.find('tbody').empty();
            enforceConsistency();
            for(var i = 0; i < items.length; i++ ) {
                var index;
                $.get('/api/products/' + items[i][0]).success(function(data) {
                    data = JSON.parse(data);
                    $table.find('tbody')
                        .append('<tr data-product-id="'+data['product_id']+'"><td class="qty"></td><td>'+data['product_name']+'</td><td>'+data['product_price'] + '</td><td><a href="#" class="remove-cart-item">Remove</a></td></tr>');
                }).always(function() {
                    $qty = $table.find('tr:last .qty');
                    for(var i = 0; i < items.length; i++) {
                        if($qty.closest('tr').attr('data-product-id') == items[i][0]) {
                            $qty.html(items[i][1]);
                            //alert(items[index][1]);
                        }

                    }
                    initializeRemoveClickEvents();
                });
            }
        };

        var enforceConsistency = function() {
            //items.sort();
            //console.log(items);
            //for(var i = 0; i < items.length; i++) {
            //    if(i < items.length - 1) {
            //        while (items[i][0] == items[i + 1][0]) {
            //            items[i][0] = String(parseInt(items[i][0]) + parseInt(items[i + 1][0]));
            //            items.splice(i + 1, 1);
            //        }
            //    }
            //}
        };

        var pullCart = function() {
            $.get('/api/cart').success(function(data) {
                data=JSON.parse(data);
                //console.log(data);
                var new_ary = [];
                if(data != null) {
                    for (var i = 0; i < data.length; i++) {
                        new_ary[i] = [String(data[i]['product_id']), String(data[i]['quantity'])];
                    }
                    items = new_ary;

                }
                else {
                    items = [];
                }
                refreshCart();
                //console.log(new_ary);

            });
        };

        var pushCart = function() {
            var post_data = {'cart_data' : {}};
            for(var i = 0; i < items.length; i++) {
                post_data['cart_data'][i] = {};
                post_data['cart_data'][i]['product_id'] = items[i][0];
                post_data['cart_data'][i]['quantity'] = items[i][1];
            }
            $.post('/api/cart', post_data).success(function() {
                //pullCart();
            });

        };
        constructor();
        // public methods
        return {
            addToCart : function(item_id, quantity) {
                var added = false;
                for(var i = 0; i < items.length; i++) {
                    if(items[i][0] == String(item_id)) {
                        items[i][1] = String(parseInt(items[i][1]) + parseInt(quantity));
                        added = true;
                    }
                }
                if(added == false) {
                    items.push([item_id, quantity]);
                }
                pushCart();
                refreshCart();
            }
        }

    });

    var LoginController = (function() {
        var _this = this;

        var constructor = function () {
        };

        var submitLogin = function(submit_data) {
            $.ajax(
                {
                    beforeSend: function() { $.mobile.loading('show'); }, //Show spinner
                    url: '/api/authenticate/login',
                    dataType: 'json',
                    method: 'post',
                    data: submit_data
                }).
                success(function(data) {
                    if(data != false) {
                        $sign_in_form.trigger('login');
                        //console.log('success: ' + data);
                        Model.setKey(data);

                        $.mobile.navigate('#browse');
                    }
                    else {
                        // @todo unvalidate
                        alert("bad login");
                    }


                }).
                error( function(data) {
                    //console.log('error ' + data);
                }).
                always( function() {
                    $.mobile.loading('hide', {
                    });
                });
        };

        var submitRegister = function(data) {
            $.ajax(
                {
                    beforeSend: function() { $.mobile.loading('show'); }, //Show spinner
                    url: '/api/users',
                    dataType: 'json',
                    method: 'post',
                    data: data
                }).
                success( function(data) {
                    //data=JSON.parse(data);
                    Model.setKey(data);
                    console.log('success' + data);
                    $.mobile.navigate('#browse');
                }).
                error( function(data) {
                    console.log(data);
                    console.log('error ' + data);
                }).
                always( function() {

                    //$sign_in_form.trigger('login');
                    $.mobile.loading('hide', {
                    });

                });
        };

        constructor();

        // Public methods
        return {
            login : function(_data) {
                submitLogin(_data);
            },
            register : function(_data) {
                submitRegister(_data);
            }

        }
    }());

    var Model = (function() {
        var _this = this;
        var session_key;

        var constructor = function () {
            var try_session = readCookie("session");
            if(try_session == undefined || try_session == "") {
                $.mobile.navigate("#landing");
            }
            else {
                session_key = try_session;
                $.mobile.navigate("#browse");
            }
        };

        function createCookie(name,value,days) {
            if (days) {
                var date = new Date();
                date.setTime(date.getTime()+(days*24*60*60*1000));
                var expires = "; expires="+date.toGMTString();
            }
            else var expires = "";
            document.cookie = name+"="+value+expires+"; path=/";
        }

        function readCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for(var i=0;i < ca.length;i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            }
            return null;
        }

        function eraseCookie(name) {
            createCookie(name,"",-1);
        }

        constructor();

        // Public methods
        return {
            setKey : function(key) {
                //console.log("setting key to " + key);
                session_key = key;
                createCookie('session', session_key, 7);
            },
            getKey : function() {
                return session_key;
            },
            getAllProducts : function(callback) {
                return $.get('/api/products');
            },
            getProductsForCategory : function(category, callback) {

            },
            logout : function() {
                eraseCookie("session");
                $.mobile.navigate("#landing");
            }
        }
    }());

    $("#logout-button").click(function(evt) {
        evt.preventDefault();
        Model.logout();
    });

    $sign_in_form.validate({
        rules: {
            'email': {
                required : true
            },
            'password' : {
                required: true
            }
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent().parent());
        },
        submitHandler: function(form) {
            var submit_data = {
                username: $("#sign-in-form-email").val(),
                password : $("#sign-in-form-password").val()
            };
            LoginController.login(submit_data);
        }
    });

    $register_form.validate({
        rules: {
            'email': {
                required : true
            },
            'zip': {
                required:true,
                digits: true
            },
            'password-1' : {
                required:true
            },
            'password-2' : {
                required:true,
                equalTo:"#register-form-password-1"
            },
            'first-name' : {
                required : true
            },
            'last-name' : {
                required:true
            }
        },
        messages : {
            'password-2' : {
                equalTo: 'The passwords must match.'
            }
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent().parent());
        },
        submitHandler: function(form) {
            var submit_data = {
                'user_email': $("#register-form-email").val(),
                'password' : $("#register-form-password-1").val(),
                'first_name' : $("#register-form-first-name").val(),
                'last_name' : $("#register-form-last-name").val(),
                'zip' : $("#register-form-zip").val()
            };
            console.log(submit_data);
            LoginController.register(submit_data);
        }
    });
});
