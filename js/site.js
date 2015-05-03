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

    //$sign_in_form.on('login', function() {
    //    BrowseController.reconstructGrid();
    //});
    //$( window ).on( "navigate", function(event,data) {
    //    console.log(event);
    //    console.log(data);
    //});

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
                grid_html += '<div class="ui-block-'+letter+'"><div class="ui-body ui-body-d grid-item"><div>'+products[i]['product_name']+'</div><img src="'+products[i]['image_thumb_url'] + '"></div></div>';
            }
            $grid.append(grid_html).hide().fadeIn();
        };

        constructor();

        // Public methods
        return {
            reconstructGrid : function(){browseUpdate();}
            //updateGrid : function() {updateGrid();}
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
                    url: '',
                    dataType: 'json',
                    data: submit_data
                }).
                success(function(data) {
                    // sign in
                    $sign_in_form.trigger('login');
                    // if server returns a session id, set it as the current cookie
                    data = JSON.parse(data);
                    console.log('success: ' + data);

                    Model.setKey(data.session);
                    //document.cookie = "session=" + data.session;
                }).
                error( function(data) {
                    console.log('error ' + data);
                }).
                always( function() {
                    var data = {};
                    data.session = "1234";
                    Model.setKey(data.session);
                    $sign_in_form.trigger('login');
                    console.log('always');
                    setTimeout(function() {
                        $.mobile.loading('hide', {
                        });
                        $.mobile.navigate('#browse');
                    }, 600);
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
                //$.mobile.navigate("#browse"); ?????
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
