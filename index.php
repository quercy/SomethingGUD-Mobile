<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="apple-mobile-web-app-capable" content="yes" />

        <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">
        <link rel="stylesheet" href="css/jquery.mobile-1.4.5.css">
        <link rel="stylesheet" href="css/themes/default/jquery.mobile-1.4.5.min.css">
        <link rel="stylesheet" href="css/site.css">
        <!--<link rel="stylesheet" href="css/jquery-ui.min.css">-->
        <!--<link rel="stylesheet" href="css/jquery-ui.structure.min.css">-->
        <link rel="stylesheet" href="css/jquery.mobile.datepicker.css">
        <link rel="stylesheet" href="css/jquery.mobile.datepicker.theme.css">
        <!--<link rel="stylesheet" href="css/jquery-ui.theme.min.css">-->
        <!--<link rel="stylesheet" href="http://demos.jquerymobile.com/1.4.0/theme-classic/theme-classic.css" />-->


        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.mobile-1.4.5.min.js"></script>
        <script src="js/jquery-ui.min.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <script src="js/jquery.mobile.datepicker.js"></script>
        <script src="js/site.js"></script>

    </head>
    <body>

    <div data-role="page" data-quicklinks="true" id="landing">
        <div role="main" class="ui-content">
            <div id="welcome-logo-wrapper">
                <img src="img/logo_transparent.png" id="welcome-logo"/>
                <a href="#sign-in" class="ui-btn ui-shadow">Sign In</a>
                <a href="#sign-in-fb" class="ui-btn ui-shadow ui-btn-c">Facebook Sign In</a>
                <a href="#register" class="ui-btn ui-shadow">Register</a>
            </div>


        </div>
    </div>

    <div data-role="page" data-close-btn="right" data-dialog="true" id="sign-in">
        <div data-role="header">
            <h1>Sign In</h1>
        </div>

        <div role="main" class="ui-content">
            <form  id="sign-in-form" method="post">
            <!--<div class="ui-field-contain">-->
                <div data-role="fieldcontainer">
                <label for="sign-in-form-email">Email:</label>
                <input type="text" name="email" id="sign-in-form-email" value="">
                </div>
                <label for="sign-in-form-password">Password:</label>
                <input type="password" name="password" id="sign-in-form-password" value="">
            <!--</div>-->
                <button aria-disabled="false" class="ui-btn-hidden" type="submit" data-theme="b" name="submit" value="submit-value">Submit</button>
            </form>
        </div>
    </div>

    <div data-role="page" data-close-btn="right" data-dialog="true" id="register">
        <div data-role="header">
            <h1>Register</h1>
        </div>

        <div role="main" class="ui-content">
            <form data-ajax="false" id="register-form" method="post">
                <!--<div class="ui-field-contain">-->
                <div data-role="fieldcontainer">
                    <label for="register-form-email">Email:</label>
                    <input type="text" name="email" id="register-form-email" value="">
                </div>
                <div data-role="fieldcontainer">
                    <label for="register-form-password-1">Password:</label>
                    <input type="password" name="password-1" id="register-form-password-1" value="">
                </div>
                <div data-role="fieldcontainer">
                    <label for="register-form-password-2">Repeat Password:</label>
                    <input type="password" name="password-2" id="register-form-password-2" value="">
                </div>
                <div data-role="fieldcontainer">
                    <label for="register-form-first-name">First Name:</label>
                    <input type="text" name="first-name" id="register-form-first-name" value="">
                </div>
                <div data-role="fieldcontainer">
                    <label for="register-form-last-name">Last Name:</label>
                    <input type="text" name="last-name" id="register-form-last-name" value="">
                </div>
                <div data-role="fieldcontainer">
                    <label for="register-form-zip">Zip:</label>
                    <input type="number" name="zip" id="register-form-zip" value="">
                </div>
                <!--</div>-->
                <button aria-disabled="false" class="ui-btn-hidden" type="submit" data-theme="b" name="submit" value="submit-value">Submit</button>
            </form>
        </div>
    </div>

    <div data-role="page" data-quicklinks="true" id="browse" data-position="left" data-display="reveal">


        <div data-role="header" style="overflow:hidden;">
            <a href="#menu-left" data-icon="home" class="ui-btn-left">Menu</a>
            <h1>Header</h1>
        </div><!-- /header -->
        <div role="main" class="ui-content">
            <div id="item-view-header">
                <div>Shopping for <h2 id="display-date"></h2><a id="change-date" href="#">[Change Date]</a><br/>
                    <div id="pick-date" class="ui-content" style="margin: 0 auto;">
                        <div data-role="date" data-inline="true"></div>
                    </div>
                </div>

            </div>

            <div class="ui-grid-b ui-responsive" data-filter="true" id="item-grid">
                <div class="ui-block-a grid-item">
                    <div class="ui-body ui-body-d"> Block 1</div>
                </div>
                <div class="ui-block-b grid-item">
                    <div class="ui-body ui-body-d"> Block 2</div>
                </div>
                <div class="ui-block-c grid-item">
                    <div class="ui-body ui-body-d"> Block 3</div>
                </div>

                <div class="ui-block-a grid-item">
                    <div class="ui-body ui-body-d"> Block 4</div>
                </div>
                <div class="ui-block-b grid-item">
                    <div class="ui-body ui-body-d"> Block 5</div>
                </div>
                <div class="ui-block-c grid-item">
                    <div class="ui-body ui-body-d"> Block 6</div>
                </div>

            </div>
        </div>
        <div data-role="footer">

        </div>
    </div>

    <div data-role="page" data-quicklinks="true" id="cart" data-position="left" data-display="reveal">


        <div data-role="header" style="overflow:hidden;">
            <a href="#menu-left" data-icon="home" class="ui-btn-left">Menu</a>
            <h1>Header</h1>
        </div><!-- /header -->
        <div role="main" class="ui-content">
            <table data-role="table" id="cart-table" data-mode="reflow" class="ui-responsive">
                  <thead>
                    <tr>
                          <th data-priority="1">Quantity</th>
                          <th data-priority="persist">Title</th>
                          <th data-priority="2">Price</th>
                        </tr>
                  </thead>
                  <tbody>
                    <tr>
                          <th>1</th>
                          <td>Fish</td>
                          <td>1941</td>

                        </tr>
                    <tr>
                          <th>1</th>
                          <td>Cheese</td>
                          <td>1942</td>
                        </tr>
                    <tr>
                          <th>3</th>
                          <td>Chicken</td>
                          <td>1972</td>
                        </tr>
                  </tbody>
            </table>
            <br/>
            <a href="#checkout" class="ui-btn ui-shadow ui-btn-corner-all" id="checkout-button">Checkout</a>
        </div>
        <div data-role="footer">

        </div>
    </div>

    <div data-role="page" data-quicklinks="true" id="account" data-position="left" data-display="reveal">
        <div data-role="header" style="overflow:hidden;">
            <a href="#menu-left" data-icon="home" class="ui-btn-left">Menu</a>
            <h1>Header</h1>
        </div><!-- /header -->
        <div role="main" class="ui-content">
        Account account account
            <a href="#" class="ui-btn ui-shadow ui-btn-corner-all" id="logout-button">Logout</a>
        </div>
        <div data-role="footer">

        </div>
    </div>

    <div data-role="page" data-quicklinks="true" id="orders" data-position="left" data-display="reveal">


        <div data-role="header" style="overflow:hidden;">
            <a href="#menu-left" data-icon="home" class="ui-btn-left">Menu</a>
            <h1>Header</h1>
        </div><!-- /header -->
        <div role="main" class="ui-content">
            Orders orders orders
        </div>
        <div data-role="footer">

        </div>
    </div>

    </body>
</html>
