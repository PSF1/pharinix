<?php

/*
 * Pharinix Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
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
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandInstall")) {
    class commandInstall extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'root' => '',
                'rootpass' => '',
                'dbhost' => '',
                'dbschema' => '',
                'dbuser' => '',
                'dbpass' => '',
            ), $params);

            $cfg = driverConfig::getCFG();
            $isInstalled = $cfg->getSection('[core]')->getAsBoolean('installed');
            if ($_POST['interface'] == 'echoHtml') {
                if ($isInstalled) {
                    // Show configuration without passwords.
                    echo '<div class="row">';
                        echo '<div class="col-md-12">';
                        echo '<legend>'.__('Pharinix is just installed, the actual configuration is').'</legend>';
                        echo '<table class="table table-striped">';
                            echo '<tbody>';

                                echo '<tr>';
                                    echo '<th>'.__('Safe mode root email').'</th>';
                                    echo '<td>'.$cfg->getSection('[safe_mode]')->get('user').'</td>';
                                echo '</tr>';

                                echo '<tr>';
                                    echo '<th>'.__('MySQL host').'</th>';
                                    echo '<td>'.$cfg->getSection('[mysql]')->get('MYSQL_HOST').'</td>';
                                echo '</tr>';

                                echo '<tr>';
                                    echo '<th>'.__('MySQL schema').'</th>';
                                    echo '<td>'.$cfg->getSection('[mysql]')->get('MYSQL_DBNAME').'</td>';
                                echo '</tr>';

                                echo '<tr>';
                                    echo '<th>'.__('MySQL user').'</th>';
                                    echo '<td>'.$cfg->getSection('[mysql]')->get('MYSQL_USER').'</td>';
                                echo '</tr>';

                                echo '<tr>';
                                    echo '<th>'.__('Web title').'</th>';
                                    echo '<td>'.$cfg->getSection('[core]')->get('CMS_TITLE').'</td>';
                                echo '</tr>';

                                echo '<tr>';
                                    echo '<th>'.__('Default base URL').'</th>';
                                    echo '<td>'.$cfg->getSection('[core]')->get('CMS_DEFAULT_URL_BASE').'</td>';
                                echo '</tr>';

                            echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    echo '</div>';
                } else {
                    // Show install interface
                    include('etc/templates/pharinix/install.php');
                }
            } else {
                // Do the installation and refresh
                // Verifications
                $msg = '';
                if ($params['root'] == '') {
                    $msg .= __('Root mail is required.')."\n";
                }
                if ($params['rootpass'] == '') {
                    $msg .= __('Root password is required.')."\n";
                }
                if ($params['dbhost'] == '') {
                    $msg .= __('MySQL host is required.')."\n";
                }
                if ($params['dbschema'] == '') {
                    $msg .= __('MySQL database name is required.')."\n";
                }
                if ($params['dbuser'] == '') {
                    $msg .= __('MySQL user is required.')."\n";
                }
                if (!empty($msg)) {
                    return array('ok' => false, 'msg' => $msg);
                }
                // Verify database connection
                $resp = driverCommand::run('queryDB', array(
                    'host' => $params['dbhost'],
                    'name' => $params['dbschema'],
                    'user' => $params['dbuser'],
                    'pass' => $params['dbpass'],
                    'sql' => 'show tables',
                ));
                if (isset($resp['ok']) && $resp['ok'] === false) {
                    return array('ok' => false, 'msg' => __('Connection error'));
                }
                // Load default database
                $sqlinstall = file_get_contents('files/pharinix.sql');
                $sqlinstall = str_replace('aaaaa976@gmail.com', $params['root'], $sqlinstall);
                $newpass = driverCommand::run('obfPass', array(
                    'pass' => $params['rootpass'],
                ));
                $sqlinstall = str_replace('c7442538de880b6772dd3731f440c695', $newpass['obf'], $sqlinstall);
                // http://stackoverflow.com/a/15025975
                $queries = preg_split("/;+(?=([^'|^\\\']*['|\\\'][^'|^\\\']*['|\\\'])*[^'|^\\\']*[^'|^\\\']$)/", $sqlinstall);
                $msg = '';
                foreach ($queries as $query) {
                    if (strlen(trim($query)) > 0) {
                        if (driverTools::str_start(trim($query), 'CREATE DATABASE') === false && driverTools::str_start(trim($query), 'USE ') === false) {
                            $resp = driverCommand::run('queryDB', array(
                                        'host' => $params['dbhost'],
                                        'name' => $params['dbschema'],
                                        'user' => $params['dbuser'],
                                        'pass' => $params['dbpass'],
                                        'sql' => $query,
                            ));
                            if (isset($resp['ok']) && $resp['ok'] === false) {
                                $msg .= $resp['msg'] . "\n";
                            }
                        }
                    }
                }
                // Apply the new config
                $cfg->getSection('[mysql]')->set('MYSQL_USER', $params['dbuser']);
                $cfg->getSection('[mysql]')->set('MYSQL_PASS', $params['dbpass']);
                $cfg->getSection('[mysql]')->set('MYSQL_HOST', $params['dbhost']);
                $cfg->getSection('[mysql]')->set('MYSQL_DBNAME', $params['dbschema']);
                // Change root user in configuration file and database
                $cfg->getSection('[safe_mode]')->set('user', $params['root']);
                $cfg->getSection('[safe_mode]')->set('pass', $params['rootpass']);
                // Save
                $cfg->getSection('[core]')->set('installed', 1);
                $cfg->save();
                // .htaccess
                // @TODO: Configure .htaccess file to the base path.
                // Redirect to home
                header("Location: ".CMS_DEFAULT_URL_BASE);
            }
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Install Pharinix"),
                "parameters" => array(
                        'root' => __('Root user email.'),
                        'rootpass' => __('Root user password.'),
                        'dbhost' => __('MySQL server address.'),
                        'dbschema' => __('MySQL data base name.'),
                        'dbuser' => __('MySQL user.'),
                        'dbpass' => __('MySQL password.'),
                    ),
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        'root' => 'string',
                        'rootpass' => 'string',
                        'dbhost' => 'string',
                        'dbschema' => 'string',
                        'dbuser' => 'string',
                        'dbpass' => 'string',
                    ),
                    "response" => array(),
                ),
                "echo" => true,
                "interface" => false,
//                "hooks" => array(
//                        array(
//                            "name" => "nothingHook",
//                            "description" => "sadasdaAllow rewrite a HTML alert message.",
//                            "parameters" => array(
//                                "alert" => "asdasdResponse to be echoed to the client.",
//                                "msg" => "asdasdThe message to show."
//                            )
//                        )
//                )
            );
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }

//        public static function getAccessFlags() {
//            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
//        }
    }
}
return new commandInstall();