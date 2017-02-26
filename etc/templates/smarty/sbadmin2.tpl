<!DOCTYPE html>
<html lang="{$user_language}">
    <head>
        <meta charset="{$page_charset}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>{$page_title}</title>
        
        <link href="{$base_url}usr/bootstrap/css/bootstrap.min.css" rel="stylesheet"></link>
        <link href="{$base_url}etc/templates/pharinix/general.css" rel="stylesheet"></link>
        <link rel="shortcut icon" href="{$base_url}etc/templates/pharinix/favicon.ico"></link>
        <script src="{$base_url}usr/jquery/1.11.1/jquery.min.js" type="text/javascript"></script>
        
        <!-- MetisMenu CSS -->
        <link href="{$base_url}etc/templates/smarty/sbadmin2/metisMenu/dist/metisMenu.min.css" rel="stylesheet">

        <!-- Timeline CSS -->
        <link href="{$base_url}etc/templates/smarty/sbadmin2/sbadmin2/css/timeline.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link href="{$base_url}etc/templates/smarty/sbadmin2/sbadmin2/css/sb-admin-2.css" rel="stylesheet">

        <!-- Custom Fonts -->
        <link href="{$base_url}etc/templates/smarty/sbadmin2/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <style>{$customcss}</style>
    </head>
    <body>
        <div id="wrapper">

            <!-- Navigation -->
            <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    {cmd command="mnuRenderBrand" menu="main"}
                </div>
                <!-- /.navbar-header -->
                {cmd command="mnuRenderList" menu="main"}
                <!-- /.navbar-top-links -->

                <div class="navbar-default sidebar" role="navigation">
                    <div class="sidebar-nav navbar-collapse">
                        {if isset($block.colLeftMenu)}{$block.colLeftMenu}{/if}
                    </div>
                    <div class="sidebar-nav col-md-12">
                        {if isset($block.colLeft)}{$block.colLeft}{/if}
                    </div>
                    <!-- /.sidebar-collapse -->
                </div>
                <!-- /.navbar-static-side -->
            </nav>

            <div id="page-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        {if isset($block.colRight)}{$block.colRight}{/if}
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /#page-wrapper -->

        </div>
        <!-- /#wrapper -->
        <!-- #footer -->
        <div class="row"  id="footer">
            <div id="col1" type="nav" class="col-sm-12">
                <div class="row"  id="foot1">
                    <div id="footCopy" class="col-md-12">
                        {if isset($block.footCopy)}{$block.footCopy}{/if}
                        <h6>{cmd command="translate" str="Admin theme based on"} <a href="https://startbootstrap.com/template-overviews/sb-admin-2/" target="_blank">SB Admin 2</a></h6>
                    </div>
                </div>
            </div>
        </div>
        <!-- /#footer -->
        <script src="{$base_url}usr/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="{$base_url}?command=getBaseJS&interface=nothing"></script>
        
        <!-- Metis Menu Plugin JavaScript -->
        <script src="{$base_url}etc/templates/smarty/sbadmin2/metisMenu/dist/metisMenu.min.js"></script>

        <!-- Custom Theme JavaScript -->
        <script src="{$base_url}etc/templates/smarty/sbadmin2/sbadmin2/js/sb-admin-2.js"></script>
        <script>{$customscripts}</script>
    </body>
</html>