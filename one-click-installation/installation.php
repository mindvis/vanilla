<?php

// include the available installation options
include('config.php');

if(isset($_POST['installation_option_action']) and $_POST['installation_option_action']=='verification'){

    if (version_compare(phpversion(), '5.2.0') < 0)
         exit('<h2>PHP version too small</h2><p>You are running PHP version '.phpversion().'. Vanilla requires PHP 5.2.0 or greater. You must upgrade PHP before you can continue.</p>');

    if(isset($_POST['options']) and in_array('vanilla',$_POST['options'])){

        foreach($_POST as $key => $value){
            if(($value=='' and $key!='database_password' and in_array('envato',$_POST['options'])) or ($value=='' and $key!='database_password' and $key!='envato_username' and $key!='envato_key' and !in_array('envato',$_POST['options'])))
                exit('<h2>Mandatory fields not set</h2><p>All fields except \'DATABASE PASSWORD\' are mandatory. Please fill them all.</p>');
        }

        $db_connect = @mysql_connect($_POST['database_host'], $_POST['database_user'], $_POST['database_password']);
        if($db_connect){

            if(!mysql_query("CREATE DATABASE ".$_POST['database_name']." CHARACTER SET utf8 COLLATE utf8_general_ci;",$db_connect)){
                if(mysql_errno()!=1007)
                    exit("<h2>Database info is incorrect</h2><p>Please make sure if all database info is correct and if the database exists.</p>");
            }

        }else{
            exit("<h2>Database info is incorrect</h2><p>Please make sure if all database info is correct.</p>");
        }

    }elseif(isset($_POST['options']) and in_array('envato',$_POST['options'])){

        if($_POST['forum_path']=='' or $_POST['envato_username']=='' or $_POST['envato_key']=='')
            exit('<h2>Mandatory field not set</h2><p>The \'Forum destination path\', \'Envato Username\' and \'Envato Api Key\' are mandatory. Please fill them.</p>');

    }else{

        if($_POST['forum_path']=='')
            exit('<h2>Mandatory field not set</h2><p>The \'Forum destination path\' is mandatory. Please fill him.</p>');

    }

    $forum_path = trim($_POST['forum_path']);
    if($forum_path[strlen($forum_path)-1]==DIRECTORY_SEPARATOR)
        $forum_path = substr($forum_path, 0, -1);

    if($forum_path=='')
        exit('The Forum Path must be defined.');

    if($_POST['admin_username']!='' and (preg_match("/^[\w]+$/", $_POST['admin_username'])==0 or strlen($_POST['admin_username'])<3 or strlen($_POST['admin_username'])>20))
        exit('<h2>Invalid username!</h2><p>Username can only contain letters, numbers, underscores, and must be between 3 and 20 characters long.</p>');

    // Clear the downloads temp files
    rrmdir('downloads'.DIRECTORY_SEPARATOR);
    mkdir("downloads", 0755);

    exit(true);

}

if(isset($_POST['installation_option_action']) and $_POST['installation_option_action']=='download'){

    $installation_option = getInstallationOptionFromId($_POST['installation_option_id']);

    if(!is_dir('downloads'))
        mkdir("downloads", 0755);

    if(!is_dir('downloads'))
        exit('\'downloads\' dir doesn\'t exist. Please create one with 755 attributes.');

    if($installation_option['download']!=''){
        if(downloadFile($installation_option['download'],'downloads'.DIRECTORY_SEPARATOR.$_POST['installation_option_id'].'.zip')===FALSE)
            exit('download failed.');
    }

    exit(true);
}

if(isset($_POST['installation_option_action']) and $_POST['installation_option_action']=='extract'){

    $installation_option = getInstallationOptionFromId($_POST['installation_option_id']);

    $zip = new ZipArchive;
    if($installation_option['type']=='Forum'){
        if ($zip->open('downloads'.DIRECTORY_SEPARATOR.$_POST['installation_option_id'].'.zip') === TRUE) {
            if(!$zip->extractTo('downloads'.DIRECTORY_SEPARATOR.'vanilla'.DIRECTORY_SEPARATOR)){
                exit('error extracting');
            }
            exit(true);
        } else {
            exit('error opening the zip file');
        }
    }else{
        if ($zip->open('downloads'.DIRECTORY_SEPARATOR.$_POST['installation_option_id'].'.zip') === TRUE) {
            if(!$zip->extractTo('downloads'.DIRECTORY_SEPARATOR)){
                exit('error extracting');
            }
            exit(true);
        } else if ($zip->open('plugins'.DIRECTORY_SEPARATOR.$_POST['installation_option_id'].'.zip') === TRUE) {
            if(!$zip->extractTo('downloads'.DIRECTORY_SEPARATOR)){
                exit('error extracting');
            }
            exit(true);
        } else {
            exit('error opening the zip file');
        }
    }

}

if(isset($_POST['installation_option_action']) and $_POST['installation_option_action']=='copy'){

    $forum_path = trim($_POST['forum_path']);
    if($forum_path[strlen($forum_path)-1]==DIRECTORY_SEPARATOR)
        $forum_path = substr($forum_path, 0, -1);

    $installation_option = getInstallationOptionFromId($_POST['installation_option_id']);

    $extra_dir = '';
    if($installation_option['type']=='Theme'){

        $old_dir = 'themes'.DIRECTORY_SEPARATOR;
        $new_dir = $forum_path.DIRECTORY_SEPARATOR.'themes';
        if(is_dir($forum_path))
            rcopy($old_dir, $new_dir);
        else
            exit('error path not found ('.$forum_path.')');

        $zip = new ZipArchive;
        if ($zip->open($new_dir.DIRECTORY_SEPARATOR.'oneClick.zip') === TRUE) {
            if(!$zip->extractTo($new_dir.DIRECTORY_SEPARATOR)){
                exit('error extracting');
            }
        } else {
            exit('error opening the zip file');
        }
        $zip->close();
        unlink($new_dir.DIRECTORY_SEPARATOR.'oneClick.zip');
        exit(true);
    }else if($installation_option['type']=='Mods'){
        exit(true);
    }else if($installation_option['type']=='Applications'){
        $zip = new ZipArchive;
        if ($zip->open('downloads'.DIRECTORY_SEPARATOR.$_POST['installation_option_id'].'.zip') === TRUE) {
            $original_option_dir = $zip->statIndex(1);
            $directory_name=dirname($original_option_dir['name']);
            $zip->close();
        } else {
            exit('error file not found');
        }
        $old_dir = 'downloads'.DIRECTORY_SEPARATOR.$directory_name.DIRECTORY_SEPARATOR;
        $new_dir = $forum_path.DIRECTORY_SEPARATOR.'applications'.DIRECTORY_SEPARATOR.$directory_name;
        if(is_dir($new_dir))
            exit('error there\'s already a directory with that name ('.$new_dir.')');
        if(@rename($old_dir,$new_dir)){
            exit(true);
        }else{
            exit('error copying to the directory ('.$new_dir.')');
        }
    }else if($installation_option['type']=='Plugins'){
        $zip = new ZipArchive;
        if ($zip->open('downloads'.DIRECTORY_SEPARATOR.$_POST['installation_option_id'].'.zip') === TRUE) {
            $original_option_dir = $zip->statIndex(1);
            $directory_name=dirname($original_option_dir['name']);
            $zip->close();
        } else if ($zip->open('plugins'.DIRECTORY_SEPARATOR.$_POST['installation_option_id'].'.zip') === TRUE) {
            $original_option_dir = $zip->statIndex(1);
            $directory_name=dirname($original_option_dir['name']);
            $zip->close();
        } else {
            exit('error file not found');
        }
        $old_dir = 'downloads'.DIRECTORY_SEPARATOR.$directory_name.DIRECTORY_SEPARATOR;
        $new_dir = $forum_path.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$directory_name;
        if(is_dir($new_dir))
            exit('error there\'s already a directory with that name ('.$new_dir.')');
        if(@rename($old_dir,$new_dir)){
            exit(true);
        }else{
            exit('error copying to the directory ('.$new_dir.')');
        }
    }else{
        /*
        $zip = new ZipArchive;
        if ($zip->open('downloads'.DIRECTORY_SEPARATOR.$_POST['installation_option_id'].'.zip') === TRUE) {
            $original_option_dir = $zip->statIndex(1);
            $directory_name=dirname($original_option_dir['name']);
            $zip->close();
        } else {
            exit('error file not found');
        }
        */
        $directory_name='vanilla';
        $old_dir = 'downloads'.DIRECTORY_SEPARATOR.$directory_name.DIRECTORY_SEPARATOR;
        $new_dir = $forum_path;

        //if(is_dir($new_dir))
            //exit('error there\'s already a directory with that name ('.$new_dir.')');

        if(!is_dir(dirname($new_dir)))
            mkdir(dirname($new_dir),0755,true);

        if(is_dir($new_dir)){
            rcopy($old_dir, $new_dir);
            exit(true);
        }else{
            if(rename($old_dir,$new_dir)){
                exit(true);
            }else{
                exit('error copying to the directory ('.$new_dir.')');
            }
        }

    }

}

if(isset($_POST['installation_option_action']) and $_POST['installation_option_action']=='activate'){

    $installation_option = getInstallationOptionFromId($_POST['installation_option_id']);

    $forum_path = realpath(trim($_POST['forum_path']));
    if($forum_path[strlen($forum_path)-1]==DIRECTORY_SEPARATOR)
        $forum_path = substr($forum_path, 0, -1);

    if(!is_file($forum_path.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'config.php'))
        exit('config file not found ('.$forum_path.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'config.php)');

    if($installation_option['type']=='Theme'){

        // Default Info
        SaveToConfigFromAjax('Garden.Theme', 'oneClick');
        SaveToConfigFromAjax('Garden.ThemeOptions.Name', 'One Click - Premium Vanilla Theme');

        // One Click Theme Options
        SaveToConfigFromAjax('ThemeOption.CurrentStyle','material-blue');
        SaveToConfigFromAjax('ThemeOption.PreviewStyle',TRUE);
        SaveToConfigFromAjax('ThemeOption.ExtraStyle','material-blue');

        // Set the order for the modules (make sure CategoriesModule is set).
        SaveToConfigFromAjax('Modules.Vanilla.Panel', array('MeModule', 'UserBoxModule', 'GuestModule', 'NewDiscussionModule', 'DiscussionFilterModule', 'CategoriesModule', 'SignedInModule', 'Ads'));

        // Set recommended images sizes
        SaveToConfigFromAjax('Garden.RewriteUrls', true);
        SaveToConfigFromAjax('Garden.Profile.MaxHeight', 300);
        SaveToConfigFromAjax('Garden.Profile.MaxWidth', 300);
        SaveToConfigFromAjax('Garden.Thumbnail.Size', 150);
        SaveToConfigFromAjax('Garden.Thumbnail.Width', 150);

        // Set the Mobile Theme
        SaveToConfigFromAjax('Garden.MobileTheme', 'oneClick');

    }else if($installation_option['type']=='Mods'){

        if(!is_dir($forum_path.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'oneClick'.DIRECTORY_SEPARATOR))
            exit('oneClick theme not found ('.$forum_path.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'oneClick'.DIRECTORY_SEPARATOR.')');

        SaveToConfigFromAjax('Garden.Registration.Method', 'Envato');
        SaveToConfigFromAjax('Garden.Registration.ConfirmEmail', FALSE);
        SaveToConfigFromAjax('Garden.Registration.EnvatoUsername', $_POST['envato_username']);
        SaveToConfigFromAjax('Garden.Registration.EnvatoAPIKey', $_POST['envato_key']);

    }else if($installation_option['type']=='Applications'){

        SaveToConfigFromAjax('EnabledApplications.'.$installation_option['id'], strtolower($installation_option['id']));

        // Define the constants we need to get going.
        define('DS', '/');
        define('PATH_ROOT', $forum_path);

        if($installation_option['id']=='BasicPages')
            include(PATH_ROOT . DS . 'applications' . DS . 'basicpages' . DS . 'settings' . DS . 'structure.php');

        if($installation_option['id']=='Yaga'){
            $ApplicationManager = new Gdn_ApplicationManager();
            $Validation = new Gdn_Validation();
            $ApplicationManager->RegisterPermissions('Yaga', $Validation);
            $ApplicationManager->EnableApplication('Yaga', $Validation);

            SaveToConfigFromAjax('Yaga.Reactions.Enabled', '1');
            SaveToConfigFromAjax('Yaga.Badges.Enabled', '1');
            SaveToConfigFromAjax('Yaga.Ranks.Enabled', '1');
            SaveToConfigFromAjax('Yaga.MenuLinks.Show', '');
            SaveToConfigFromAjax('Yaga.LeaderBoard.Enabled', '');
            SaveToConfigFromAjax('Yaga.LeaderBoard.Limit', '');
        }

    }else if($installation_option['type']=='Plugins'){

        SaveToConfigFromAjax('EnabledPlugins.'.$installation_option['id'], TRUE);

        if($installation_option['id']=='editor'){
            SaveToConfigFromAjax('EnabledPlugins.cleditor', FALSE);
            SaveToConfigFromAjax('EnabledPlugins.ButtonBar', FALSE);
            SaveToConfigFromAjax('EnabledPlugins.Emotify', FALSE);
            SaveToConfigFromAjax('EnabledPlugins.FileUpload', FALSE);
            SaveToConfigFromAjax('Garden.InputFormatter', 'Wysiwyg');
            SaveToConfigFromAjax('Garden.MobileInputFormatter', 'TextEx');
            SaveToConfigFromAjax('Garden.AllowFileUploads', TRUE);
            SaveToConfigFromAjax('Plugins.editor.ForceWysiwyg', '1');
        }

        if($installation_option['id']=='PrivateCommunity')
            SaveToConfigFromAjax('Garden.PrivateCommunity', TRUE);

        if($installation_option['id']=='SplitMerge')
            SaveToConfigFromAjax('Vanilla.AdminCheckboxes.Use', TRUE);

    }

    exit(true);

}

?>
<form id="installation_form" name="installation_form" method="post" action="">
    <input type="hidden" id="installation_option_action" name="installation_option_action" value="verification" />

    <div style="margin-bottom: 50px;">

        <label for="#installation_type" style="clear: both;">Type Of Installation</label>
        <select id="installation_type" name="installation_type" onchange="changeInstallationType();" style="float: left; margin-right: 10px;">
            <option value="forum">Public Forum</option>
            <option value="webdeveloping">Web Development Forum</option>
            <option value="envato">Envato Support Forum</option>
            <option value="clean">Clean Installation</option>
            <option value="existing">Existing Installation</option>
            <option value="custom">Custom Installation</option>
        </select>
        <div id="installation_type_description">
            <div id="installation_type_description_forum">A commom public forum</div>
            <div id="installation_type_description_webdeveloping" style="display:none;">A web development forum, with a syntax highlighter installed</div>
            <div id="installation_type_description_envato" style="display:none;">An Envato support forum</div>
            <div id="installation_type_description_clean" style="display:none;">A clean installation with only the Vanilla core and this theme installed</div>
            <div id="installation_type_description_existing" style="display:none;">You already have a vanilla forum installed and you want to install this theme on it</div>
            <div id="installation_type_description_custom" style="display:none;">A custom installation allows you to select which components you want to install</div>
            <div id="installation_show_hide_options">
                <a id="show_options" href="javascript: hideShow('#show_options','#hide_options,#installation_options');">show settings</a>
                <a id="hide_options" href="javascript: hideShow('#hide_options,#installation_options','#show_options');" style="display: none;">hide settings</a>
            </div>
        </div>

    </div>

    <div id="installation_options" style="margin-bottom: 50px; display: none;">
    <?php
    foreach($installation_options as $key => $installation_category){
        echo '<h3 style="clear: both;">'.$key.'</h3>';
        echo '<ul>';
        foreach($installation_category as $key2 => $installation_option){
            echo '<li id="installation_'.$installation_option['id'].'">'
                        .'<input type="checkbox" name="options[]" value="'.$installation_option['id'].'" />'
                        .($installation_option['url']!='' ? '<a href="'.$installation_option['url'].'" target="_blank" class="name">'.$installation_option['name'].'</a>' : '<span class="name">'.$installation_option['name'].'</span>')
                        .(strpos($installation_option['actions'],'download')!==false ? '<span class="download" title="download">download</span>' : '' )
                        .(strpos($installation_option['actions'],'extract')!==false ? '<span class="extract" title="extract">extract</span>' : '' )
                        .(strpos($installation_option['actions'],'copy')!==false ? '<span class="copy" title="copy">copy</span>' : '' )
                        .(strpos($installation_option['actions'],'activate')!==false ? '<span class="activate" title="activate">activate</span>' : '' )
                        .'<div>'.$installation_option['description'].'</div></li>';
        }
        echo '</ul>';
    }
    ?>
    </div>

    <div style="margin-bottom: 50px;">

        <label for="#forum_path">Forum path</label>
        <input type="text" id="forum_path" name="forum_path" value="<?php echo dirname(getcwd()).DIRECTORY_SEPARATOR.'vanilla'; ?>" />

        <label for="#forum_url">Forum url</label>
        <input type="text" id="forum_url" name="forum_url" value="<?php echo dirname(substr(selfUrl(),0,strrpos(selfUrl(),'/'))).'/vanilla/'; ?>" />

    </div>

    <div class="new_installation" style="margin-bottom: 35px; overflow: hidden;">

        <div style="width: 309px; float: left; margin-right: 30px;">
            <label for="#forum_title">Forum title</label>
            <input type="text" id="forum_title" name="forum_title" value="" class="w1_2" />
        </div>

        <div style="width: 309px; float: left;">
            <label for="admin_email">Admin Email</label>
            <input type="text" id="admin_email" name="admin_email" value="" class="w1_2" />
        </div>

        <div style="width: 309px; float: left; margin-right: 30px;">
            <label for="admin_username">Admin Username</label>
            <input type="text" id="admin_username" name="admin_username" value="" class="w1_2" />
        </div>

        <div style="width: 309px; float: left;">
            <label for="admin_password">Admin Password</label>
            <input type="text" id="admin_password" name="admin_password" value="" class="w1_2" />
        </div>

        <div style="width: 309px; float: left; margin-right: 30px; margin-top: 15px;">
            <label for="database_host">Database Host</label>
            <input type="text" id="database_host" name="database_host" value="localhost" class="w1_2" />
        </div>

        <div style="width: 309px; float: left; margin-top: 15px;">
            <label for="database_name">Database Name</label>
            <input type="text" id="database_name" name="database_name" value="" class="w1_2" />
        </div>

        <div style="width: 309px; float: left; margin-right: 30px;">
            <label for="database_user">Database User</label>
            <input type="text" id="database_user" name="database_user" value="" class="w1_2" />
        </div>

        <div style="width: 309px; float: left;">
            <label for="database_password">Database Password</label>
            <input type="text" id="database_password" name="database_password" value="" class="w1_2" />
        </div>

    </div>

    <div class="envato_installation" style="width: 309px; padding-bottom: 30px; float: left; margin-right: 30px; display: none;">
        <label for="#envato_username">Envato Username</label>
        <input type="text" id="envato_username" name="envato_username" value="" class="w1_2" />
    </div>

    <div class="envato_installation" style="width: 309px; padding-bottom: 30px; float: left; display: none;">
        <label for="#envato_key">Envato API key <a id="envato_api_key" href="images/envato_api_key.jpg" style="float: right;">where do I find the API key?</a></label>
        <input type="text" id="envato_key" name="envato_key" value="" class="w1_2" />
    </div>

    <hr style="margin: 30px -30px; clear: both;" />

    <div style="text-align: center;">
        <a id="one_click_installation" href="javascript: oneClickInstallation();">ONE CLICK INSTALLATION</a>
    </div>

    <div id="installation_form_msg" class="alert_msg" style="display: none;"></div>

</form>

<div id="installation_log_container" style="display: none;">

    <h1 id="installation_log_title">Installation progress</h1>
    <p id="installation_log_description">Please wait while the the installation process doesn't finishes.</p>

    <div id="progressbar"></div>

    <a id="view_log" href="javascript: hideShow('#view_log','#hide_log,#installation_log');">view log</a>
    <a id="hide_log" href="javascript: hideShow('#hide_log,#installation_log','#view_log');" style="display: none;">hide log</a>
    <div id="installation_log" style="display: none;"></div>

    <div id="installation_log_msg" style="display: none;"></div>

</div>

<script>
function hideShow(hide,show){
    $(hide).hide();
    $(show).show();
}

function changeInstallationType(){

    if($('#installation_type').val()=='envato'){
        var arr_options = new Array("vanilla","theme","envato","BasicPages","Gravatar","VanillaInThisDiscussion","PrivateCommunity","SplitMerge","CreativeSyntaxHighlighter","editor","Sprites");
        $('.new_installation, .envato_installation').show();
        $('#installation_type_description div').not('#installation_show_hide_options').hide();
        $('#installation_type_description_envato').show();
    }
    if($('#installation_type').val()=='webdeveloping'){
        var arr_options = new Array("vanilla","theme","BasicPages","Gravatar","VanillaInThisDiscussion","SplitMerge","Tagging","CreativeSyntaxHighlighter","Voting","editor","Sprites");
        $('.new_installation').show();
        $('#installation_type_description div, .envato_installation').not('#installation_show_hide_options').hide();
        $('#installation_type_description_webdeveloping').show();
    }
    if($('#installation_type').val()=='forum'){
        var arr_options = new Array("vanilla","theme","BasicPages","Yaga","Gravatar","VanillaInThisDiscussion","SplitMerge","Tagging","Voting","editor","Sprites");
        $('.new_installation').show();
        $('#installation_type_description div, .envato_installation').not('#installation_show_hide_options').hide();
        $('#installation_type_description_forum').show();
    }
    if($('#installation_type').val()=='clean'){
        var arr_options = new Array("vanilla","theme");
        $('.new_installation').show();
        $('#installation_type_description div, .envato_installation').not('#installation_show_hide_options').hide();
        $('#installation_type_description_clean').show();
    }
    if($('#installation_type').val()=='existing'){
        var arr_options = new Array("theme");
        $('.new_installation').hide();
        $('#installation_type_description div, .envato_installation').not('#installation_show_hide_options').hide();
        $('#installation_type_description_existing').show();
    }
    if($('#installation_type').val()=='custom'){
        var arr_options = new Array();
        $('.new_installation, .envato_installation').hide();
        $('#installation_type_description div').not('#installation_show_hide_options').hide();
        $('#installation_type_description_custom').show();
        hideShow('#show_options','#hide_options,#installation_options');
    }

    $('#installation_form input[type=checkbox]').attr('checked', false);
    for(i=0; i<arr_options.length; i++){
        $('#installation_form input[value='+arr_options[i]+']').attr('checked', true);
    }
}

var total_downloads = 0;
var total_extracts = 0;
var total_copies = 0;
var total_activations = 0;

function download(){

    $('#installation_log').append('<h2>Download phase</h2><ul id="download_log"></ul>');

    animateProgressbarTo('25%',20000,false);

    var ajax_count=0;
    $('.download').each(function(index) {

        if($(this).parent().find('input[type=checkbox]').is(':checked')){

            var installation_option_id = $(this).parent().attr('id').substr(13);
            var installation_option_name = $(this).parent().find('.name').html();

            $('#download_log').append('<li id="download_log_'+installation_option_id+'"><span class="downloading" title="downloading..."></span>'+installation_option_name+'<span class="magenta"> - downloading...</span></li>');

            $.ajax({
                type: "POST",
                url: "installation.php",
                data: {
                    'installation_option_action' : 'download',
                    'installation_option_id'     : installation_option_id
                },
                success: function(out){

                    if(out!=1)
                        $('#download_log_'+installation_option_id).html('<span class="cross" title="error"></span>'+installation_option_name+'<span class="red"> - '+out+'</span></li>');
                    else
                        $('#download_log_'+installation_option_id).html('<span class="check" title="downloaded"></span>'+installation_option_name+'<span class="green"> - downloaded</span></li>');

                    ajax_count++;
                    if(ajax_count==total_downloads)
                        extract();

                    return false;
                }
            });

        }

    });

}

function extract(){

    $('#installation_log').append('<h2>Extraction phase</h2><ul id="extract_log"></ul>');

    animateProgressbarTo('50%',20000,false);

    var ajax_count=0;
    $('.extract').each(function(index) {

        if($(this).parent().find('input[type=checkbox]').is(':checked')){

            var installation_option_id = $(this).parent().attr('id').substr(13);
            var installation_option_name = $(this).parent().find('.name').html();

            $('#extract_log').append('<li id="extract_log_'+installation_option_id+'"><span class="extracting" title="extracting..."></span>'+installation_option_name+'<span class="magenta"> - extracting...</span></li>');

            $.ajax({
                type: "POST",
                url: "installation.php",
                data: {
                    'installation_option_action' : 'extract',
                    'installation_option_id'     : installation_option_id
                },
                success: function(out){

                    if(out!=1)
                        $('#extract_log_'+installation_option_id).html('<span class="cross" title="error"></span>'+installation_option_name+'<span class="red"> - '+out+'</span></li>');
                    else
                        $('#extract_log_'+installation_option_id).html('<span class="check" title="extracted"></span>'+installation_option_name+'<span class="green"> - extracted</span></li>');

                    ajax_count++;
                    if(ajax_count==total_extracts)
                        copy();

                    return false;
                }
            });

        }

    });

}


var arr_ids = new Array();
var arr_names = new Array();

function copy(){

    $('#installation_log').append('<h2>Copy phase</h2><ul id="copy_log"></ul>');

    animateProgressbarTo('75%',20000,false);

    var i=0;
    $('.copy').each(function(index) {

        if($(this).parent().find('input[type=checkbox]').is(':checked')){
            arr_ids[i] = $(this).parent().attr('id').substr(13);
            arr_names[i] = $(this).parent().find('.name').html();
            i++;
        }

    });

    copyAsync(0);

}

function copyAsync(index){

    var installation_option_id = arr_ids[index];
    var installation_option_name = arr_names[index];

    $('#copy_log').append('<li id="copy_log_'+installation_option_id+'"><span class="copying" title="copying..."></span>'+installation_option_name+'<span class="magenta"> - copying...</span></li>');

    $.ajax({
        type: "POST",
        url: "installation.php",
        data: {
            'installation_option_action' : 'copy',
            'installation_option_id'     : installation_option_id,
            'forum_path'                 : $('#forum_path').val()
        },
        success: function(out){

            if(out!=1)
                $('#copy_log_'+installation_option_id).html('<span class="cross" title="error"></span>'+installation_option_name+'<span class="red"> - '+out+'</span></li>');
            else
                $('#copy_log_'+installation_option_id).html('<span class="check" title="copied"></span>'+installation_option_name+'<span class="green"> - copied</span></li>');

            if(index < arr_ids.length-1)
                copyAsync(index+1);
            else
                activate();

            return false;
        }
    });


}

function activate(){

    $('#installation_log').append('<h2>Activation phase</h2><ul id="activate_log"></ul>');

    animateProgressbarTo('100%',20000,false);

    arr_ids = new Array();
    arr_names = new Array();

    var i=0;
    $('.activate').each(function(index) {

        if($(this).parent().find('input[type=checkbox]').is(':checked')){

            arr_ids[i] = $(this).parent().attr('id').substr(13);
            arr_names[i] = $(this).parent().find('.name').html();
            i++;

        }

    });

    activateAsync(0);

}

function activateAsync(index){

    var installation_option_id = arr_ids[index];
    var installation_option_name = arr_names[index];

    $('#activate_log').append('<li id="activate_log_'+installation_option_id+'"><span class="activating" title="activating..."></span>'+installation_option_name+'<span class="magenta"> - activating...</span></li>');

    if(installation_option_id=='vanilla'){

        $.ajax({
            type: "POST",
            url: $('#forum_url').val()+($('#forum_url').val().slice(-1)=='/' ? '' : '/')+"index.php",
            async: false
        });

        $.ajax({
            type: "POST",
            url: $('#forum_url').val()+($('#forum_url').val().slice(-1)=='/' ? '' : '/')+"index.php?p=/dashboard/setup",
            data: {
                'TransientKey'            : '',
                'hpt'                     : '',
                'RewriteUrls'             : '1',
                'Database-dot-Host'       : $('#database_host').val(),
                'Database-dot-Name'       : $('#database_name').val(),
                'Database-dot-User'       : $('#database_user').val(),
                'Database-dot-Password'   : $('#database_password').val(),
                'Garden-dot-Title'        : $('#forum_title').val(),
                'Email'                   : $('#admin_email').val(),
                'Name'                    : $('#admin_username').val(),
                'Password'                : $('#admin_password').val(),
                'PasswordMatch'           : $('#admin_password').val()
            },
            success: function(out){

                $('#activate_log_'+installation_option_id).html('<span class="check" title="activated"></span>'+installation_option_name+'<span class="green"> - activated</span></li>');

                if(index < arr_ids.length-1)
                    activateAsync(index+1);
                else
                    success();

            },
            error:  function(out){

                console.log(out);

                $('#activate_log_'+installation_option_id).html('<span class="cross" title="error"></span>'+installation_option_name+'<span class="red"> - index file not found ('+$('#forum_path').val()+'/index.php)</span></li>');

                if(index < arr_ids.length-1)
                    activateAsync(index+1);
                else
                    success();

            }
        });

    }else{

        $.ajax({
            type: "POST",
            url: "installation.php",
            data: {
                'installation_option_action' : 'activate',
                'installation_option_id'     : installation_option_id,
                'forum_path'                 : $('#forum_path').val(),
                'envato_username'            : $('#envato_username').val(),
                'envato_key'                 : $('#envato_key').val()
            },
            success: function(out){

                if(out!=1)
                    $('#activate_log_'+installation_option_id).html('<span class="cross" title="error"></span>'+installation_option_name+'<span class="red"> - '+out+'</span></li>');
                else
                    $('#activate_log_'+installation_option_id).html('<span class="check" title="activated"></span>'+installation_option_name+'<span class="green"> - activated</span></li>');

                if(index < arr_ids.length-1)
                    activateAsync(index+1);
                else
                    success();

                return false;
            }
        });

    }

}

function success(){
    animateProgressbarTo('100%',300,true);
    hideShow('#hide_log,#installation_log','#view_log');
}

function animateProgressbarTo(w,d,done){
    $("#progressbar .ui-progressbar-value").stop(true).animate({
        width: w
    },{
        duration: d,
        easing: 'easeOutCubic',
        complete: function(){
            if(done){

                $('#installation_log_title').html('Installation complete');
                $('#installation_log_description').html('The installation process has been completed.');

                if($('.cross').length > 0){
                    $('#installation_log_msg').append('<h2>Installation Failed</h2><p>There were some errors durring the installation. Pease view the <a href="javascript: hideShow(\'#view_log\',\'#hide_log,#installation_log\');">Log</a> to understand what happened.</p><p>If you can\'t find a solution for your problem, please go to our <a href="http://www.one-click-forum.com/support/" target="_blank">Support Center</a>, see the <a href="http://www.one-click-forum.com/support/faq/" target="_blank">FAQs</a> or go to our <a href="http://www.one-click-forum.com/support/forum/" target="_blank">Support Forum</a> to get a fast response.</p>').addClass('error_msg');
                    hideShow('#view_log','#hide_log, #installation_log, #installation_log_msg');
                }else{
                    $('#installation_log_msg').append('<h2>Installation Completed Successfully</h2><p>You can now access to your new forum by clicking bellow:</p><a href="'+$('#forum_url').val()+($('#forum_url').val().slice(-1)=='/' ? '' : '/')+'" target="_blank" class="button">OPEN YOUR NEW FORUM</a>').addClass('ok_msg');
                    hideShow('','#installation_log_msg');
                }

            }
        }
    });
}

function oneClickInstallation(){

    $.ajax({
        type: "POST",
        url: "installation.php",
        data: $('#installation_form').serialize(),
        async : false,
        success: function(out){

            if(out!=1){

                $('#installation_form_msg').html(out).show();

            }else{

                $("#progressbar").progressbar({value: 1});

                hideShow('#installation_form','#installation_log_container');

                total_downloads = $('.download').parent().find('input:checked').length;
                total_extracts = $('.extract').parent().find('input:checked').length;
                total_copies = $('.copy').parent().find('input:checked').length;
                total_activations = $('.activate').parent().find('input:checked').length;

                if(total_downloads > 0)
                    download();
                else if(total_extracts > 0)
                    extract();
                else if(total_copies > 0)
                    copy();
                else
                    activate();

            }

            return;
        }
    });

}

$(function(){

    changeInstallationType();

    $('input[type=checkbox]').change(function(){
        $('#installation_type').val('custom');
    });

    if($('#installation_vanilla input').is(':checked'))
        $('.new_installation').show();
    else
        $('.new_installation').hide();

    $('#installation_vanilla input').change(function(){
        if($(this).is(':checked'))
            $('.new_installation').show();
        else
            $('.new_installation').hide();
    });

    if($('#installation_envato input').is(':checked'))
        $('.envato_installation').show();
    else
        $('.envato_installation').hide();

    $('#installation_envato input').change(function(){
        if($(this).is(':checked'))
            $('.envato_installation').show();
        else
            $('.envato_installation').hide();
    });

    $('#envato_api_key').fancybox({
        'transitionIn'	:	'elastic',
        'transitionOut'	:	'elastic',
        'speedIn'		:	300,
        'speedOut'		:	200
    });

});
</script>