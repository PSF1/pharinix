<?php

/*
 * Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
 * Sources https://github.com/PSF1/pharinix
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/*
 * Transform a XML page to HTML
 * Parameters:
 * page = XML page to convert
 */
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

if (!class_exists("commandMenuInlineToHTML")) {

    class commandMenuInlineToHTML extends driverCommand {

        public static function runMe(&$params, $debug = true) {
?>
<style>
body {
    padding-top: 61px;
}
</style>
<div id="top">
    <nav class="navbar navbar-fixed-top navbar-default ">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo CMS_DEFAULT_URL_BASE;?>">Pharinix</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <!-- DEFAULT LEFT -->
          <ul class="nav navbar-nav">
            <li><a href="<?php echo CMS_DEFAULT_URL_BASE;?>">
                    <span class="glyphicon glyphicon-home" aria-hidden="true"></span>
                    &nbsp;<?php __e('Home'); ?>
                </a></li>
          </ul>
          <!-- LEFT -->
    <!--      <form class="navbar-form navbar-left" role="search">
            <div class="form-group">
              <input type="text" class="form-control" placeholder="Search">
            </div>
            <button type="submit" class="btn btn-default">Submit</button>
          </form>-->

          <!-- RIGHT -->
          <ul class="nav navbar-nav navbar-right">
            <li>
                <?php
                if (driverUser::isSudoed()) {
                    ?>
                <form class="navbar-form navbar-left" role="form" action="<?php echo CMS_DEFAULT_URL_BASE;?>" method="post" enctype="application/x-www-form-urlencoded">
                    <input type="hidden" name="command" value="sudo"/>
                    <input type="hidden" name="user" value=""/>
                    <input type="hidden" name="interface" value="goHome"/>
                    <button type="submit" class="btn btn-danger"><?php __e('Exit superuser'); ?></button>
                </form>
                <?php
                } elseif (driverUser::haveSudoersGroup()) {
                    ?>
                <form class="navbar-form navbar-left" role="form" action="<?php echo CMS_DEFAULT_URL_BASE;?>" method="post" enctype="application/x-www-form-urlencoded">
                    <input type="hidden" name="command" value="sudo"/>
                    <input type="hidden" name="user" value="root@localhost"/>
                    <input type="hidden" name="interface" value="goHome"/>
                    <button type="submit" class="btn btn-link"><?php __e('Get superuser'); ?></button>
                </form>
                <?php
                }
                if (!driverUser::isLoged()) {
                    ?>
                    <form class="navbar-form navbar-left" role="form" action="<?php echo CMS_DEFAULT_URL_BASE;?>" method="post" enctype="application/x-www-form-urlencoded">
                        <input type="hidden" name="command" value="startSession"/>
                        <input type="hidden" name="interface" value="goHome"/>
                        <div class="form-group">
                            <input type="text" class="form-control" name="user" placeholder="<?php __e('mail');?>">
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="pass" placeholder="<?php __e('password'); ?>">
                        </div>
                        <button type="submit" class="btn btn-default"><?php __e('Login'); ?></button>
                    </form>
                <?php
                } else {
                    ?>
                <form class="navbar-form navbar-left" role="form" action="<?php echo CMS_DEFAULT_URL_BASE;?>" method="post" enctype="application/x-www-form-urlencoded">
                    <input type="hidden" name="command" value="endSession"/>
                    <input type="hidden" name="interface" value="goHome"/>
                    <button type="submit" class="btn btn-link"><?php __e('Logout'); ?></button>
                </form>
                <?php
                }
                ?>
            </li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                  <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                  &nbsp;<?php __e('Help'); ?> <span class="caret"></span>
              </a>
              <ul class="dropdown-menu" role="menu">
                <li><a href="<?php echo CMS_DEFAULT_URL_BASE; ?>help/console"><?php __e('Console'); ?></a></li>
                <li><a href="<?php echo CMS_DEFAULT_URL_BASE; ?>help/command"><?php __e('Commands'); ?></a></li>
                <li><a href="<?php echo CMS_DEFAULT_URL_BASE; ?>help/formatters"><?php __e('Formatters demo'); ?></a></li>
                <li class="divider"></li>
                <li><a href="<?php echo CMS_DEFAULT_URL_BASE; ?>help/icons"><?php __e('Bootstrap icons'); ?></a></li>
                <li><a href="<?php echo CMS_DEFAULT_URL_BASE; ?>help/tabs_panels"><?php __e('Tabs & Panels'); ?></a></li>
                <li><a href="<?php echo CMS_DEFAULT_URL_BASE; ?>help/ui_elements"><?php __e('UI Elements');?></a></li>
                <li><a href="<?php echo CMS_DEFAULT_URL_BASE; ?>help/forms"><?php __e('Forms'); ?></a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                  <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>
                  &nbsp;<?php __e('Types'); ?> <span class="caret"></span>
              </a>
              <ul class="dropdown-menu" role="menu">
                <li role="presentation" class="dropdown-header"><?php __e('Visible types'); ?></li>
                <?php
                    $types = driverCommand::run("getNodeTypeList");
                    foreach($types as $type) {
                        echo '<li><a href="'.CMS_DEFAULT_URL_BASE.'node/type/'.$type.'">'.$type.'</a></li>';
                    }
                ?>
                <li role="presentation" class="dropdown-header"><?php __e('Fixed types');?></li>
                <li><a href="<?php echo CMS_DEFAULT_URL_BASE; ?>node/type/user">
                        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                        &nbsp;user
                    </a></li>
                <li><a href="<?php echo CMS_DEFAULT_URL_BASE; ?>node/type/group">
                        <span class="glyphicon glyphicon-tags" aria-hidden="true"></span>
                        &nbsp;group
                    </a></li>
                <li><a href="<?php echo CMS_DEFAULT_URL_BASE; ?>node/type/modules">
                        <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>
                        &nbsp;modules
                    </a></li>
              </ul>
            </li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>
</div>
<?php
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getAccessFlags() {
            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __('Transform a menu to HTML inline navigation bar'),
                "parameters" => array(
                    "menu" => __("Menu to convert.")
                    ),
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "menu" => "string"
                    ), 
                    "response" => array(),
                )
            );
        }

    }

}
return new commandMenuInlineToHTML();
