/**
 * Created by reidsavage on 4/29/15.
 */

$(document).ready(function() {
    var menu = '<div data-role="panel" id="menu-left" data-display="push" data-theme="a" data-position="left"> <ul data-role="listview"> <li><a href="#browse" data-rel="close">Browse Items</a></li> <li><a href="#cart" data-rel="close">Shopping cart</a></li> <li><a href="#" data-rel="close">Orders</a></li> <li><a href="#" data-rel="close">Account Information</a></li> </ul> </div>';
    var $sign_in_form = $("#sign-in-form");
    var $register_form = $("#register-form");
    setTimeout(function () {   window.scrollTo(0, 1); }, 1000);
    //var submitLogin = function(data, callback) {
    //    console.log(data);
    //    callback();
    //    return true;
    //};
    //
    //$(document).one('pagebeforecreate', function () {
    //    $.mobile.pageContainer.prepend(menu);
    //    $("#menu-left").panel();
    //});

    $(document).on('pagebeforecreate', function() {
        //alert("pagebeforecreate");
        $.mobile.pageContainer.prepend(menu);
        $("#menu-left").panel().enhanceWithin();
    });

    //$sign_in_form.on('login', function() {
    //    $("#item-grid").append('<div class="ui-block-b"> <div class="ui-body ui-body-d"> Block 2</div> </div>');
    //});


    $sign_in_form.on('login', function() {
        BrowseController();
    });

    var BrowseController = (function() {
        var _this = this;
        var $grid = $("#item-grid");
        var $date_display = $("#display-date");
        var shopping_date = new Date(Date.now());
        var $change_date_link = $("#change-date");
        var $date_pick_div = $("#pick-date");

        // constructor -- this isn't the JS OO way to do it, but I'll figure it out later
        var constructor = function () {
            //$date_display.data('','');
            console.log(shopping_date.toDateString());
            //update_date(Date.now());
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
                console.log("trying to set date to " + date_string);
            }
            $date_display.text(shopping_date.toDateString());
        };

        var tableUpdate = function (category) {
            // clear old items
            // AJAX in new items

            return {
                // return the AJAX data
            }

        };
        constructor();

        // Public methods
        return {
            getTableData : function(){return tableUpdate();}
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
            $.ajax(
                {
                    beforeSend: function() { $.mobile.loading('show'); }, //Show spinner
                    url: '',
                    dataType: 'json',
                    data: submit_data
                }).
                success(function(data) {
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
