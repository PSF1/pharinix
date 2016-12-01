<!DOCTYPE html>
<html lang="{$user_language}">
    <head>
        <meta charset="{$page_charset}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{$page_title}</title>
        <script src="{$base_url}usr/jquery/1.11.1/jquery.min.js" type="text/javascript"></script>
        <script src="{$base_url}usr/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <link href="{$base_url}usr/bootstrap/css/bootstrap.min.css" rel="stylesheet"></link>
        <link href="{$base_url}etc/templates/pharinix/general.css" rel="stylesheet"></link>
        <link rel="shortcut icon" href="{$base_url}etc/templates/pharinix/favicon.ico"></link>
        <script src="{$base_url}?command=getBaseJS&interface=nothing"></script>
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <style>{$customcss}</style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row"  id="menu">
                <div id="mainMenu" type="nav" class="col-md-12">
                    {$block.mainMenu}
                </div>
            </div>
            <div class="row"  id="content">
                <div id="colLeft" class="col-md-3 topSpace">
                    {$block.colLeft}
                </div>
                <div id="colRight" class="col-md-9 topSpace">
                    {$block.colRight}
                </div>
            </div>
            <div class="row"  id="footer">
                <div id="col1" type="nav" class="col-sm-12">
                    <div class="row"  id="foot1">
                        <div id="footCopy" class="col-md-12">
                            {$block.footCopy}
                            <h4>Pharinix Copyright © 2016 Pedro Pelaez</h4>
                            <h5>GNU Software</h5></div>
                    </div>
                </div>
            </div>
        </div>
        <script>{$customscripts}</script>
    </body>
</html>