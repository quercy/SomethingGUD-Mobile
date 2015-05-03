/**
 * Created by reidsavage on 4/29/15.
 */

$(document).ready(function() {
    var menu = '<div data-role="panel" id="menu-left" data-display="push" data-theme="a" data-position="left"> <ul data-role="listview"> <li><a href="#browse" data-rel="close">Browse Items</a></li> <li><a href="#cart" data-rel="close">Shopping cart</a></li> <li><a href="#orders" data-rel="close">Orders</a></li> <li><a href="#account" data-rel="close">Account Information</a></li> </ul> </div>';
    var $sign_in_form = $("#sign-in-form");
    var $register_form = $("#register-form");
    setTimeout(function () {   window.scrollTo(0, 1); }, 1000); // supposed to hide safart navbar

    $(document).one('pagebeforecreate', function() {
        $.mobile.pageContainer.prepend(menu);
        $("#menu-left").panel().enhanceWithin();
    });

    //$sign_in_form.on('login', function() {
    //    $("#item-grid").append('<div class="ui-block-b"> <div class="ui-body ui-body-d"> Block 2</div> </div>');
    //});


    $sign_in_form.on('login', function() {
    });

    var BrowseController = (function() {
        var _this = this;
        var $grid = $("#item-grid");
        var $date_display = $("#display-date");
        var shopping_date = new Date(Date.now());
        var $change_date_link = $("#change-date");
        var $date_pick_div = $("#pick-date");
        var number_of_columns = 3;

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
            $grid.empty();
            var products = {};
            var grid_html = "";
            if(category == undefined) {
                products = Model.getAllProducts();
            }
            else {
                Model.getProductsForCategory(category);
            }

            for(var i = 0; i < products.length; i++) {
                var letter = String.fromCharCode(i%number_of_columns + 97); // repeats abc, etc - for the ui-block class
                grid_html += '<div class="ui-block-'+letter+'"><div class="ui-body ui-body-d"> Block 1</div></div>';
            }

            $grid.append(grid_html);

        };
        constructor();

        // Public methods
        return {
            reconstructGrid : function(){browseUpdate();}
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
            var try_session = getCookie("session");
            console.log("session cookie is " + try_session);
            if(try_session == undefined || try_session == "") {
                $.mobile.navigate("#landing");
            }
            else {
                session_key = try_session;
                $.mobile.navigate("#browse");
            }
        };

        var getCookie = function(name) {
            var value = "; " + document.cookie;
            var parts = value.split("; " + name + "=");
            if (parts.length == 2) return parts.pop().split(";").shift();
        };

        constructor();

        // Public methods
        return {
            setKey : function(key) {
                console.log("setting key to " + key);
                session_key = key;
                document.cookie = "session=" + key;
            },
            getKey : function() {
                return session_key;
            },
            getAllProducts : function() {

            },
            getProductsForCategory : function() {

            }
        }
    }());


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
