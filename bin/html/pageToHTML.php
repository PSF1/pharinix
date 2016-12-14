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

/*
 * Transform a XML page to HTML
 * Parameters:
 * page = XML page to convert
 */
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

if (!class_exists("commandPageToHTML")) {

    class commandPageToHTML extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            self::getRegister("customscripts"); // To allow commands set javascripts
            self::getRegister("customcss"); // To allow commands set css
            
            include_once("usr/xml2array/xml2array.php");
            include_once("etc/drivers/pages.php");
            if (!function_exists("pageToHTMLParseBlock")) {

                function pageToHTMLParseBlock($pageId, $blk) {
                    foreach ($blk as $key => $rows) {
                        if ($key != '@attributes') {
                            foreach ($rows as $row) {
                                $auxHook = "<div class=\"row\" ";
                                
                                if(isset($row['@attributes'])) {
                                    foreach ((array)$row['@attributes'] as $name => $attr) {
                                        $auxHook .= " $name=\"$attr\"";
                                    }
                                }
                                $auxHook .= ">"."\n";
                                if(isset($row['@attributes'])) {
                                    $rowTag = pageToHtmlOpenRow($auxHook, $row['@attributes'], 'div');
                                }
                                else {
                                    $rowTag = pageToHtmlOpenRow($auxHook,"", 'div');
                                }
                                if (driverPages::showAreas() && isset($row['@attributes'])) {
                                    $rid = '';
                                    if (isset($row['@attributes']["id"])) {
                                        $rid = $row['@attributes']["id"];
                                    }
                                    echo "<h6><span class=\"label label-success\">".__("row ID").": " . $rid . "</span></h6>";
                                }
                                foreach ($row["col"] as $col) {
                                    $auxHook = "<div";
                                    foreach ($col['@attributes'] as $name => $attr) {
                                        $auxHook .= " $name=\"$attr\"";
                                    }
                                    $auxHook .= ">"."\n";
                                    $colTag = pageToHtmlOpenCol($auxHook, $col['@attributes'], 'div');
                                    
                                    if (driverPages::showAreas() && isset($col['@attributes']["id"])) {
                                        $rid = '';
                                        if (isset($col['@attributes']["id"])) {
                                            $rid = $col['@attributes']["id"];
                                        }
                                        echo "<h6><span class=\"label label-success\">".__("Col ID").": " . $rid . "</span></h6>";
                                    }
                                    // Call command list
                                    if(isset($col['@attributes']["id"])) {
                                        $cmd = driverPages::getCommands($pageId, $col['@attributes']["id"]);
                                        while ($cmd !== false && !$cmd->EOF) {
                                            $params = array();
                                            // Change URL context variables in parameters
                                            $context = &driverCommand::getRegister("url_context");
                                            $rawParams = driverUrlRewrite::mapReplace($context, $cmd->fields["parameters"]);
                                            parse_str($rawParams, $params);
                                            if (driverPages::showAreas()) {
                                                $iParams = ' ()';
                                                if (count($params) > 0) {
                                                    $iParams = str_replace("<", "&lt;", print_r($params, 1));
                                                    $iParams = str_replace("\t", "&nbsp;", $iParams);
                                                    $iParams = str_replace("\n", "<br>", $iParams);
                                                    $iParams = " <br>$iParams";
                                                }
                                                echo "<div class=\"alert alert-success\" role=\"alert\"><h6><b>" . __("Command") . "</b>: {$cmd->fields["command"]}" . $iParams . "</h6></div>";
                                            }
                                            driverCommand::run($cmd->fields["command"], $params);
                                            $cmd->MoveNext();
                                        }
                                    }
                                    if (isset($col['row'])) {
                                        pageToHTMLParseBlock($pageId, $col);
                                    }
                                    if ($colTag != '') echo "</$colTag>"."\n";
                                }
                                if ($rowTag != '') echo "</$rowTag>"."\n";
                            }
                        }
                    }
                }
                
                /**
                 * Open a row block and return her tag type.
                 * @param string $element
                 * @param array $attribs
                 * @param string $tag
                 */
                function pageToHtmlOpenRow($element, $attribs, $tag = 'div') {
                    driverHook::CallHook('pageToHtmlOpenRowHook', array(
                        'element' => &$element,
                        'attributes' => &$attribs,
                        'tag' => &$tag,
                    ));
                    echo $element;
                    return $tag;
                }
                
                /**
                 * Open a row block and return her tag type.
                 * @param string $element
                 * @param array $attribs
                 * @param string $tag
                 */
                function pageToHtmlOpenCol($element, $attribs, $tag = 'div') {
                    driverHook::CallHook('pageToHtmlOpenColHook', array(
                        'element' => &$element,
                        'attributes' => &$attribs,
                        'tag' => &$tag,
                    ));
                    echo $element;
                    return $tag;
                }

            }

            $def = driverPages::getPage($params["page"]);
            if ($def === false) {
                header("HTTP/1.0 404 Not Found");
                $def = driverPages::getPage('404');
            }
            if ($def !== false) {
                $finfo = driverTools::pathInfo($def->fields["template"]);
                if (is_file($def->fields["template"]) || strtolower($finfo['ext']) == 'tpl') {
                    if (strtolower($finfo['ext']) == 'tpl') {
                        // It's a smarty template
                        driverCommand::run('smartyRender', array(
                            "page" => $params["page"],
                            "tpl" => $def->fields["template"],
                        ));
                        return;
                    }
                    $page = file_get_contents($def->fields["template"]);
                    $struct = xml_string_to_array($page);
                    $htmlLang = "";
                    $charset = "";
                    foreach ($struct["page"][0]["@attributes"] as $key => $value) {
                        switch ($key) {
                            case "lang":
                                $htmlLang = $value;
                                break;
                            case "charset":
                                $charset = $value;
                                break;
                        }
                    }
                    $auxHook = '<!DOCTYPE html>';
                    driverHook::CallHook('pageToHtmlTypeHook', array(
                        'element' => &$auxHook,
                    ));
                    echo $auxHook;
                    
                    $auxHook =  '<html lang="' . $htmlLang . '">';
                    driverHook::CallHook('pageToHtmlRootHook', array(
                        'element' => &$auxHook,
                        'lang' => &$htmlLang,
                    ));
                    echo $auxHook;
                    
                    $optionsHook = array();
                    $auxHook = '<head>';
                    $auxHook .= '<meta charset="utf-8">'."\n";
                    $auxHook .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">'."\n";
                    $auxHook .= '<meta name="viewport" content="width=device-width, initial-scale=1">'."\n";
                    if ($charset != "") {
                        $auxHook .= "<meta charset=\"$charset\">"."\n";
                        $optionsHook['charset'] = $charset;
                    }
                    if (isset($struct["page"][0]["title"][0])) {
                        $context = &driverCommand::getRegister("url_context");
                        $rawTitle = driverUrlRewrite::mapReplace($context, $def->fields["title"]);
                        $auxHook .= '<title>' . $rawTitle;
                        $optionsHook['pagetitle'] = $rawTitle;
                        if ($struct["page"][0]["title"][0] != "") {
                            $auxHook .= " :: ";
//                            $auxHook .= $struct["page"][0]["title"][0];
                            $auxHook .= driverConfig::getCFG()->getSection('[core]')->get('CMS_TITLE');
                            $optionsHook['generalTitle'] = $struct["page"][0]["title"][0];
                        }
                        $auxHook .= '</title>'."\n";
                    }
                    $optionsHook['metas'] = array();
                    foreach ($struct["page"][0]["head"][0] as $tag => $attr) {
                        foreach ($attr as $value) {
                            if ($tag != "#comment" && isset($value['@attributes']) && count($value['@attributes']) > 0) {
                                $auxHook .= "<$tag";
                                $auxTag = array($tag => array());
                                foreach ($value['@attributes'] as $name => $val) {
                                    if ($name == "src" || $name == "href") {
                                        $val = CMS_DEFAULT_URL_BASE . $val;
                                    }
                                    $auxHook .= " $name=\"$val\"";
                                    $auxTag[$tag][$name] = $val;
                                }
                                $auxHook .= "></$tag>"."\n";
                                $optionsHook['metas'][] = $auxTag;
                            }
                        }
                    }
                    $auxHook .= '<script src="'.CMS_DEFAULT_URL_BASE.'?command=getBaseJS&interface=nothing"></script>
            <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
            <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
            <!--[if lt IE 9]>
              <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
              <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->'."\n";
                    $auxHook .= '</head>'."\n";
                    $optionsHook['element'] = &$auxHook;
                    driverHook::CallHook('pageToHtmlHeadHook', array(
                        'element' => &$auxHook,
                        'charset' => &$optionsHook['charset'],
                        'pagetitle' => &$optionsHook['pagetitle'],
                        'generalTitle' => &$optionsHook['generalTitle'],
                        'metas' => &$optionsHook['metas'],
                    ));
                    echo $auxHook;
                    
                    $auxHook = '<body>'."\n";
                    driverHook::CallHook('pageToHtmlOpenBodyHook', array(
                        'element' => &$auxHook,
                    ));
                    echo $auxHook;
                    
                    $auxHook = '<div class="container-fluid">'."\n";
                    $classHook = 'container-fluid';
                    $tagContainerHook = 'div';
                    driverHook::CallHook('pageToHtmlOpenMainContentHook', array(
                        'element' => &$auxHook,
                        'class' => &$classHook,
                        'tag' => &$tagContainerHook,
                    ));
                    echo $auxHook;
                    
                    if (driverPages::showAreas())
                        echo "<h6><span class=\"label label-success\">Body of {$params["page"]} [{$def->fields['id']}]</span></h6>";
                    pageToHTMLParseBlock($def->fields["id"], $struct["page"][0]["body"][0]);
                    
                    $auxHook = "</$tagContainerHook>"."\n";
                    driverHook::CallHook('pageToHtmlCloseMainContentHook', array(
                        'element' => &$auxHook,
                        'tag' => &$tagContainerHook,
                    ));
                    echo $auxHook;
                    
//                    echo '<div id="footer">';
//                    echo '<div class="container-fluid">';
//                    if (driverPages::showAreas())
//                        echo "<h6><span class=\"label label-success\">Foot</span></h6>";
//                    pageToHTMLParseBlock($def->fields["id"], $struct["page"][0]["foot"][0]);
//                    echo "</div>";
//                    echo "</div>";
                    
                    $cssFiles = &self::getRegister("filecss");
                    if ($cssFiles != null) {
                        foreach($cssFiles as $cssFile) {
                            echo '<link href="'.CMS_DEFAULT_URL_BASE.$cssFile.'" rel="stylesheet" type="text/css"/>'."\n";
                        }
                    }

                    $cssFiles = &self::getRegister("filescripts");
                    if ($cssFiles != null) {
                        foreach($cssFiles as $cssFile) {
                            echo '<script src="'.CMS_DEFAULT_URL_BASE.$cssFile.'"></script>'."\n";
                        }
                    }
                    
                    $reg = self::getRegister("customscripts");
                    if ($reg != "") {
                        $auxHook = "<script>".$reg."</script>"."\n";
                        $regHook = $reg;
                        driverHook::CallHook('pageToHtmlCustomJavascriptHook', array(
                            'element' => &$auxHook,
                            'raw' => &$regHook,
                        ));
                        echo $auxHook;
                    }
                    $reg = self::getRegister("customcss");
                    if ($reg != "") {
                        $auxHook = "<style>".$reg."</style>"."\n";
                        $regHook = $reg;
                        driverHook::CallHook('pageToHtmlCustomStylesHook', array(
                            'element' => &$auxHook,
                            'raw' => &$regHook,
                        ));
                        echo $auxHook;
                    }
                    $auxHook = '</body>'."\n";
                    driverHook::CallHook('pageToHtmlCloseBodyHook', array(
                        'element' => &$auxHook,
                        'tag' => &$tagContainerHook,
                    ));
                    echo $auxHook;
                    echo '</html>';
                } else {
                    throw new Exception(sprintf(__("Page template '%s' not found."), $def->fields["template"]));
                }
            } else {
                throw new Exception(sprintf(__("Page '%s' not found."), $params["page"]));
            }
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
                "description" => __("Transform a page to HTML"),
                "parameters" => array("page" => __("Page to convert, see 'url_rewrite' table.")),
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "page" => "string"
                    ), 
                    "response" => array(),
                ),
                "echo" => true,
                "hooks" => array(
                        array(
                            "name" => "pageToHtmlTypeHook",
                            "description" => __("Allow change the document type."),
                            "parameters" => array(
                                'element' => __("Document type tag, default is '<!DOCTYPE html>'."),
                            )
                        ),
                        array(
                            "name" => "pageToHtmlRootHook",
                            "description" => __("Allow change default HTML root tag."),
                            "parameters" => array(
                                'element' => __("The actual tag."),
                                'lang' => __("Raw language code needed."),
                            )
                        ),
                        array(
                            "name" => "pageToHtmlHeadHook",
                            "description" => __("Allow change default head tag."),
                            "parameters" => array(
                                'charset' => __("Needed charset"),
                                'pagetitle' => __("Main page title."),
                                'generalTitle' => __("General page title."),
                                'metas' => __("Array of tags required by the XML template."),
                                'element' => __("Prebuild head tag."),
                            )
                        ),
                        array(
                            "name" => "pageToHtmlOpenBodyHook",
                            "description" => __("Allow change default open body tag."),
                            "parameters" => array(
                                'element' => __("Prebuild open body tag."),
                            )
                        ),
                        array(
                            "name" => "pageToHtmlCloseBodyHook",
                            "description" => __("Allow change default close body tag."),
                            "parameters" => array(
                                'element' => __("Prebuild close body tag."),
                            )
                        ),
                        array(
                            "name" => "pageToHtmlOpenMainContentHook",
                            "description" => __("Allow change default container block."),
                            "parameters" => array(
                                'element' => __("Prebuild open tag."),
                                'class' => __("Class attribute to set to the block."),
                                'tag' => __("Tag type to close the block, default is 'div'."),
                            )
                        ),
                        array(
                            "name" => "pageToHtmlCloseMainContentHook",
                            "description" => __("Allow change default container block."),
                            "parameters" => array(
                                'element' => __("Prebuild open tag."),
                                'tag' => __("Tag type to close the block, default is 'div'."),
                            )
                        ),
                        array(
                            "name" => "pageToHtmlCustomJavascriptHook",
                            "description" => __("Allow change custom Javascript in page foot."),
                            "parameters" => array(
                                'element' => __("Prebuild <script> block."),
                                'raw' => __("Required Javascript code."),
                            )
                        ),
                        array(
                            "name" => "pageToHtmlCustomStylesHook",
                            "description" => __("Allow change custom CSS styles in page foot."),
                            "parameters" => array(
                                'element' => __("Prebuild <style> block."),
                                'raw' => __("Required CSS."),
                            )
                        ),
                        array(
                            "name" => "pageToHtmlOpenRowHook",
                            "description" => __("Allow change the open row tag."),
                            "parameters" => array(
                                'element' => __("Prebuild open tag."),
                                'attributes' => __("Array of attributes to apply."),
                                'tag' => __("Tag type to close the block, default is 'div'. If is empty, '', pageToHTML don't add the close tag."),
                            )
                        ),
                        array(
                            "name" => "pageToHtmlOpenColHook",
                            "description" => __("Allow change the open column tag."),
                            "parameters" => array(
                                'element' => __("Prebuild open tag."),
                                'attributes' => __("Array of attributes to apply."),
                                'tag' => __("Tag type to close the block, default is 'div'. If is empty, '', pageToHTML don't add the close tag."),
                            )
                        ),
                )
            );
        }

    }

}
return new commandPageToHTML();
