<?php

// One Click Installation options
$installation_options = array(

    'Forum' => array(
        array(
            'id'          => 'vanilla',
            'name'        => 'Vanilla Core',
            'description' => 'Forum main core',
            'url'         => 'http://vanillaforums.org/addon/vanilla-core',
            'actions'     => 'download extract copy activate',
            'download'    => 'https://us.v-cdn.net/5018160/uploads/addons/J48X62QECRY1.zip'
        )
    ),
    'Theme' => array(
        array(
            'id'          => 'theme',
            'name'        => 'One Click',
            'description' => 'This amazing theme',
            'url'         => '',
            'actions'     => 'copy activate',
            'download'    => ''
        )
    ),
    'Mods' => array(
        array(
            'id'          => 'envato',
            'name'        => 'Envato Item Purchase Code verification',
            'description' => 'Activates the Envato Item Purchase Code verification.',
            'url'         => '',
            'actions'     => 'activate',
            'download'    => ''
        )
    ),
    'Applications' => array(
        array(
            'id'          => 'BasicPages',
            'name'        => 'Basic Pages',
            'description' => 'Basic Pages is an application that provides a way for you to create basic public pages for static content in Garden.',
            'url'         => 'http://vanillaforums.org/addon/basicpages-application',
            'actions'     => 'download extract copy activate',
            'download'    => 'http://cd8ba0b44a15c10065fd-24461f391e20b7336331d5789078af53.r23.cf1.rackcdn.com/www.vanillaforums.org/addons/CD2A09868K86.zip'
        ),
        array(
            'id'          => 'Yaga',
            'name'        => 'Yet Another Gamification Application',
            'description' => 'Yaga provides customizable reactions, badges, and ranks for your Vanilla forum software.',
            'url'         => 'http://vanillaforums.org/addon/yaga-application',
            'actions'     => 'download extract copy activate',
            'download'    => 'http://15254b2dcaab7f5478ab-24461f391e20b7336331d5789078af53.r23.cf1.rackcdn.com/www.vanillaforums.org/addons/BOZ83LD8C1DZ.zip'
        )
    ),
    'Plugins' => array(
        array(
            'id'          => 'editor',
            'name'        => 'Editor',
            'description' => 'Enables advanced editing of posts in several formats, including WYSIWYG, simple HTML, Markdown, and BBCode.',
            'url'         => '',
            'actions'     => 'activate',
            'download'    => ''
        ),
        array(
            'id'          => 'CreativeSyntaxHighlighter',
            'name'        => 'Creative Syntax Highlighter',
            'description' => 'Adds a Code Syntax Highlighter on discussions and comments.',
            'url'         => 'http://vanillaforums.org/addon/creativesyntaxhighlighter-plugin',
            'actions'     => 'download extract copy activate',
            'download'    => 'http://cdn.vanillaforums.com/www.vanillaforums.org/addons/0TXLIVTRYKR4.zip'
        ),
        array(
            'id'          => 'Gravatar',
            'name'        => 'Gravatar',
            'description' => 'Implements Gravatar avatars for all users who have not uploaded their own custom profile picture & icon.',
            'url'         => '',
            'actions'     => 'activate',
            'download'    => ''
        ),
        array(
            'id'          => 'VanillaInThisDiscussion',
            'name'        => 'In this discussion',
            'description' => 'Adds a list of users taking part in the discussion to the side panel of the discussion page in Vanilla.',
            'url'         => '',
            'actions'     => 'activate',
            'download'    => ''
        ),
        array(
            'id'          => 'PrivateCommunity',
            'name'        => 'Private Community',
            'description' => 'Adds an option to Roles & Permissions allowing administrators to make all pages only visible for signed-in community members.',
            'url'         => 'http://vanillaforums.org/addon/privatecommunity-plugin',
            'actions'     => 'activate',
            'download'    => 'http://cdn.vanillaforums.com/www.vanillaforums.org/addons/cshtosw0gyc1.zip'
        ),
        array(
            'id'          => 'SplitMerge',
            'name'        => 'Split Merge',
            'description' => 'Allows moderators with discussion edit permission to split & merge discussions.',
            'url'         => '',
            'actions'     => 'activate',
            'download'    => ''
        ),
        array(
            'id'          => 'Sprites',
            'name'        => 'Sprites',
            'description' => 'Adds sprites (icons) to all menus and nav buttons throughout Vanilla.',
            'url'         => '',
            'actions'     => 'extract copy activate',
            'download'    => ''
        ),
        array(
            'id'          => 'Tagging',
            'name'        => 'Tagging',
            'description' => 'Allow tagging of discussions.',
            'url'         => '',
            'actions'     => 'activate',
            'download'    => ''
        ),
        array(
            'id'          => 'Voting',
            'name'        => 'Voting',
            'description' => 'Allows users to vote on comments and discussions.',
            'url'         => 'http://vanillaforums.org/addon/voting-plugin',
            'actions'     => 'download extract copy activate',
            'download'    => 'http://cdn.vanillaforums.com/www.vanillaforums.org/addons/GG25CSRVDZVR.zip'
        )
    )
);

// Get installation option by id
function getInstallationOptionFromId($installation_option_id){
    global $installation_options;

    foreach($installation_options as $key => $installation_category){
        $type = $key;
        foreach($installation_category as $key2 => $installation_option){
            if($installation_option['id']==$installation_option_id){
                $installation_option['type'] = $type;
                return $installation_option;
            }
        }
    }

}

// Download a file from url to path
function downloadFile ($url, $path) {
    $newfname = $path;
    $file = @fopen ($url, "rb");
    if ($file) {
        $newf = fopen ($newfname, "wb");
        if ($newf){
            while(!feof($file)) {
                fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
            }
        }else{
            return false;
        }
        if ($newf)
            fclose($newf);
    }else{
        return false;
    }
    if ($file)
        fclose($file);
}


// Saves to Config from an AJAX call
function SaveToConfigFromAjax($Name, $Value = '', $Options = array()){

    global $forum_path;

    define('APPLICATION', 'Vanilla');
    define('APPLICATION_VERSION', '2.3');

    // Report and track all errors.

    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
    ini_set('display_errors', 0);
    ini_set('track_errors', 1);

    //ob_start();

    // Define the constants we need to get going.
    define('DS', '/');
    define('PATH_ROOT', $forum_path);

    // Include the bootstrap to configure the framework.
    require_once(PATH_ROOT.'/bootstrap.php');

    // Create and configure the dispatcher.
/*
    $Dispatcher = Gdn::Dispatcher();

    $EnabledApplications = Gdn::ApplicationManager()->EnabledApplicationFolders();
    $Dispatcher->EnabledApplicationFolders($EnabledApplications);
    $Dispatcher->PassProperty('EnabledApplications', $EnabledApplications);

    // Process the request.

    $Dispatcher->Start();
    $Dispatcher->Dispatch();
*/
    SaveToConfig($Name, $Value, $Options);

}


function runStructure($application){

    global $forum_path;

    define('APPLICATION', 'Vanilla');
    define('APPLICATION_VERSION', '2.3');

    // Report and track all errors.

    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
    ini_set('display_errors', 0);
    ini_set('track_errors', 1);

    // Define the constants we need to get going.
    define('DS', '/');
    define('PATH_ROOT', $forum_path);

    // Include the bootstrap to configure the framework.
    require_once(PATH_ROOT.'/bootstrap.php');

    Gdn::runStructure($application);

}


// Copies files and non-empty directories
function rcopy($src, $dst) {
    //if (file_exists($dst)) rrmdir($dst);
    if (is_dir($src)) {
        if (!is_dir($dst))
            mkdir($dst);
        $files = scandir($src);
        foreach ($files as $file)
            if ($file != "." && $file != "..") rcopy("$src".DIRECTORY_SEPARATOR."$file", "$dst".DIRECTORY_SEPARATOR."$file");
    }
    else if (file_exists($src)) copy($src, $dst);
}

// Removes files from directory
function rrmdir($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file)
            if ($file != "" && $file != "." && $file != "..") rrmdir("$dir".DIRECTORY_SEPARATOR."$file");
            rmdir($dir);
    }
    else if (file_exists($dir)) unlink($dir);
}

// Get the current URL (apache and IIS)
function selfUrl(){
    $serverrequri = !isset($_SERVER['REQUEST_URI']) ? $_SERVER['PHP_SELF'] : $_SERVER['REQUEST_URI'];
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"],'/'))).$s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol."://".$_SERVER['SERVER_NAME'].$port.$serverrequri;
}

?>