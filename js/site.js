/**
 * Created by reidsavage on 4/29/15.
 */

$(document).ready(function() {
    var myLazyLoad = new LazyLoad();
    var menu = '<div data-role="panel" id="menu-left" data-display="push" data-theme="a" data-position="left"> <ul data-role="listview"> <li><a href="#browse" data-rel="close">Browse Items</a></li> <li><a href="#cart" data-rel="close">Shopping cart</a></li> <li><a href="#orders" data-rel="close">Orders</a></li> <li><a href="#account" data-rel="close">Account Information</a></li> </ul> </div>';
    var $sign_in_form = $("#sign-in-form");
    var $register_form = $("#register-form");
    setTimeout(function () {   window.scrollTo(0, 1); }, 1000); // supposed to hide safart navbar

    $(document).one('pagebeforecreate', function() {
        $.mobile.pageContainer.prepend(menu);
        $("#menu-left").panel().enhanceWithin();
    });

    $( document ).on( "pagechange", function( event, ui ) { // okay
        if(event.currentTarget.URL.split("/").slice(-1) == "#browse") {
            BrowseController.reconstructGrid();
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
                // @todo close dialog
                // @todo add to cart
            });
            $("#categories-list li a").click(function() {
                $("#filter-grid-input").val($(this).text()).trigger('keyup');
            });
        };

        var update_date = function(date_string) {
            if(!(date_string == '' || date_string == undefined)) {
                shopping_date = new Date(date_string);
            }
            $date_display.text(shopping_date.toDateString());
        };

        var browseUpdate = function (category) {
            if(category == undefined) {
                if(products == undefined) {
                    Model.getAllProducts().success(function (data) {
                        products = JSON.parse(data);
                        updateGrid();
                    }).always(function () {

                    });
                }
                else {
                    // no need to reinitialize
                }
            }
            else {

                //products = Model.getProductsForCategory(category, updateGrid(products));
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

        var constructor = function() {

        };

        var refreshCart = function() {
            console.log(items);
            items.sort();
            var len = items.length;
            // My attempt to solve duplicate items: not working
            //for(var i = 0; i < items.length; i++) {
            //    console.log(items);
            //    //console.log('i is ' + i);
            //    if(i+1 < len) { // if there's an item past this one
            //        //console.log('i is less than ' + len);
            //        while(items[i][0] == items[i+1][0]) {
            //            //console.log('matching');
            //            items[i][1] = String(parseInt(items[i][1]) + parseInt(items[i+1][1]));
            //            //console.log('items is ' + items.slice(0,i));
            //            console.log(items);
            //            var items_new = items.slice(0,i);
            //            console.log(items);
            //            len--;
            //            if(i + 1 < len) {
            //                console.log(items);
            //                items_new = items.concat(items.slice(i+1));
            //            }
            //            items = items_new;
            //        }
            //
            //    }
            //}
        };

        // public methods
        return {
            addToCart : function(item_id, quantity) {
                items.push([item_id, quantity]);
                refreshCart();
            }
        }
    }());

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
                        console.log('success: ' + data);
                        Model.setKey(data);

                        $.mobile.navigate('#browse');
                    }
                    else {
                        alert("bad login");
                    }


                }).
                error( function(data) {
                    console.log('error ' + data);
                }).
                always( function() {
                    $.mobile.loading('hide', {
                    });
                    //var data = {};
                    //data.session = "1234";
                    //Model.setKey(data.session);
                    //$sign_in_form.trigger('login');
                    //console.log('always');
                    //setTimeout(function() {
                    //    $.mobile.loading('hide', {
                    //    });
                    //    $.mobile.navigate('#browse');
                    //}, 600);
                });
        };

        constructor();

        // Public methods
        return {
            login : function(_data) {
                submitLogin(_data);
            }
        }
    }());

    var Model = (function() {
        var _this = this;
        var session_key;

        var constructor = function () {
            var try_session = readCookie("session");
            console.log("session cookie is " + try_session);
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
                console.log("setting key to " + key);
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
                email: $("#register-form-email").val(),
                password : $("#register-form-password").val(),
                'first-name' : $("#register-form-first-name").val(),
                'last-name' : $("#register-form-last-name").val(),
                'zip' : $("#register-form-zip").val()
            };
            $.ajax(
                {
                    beforeSend: function() { $.mobile.loading('show'); }, //Show spinner
                    url: '',
                    dataType: 'json',
                    data: submit_data
                }).
                success( function(data) {
                    // sign in
                    //$sign_in_form.trigger('login');
                    console.log('success' + data);
                }).
                error( function(data) {
                    console.log('error ' + data);
                }).
                always( function() {
                    $sign_in_form.trigger('login');
                    console.log('always');
                    setTimeout(function() {
                        $.mobile.loading('hide', {
                        });
                        $.mobile.navigate('#browse');
                    }, 600);
                });
        }
    });
});
