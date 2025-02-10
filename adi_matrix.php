<?php
// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'adi_matrix';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '3.0.0';
$plugin['author'] = 'Adi Gilbert / Stef Dawson';
$plugin['author_uri'] = 'https://www.stefdawson.com/';
$plugin['description'] = 'Multi-article update panels';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public              : only on the public side of the website (default)
// 1 = public+admin        : on both the public and admin side
// 2 = library             : only when include_plugin() or require_plugin() is called
// 3 = admin               : only on the admin side (no AJAX)
// 4 = admin+ajax          : only on the admin side (AJAX supported)
// 5 = public+admin+ajax   : on both the public and admin side (AJAX supported)
$plugin['type'] = '1';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = 0x0001 | 0x0002;

// Plugin 'textpack' is optional. It provides i18n strings to be used in conjunction with gTxt().
// Syntax:
// ## arbitrary comment
// #@event
// #@language ISO-LANGUAGE-CODE
// abc_string_name => Localized String

$plugin['textpack'] = <<<EOT
#@owner adi_matrix
#@language en, en-gb, en-us
#@admin-side
adi_article_matrix => Article Matrix
adi_matrix_default_sort => Default sort
adi_matrix_alphabetical => Alphabetical
adi_matrix_numerical => Numerical
adi_matrix_sort_type => Sort type
#@prefs
adi_matrix => Adi Matrix
adi_matrix_article_highlighting => Article title highlighting
adi_matrix_article_tooltips => Article title tooltips
adi_matrix_display_id => Show article IDs
adi_matrix_input_field_tooltips => Input field tooltips
adi_matrix_jquery_ui => jQuery UI script file
adi_matrix_jquery_ui_css => jQuery UI CSS file
adi_matrix_tinymce => TinyMCE settings
adi_matrix_tiny_mce => Use TinyMCE
adi_matrix_tiny_mce_dir => TinyMCE directory path
adi_matrix_tiny_mce_config => TinyMCE configuration
#@adi_matrix_admin
adi_matrix_heading => Matrix
adi_matrix_any_category => Any category
adi_matrix_any_child_category => Any child category
adi_matrix_article_data => Article Display
adi_matrix_article_limit => Maximum number of articles
adi_matrix_articles_not_modified => No articles modified
adi_matrix_article_selection => Article Selection
adi_matrix_article_update_fail => Article update failed
adi_matrix_articles_saved => Articles saved
adi_matrix_blank_url_title => URL-only title blank
adi_matrix_cancel => Cancel
adi_matrix_custom_condition => Custom condition
adi_matrix_cf_links => Custom field links
adi_matrix_display_article_id => Display article ID#
adi_matrix_duplicate_url_title => URL-only title already used
adi_matrix_edit_preferences => Plugin preferences
adi_matrix_edit_titles => Edit titles
adi_matrix_expiry => Expiry
adi_matrix_footer => Footer
adi_matrix_has_expiry => Has expiry
adi_matrix_include_descendent_cats => Include descendent categories
adi_matrix_install_fail => Unable to install
adi_matrix_installed => Installed
adi_matrix_invalid_timestamp => Invalid timestamp
adi_matrix_logged_in_user => Logged in user
adi_matrix_admin => Article Matrix Admin
adi_matrix_total_articles => Total articles in matrix:
adi_matrix_cfs_modified => Custom field list modified
adi_matrix_delete_fail => Matrix delete failed
adi_matrix_deleted => Matrix deleted
adi_matrix_validation_error => Validation errors
adi_matrix_name => Matrix name
adi_matrix_order => Matrix order
adi_matrix_update_fail => Matrix settings update failed
adi_matrix_updated => Matrix settings updated
adi_matrix_new_article => New article
adi_matrix_no_category => No category
adi_matrix_no_expiry => No expiry
adi_matrix_not_installed => Not installed
adi_matrix_ok => OK
adi_matrix_one_category => One category
adi_matrix_any_parent_category => Any parent category
adi_matrix_reset => Reset
adi_matrix_scroll => Scroll
adi_matrix_show_section => Show section
adi_matrix_sort => Sort by
adi_matrix_sort_direction => Sort direction
adi_matrix_tab => Tab
adi_matrix_time_any => Any time
adi_matrix_time_future => Future
adi_matrix_time_past => Past
adi_matrix_two_categories => Two categories
adi_matrix_uninstall => Uninstall
adi_matrix_uninstall_fail => Unable to uninstall
adi_matrix_uninstalled => Uninstalled
adi_matrix_update_matrix => Update matrix settings
adi_matrix_upgrade_fail => Unable to upgrade
adi_matrix_upgrade_required => Upgrade required
adi_matrix_upgraded => Upgraded
adi_matrix_user => User
EOT;

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
/*
    adi_matrix - Multi-article update tabs

    Written by Adi Gilbert

    Released under the GNU General Public License

    Custom fields
    - "standard" TXP custom fields: custom_1 ...custom_10, always present
    - with glz_custom_fields, standard CFs can disappear (on "reset"), or additional ones added: custom_11 ...

    Upgrade notes (1.0 - 1.1+)
    - due to bug in 1.0, expires sort option will get changed to modified

    Downgrade (from 2.0 to 1.1/1.2 only)
    - go to adi_matrix plugin options tab
    - add "&step=downgrade" to end of URL & hit return
    - then immediately install previous version of adi_matrix
    - BEWARE: multiple sections won't translate very well
    - adi_matrix_article_limit pref will be reset to 100

*/

/* TODO
    - fix up proper ui-icon/ui-icon-pencil for edit link
*/

if (txpinterface === 'admin') {
    new adi_matrix();
}

class adi_matrix
{
    protected $event = 'adi_matrix';
    protected $privs = array();
    protected $debug = 0; // general debuggy info
    protected $dump = 0; // dump of article data
    protected $plugin_status = false;
    protected $has_glz_cf = false;
    protected $is_txp460 = false;
    protected $is_txp470 = false;
    protected $is_cfs_v20 = false;
    protected $nulldatetime = '';
    protected $categories = array();

    /**
     * Plugin startup.
     */
    public function __construct()
    {
        global $event, $step, $prefs, $txp_groups, $txp_user, $txp_permissions, $adi_matrix_cfs, $adi_matrix_list, $adi_matrix_validation_errors;

        // using article_validate & new default section pref (4.5.0), so decamp sharpish if need be
        if (!version_compare(txp_version,'4.5.0','>=')) {
            return;
        }

        $this->is_txp460 = (version_compare(txp_version,'4.6-dev','>='));
        $this->is_txp470 = (version_compare(txp_version,'4.7-dev','>='));
        $this->nulldatetime = ($this->is_txp460 ? 'NULL' : NULLDATETIME);

        // defines privileges required to view a matrix with privilege restriction (same indexing as $txp_groups)
        $this->privs = array(
            1 => '1',           // publisher
            2 => '1,2',         // managing_editor
            3 => '1,2,3',       // copy_editor
            4 => '1,2,3,4',     // staff_writer
            5 => '1,2,3,4,5',   // freelancer
            6 => '1,6',         // designer
        );

        // plugin lifecycle
        register_callback(array($this, 'lifecycle'),'plugin_lifecycle.' . $this->event);

        // adi_matrix admin tab
        add_privs('adi_matrix_admin'); // add priv group - defaults to priv '1' only
        register_tab('extensions','adi_matrix_admin',gTxt('adi_article_matrix')); // add new tab under 'Extensions'
        register_callback(array($this, 'matrix_admin'),'adi_matrix_admin');

        // look for glz_custom_fields
        $this->has_glz_cf = load_plugin('glz_custom_fields');

        if ($this->has_glz_cf) {
            $glz_cfs_version = safe_field("version", 'txp_plugin', 'name = "glz_custom_fields"');
            $this->is_cfs_v20 = (version_compare($glz_cfs_version, '1.9','>=')); // using 1.9 coz it don't recognise "2.0 beta" (no hyphen?)
        }

        if ($this->debug) {
            echo "<b>glz_custom_fields:</b>".br;

            if ($this->has_glz_cf) {
                echo "version = $glz_cfs_version";

                if ($this->is_cfs_v20) {
                    echo " (v2.0)";
                }
            } else {
                echo "not installed";
            }

            echo br;
        }

        /*  User privilege summary:
                0 - none            - can't even login
                1 - publisher       - full matrix data & adi_matrix admin capability
                2 - manager         - matrix data only
                3 - copy editor     - matrix data only
                4 - staff writer    - matrix data only
                5 - freelancer      - matrix data only
                6 - designer        - matrix data only
            Standard article editing privileges:
                'article.edit'                => '1,2,3',
                'article.edit.published'      => '1,2,3',
                'article.edit.own'            => '1,2,3,4,5,6',
                'article.edit.own.published'  => '1,2,3,4',
        */

        // discover custom fields (standard 1-10 & glz 11+) and their non-lowercased titles
        $adi_matrix_cfs = getCustomFields();

        foreach ($adi_matrix_cfs as $index => $value)
            $adi_matrix_cfs[$index] = $prefs['custom_'.$index.'_set']; // index = custom fields number, value = custom field title

        // build a picture of article categories
        $this->categories = $this->get_categories(getTree('root','article'));

        // validation errors
        $adi_matrix_validation_errors = array(
            0 => gTxt('adi_matrix_invalid_timestamp'),
            1 => gTxt('article_expires_before_postdate'),
            2 => gTxt('adi_matrix_duplicate_url_title'),
            3 => gTxt('adi_matrix_blank_url_title'),
        );

        $this->plugin_status = fetch('status', 'txp_plugin', 'name', 'adi_matrix', $this->debug);

        add_privs('prefs.' . $this->event, '1,2');
        add_privs('plugin_prefs.' . $this->event, '1, 2');
        register_callback(array($this, 'options'),'plugin_prefs.' . $this->event);

        // glz_custom_fields stuff
        if ($this->has_glz_cf) {
            if (strstr($event, 'adi_matrix_matrix_')) {
                // date & time pickers
                if ($this->is_cfs_v20) {
                    add_privs('glz_custom_fields_inject_css_js',"1,2,3,4,5,6");
                    register_callback('glz_custom_fields_inject_css_js','admin_side','head_end');
                } else {
                    add_privs('glz_custom_fields_css_js',"1,2,3,4,5,6");
                    register_callback('glz_custom_fields_css_js','admin_side','head_end');
                }

                // TinyMCE
                if ($this->get_pref('adi_matrix_tiny_mce')) {
                    register_callback(array($this, 'tiny_mce_style'),'admin_side','head_end');
                    register_callback(array($this, 'tiny_mce_'.$this->get_pref('adi_matrix_tiny_mce_type')),'admin_side','footer');
                }

            }
        }
                register_callback(array($this, 'inject_prefs_js'), 'prefs');

        // article matrix tabs
        $adi_matrix_list = array();

        if ($this->installed()) {
            $adi_matrix_list = $this->read_settings($this->upgrade());
        }

        $all_privs = '1,2,3,4,5,6'; // everybody

        foreach ($adi_matrix_list as $index => $matrix) {
            $matrix_event = 'adi_matrix_matrix_'.$index;
            $matrix_tab_name = $matrix['name'];

            if ($matrix['privs']) {
                $priv_set = $this->privs[$matrix['privs']];
            } else {
                $priv_set = $all_privs; // everybody's welcome
            }

            add_privs($matrix_event,$priv_set); // add priv set for each matrix event (to $txp_permissions)

            if ($matrix['user']) {
                $user_allowed = ($txp_user == $matrix['user']);
            } else {
                // open to all users
                $user_allowed = true;
            }

            $has_privs = has_privs($matrix_event);
            $register_this_tab = ($user_allowed && $has_privs) || has_privs('adi_matrix_admin');

            if ($register_this_tab) {
                $tab = $matrix['tab'];

                if ($tab == 'start') {
                    // switch on Home tab
                    add_privs('tab.start',$all_privs); // all privs
                }

                register_tab($tab, $matrix_event,$matrix_tab_name);
                register_callback(array($this, 'matrix_matrix'), $matrix_event);
            }

            if ($this->debug) {
                echo "MATRIX: index=$index, event=$matrix_event, name=".$matrix['name'].", user=".$matrix['user'].", privs=".$matrix['privs'].", priv_set=$matrix_event($priv_set), tab=".$matrix['tab'].br;
                $user_privs = safe_field('privs','txp_users',"name='$txp_user'");
                echo "USER: user=$txp_user, user_privs=$user_privs, user_allowed=$user_allowed, has_privs=$has_privs".br;
                echo "TAB: register_this_tab=$register_this_tab".br.br;
            }
        }

        if ($this->debug) {
            echo '<b>$adi_matrix_privs:</b>';
            dmp($this->privs);
            echo '<b>adi_matrix added priv sets ($txp_permissions):</b>'.br;

            foreach ($txp_permissions as $index => $value) {
                if (strpos($index,'adi_matrix') === 0) {
                    echo $index.' = '.$value.br;
                }
            }
        }

        // style
        if (strstr($event,'adi_matrix_matrix_') || ($event == 'adi_matrix_admin')) {
            register_callback(array($this, 'inject_styles'),'admin_side','head_end');
        }

        // script
        if (strstr($event,'adi_matrix_admin')) {
            register_callback(array($this, 'matrix_admin_script'),'admin_side','head_end');
        }

        if (strstr($event,'adi_matrix_matrix_')) {
            register_callback(array($this, 'matrix_script'),'admin_side','head_end');
        }
    }

    // some style (Classic: table#list, Remora/Hive: table.txp-list)
    public function inject_styles()
    {
        global $prefs;

        $rules = <<<EOCSS
/* admin tab */
.adi_matrix_admin input.radio { margin-left:0.5em }
.adi_matrix_field label { display:block; float:left; width:8em }
.adi_matrix_field label.adi_matrix_label2 { width:auto }
.adi_matrix_field p { min-height:1.2em }
.adi_matrix_field p > span { float:left; width:8em } /* pseudo label */
.adi_matrix_field p > span + label, .adi_matrix_field p > span + label + label { width:auto } /* radio labels */
.adi_matrix_custom_field label { width:12em }
.adi_matrix_multi_checkboxes { margin:0.3em 0 0.5em; height:5em; padding:0.2em; overflow:auto; border:1px solid #ccc }
.adi_matrix_multi_checkboxes label { float:none; width:auto }
.adi_matrix_admin_delete { position: absolute; right: 0.5em }
.adi_matrix_row, .adi_matrix_data_block { display: flex; grid-gap:1em; }
.adi_matrix_row { position: relative; flex-direction: column; border:1px solid #ccc; padding:0.5em; }
@media (min-width:47.05em) {
   .adi_matrix_row, .adi_matrix_data_block { flex-direction: row; justify-content:space-between; }
}
/* matrix tabs */
.adi_matrix_matrix table#list th.adi_matrix_noborder { border:0 }
.adi_matrix_none { margin-top:2em; text-align:center }
.adi_matrix_field_title { white-space:nowrap } /* stops edit link dropping below */
.adi_matrix_timestamp { white-space:nowrap } /* stops date or time being split */
.adi_matrix_future a { font-weight:bold }
.adi_matrix_expired a { font-style:italic }
.adi_matrix_error input { border-color:#b22222; color:#b22222 }
.adi_matrix_edit_link span { display:inline-block; width:20px; height:20px; }
/* matrix tabs 4.5 */
.txp-list .adi_matrix_timestamp .time input { margin-top:0.5em }
.txp-list .adi_matrix_timestamp { min-width:11em }
/* matrix tabs 4.6 */
.txp-list th,.txp-list td { padding-left:0.5em }
/* glz_custom_fields */
html[xmlns] td.glz_custom_date-picker_field.clearfix { display:table-cell!important } /* override clearfix  - for date-picker field */
/*.adi_matrix_matrix input.date-picker { float:left; width:7em }*/
.adi_matrix_matrix input.date-picker { width:7em } /* 2.0 layout tweak */
.adi_matrix_matrix td.glz_custom_date-picker_field a.dp-choose-date { display:block; clear:left } /* 2.0 layout tweak */
/*.adi_matrix_matrix td.glz_custom_date-picker_field { min-width:10em }*/ /* 2.0 layout tweak */
.adi_matrix_matrix input.time-picker { width:4em }
/* tinyMCE */
.adi_matrix_matrix .glz_text_area_field div.tie_div { overflow-y:scroll; width:17.6em; height:5.6em; padding:0.2em; border:1px solid; border-color:#aaa #eee #eee #aaa; background-color: #eee }
/* scrolling matrix */
.adi_matrix_scroll table#list th:first-child,
.adi_matrix_scroll table#list td:first-child { position:absolute; width:13%; left:0; top:auto; padding-right:1em; border-bottom-width:0 }
.adi_matrix_scroll table#list thead th:first-child { border-bottom-width:1px }
.adi_matrix_scroll div.scroll_box { width:87%; margin-left:13%; padding-bottom:1em;overflow-x:scroll; overflow-y:visible; border:solid #eee; border-width:0 1px }
EOCSS;

        if ($prefs['theme_name'] == 'hive') {
            $rules .= <<<EOCSS
p.prev-next, form.pageby { text-align:center }
EOCSS;
        }

        if (class_exists('\Textpattern\UI\Style')) {
            $css = Txp::get('\Textpattern\UI\Style')->setContent($rules);
        } else {
            $css = '<style>' . $rules . '</style>';
        }

        echo $css;
    }

    // jQuery magic for admin tab
    public function matrix_admin_script()
    {
        echo script_js(<<<END_SCRIPT
$(function() {
    var adi_matrix_last_edited = localStorage.getItem('adi_matrix_selected');

    if ($("#matrix_id option[value='"+adi_matrix_last_edited+"']").length == 0) {
        adi_matrix_last_edited = 'new';
        localStorage.setItem('adi_matrix_selected', 'new');
    }

    $('#matrix_id').val(adi_matrix_last_edited);

    $("#peekaboo").hide();
    $('input[name="adi_matrix_tiny_mce"][value="1"]:checked').each(function(){
        $("#peekaboo").show();
    });
    $('input[name="adi_matrix_tiny_mce"]:radio:eq(0)').change(function(){
        $("#peekaboo").show();
    });
    $('input[name="adi_matrix_tiny_mce"]:radio:eq(1)').change(function(){
        $("#peekaboo").hide();
    });

    // Handle hide/show of the matrices and store the selected one.
    $('#matrix_id').on('change', function(e) {
        let sel = this.value;
        $('.adi_matrix_admin .adi_matrix_row').hide();
        $('.adi_matrix_admin #matrix_id_'+sel).show();
        localStorage.setItem('adi_matrix_selected', sel);
    }).change();
});
END_SCRIPT
        );
    }

    // jQuery action
    public function matrix_script()
    {
        // add class to <body>
        echo script_js(<<<END_SCRIPT
$(function(){
    $('body').addClass('adi_matrix');
    // enforce unique ids on glz checkboxes
    $('table.adi_matrix_matrix td.glz_custom_checkbox_field input.checkbox').each(function() {
        var name = $(this).attr('name'); // get value of name (contains article id)
        var new_name = name.replace('article_','');
        var matches = new_name.match(/^[0-9,new]*/); // extract article id#
        var new_id = this.id + '_' + matches[0]; // use article id# as suffix for tag id
        $(this).attr('id',new_id); // set id on input tag
        var td = $(this).parent();
        $('label',td).attr('for',new_id); // set id on label tag
//          console.log(name,new_name,res[0],td);
    });
});
END_SCRIPT
        );
    }

    // get matrix settings from database
    public function read_settings($just_the_basics=false)
    {
        global $adi_matrix_cfs;

        $rs = safe_rows_start('*','adi_matrix',"1=1 ORDER BY `ordinal` ASC, `id` ASC");
        $matrix_list = array();

        if ($rs) {
            while ($a = nextRow($rs)) {
                extract($a);
                // just enough to display matrix tab
                $matrix_list[$id]['ordinal'] = $ordinal;
                $matrix_list[$id]['name'] = $name;
                $matrix_list[$id]['user'] = $user;
                $matrix_list[$id]['privs'] = $privs;

                if (!isset($tab)) {
                    // tab introduced in v2.0, so may not be present during upgrade install
                    $tab = 'content';
                }

                $matrix_list[$id]['tab'] = $tab;

                // load in the rest
                if (!$just_the_basics) {
                    $the_rest = array('sort','dir','sort_type','scroll','footer','title','publish','show_section','cf_links','criteria_section','criteria_category','criteria_descendent_cats','criteria_status','criteria_author','criteria_keywords','criteria_timestamp','criteria_expiry','criteria_condition','status','keywords','article_image','category1','category2','posted','expires','section');

                    foreach ($the_rest as $item) {
                        $matrix_list[$id][$item] = $$item;
                    }

                    // custom fields
                    foreach ($adi_matrix_cfs as $index => $value) {
                        $custom_x = 'custom_'.$index;

                        if (isset($$custom_x)) {
                            // check that custom field is known to adi_matrix
                            $matrix_list[$id][$custom_x] = $$custom_x;
                        }
                    }
                }
            }
        }

        return $matrix_list;
    }

    // take matrix criteria & create a WHERE clause
    // mostly ripped off from doArticles() in publish.php
    public function build_where($criteria=array())
    {
        if ($this->debug) {
            echo "<b>Criteria:</b>";
            dmp($criteria);
        }

        extract($criteria);
        $excerpted = '';
        $month = '';
        $time = $timestamp;

        // categories
        $cats = array();

        if ($category == '!no_category!') {
            $category = " AND (Category1 = '' AND Category2 = '')";
        } elseif ($category == '!any_category!') {
            $category = " AND (Category1 != '' OR Category2 != '')";
        } elseif ($category == '!one_category!') {
            $category = " AND (Category1 != '' AND Category2 = '') OR  (Category1 = '' AND Category2 != '')";
        } elseif ($category == '!two_categories!') {
            $category = " AND (Category1 != '' AND Category2 != '')";
        } elseif ($category == '!any_parent_category!') {
            foreach ($this->categories as $name => $this_cat) {
                if ($this_cat['children']) {
                    $cats[] = $name;
                }
            }

            $category = implode(',',$cats);
            $category = implode("','", doSlash(do_list($category)));
            $category = (!$category) ? '' : " AND (Category1 IN ('".$category."') OR Category2 IN ('".$category."'))";
        } elseif ($category == '!any_child_category!') {
            foreach ($this->categories as $name => $this_cat) {
                if ($this_cat['parent'] != 'root') {
                    $cats[] = $name;
                }
            }

            $category = implode(',',$cats);
            $category = implode("','", doSlash(do_list($category)));
            $category = (!$category) ? '' : " AND (Category1 IN ('".$category."') OR Category2 IN ('".$category."'))";
        } else {
            // single category (perhaps with optional descendents)
            if ($descendent_cats) {
                $category .= ','.implode(',',$this->categories[$category]['children']);
            }

            $category = implode("','", doSlash(do_list($category)));
            $category = (!$category) ? '' : " AND (Category1 IN ('".$category."') OR Category2 IN ('".$category."'))";
        }

        $section   = (!$section) ? '' : " AND Section IN ('".implode("','", doSlash(do_list($section)))."')";
        $excerpted = ($excerpted == 'y') ? " AND Excerpt !=''" : '';

        if ($author == '!logged_in_user!') {
            $author = $txp_user;
        }

        $author    = (!$author) ? '' : " AND AuthorID IN ('".implode("','", doSlash(do_list($author)))."')";
        $month     = (!$month) ? '' : " AND Posted LIKE '".doSlash($month)."%'";

        // posted timestamp
        switch ($time) {
            case 'any':
                $time = "";
                break;
            case 'future':
                $time = " AND Posted > now()";
                break;
            default:
                $time = " AND Posted <= now()";
        }

        // expiry
        switch ($expiry) {
            case '1': // no expiry set
                $time .= " AND Expires = ".$this->nulldatetime;
                break;
            case '2': // has expiry set
                $time .= " AND Expires != ".$this->nulldatetime;
                break;
            case '3': // expired
                $time .= " AND now() > Expires AND Expires != ".$this->nulldatetime;
                break;
        }

        $custom = ''; // MAY GET CONFUSING WITH criteria_condition

        if ($keywords) {
            $keys = doSlash(do_list($keywords));

            foreach ($keys as $key) {
                $keyparts[] = "FIND_IN_SET('".$key."',Keywords)";
            }

            $keywords = " AND (" . implode(' or ',$keyparts) . ")";
        }

        if ($status) {
            $statusq = ' AND Status = '.intval($status);
        } else {
            // either blank or zero
            $statusq = ''; // all statuses
        }

        if ($condition) {
            $conditionq = ' AND '.$condition;
        } else {
            $conditionq = '';
        }

        $where = '1'.$statusq.$time.$category.$section.$excerpted.$month.$author.$keywords.$custom.$conditionq;

        if ($this->debug) {
            echo "<b>WHERE clause:</b> $where".br;
        }

        return $where;
    }

    // generate ORDER BY clause
    // mostly ripped off from doArticles() in publish.php
    public function build_order_by($sort,$dir,$sort_type)
    {
        // map columns to sort query
        switch ($sort) {
            case 'posted':
                $sortq = 'Posted';
                break;
            case 'expires':
                $sortq = 'Expires';
                break;
            case 'lastmod':
                $sortq = 'LastMod';
                break;
            case 'title':
                $sortq = 'Title';
                break;
            case 'id':
                $sortq = 'ID';
                break;
            case 'status':
                $sortq = 'Status';
                break;
            case 'article_image':
                $sortq = 'Image';
                break;
            case 'category1':
                $sortq = 'Category1';
                break;
            case 'category2':
                $sortq = 'Category2';
                break;
            case 'section':
                $sortq = 'Section';
                break;
            default:
                // custom_x will fall through to here
                // find out if column (glz custom field probably) still exists
                $rs = safe_query('SHOW FIELDS FROM '.safe_pfx('textpattern')." LIKE '$sort'",$this->debug); 
                $a = nextRow($rs);

                if (empty($a)) {
                    $sortq = 'Posted';
                } else {
                    $sortq = $sort;
                }
        }

        // sort type
        if ($sort_type == 'numerical') {
            $sort_typeq = ' + 0';
        } else {
            $sort_typeq = '';
        }

        // sort it all out
        $sortq = ' ORDER BY '.$sortq.$sort_typeq.' '.$dir.', Posted desc'; // add direction ... & also secondary sort (pointless but harmless with ID, Posted & Expires)

        if ($this->debug) {
            echo "<b>Sort:</b> sort=$sort, dir=$dir, sort_type=$sort_type".br;
            echo "<b>ORDER BY clause:</b> $sortq".br;
        }

        return $sortq;
    }

    // read required articles from database and populate $adi_matrix_articles
    public function get_articles($matrix_index,$offset,$limit,$where='1',$sortq='')
    {
        global $adi_matrix_cfs,$adi_matrix_list;

        $adi_matrix_articles = array();

        // get the required articles from database
        $rs = safe_rows_start(
                "*, unix_timestamp(Posted) as uPosted, unix_timestamp(Expires) as uExpires, unix_timestamp(LastMod) as uLastMod"
                ,'textpattern'
                ,$where.doSlash($sortq).' LIMIT '.intval($offset).', '.intval($limit)
                ,$this->debug
                );

        if ($rs) {
            // populate $adi_matrix_articles array
            while ($a = nextRow($rs)) {
                extract($a);
                $adi_matrix_articles[$ID] = array();
                $adi_matrix_articles[$ID]['title'] = html_entity_decode($Title, ENT_QUOTES, 'UTF-8');
                $adi_matrix_articles[$ID]['section'] = $Section;
                $adi_matrix_articles[$ID]['author'] = $AuthorID; // need author for article edit priv check
                $adi_matrix_articles[$ID]['status'] = $Status; // need status for article edit priv check
                $adi_matrix_articles[$ID]['keywords'] = $Keywords;
                $adi_matrix_articles[$ID]['article_image'] = $Image;
                $adi_matrix_articles[$ID]['category1'] = $Category1;
                $adi_matrix_articles[$ID]['category2'] = $Category2;

                foreach ($adi_matrix_cfs as $index => $cf_name) {
                    $custom_x = 'custom_'.$index;
                    $adi_matrix_articles[$ID][$custom_x] = $$custom_x;
                }

                // article timestamps
                $adi_matrix_articles[$ID]['uposted'] = $uPosted; // unix timestamp format (in server timezone)
                $adi_matrix_articles[$ID]['posted'] = $Posted; // article date/time string (YY-MM-DD HH:MM:SS) from database
                $adi_matrix_articles[$ID]['display_posted'] = safe_strftime('%Y-%m-%d %X',$uPosted); // article date/time string (YY-MM-DD HH:MM:SS) displayed to user (TXP time)
                $adi_matrix_articles[$ID]['uexpires'] = $uExpires; // unix timestamp format (in server timezone)
                $adi_matrix_articles[$ID]['expires'] = $Expires; // article date/time string (YY-MM-DD HH:MM:SS) from database

                if ($Expires == '0000-00-00 00:00:00') {
                    // keep it zeroed
                    $adi_matrix_articles[$ID]['display_expires'] = $Expires;
                } else {
                    // article date/time string (YY-MM-DD HH:MM:SS) displayed to user (TXP time)
                    $adi_matrix_articles[$ID]['display_expires'] = safe_strftime('%Y-%m-%d %X',$uExpires);
                }

                // highlighting
                $highlight = 0;
                $now = time();

                if (($now > $uExpires) && ($uExpires != 0)) {
                    // expired article
                    $highlight = 1;
                }

                if ($now < $uPosted) {
                    // future article
                    $highlight = 2;
                }

                $adi_matrix_articles[$ID]['highlight'] = $highlight;
            }
        }

        return $adi_matrix_articles;
    }

    // translate $_POSTED article data into SQL-speak
    public function update_article($id,$data)
    {
        global $txp_user,$adi_matrix_cfs,$adi_matrix_articles,$vars,$prefs;

        include_once txpath.'/include/txp_article.php'; // to get textile_main_fields()

        // set up variables in the style of $vars
        $Title = $Title_plain = isset($data['title']) ? $data['title'] : '';
        $Status = isset($data['status']) ? $data['status'] : '';
        $Section = isset($data['section']) ? $data['section'] : '';
        $Keywords = isset($data['keywords']) ? trim(preg_replace('/( ?[\r\n\t,])+ ?/s', ',', preg_replace('/ +/', ' ', $data['keywords'])), ', ') : '';
        $Image = isset($data['article_image']) ? $data['article_image'] : '';
        $Category1 = isset($data['category1']) ? $data['category1'] : '';
        $Category2 = isset($data['category2']) ? $data['category2'] : '';

        // posted
        if (isset($data['posted']['reset_time'])) {
            $publish_now = '1';
        } else {
            $publish_now = '';
        }

        if (isset($data['posted'])) {
            // this is in TXP date/time
            extract($data['posted']);
            $Posted = $year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second;
        } else {
            // force now
            // NOT SURE WHY DOING THIS, BUT DOESN'T DO ANY HARM (IS IGNORED IF POSTED NOT CHANGED)
            $publish_now = '1';
        }

        // expires
        if (isset($data['expires'])) {
            // this is in TXP date/time
            foreach ($data['expires'] as $index => $value) {
                // convert expiry vars ($year -> $exp_year) to align with $vars in txp_article.php
                $var = 'exp_'.$index;
                $$var = $value;
            }

            $Expires = $exp_year.'-'.$exp_month.'-'.$exp_day.' '.$exp_hour.':'.$exp_minute.':'.$exp_second;
        } else {
            // force no expiry
            $exp_year = '0000';
        }

        // custom Fields
        foreach ($adi_matrix_cfs as $index => $cf_name) {
            $custom_x = 'custom_'.$index;

            if (isset($data[$custom_x])) {
                $$custom_x = $data[$custom_x];
            } else {
                $$custom_x = '';
            }
        }

        // set the rest (not used by adi_matrix_update_article)
        $Body = '';
        $Body_html = '';
        $Excerpt = '';
        $Excerpt_html = '';
        $textile_body = $prefs['use_textile'];
        $textile_excerpt = $prefs['use_textile'];
        $Annotate = '0';
        $override_form = '';
        $AnnotateInvite = '';

        // package them all up
        $updates = compact($vars);

        if ($this->debug) {
            echo '<b>Article '.$id.' data:</b>';
            dmp($updates);
        }

        // do some validation, textilation & slashing
        $incoming = array_map('assert_string',$updates);
        $incoming = textile_main_fields($incoming); // converts ampersands to &amp; in titles
        extract(doSlash($incoming));

        if (isset($data['status'])) {
            extract(array_map('assert_int', array('Status' => $Status)));
        }

        // title
        if (isset($data['title'])) {
            $titleq = "Title='$Title', ";
        } else {
            $titleq = '';
        }

        // status
        $old_status = $new_status = $adi_matrix_articles[$id]['status'];

        if (isset($data['status'])) {
            $new_status = $Status;

            // tweak status according to privs
            if (!has_privs('article.publish') && $new_status >= STATUS_LIVE) {
                $new_status = STATUS_PENDING;
            }

            $statusq = 'Status='.doSlash($new_status).', ';

            if ($new_status >= STATUS_LIVE) {
                // live & sticky articles only
                update_lastmod();
            }
        } else {
            $statusq = '';
        }
        // section
        if (isset($data['section'])) {
            $sectionq = "Section='$Section', ";
        } else {
            $sectionq = '';
        }
        // keywords
        if (isset($data['keywords'])) {
            $keywordsq = "Keywords='$Keywords', ";
        } else {
            $keywordsq = '';
        }
        // article image
        if (isset($data['article_image'])) {
            $article_imageq = "Image='$Image', ";
        } else {
            $article_imageq = '';
        }
        // categories
        if (isset($data['category1'])) {
            $categoryq = "Category1='$Category1', ";
        } else {
            $categoryq = '';
        }
        if (isset($data['category2'])) {
            $categoryq .= "Category2='$Category2', ";
        } else {
            $categoryq .= '';
        }
        // posted
        $postedq = '';

        if (isset($data['posted'])) {
            if ($publish_now) {
                $postedq = "Posted=now(), ";
            } else {
                // convert TXP date/time to DB timestamp
                $ts = strtotime($Posted);
                $date_error = ($ts === false || $ts === -1);

                if (!$date_error) {
                    $when_ts = $ts - tz_offset($ts);
                    $when = "from_unixtime($when_ts)";
                    $postedq = "Posted=$when, ";
                }
            }
        }

        // expires
        $expiresq = '';

        if (isset($data['expires'])) {
            if ($exp_year == '0000') {
                $expiry = 0;
            } else {
                // convert TXP date/time to DB timestamp
                $ts = strtotime($Expires);
                $expiry = $ts - tz_offset($ts);
            }

            if ($expiry) {
                $date_error = ($ts === false || $ts === -1);

                if (!$date_error) {
                    $expires = $ts - tz_offset($ts);
                    $whenexpires = "from_unixtime($expires)";
                    $expiresq = "Expires=$whenexpires, ";
                }
            } else {
                $expiresq = "Expires=".$this->nulldatetime.", ";
            }
        }

        // custom fields
        $cfq = array();

        foreach($adi_matrix_cfs as $i => $cf_name) {
            $custom_x = "custom_{$i}";

            if (isset($data[$custom_x])) {
                $cfq[] = "custom_$i = '".$$custom_x."'";
            }
        }

        $cfq = implode(', ', $cfq);

        // update article in database
        $res = safe_update("textpattern",
           $titleq.$sectionq.$statusq.$keywordsq.$article_imageq.$categoryq.$postedq.$expiresq.(($cfq) ? $cfq.', ' : '')."LastMod=now(), LastModID='$txp_user'",
            "ID=$id",
            $this->debug
        );

        if ($new_status >= STATUS_LIVE && $old_status < STATUS_LIVE) {
            do_pings();
        }

        if ($new_status >= STATUS_LIVE || $old_status >= STATUS_LIVE) {
            update_lastmod();
        }

        return $res;
    }

    // update articles
    public function update_articles($updates,$matrix_index)
    {
        $res = true;

        if ($updates) {
            foreach ($updates as $id => $data)
                if ($id == 'new') {
                    $res = $res && $this->publish_article($data,$matrix_index);
                } else {
                    $res = $res && $this->update_article($id,$data);
                }
        }

        return $res;
    }

    // default values for new article, adjusted for specified matrix
    public function article_defaults($matrix_index)
    {
        global $adi_matrix_list,$prefs, $adi_matrix_cfs;

    //  Article field   $defaults['xx'] Who determines                      Default values
    //  -------------   --------------- --------------                      --------------
    //  ID              -               MySQL                               generated on publish
    //  Posted          posted          adi_matrix_article_defaults,user    current date/time
    //  Expires         expires         adi_matrix_article_defaults,user    blank (converted to 0000-00-00 00:00:00 by adi_matrix_validate_post_data)
    //  AuthorID        -               adi_matrix_publish_article          current user
    //  LastMod         -               adi_matrix_publish_article          generated on publish
    //  LastModID       -               adi_matrix_publish_article          current user
    //  Title           title           adi_matrix_article_defaults,user    blank
    //  Title_html      -               adi_matrix_publish_article          blank
    //  Body            -               adi_matrix_publish_article          blank
    //  Body_html       -               adi_matrix_publish_article          blank
    //  Excerpt         -               adi_matrix_publish_article          blank
    //  Excerpt_html    -               adi_matrix_publish_article          blank
    //  Image           article_image   adi_matrix_article_defaults,user    blank
    //  Category1       category1       adi_matrix_article_defaults,user    criteria_category (if specific category set), blank
    //  Category2       category2       adi_matrix_article_defaults,user    blank
    //  Annotate        -               adi_matrix_publish_article          0
    //  AnnotateInvite  -               adi_matrix_publish_article          blank
    //  comments_count  -               MySQL                               default
    //  Status          status          adi_matrix_article_defaults,user    criteria_status (if set), "live"
    //  textile_body    -               adi_matrix_publish_article          'use_textile' from $prefs
    //  textile_excerpt -               adi_matrix_publish_article          'use_textile' from $prefs
    //  Section         section         adi_matrix_article_defaults         first section from criteria_section (if set), 'default_section' from $prefs
    //  override_form   -               adi_matrix_publish_article          blank
    //  Keywords        keywords        adi_matrix_article_defaults,user    criteria_keywords (if set), blank
    //  url_title       -               adi_matrix_publish_article          generated-from-title
    //  custom_x        custom_x        adi_matrix_article_defaults,user    blank (if TXP CFs), GLZ default value (if GLZ CFs)
    //  uid             -               adi_matrix_publish_article          generated on publish
    //  feed_time       -               adi_matrix_publish_article          generated on publish

        $defaults = array();

        // title - blank
        $defaults['title'] = '';
        // status - from criteria (or live if not set)
        if ($adi_matrix_list[$matrix_index]['criteria_status']) {
            $defaults['status'] = $adi_matrix_list[$matrix_index]['criteria_status'];
        } else {
            $defaults['status'] = '4';
        }

        // article image - blank
        $defaults['article_image'] = '';

        // keywords - from criteria
        $defaults['keywords'] = $adi_matrix_list[$matrix_index]['criteria_keywords'];

        // category1 - if specific category set - assign it to cat1, otherwise leave it up to user (i.e. blank)
        if (($adi_matrix_list[$matrix_index]['criteria_category']) && (strpos($adi_matrix_list[$matrix_index]['criteria_category'],'!') === false)) {
            $defaults['category1'] = $adi_matrix_list[$matrix_index]['criteria_category'];
        } else {
            $defaults['category1'] = '';
        }

        // category2 - leave blank
        $defaults['category2'] = '';

        // posted
        $defaults['uposted'] = time();
        $defaults['posted'] = date("Y-m-d H:i:s",$defaults['uposted']);

        // expires - blank
        $defaults['uexpires'] = '';
        $defaults['expires'] = '';

        // section - $prefs default_section if blank, else first section on list if criteria set
        if ($adi_matrix_list[$matrix_index]['criteria_section']) {
            $sections = explode(',',$adi_matrix_list[$matrix_index]['criteria_section']);
            $defaults['section'] = $sections[0];
        } else {
            $defaults['section'] = $prefs['default_section'];
        }

        // custom fields - blank
        foreach ($adi_matrix_cfs as $index => $cf_name) {
            $custom_x = 'custom_'.$index;
            $defaults[$custom_x] = '';
        }

        // glean glz_cfs defaults & apply them
        if ($this->has_glz_cf) {
            // array indexed by custom_xx_set, value array(name,position,type)
            if ($this->is_cfs_v20) {
                $all_custom_sets = glz_db_get_all_custom_sets();
            } else {
                $all_custom_sets = glz_custom_fields_MySQL("all");
            }

            if ($this->debug) {
                echo "<b>glz_cfs defaults:</b>".br;
            }

            foreach ($all_custom_sets as $custom => $custom_set) {
                // index format is "custom_x_set"
                if ($this->is_cfs_v20) {
                    $arr_custom_field_values = glz_db_get_custom_field_values($custom, array('custom_set_name' => $custom_set['name']));
                    $default_value = glz_clean_default(glz_default_value($arr_custom_field_values));
                } else {
                    $arr_custom_field_values = glz_custom_fields_MySQL("values",$custom,'',array('custom_set_name' => $custom_set['name']));
                    $default_value = glz_return_clean_default(glz_default_value($arr_custom_field_values));
                }

                $custom_x = str_replace('_set','',$custom); // convert custom_x_set to custom_x

                if ($this->debug) {
                    echo $custom_x.' default = '.'['.$default_value.']'.br;
                }

                $defaults[$custom_x] = $default_value; // use glz_cfs default
            }
        }

        if ($this->debug) {
            echo br.'<b>New article defaults:</b>';
            dmp($defaults);
        }

        return $defaults;
    }

    // new article - based on article_post() from txp_article.php
    // update database with posted data or article defaults
    public function publish_article($data,$matrix_index)
    {
        global $adi_matrix_cfs,$txp_user,$prefs,$vars,$step,$adi_matrix_list,$adi_matrix_article_defaults;

        include_once txpath.'/include/txp_article.php'; // to get article_validate() (TXP 4.5.0+), textile_main_fields()

        $defaults = $adi_matrix_article_defaults; // life's still to short, so make variable name short

        // translate adi_matrix stuff into article_post() stuff
        $ID = $description = $Posted = $Expires = $reset_time = $expire_now = $AuthorID = $sPosted = $LastModID = $sLastMod = $year = $month = $day = $hour = $minute = $second = $url_title = $exp_month = $exp_day = $exp_hour = $exp_minute = $exp_second = $sExpires = '';
        $Title = $Title_plain = $data['title'];
        $Body = '';
        $Body_html = '';
        $Excerpt = '';
        $Excerpt_html = '';
        $textile_body = $prefs['use_textile'];
        $textile_excerpt = $prefs['use_textile'];
        $Annotate = '0';
        $override_form = '';
        $AnnotateInvite = '';

        // article image
        if (isset($data['article_image'])) {
            $Image = $data['article_image'];
        } else {
            $Image = $defaults['article_image'];
        }

        // keywords
        if (isset($data['keywords'])) {
            $Keywords = trim(preg_replace('/( ?[\r\n\t,])+ ?/s', ',', preg_replace('/ +/', ' ', $data['keywords'])), ', ');
        } else {
            $Keywords = $defaults['keywords'];
        }

        // status
        if (isset($data['status'])) {
            $Status = $data['status'];
        } else {
            $Status = $defaults['status'];
        }

        // posted
        if (isset($data['posted']['reset_time'])) {
            $publish_now = '1';
        } else {
            $publish_now = '';
        }

        if (isset($data['posted'])) {
            // this is in TXP date/time
            extract($data['posted']);
            $Posted = $year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second;
        } else {
            // force now
            $publish_now = '1';
        }

        // expires
        if (isset($data['expires'])) {
            // this is in TXP date/time
            foreach ($data['expires'] as $index => $value) {
                // convert expiry vars ($year -> $exp_year) to align with $vars in txp_article.php
                $var = 'exp_'.$index;
                $$var = $value;
            }

            $Expires = $exp_year.'-'.$exp_month.'-'.$exp_day.' '.$exp_hour.':'.$exp_minute.':'.$exp_second;
        } else {
            // force no expiry
            $exp_year = '0000';
        }

        // section
        if (isset($data['section'])) {
            $Section = $data['section'];
        } else {
            $Section = $defaults['section'];
        }

        // categories
        $Category1 = isset($data['category1']) ? $data['category1'] : $defaults['category1'];
        $Category2 = isset($data['category2']) ? $data['category2'] : $defaults['category2'];

        // custom Fields
        foreach ($adi_matrix_cfs as $index => $cf_name) {
            $custom_x = 'custom_'.$index;

            if (array_key_exists($custom_x,$adi_matrix_list[$matrix_index])) {
                // check that custom field is known to adi_matrix
                if (isset($data[$custom_x])) {
                    // present in data posted
                    $$custom_x = $data[$custom_x];
                } else {
                    // not present in posted data, so set default
                    $$custom_x = $defaults[$custom_x];
                }
            }
        }

        // package them all up
        $new = compact($vars);

        if ($this->debug) {
            echo '<b>New article data:</b>';
            dmp($new);
        }

        // all fields are strings ...
        $incoming = array_map('assert_string',$new);

        // textilation (converts ampersands to &amp; in titles)
        $incoming = textile_main_fields($incoming);

        // slash attack
        extract(doSlash($incoming));

        // ... except some are more integer than string
        extract(array_map('assert_int', array('Status' => $Status, 'textile_body' => $textile_body, 'textile_excerpt' => $textile_excerpt)));
        $Annotate = (int) $Annotate;

        // set posted timestamp (already validated by adi_matrix_validate_post_data)
        if ($publish_now == 1) {
            $when = 'now()';
            $when_ts = time();
        } else { // convert TXP date/time to DB timestamp
            $ts = strtotime($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second);
            $when_ts = $ts - tz_offset($ts);
            $when = "from_unixtime($when_ts)";
        }

        // and I quote: "Force a reasonable 'last modified' date for future articles, keep recent articles list in order"
        $lastmod = ($when_ts > time() ? 'now()' : $when);

        // set expiry timestamp (already validated/massaged by adi_matrix_get_post_data)
        if ($exp_year == '0000') {
            $expires = 0;
        } else {
            // convert TXP date/time to DB timestamp
            $ts = strtotime($exp_year.'-'.$exp_month.'-'.$exp_day.' '.$exp_hour.':'.$exp_minute.':'.$exp_second);
            $expires = $ts - tz_offset($ts);
        }

        if ($expires) {
            $whenexpires = "from_unixtime($expires)";
        } else {
            $whenexpires = $this->nulldatetime;
        }

        // who's doing the doing?
        $user = doSlash($txp_user);

        $msg = '';

        // tweak status according to privs
        if (!has_privs('article.publish') && $Status >= STATUS_LIVE) {
            $Status = STATUS_PENDING;
        }

        // set url-title
        if (empty($url_title)) {
            $url_title = stripSpace($Title_plain, 1);
        }

        // custom fields
        $cfq = array();

        foreach($adi_matrix_cfs as $i => $cf_name) {
            $custom_x = "custom_{$i}";

            if (isset($$custom_x)) {
                $cfq[] = "custom_$i = '".$$custom_x."'";
            }
        }

        $cfq = implode(', ',$cfq);

        $rs = compact($vars);

        if ($this->debug) {
            article_validate($rs, $msg);
            echo '<b>article_validate():</b>';
            dmp($msg);
        }

        if (article_validate($rs, $msg)) {
            $ok = safe_insert(
               "textpattern",
               "Title           = '$Title',
                Body            = '$Body',
                Body_html       = '$Body_html',
                Excerpt         = '$Excerpt',
                Excerpt_html    = '$Excerpt_html',
                Image           = '$Image',
                Keywords        = '$Keywords',
                Status          =  $Status,
                Posted          =  $when,
                Expires         =  $whenexpires,
                AuthorID        = '$user',
                LastMod         =  $lastmod,
                LastModID       = '$user',
                Section         = '$Section',
                Category1       = '$Category1',
                Category2       = '$Category2',
                textile_body    =  $textile_body,
                textile_excerpt =  $textile_excerpt,
                Annotate        =  $Annotate,
                override_form   = '$override_form',
                url_title       = '$url_title',
                AnnotateInvite  = '$AnnotateInvite',"
                .(($adi_matrix_cfs) ? $cfq.',' : '').
                "uid            = '".md5(uniqid(rand(),true))."',
                feed_time       = now()"
                ,$this->debug
            );

            if ($ok) {
                if ($Status >= STATUS_LIVE) {
                    do_pings();
                    update_lastmod();
                }
                return true;
            }
        }

        return false;
    }

    // delete an article - ripped off from list_multi_edit() in txp_list.php
    public function delete_article($id)
    {
        global $txp_user;

        // keeping the multi-edit/array thing going - just in case
        $selected = array();
        $selected[] = $id;

        // is allowed?
        if (!has_privs('article.delete')) {
            $allowed = array();

            if (has_privs('article.delete.own')) {
                $allowed = safe_column_num('ID', 'textpattern', 'ID in('.implode(',',$selected).') and AuthorID=\''.doSlash($txp_user).'\'',$this->debug);
            }

            $selected = $allowed;
        }

        // in the bin
        foreach ($selected as $id) {
            if (safe_delete('textpattern', "ID = $id",$this->debug)) {
                $ids[] = $id;
            }
        }

        // housekeeping
        $changed = implode(', ', $ids);
        if ($changed) {
            safe_update('txp_discuss', "visible = ".MODERATE, "parentid in($changed)",$this->debug);
            return true;
        }

        return false;
    }

    // output custom field input according to glz_custom_fields format
    public function glz_cfs_input($custom_x,$var,$val,$id)
    {
        $row = safe_row('html','txp_prefs',"name = '".$custom_x."_set'"); // get html input type from prefs
        $html = $row['html'];

        if ($this->is_cfs_v20) {
            $arr_custom_field_values = glz_db_get_custom_field_values($custom_x."_set", array('custom_set_name' => $custom_x."_set"));
            $default_value = glz_clean_default(glz_default_value($arr_custom_field_values));
        } else {
            $arr_custom_field_values = glz_custom_fields_MySQL("values", $custom_x."_set", '', array('custom_set_name' => $custom_x."_set"));
            $default_value = glz_return_clean_default(glz_default_value($arr_custom_field_values));
        }

        if (is_array($arr_custom_field_values)) {
            array_walk($arr_custom_field_values, "glz_clean_default_array_values"); // from glz_custom_fields_replace()
        }

        // glz radio reset - relies on name="field", which has to match for="field" in main label, which picks up for="field_value" in sub labels but adi_matrix don't have main label & needs name="article_xx[field_xx]" anyway - ne'er the twain shall neet! (would have to write our own reset jQuery I guess)
        // glz radio - uses an ID based on field & value i.e. field_value, but adi_matrix ends up as article_2[custom_3]_value which ain't valid (& don't work in jQuery anyway)
        // glz checkbox - uses an ID based on value only, so may get duplicate ID warnings on Write tab (& definitely on matrix tab)!!!
        if ($html == 'radio') {
            // create a clean ID prefix (i.e. without [ or ]) for radio buttons - to get rid of some error messages
            $glz_id_prefix = str_replace('[','_',$var);
            $glz_id_prefix = str_replace(']','_',$glz_id_prefix);
        } else {
            $glz_id_prefix = '';
        }

        $out = glz_format_custom_set_by_type($var,$glz_id_prefix,$html,$arr_custom_field_values,$val,$default_value);

        // html in $out[0], glz class in $out[1]
        return $out;
    }

    // analyse submitted article data ($_POST), massage if necessary, & create new list of articles & data ($adi_matrix_post)
    public function get_post_data($adi_matrix_articles,$matrix_index)
    {
        global $adi_matrix_list,$adi_matrix_cfs;

        if ($this->debug) {
            echo '<b>$_POST:</b>';
            dmp($_POST);
        }

        // copy $_POST['article_xx'] to $adi_matrix_post[xx] (where xx is article ID or "new")
        $adi_matrix_post = array();
        foreach ($_POST as $index => $value) {
            $this_index = explode('_',$index);
            if (strpos($index,'article_') === 0) {
                // pick out anything from $_POST that starts with "article_" ... i.e. article_xx or article_new
                if ($this->has_glz_cf) {
                    // tweak POSTED values to convert from array to bar|separated|list - based on glz_custom_fields_before_save()
                    foreach ($value as $key => $val) {
                        if (strstr($key, 'custom_') && is_array($val)) {
                            // check for custom fields with multiple values e.g. arrays
                            $val = implode($val,'|');
                            $value[$key] = $val;
                        }
                    }
                }

                $adi_matrix_post[$this_index[1]] = $value;
            }
        }

        // new article fiddling
        if (isset($adi_matrix_post['new'])) {
            if (trim($adi_matrix_post['new']['title']) == '') {
                // remove from the equation if title is blank
                unset($adi_matrix_post['new']);
            }
        }

        // check for missing glz custom field values & fire blanks if necessary - required for checkboxes/multiselects that have been completely unchecked (otherwise updates not registered)
        // will also pick up radios & multiselects (though deselected multiselect may be present in $_POST anyway)
        if ($this->has_glz_cf) {
            if ($this->debug) {
                echo '<b>glz_cfs blanks generated for:</b>'.br;
            }

            foreach ($adi_matrix_articles as $id => $article_data) {
                // check all articles (& new) on page (article may be absent from POST if checkbox is the only data field & it's completely unticked)
                if ($this->debug) {
                    echo "Article $id - ";
                }

                if (!array_key_exists($id,$adi_matrix_post)) {
                    // article missing from POST completely
                    if ($this->debug) {
                        echo "(article absent) ";
                    }

                    $adi_matrix_post[$id] = array();
                }

                foreach ($adi_matrix_cfs as $index => $title) {
                    // check each custom field
                    if ($adi_matrix_list[$matrix_index]['custom_'.$index]) {
                        // only interested in custom field if it's visible in this matrix
                        if (!array_key_exists('custom_'.$index,$adi_matrix_post[$id])) {
                            // custom field absent from article in POST
                            $adi_matrix_post[$id]['custom_'.$index] = ''; // fire a blank
                            if ($this->debug) {
                                echo "custom_$index ";
                            }
                        }
                    }
                }

                if ($this->debug) {
                    echo br;
                }
            }

            if ($this->debug) {
                echo br;
            }
        }

        // expires - change all blanks to all zeroes (existing & "new"), coz stored as zeroes but displayed as blanks
        foreach ($adi_matrix_post as $id => $this_article) {
            // check each article
            if (array_key_exists('expires',$this_article)) {
                if (($this_article['expires']['year'] == '') && ($this_article['expires']['month'] == '') && ($this_article['expires']['day'] == '') && ($this_article['expires']['hour'] == '') && ($this_article['expires']['minute'] == '') && ($this_article['expires']['second'] == '')) {
                    $adi_matrix_post[$id]['expires']['year'] = '0000';
                    $adi_matrix_post[$id]['expires']['month'] = $adi_matrix_post[$id]['expires']['day'] = $adi_matrix_post[$id]['expires']['hour'] = $adi_matrix_post[$id]['expires']['minute'] = $adi_matrix_post[$id]['expires']['second'] = '00';
                }
            }
        }

        if ($this->debug) {
            echo '<b>$adi_matrix_post:</b>';
            dmp($adi_matrix_post);
        }

        return $adi_matrix_post;
    }

    // compare submitted article data with database data & create $adi_matrix_updates[id][field] if changed
    public function get_updates($adi_matrix_post,$adi_matrix_articles)
    {
        if ($this->debug) {
            echo '<b>Update processing (adi_matrix_get_updates):</b>'.br;
        }

        $adi_matrix_updates = array();

        foreach ($adi_matrix_post as $id => $data) {
            foreach ($data as $field => $new_value) {
                if (($field == 'posted') || ($field == 'expires')) {
                    $date_string = $new_value['year'].'-'.$new_value['month'].'-'.$new_value['day'].' '.$new_value['hour'].':'.$new_value['minute'].':'.$new_value['second']; // set up new date/time string
                }

                if ($id == 'new') {
                    // new article
                    $adi_matrix_updates[$id][$field] = $new_value;

                    if (($field == 'posted') || ($field == 'expires')) {
                        $new_value = $date_string; // use new time date/time string
                    }

                    if ($this->debug) {
                        echo 'id='.$id.', field='.$field.', new_value='.$new_value.' (NEW ARTICLE)'.br;
                    }
                } else { // existing article
                    $equal = true;
                    $old_value = $adi_matrix_articles[$id][$field];
                    $test_value = $new_value;

                    if ($field == 'keywords') {
                        // remove human friendly spaces after commas
                        $test_value = str_replace(', ' ,',', $new_value);
                    }

                    if (($field == 'posted') || ($field == 'expires')) {
                        // date/time requires special attention (because DB time may be different to TXP human time,
                        // so direct comparison not valid)
                        if (array_key_exists('reset_time',$new_value)) {
                            $equal = false; // force inequality - "NOW()" time will be set in database update
                            if ($this->debug) {
                                echo 'id='.$id.', field='.$field.' (RESET)';
                            }
                        } else {
                            // use article's DB unix timestamp & convert to TXP date/time for comparison
                            $ufield = 'u'.$field; // tweak field name from posted/expires to uposted/uexpires

                            if ($adi_matrix_articles[$id][$ufield]) {
                                // use DB unix timestamp & convert to TXP date/time
                                $old_value = safe_strftime("%Y-%m-%d %X",$adi_matrix_articles[$id][$ufield]);
                            } else {
                                $old_value = '0000-00-00 00:00:00';
                            }

                            $test_value = $date_string; // use new time date/time string

                            if ($this->debug) {
                                echo 'id='.$id.', field='.$field.', old_value='.$old_value.', test_value='.$test_value;
                            }
                        }
                    } else {
                        if ($this->debug) {
                            echo 'id='.$id.', field='.$field.', new_value='.$new_value.', old_value='.$old_value.', test_value='.$test_value;
                        }
                    }

                    $equal = $equal && (strcmp($test_value,$old_value) == 0);

                    if ($this->debug) {
                        if ($equal) {
                            echo " (EQUAL)".br; 
                        } else {
                            echo " <b>(NOT EQUAL)</b>".br;
                        }
                    }

                    if (!$equal) {
                        $adi_matrix_updates[$id][$field] = $new_value;
                    }
                }
            }
        }

        if ($this->debug) {
            echo br.'<b>$adi_matrix_updates:</b>';
            dmp($adi_matrix_updates);
        }

        return $adi_matrix_updates;
    }

    // article data validation
    public function validate_post_data($adi_matrix_articles,$post_data)
    {
        global $adi_matrix_validation_errors;

        // create array of empties indexed by $adi_matrix_validation_errors id
        $new_error_list = array();
        foreach ($adi_matrix_validation_errors as $i => $v) {
            $new_error_list[$i] = array();
        }

        foreach ($post_data as $id => $data) {
            // add empty "error" slots for article
            foreach ($adi_matrix_validation_errors as $i => $v) {
                $new_error_list[$i][$id] = array();
            }

            // remember old timestamp values (existing articles only)
            if ($id != 'new') {
                $posted = $adi_matrix_articles[$id]['posted'];
                $expires = $adi_matrix_articles[$id]['expires'];
            }

            // iterate through $data (OTT but may change in the future)
            foreach ($data as $field => $value) {
                // do some date/time checking
                if (($field == 'posted') || ($field == 'expires')) {
                    // record new (i.e. $_POSTed) timestamp values
                    $$field = $value['year'].'-'.$value['month'].'-'.$value['day'].' '.$value['hour'].':'.$value['minute'].':'.$value['second'];
                    if ($field == 'posted') {
                        if (array_key_exists('reset_time',$value)) {
                            $$field = date('Y-m-d H:i:s',time()); // have to predict the reset date/time (Article tab does it this way too!)
                        }
                    }

                    // check it's a valid date/time
                    $error = (!is_numeric($value['year']) || !is_numeric($value['month']) || !is_numeric($value['day']) || !is_numeric($value['hour'])  || !is_numeric($value['minute']) || !is_numeric($value['second']));
                    $ts = strtotime($value['year'].'-'.$value['month'].'-'.$value['day'].' '.$value['hour'].':'.$value['minute'].':'.$value['second']);
                    $error = $error || ($ts === false || $ts === -1);
                    // special case - allow all blanks in expires
                    if ($error && ($field == 'expires')) {
                        $error = !(empty($value['year']) && empty($value['month']) && empty($value['day']) && empty($value['hour']) && empty($value['minute']) && empty($value['second']));
                    }

                    if ($error) {
                        if ($id != 'new') {
                            $$field = $adi_matrix_articles[$id][$field]; // restore old value (so it doesn't influence later "expires before posted" checking)
                        }

                        $new_error_list['0'][$id][] = $field;
                    }
                }
            }

            // check expires is not before posted (but only if expires is set) TXP 4.6 - EXPIRES CAN BE BLANK TOO
            if ((strtotime($expires) < strtotime($posted)) && ($expires != '0000-00-00 00:00:00') && ($expires != '')) {
                $new_error_list['1'][$id][] = 'posted';
                $new_error_list['1'][$id][] = 'expires';
            }

            // check URL-titles (duplicates & blanks)
            // title supplied if new or edited, get title if not supplied
            if (isset($data['title'])) {
                $title = $data['title'];
            } else {
                $title = $adi_matrix_articles[$id]['title'];
            }

            $msg = 0;
            $url_title = stripSpace($title, 1);

            if (trim($url_title) == '') {
                // blank
                $msg = 3;
            }

            if ($msg) {
                $new_error_list[$msg][$id][] = 'url_title';
            }

            $msg = 0;

            // duplicates?
            $url_title_count = safe_count('textpattern',"url_title = '$url_title'");

            if (($url_title_count > 1) || (($id == 'new') && $url_title_count)) {
                // duplicates found (existing articles: multiple in DB, new article: one or more in DB)
                $msg = 2;

                // get ids of all other articles with matching URL-only titles (they may not be in this matrix, or in this page of this matrix)
                $duplicates = safe_rows('id','textpattern',"url_title = '$url_title'"); // returns array of arrays containing 'id' => id#

                if ($id == 'new') {
                    $duplicates[]['id'] = gTxt('adi_matrix_new_article'); // using "new article" in duplicates list coz don't have new article list
                }
            }

            if ($msg) {
                foreach ($duplicates as $duplicate) {
                    $new_error_list[$msg][$duplicate['id']][] = 'url_title';
                }
            }

        }

        // lose the empties
        $new_error_list = array_filter(array_map('array_filter', $new_error_list));

        if ($this->debug) {
            echo '<b>Invalid fields:</b>';
            dmp($new_error_list);
        }

        return $new_error_list;
    }

    // remove fields with invalid data from article update list
    public function remove_errors($updates,$errors)
    {
        foreach ($errors as $article)
            foreach ($article as $id => $fields)
                foreach ($fields as $field)
                    unset($updates[$id][$field]);

        return $updates;
    }

    // plot in the title
    public function debug($adi_matrix_articles,$matrix_index)
    {
        global $event,$step,$adi_matrix_cfs,$adi_matrix_list;

        echo "<b>Event:</b> ".$event.", <b>Step:</b> ".$step.br;
        echo "<b>Date/time:</b> "
            .'date = '.date("Y-m-d H:i:s")
            .'; tz_offset() = '.tz_offset()
            .'; date + tz_offset() = '.date("Y-m-d H:i:s",time()+tz_offset()) // this is actual (TXP adjusted) local time (incl DST) regardless of server timezone
            .br;
        $rs = safe_query('SELECT NOW()');
        $a = nextRow($rs);
        if ($a) {
            echo '<b>Article timestamp in DB &hellip; SELECT NOW()</b> = '.current($a).br;
        } else {
            echo 'Unable to determine article DB time'.br;
        }

        $rs = safe_query('SELECT UNIX_TIMESTAMP(NOW())');
        $a = nextRow($rs);
        $article_time = current($a);

        if ($a) {
            echo '<b>Article time displayed in article tab &hellip; safe_strftime("%Y-%m-%d %X")</b> = '.safe_strftime("%Y-%m-%d %X",$article_time).br;
            $posted = safe_strftime('%Y-%m-%d %X');
            $ts = strtotime($posted);
            $when_ts = $ts - tz_offset($ts);
            $rs = safe_query("SELECT FROM_UNIXTIME($when_ts)");
            $a = nextRow($rs);

            if ($a) {
                echo '<b>Article date/time written to DB</b>  = '.current($a).br;
            } else {
                echo 'Unable to determine date/time written to DB'.br;
            }
        } else {
            echo 'Unable to determine article tab timestamp'.br;
        }

        echo br;
        echo '<b>This matrix:</b>';
        dmp($adi_matrix_list[$matrix_index]);
        echo '<b>$adi_matrix_cfs:</b>';
        dmp($adi_matrix_cfs);

        if ($this->has_glz_cf) {
            echo '<b>Custom field input types:</b>',br;

            foreach ($adi_matrix_cfs as $index => $title) {
                $row = safe_row('html','txp_prefs',"name = 'custom_".$index."_set'"); // get html input type from prefs
                echo 'custom_'.$index.' - '.$row['html'].br;
            }
        }

        echo '<b>$adi_matrix_categories:</b>';
        dmp($this->categories);
        echo '<b>$adi_matrix_articles:</b>';
        dmp($adi_matrix_articles);
    }

    // matrix <table> header stuff
    // note that clicking a column header will reset back to the first page, which is standard TXP behaviour
    // - to fix would involve a custom elink() function to pass the 'page' variable on
    public function table_head($matrix_index,$type)
    {
        global $event,$adi_matrix_cfs,$adi_matrix_list;

        // get current sort settings
        list($sort,$dir,$sort_type) = explode(',',get_pref($event.'_sort',$adi_matrix_list[$matrix_index]['sort'].','.$adi_matrix_list[$matrix_index]['dir'].','.$adi_matrix_list[$matrix_index]['sort_type']));

        if ($type == 'header') {
            $tag = 'th';
            $wraptag = 'thead';
        } else {
            $tag = 'td';
            $wraptag = 'tfoot';
        }

        // article id/title heading
        $field_list = array('id','title');

        foreach ($field_list as $field) {
            $var = $field.'_hcell';
            $class = array();

            if ($field == $sort) {
                // sort value matches field
                $dir == 'desc' ? $class[] = 'desc' : $class[] = 'asc'; // up/down arrow
            }

            $class[] = 'adi_matrix_field_'.$field; // add field name to class
            $class = ' class="'.implode(' ',$class).'"';
            $$var = tag(elink($event,'','sort',$field,gTxt($field),'dir',($dir == 'asc' ? 'desc' : 'asc'),''),$tag,$class); // column heading/toggle sort
        }

        // standard field headings
        $field_list = array('status','article_image','keywords','category1','category2','posted','expires','section');

        foreach ($field_list as $field) {
            $var = $field.'_hcell';
            $class = array();

            if ($field == $sort) {
                // sort value matches field
                $dir == 'desc' ? $class[] = 'desc' : $class[] = 'asc'; // up/down arrow
            }

            $class[] = 'adi_matrix_field_'.$field; // add field name to class
            $class = ' class="'.implode(' ',$class).'"';
            $adi_matrix_list[$matrix_index][$field] ? $$var = tag(elink($event,'','sort',$field,gTxt($field),'dir',($dir == 'asc' ? 'desc' : 'asc'),''),$tag,$class) : $$var = ''; // column heading/toggle sort
        }

        // custom field headings
        $cf_hcell = '';

        foreach ($adi_matrix_cfs as $index => $cf_name) {
            $custom_x = 'custom_'.$index;
            $class = array();
            if ($field == $sort) {
                // sort value matches field
                $dir == 'desc' ? $class[] = 'desc' : $class[] = 'asc'; // up/down arrow
            }

            $class[] = 'adi_matrix_field_'.$custom_x; // add field name to class
            $class = ' class="'.implode(' ',$class).'"';

            if (array_key_exists($custom_x,$adi_matrix_list[$matrix_index])) {
                // check that custom field is known to adi_matrix
                if ($adi_matrix_list[$matrix_index][$custom_x]) {
                    $cf_hcell .= tag(elink($event,'','sort',$custom_x,$cf_name,'dir',($dir == 'asc' ? 'desc' : 'asc'),''),$tag,$class);
                }
            }
        }

        // delete heading placeholder
        $del_hcell = tag(sp,$tag,' class="adi_matrix_delete"'); // spacer

        // "Show section" heading
        if ($sort == 'section') {
            // sort value matches field
            $dir == 'desc' ? $class = ' class="desc section"' : $class = ' class="asc section"'; // up/down arrow
        } else {
            // no arrow - sort set in admin
            $class = ' class= "section"';
        }

        $show_section_hcell = tag(elink($event,'','sort','section',gTxt('section'),'dir',($dir == 'asc' ? 'desc' : 'asc'),''),$tag,$class);

        return
            tag(
                tr(
                    ($this->get_pref('adi_matrix_display_id') ? $id_hcell : '')
                    .$title_hcell
                    .($adi_matrix_list[$matrix_index]['show_section'] ? $show_section_hcell : '') // THIS NEEDS SORTING OUT
                    .($adi_matrix_list[$matrix_index]['section'] ? $section_hcell : '') // THIS NEEDS SORTING OUT
                    .$status_hcell
                    .$cf_hcell
                    .$article_image_hcell
                    .$keywords_hcell
                    .$category1_hcell
                    .$category2_hcell
                    .$posted_hcell
                    .$expires_hcell
                    .($adi_matrix_list[$matrix_index]['publish'] ? $del_hcell : '')
                )
                ,$wraptag
            );
    }

    // generates matrix <table> and <form> for article data updates
    // stylish classes:
    // tr (article ids):    adi_matrix_article_xx, adi_matrix_article_new
    // td (field type):     adi_matrix_timestamp, adi_matrix_category
    // td (field specific): adi_matrix_field_id, adi_matrix_field_title, adi_matrix_field_custom_x etc etc
    public function matrix_table($adi_matrix_articles,$matrix_index,$page,$errors=array(),$updates=array())
    {
        global $step,$adi_matrix_cfs,$adi_matrix_list,$txp_user;

        $out = '';
        $out .= $this->table_head($matrix_index,'header');

        if ($adi_matrix_list[$matrix_index]['footer']) {
            $out .= $this->table_head($matrix_index,'footer');
        }

        $out .= '<tbody>';

        if ($adi_matrix_articles) {
            $statuses = $this->get_statuses();

            foreach ($adi_matrix_articles as $id => $data) {
                // set up validation error flags for this article
                $article_errors = array();

                foreach ($errors as $error_type) {
                    if (isset($error_type[$id])) {
                        $article_errors = array_merge($article_errors,$error_type[$id]);
                    }
                }

                $article_errors = array_unique($article_errors);

                if ($this->debug && ($step == 'update')) {
                    echo '<b>Validation errors #'.$id.':</b>';
                    dmp($article_errors);
                }
                // based on standard save button in txp_article.php
                $Status = $data['status'];
                $AuthorID = $data['author'];
                $has_privs = // work out if user has a right to fiddle with article
                    (($Status >= 4 and has_privs('article.edit.published'))
                    or ($Status >= 4 and $AuthorID==$txp_user and has_privs('article.edit.own.published'))
                    or ($Status <  4 and has_privs('article.edit'))
                    or ($Status <  4 and $AuthorID==$txp_user and has_privs('article.edit.own')));
                $prefix = 'article_'.$id; // use array index 'article_id' rather than 'id' in POST data (clearer/safer?)
                $out .= '<tr class="adi_matrix_'.$prefix.'">';
                $highlight = $data['highlight'];

                // article title link tooltip text
                // tooltip (&#10; = newline in non-Firefox tooltip)
                if ($this->get_pref('adi_matrix_article_tooltips')) {
                    $title_text = '#'.$id.', '.gTxt('posted').' '.$data['display_posted'];

                    if ($data['expires'] != '0000-00-00 00:00:00') {
                        $title_text .= ', '.gTxt('expires').' '.$data['display_expires'];
                    }
                    if ($highlight == 1) {
                        $title_text .= ' ('.gTxt('expired').')';
                    }
                    if ($highlight == 2) {
                        $title_text .= ' ('.gTxt('adi_matrix_time_future').')';
                    }

                    $title_text .= ', '.$data['section'];
                    $title_text .= ', '.$AuthorID;
                } else {
                    $title_text = gTxt('edit');
                }
                $class = '';
                // highlighting for expired/future articles
                if ($this->get_pref('adi_matrix_article_highlighting')) {
                    if ($highlight) {
                        if ($highlight == 1) {
                            $class = ' class="adi_matrix_expired"';
                        }
                        if ($highlight == 2) {
                            $class = ' class="adi_matrix_future"';
                        }
                    }
                }

                // ID
                if ($this->get_pref('adi_matrix_display_id')) {
                    $id_link = eLink('article','edit','ID',$id,$id);
                    $out .= tag($id_link,'td',' class="adi_matrix_field_id"');
                }

                // title
                $article_title = trim($data['title']);
                if ($article_title == '') {
                    // blank title
                    $title_link = tag(eLink('article','edit','ID',$id,gTxt('untitled'),'','',$title_text),'em');
                } else {
                    $title_link = eLink('article','edit','ID',$id,$article_title,'','',$title_text);
                }

                $title_link = '<span title="'.$title_text.'"'.$class.'>'.$title_link.'</span>';
                $arrow_link = sp.href(
                    tag(sp, 'span', array('class' => 'ui ui-icon-pencil')),
                    array(
                        'event'      => 'article',
                        'step'       => 'edit',
                        'ID'         => $id,
                        '_txp_token' => form_token(),
                    ), array(
                        'class' => 'adi_matrix_edit_link',
                        'title' => $title_text,
                    ));

                if ($adi_matrix_list[$matrix_index]['title'] && $this->get_pref('adi_matrix_display_id')) {
                    $arrow_link = '';
                }

                if ($adi_matrix_list[$matrix_index]['title']) {
                    $has_privs ? // decide if user gets input fields or not
                        $out .= tda(finput("text",$prefix."[title]",$data['title'],'',($this->get_pref('adi_matrix_input_field_tooltips') ?htmlspecialchars($data['title']):'')).$arrow_link,' class="adi_matrix_field_title"') :
                        $out .= tda($title_link,' class="adi_matrix_field_title"');
                } else {
                    $out .= tag($title_link,'td',' class="adi_matrix_field_title"');
                }

                // section
                if ($adi_matrix_list[$matrix_index]['show_section']) {
                    $out .= tda($data['section']);
                }
                if ($adi_matrix_list[$matrix_index]['section']) {
                    $has_privs ? // decide if user gets input fields or not
                        $out .= tda($this->section_popup($prefix."[section]",$data['section'],$adi_matrix_list[$matrix_index]['criteria_section']),' class="adi_matrix_field_section"') :
                        $out .= ($data['section'] ? tda($data['section'],' class="adi_matrix_field_section"') : tda(sp,' class="adi_matrix_field_section"'));
                }
                // status
                if ($adi_matrix_list[$matrix_index]['status']) {
                    $has_privs ? // decide if user gets input fields or not
                        $out .= tda(selectInput($prefix.'[status]',$statuses,$data['status']),' class="adi_matrix_field_status"') :
                        $out .= tda($statuses[$data['status']],' class="adi_matrix_field_status"');
                }
                // custom fields
                foreach ($adi_matrix_cfs as $index => $cf_name) {
                    $custom_x = 'custom_'.$index;
                    if (array_key_exists($custom_x,$adi_matrix_list[$matrix_index])) {
                        // check that custom field is known to adi_matrix
                        if ($adi_matrix_list[$matrix_index][$custom_x]) {
                            if ($has_privs) {
                                // decide if user gets input fields or not
                                if ($this->has_glz_cf) {
                                    $glz_input_stuff = $this->glz_cfs_input($custom_x,$prefix."[$custom_x]",$data[$custom_x],$id);

                                    if ($glz_input_stuff[1] == 'glz_custom_radio_field') {
                                        // don't apply glz_class coz can't handle glz reset function properly yet - see below
                                        $out .= tda($glz_input_stuff[0],' class="adi_matrix_field_'.$custom_x.'"');
                                    } else {
                                        $out .= tda($glz_input_stuff[0],' class="'.$glz_input_stuff[1].' adi_matrix_field_'.$custom_x.'"');
                                    }
                                } else {
                                    $out .= tda(finput("text",$prefix."[$custom_x]",$data[$custom_x],'',($this->get_pref('adi_matrix_input_field_tooltips')?htmlspecialchars($data[$custom_x]):'')),' class="adi_matrix_field_'.$custom_x.'"');
                                }
                            } else {
                                $out .= ($data[$custom_x] ? tda($data[$custom_x],' class="adi_matrix_field_'.$custom_x.'"') : tda(sp,' class="adi_matrix_field_'.$custom_x.'"')); // make sure the table cell stretches if no data
                            }
                        }
                    }
                }

                // article image
                if ($adi_matrix_list[$matrix_index]['article_image']) {
                    $arrow_link = '';
                    // CUSTOM FIELD LINKS DISABLED
    //              if (trim($data['article_image']) && ($adi_matrix_list[$matrix_index]['cf_links'] == 'article_image')) {
    //                  $image_ids = explode(',',$data['article_image']);
    //                  $image_id = $image_ids[0];
    //                  if (safe_count('txp_image',"id=$image_id",$this->debug))
    //                      $arrow_link = sp.eLink('image','image_edit','id',$image_id,'&rarr;','','',gTxt('edit_image').' #'.$image_id);
    //                  else // image not found
    //                      $arrow_link = sp.'?';
    //              }
                    $has_privs ? // decide if user gets input fields or not
                        $out .= tda(finput("text",$prefix."[article_image]",$data['article_image'],'',($this->get_pref('adi_matrix_input_field_tooltips')?htmlspecialchars($data['article_image']):'')).$arrow_link,' class="adi_matrix_field_image"') :
                        $out .= ($data['article_image'] ? tda($data['article_image'].$arrow_link,' class="adi_matrix_field_image"') : tda(sp,' class="adi_matrix_field_image"')); // make sure the table cell stretches if no data
                }
                // keywords
                if ($adi_matrix_list[$matrix_index]['keywords']) {
                    $has_privs ? // decide if user gets input fields or not
                        $out .= tda('<textarea name="'.$prefix."[keywords]".'" cols="18" rows="5" class="mceNoEditor">'.htmlspecialchars(str_replace(',' ,', ', $data['keywords'])).'</textarea>',' class="adi_matrix_field_keywords"') :
                        $out .= ($data['keywords'] ? tda($data['keywords'],' class="adi_matrix_field_keywords"') : tda(sp,' class="adi_matrix_field_keywords"'));
                }
                // category1
                if ($adi_matrix_list[$matrix_index]['category1']) {
                    $has_privs ? // decide if user gets input fields or not
                        $out .= tda($this->category_popup($prefix."[category1]",$data['category1'],false),' class="adi_matrix_category adi_matrix_field_category1"') :
                        $out .= ($data['category1'] ? tda($data['category1'],' class="adi_matrix_field_category1"') : tda(sp,' class="adi_matrix_category adi_matrix_field_category1"'));
                }
                // category2
                if ($adi_matrix_list[$matrix_index]['category2']) {
                    $has_privs ? // decide if user gets input fields or not
                        $out .= tda($this->category_popup($prefix."[category2]",$data['category2'],false),' class="adi_matrix_category adi_matrix_field_category2"') :
                        $out .= ($data['category2'] ? tda($data['category2'],' class="adi_matrix_field_category2"') : tda(sp,' class="adi_matrix_category adi_matrix_field_category2"'));
                }

                // posted
                $class = 'adi_matrix_timestamp adi_matrix_field_posted';

                if (array_search('posted',$article_errors) !== false) {
                    $class .= ' adi_matrix_error';
                }

                if ($adi_matrix_list[$matrix_index]['posted']) {
                    $has_privs ? // decide if user gets input fields or not
                        $out .= tda($this->timestamp_input($prefix."[posted]",$data['posted'],$data['uposted'],'posted'),' class="'.$class.'"') :
                        $out .= ($data['posted'] ? tda($data['posted']) : tda(sp));
                }

                // expires
                $class = 'adi_matrix_timestamp adi_matrix_field_expires';
                if (array_search('expires',$article_errors) !== false) {
                    $class .= ' adi_matrix_error';
                }

                if ($adi_matrix_list[$matrix_index]['expires']) {
                    $has_privs ? // decide if user gets input fields or not
                        $out .= tda($this->timestamp_input($prefix."[expires]",$data['expires'],$data['uexpires'],'expires'),' class="'.$class.'"') :
                        $out .= ($data['expires'] ? tda($data['expires']) : tda(sp));
                }

                // delete button
                if ($adi_matrix_list[$matrix_index]['publish']) {
                    // got publish? - might delete!
                    // a closer look at credentials - override delete priv OR (delete own priv and it's yours to delete)
                    if (has_privs('article.delete') || (has_privs('article.delete.own') && ($AuthorID == $txp_user))) {
                        if ($this->is_txp460) {
                            $button =
                                href(
                                    span('Delete',' class="ui-icon ui-icon-close"')
                                    ,array(
                                        'event' => 'adi_matrix_matrix_'.$matrix_index,
                                        'step' => 'delete',
                                        'id' => $id,
                                        'page' => $page,
                                        '_txp_token'    => form_token(),
                                    )
                                    ,array(
                                        'class'       => 'dlink destroy',
                                        'title'       => gTxt('delete'),
                                        'data-verify' => gTxt('confirm_delete_popup'),
                                    )
                                );
                        } else {
                            $url = '?event='.'adi_matrix_matrix_'.$matrix_index.a.'step=delete'.a.'id='.$id.a.'page='.$page;
                            $button =
                                    '<a href="'
                                    .$url
                                    .'" class="dlink" title="Delete?" onclick="return verify(\''
                                    .addslashes(htmlentities($data['title']))
                                    .' - '
                                    .gTxt('confirm_delete_popup')
                                    .'\')">&#215;</a>';
                        }

                        $out .= tda($button,' class="adi_matrix_delete"');
                    } else {
                        $out .= tda(sp);
                    }
                }

                $out .= '</tr>';
            }
        }

        if ($adi_matrix_list[$matrix_index]['publish'] && has_privs('article')) {
            $out .= $this->new_article($matrix_index);
        }

        $out .= '</tbody>';

        return $out;
    }

    // data input fields for new article
    // styles as per matrix_table()
    public function new_article($matrix_index)
    {
        global $adi_matrix_cfs,$adi_matrix_list,$adi_matrix_article_defaults;

        $defaults = $adi_matrix_article_defaults; // life's too short to type
        $statuses = $this->get_statuses();

        $prefix = 'article_new';
        $out = '<tr class="adi_matrix_'.$prefix.'">';

        // ID placeholder
        if ($this->get_pref('adi_matrix_display_id')) {
            $out .= tag('&#43;','td',' class="adi_matrix_add adi_matrix_field_id"'); // plus
        }

        // title
        $out .= tda(finput("text",$prefix."[title]",$defaults['title']),' class="adi_matrix_field_title"');

        // section
        if ($adi_matrix_list[$matrix_index]['show_section']) {
            $out .= tda($defaults['section'],' class="adi_matrix_field_section"');
        } elseif ($adi_matrix_list[$matrix_index]['section']) {
            $out .= tda($this->section_popup($prefix."[section]",$defaults['section'],$adi_matrix_list[$matrix_index]['criteria_section']),' class="adi_matrix_field_section"');
        }

        // status
        if ($adi_matrix_list[$matrix_index]['status']) {
            $out .= tda(selectInput($prefix.'[status]',$statuses,$defaults['status']),' class="adi_matrix_field_status"');
        }

        // custom fields
        foreach ($adi_matrix_cfs as $index => $cf_name) {
            $custom_x = 'custom_'.$index;

            if (array_key_exists($custom_x,$adi_matrix_list[$matrix_index])) {
            // check that custom field is known to adi_matrix
                if ($adi_matrix_list[$matrix_index][$custom_x]) {
                    if ($this->has_glz_cf) {
                        $glz_input_stuff = $this->glz_cfs_input($custom_x,$prefix."[$custom_x]",$defaults[$custom_x],0);

                        if ($glz_input_stuff[1] == 'glz_custom_radio_field') {
                            // don't apply glz_class coz can't handle glz reset function properly yet - see below
                            $out .= tda($glz_input_stuff[0],' class="adi_matrix_field_'.$custom_x.'"');
                        } else {
                            $out .= tda($glz_input_stuff[0],' class="'.$glz_input_stuff[1].' adi_matrix_field_'.$custom_x.'"');
                        }
                    } else {
                        $out .= tda(finput("text",$prefix."[$custom_x]",$defaults[$custom_x],'',($this->get_pref('adi_matrix_input_field_tooltips')?htmlspecialchars($defaults[$custom_x]):'')),' class="adi_matrix_field_'.$custom_x.'"');
                    }
                }
            }
        }

        // article image
        if ($adi_matrix_list[$matrix_index]['article_image']) {
            $out .= tda(finput("text",$prefix."[article_image]",$defaults['title']),' class="adi_matrix_field_image"');
        }

        // keywords
        if ($adi_matrix_list[$matrix_index]['keywords']) {
            $out .= tda('<textarea name="'.$prefix."[keywords]".'" cols="18" rows="5" class="mceNoEditor">'.htmlspecialchars(str_replace(',' ,', ', $defaults['keywords'])).'</textarea>',' class="adi_matrix_field_keywords"');
        }

        // category1
        if ($adi_matrix_list[$matrix_index]['category1']) {
            $out .= tda($this->category_popup($prefix."[category1]",$defaults['category1'],false),' class="adi_matrix_category adi_matrix_field_category1"');
        }

        // category2
        if ($adi_matrix_list[$matrix_index]['category2']) {
            $out .= tda($this->category_popup($prefix."[category2]",$defaults['category2'],false),' class="adi_matrix_category adi_matrix_field_category2"');
        }

        // posted
        if ($adi_matrix_list[$matrix_index]['posted'])
            $out .= tda($this->timestamp_input($prefix."[posted]",$defaults['posted'],$defaults['uposted'],'posted'),' class="adi_matrix_timestamp adi_matrix_field_posted"');

        // expires
        if ($adi_matrix_list[$matrix_index]['expires']) {
            $out .= tda($this->timestamp_input($prefix."[expires]",$defaults['expires'],$defaults['uexpires'],'expires'),' class="adi_matrix_timestamp adi_matrix_field_expires"');
        }

        // Delete placeholder
        if ($adi_matrix_list[$matrix_index]['publish']) {
            $out .= tag(sp,'td',' class="adi_matrix_delete"');
        }

        $out .= '</tr>';

        return $out;
    }

    // a matrix tab
    public function matrix_matrix($event,$step)
    {
        global $prefs, $adi_matrix_list, $adi_matrix_articles, $adi_matrix_cfs, $adi_matrix_validation_errors, $adi_matrix_article_defaults;

        $sort_options = $this->get_sort_options();
        $sort_types = $this->get_sort_types();
        $sort_dirs = $this->get_sort_dirs();

        // extract matrix index from event (e.g. adi_matrix_matrix_0 => 0)
        $matrix_index = str_replace('adi_matrix_matrix_','',$event);

        // bomb out if upgrade needed
        $upgrade_required = $this->upgrade();
        if ($upgrade_required) {
            pagetop($adi_matrix_list[$matrix_index]['name'],array(gTxt('adi_matrix_upgrade_required'),E_WARNING));
            return;
        }

        // current sort settings (read from user pref, default to matrix settings)
        list($sort,$dir,$sort_type) = explode(',',get_pref($event.'_sort',$adi_matrix_list[$matrix_index]['sort'].','.$adi_matrix_list[$matrix_index]['dir'].','.$adi_matrix_list[$matrix_index]['sort_type']));

        // user sort changes
        $new_sort = doStripTags(gps('sort'));
        $new_dir = doStripTags(gps('dir'));
        $new_sort_type = doStripTags(gps('sort_type'));
        $reset_sort = doStripTags(gps('reset_sort'));
        // sort it all out
        if ($new_sort || $new_dir || $new_sort_type || $reset_sort) {
            if ($new_sort && $new_dir) {
                // column heading clicked
                $this->get_pref($event.'_sort',$new_sort.','.$new_dir.','.$sort_type,true); // update user pref with sort & dir
            } elseif ($new_sort_type) {
                // sort_type change
                $this->get_pref($event.'_sort',$sort.','.$dir.','.$new_sort_type,true); // update user pref with sort_type
            } elseif ($reset_sort) {
                // reset sort to default
                safe_delete('txp_prefs',"name = '".$event."_sort'",$this->debug); // delete user pref
                unset($prefs[$event.'_sort']);
            }
            // reread user pref, defaulting to matrix settings
            list($sort,$dir,$sort_type) = explode(',',get_pref($event.'_sort',$adi_matrix_list[$matrix_index]['sort'].','.$adi_matrix_list[$matrix_index]['dir'].','.$adi_matrix_list[$matrix_index]['sort_type']));
        }

        // initialise some bits
        $message = '';
        $updates = $errors = array();

        // article selection criteria
        $criteria = array();
        $criteria['section'] = $adi_matrix_list[$matrix_index]['criteria_section'];
        $criteria['category'] = $adi_matrix_list[$matrix_index]['criteria_category'];
        $criteria['descendent_cats'] = $adi_matrix_list[$matrix_index]['criteria_descendent_cats'];
        $criteria['status'] = $adi_matrix_list[$matrix_index]['criteria_status'];
        $criteria['author'] = $adi_matrix_list[$matrix_index]['criteria_author'];
        $criteria['keywords'] = $adi_matrix_list[$matrix_index]['criteria_keywords'];
        $criteria['timestamp'] = $adi_matrix_list[$matrix_index]['criteria_timestamp'];
        $criteria['expiry'] = $adi_matrix_list[$matrix_index]['criteria_expiry'];
        $criteria['condition'] = $adi_matrix_list[$matrix_index]['criteria_condition'];

        // article selection WHERE clause
        $where = $this->build_where($criteria);

        // article sort query
        $sortq = $this->build_order_by($sort,$dir,$sort_type);

        // paging Mr. Matrix
        if ($step == $event.'_change_pageby') {
            // change of page length
            $qty = gps('qty');
            $this->get_pref($event.'_pageby',$qty,true);
        }

        $page = gps('page'); // get page number

        // get current page size (paging default, if not saved as pref)
        $pageby = get_pref($event.'_pageby',($this->is_txp470 ? 12 : 15)); 
        $total = safe_count('textpattern',"$where");
        list($page,$offset,$num_pages) = pager($total,$pageby,$page);

        if ($this->debug) {
            echo "<b>Paging:</b> pageby=$pageby, total=$total, page=$page, offset=$offset, num_pages=$num_pages".br;
        }

        // get a page of articles
        $adi_matrix_articles = $this->get_articles($matrix_index,$offset,$pageby,$where,$sortq);

        // article defaults
        $adi_matrix_article_defaults = $this->article_defaults($matrix_index);

        // $step aerobics
        if ($step == 'update') {
            $post_data = $this->get_post_data($adi_matrix_articles,$matrix_index);
            $errors = $this->validate_post_data($adi_matrix_articles,$post_data);
            $updates = $this->get_updates($this->remove_errors($post_data,$errors),$adi_matrix_articles);

            if ($updates) {
                $ok = $this->update_articles($updates,$matrix_index);
                $ok ? $message = gTxt('adi_matrix_articles_saved') : $message = array(gTxt('adi_matrix_article_update_fail'),E_WARNING);
            } else {
                $message = gTxt('adi_matrix_articles_not_modified');
            }

            if ($errors) {
                $message .= '. '.gTxt('adi_matrix_validation_error');

                foreach ($errors as $i => $v)
                    $message .= ' '.$adi_matrix_validation_errors[$i].' ('.implode(',',array_keys($v)).')';
                $message = array($message,E_WARNING);
            }
        } elseif ($step == 'delete') {
            $id = gps('id');

            if (isset($adi_matrix_articles[$id])) {
                $ok = $this->delete_article($id);
                $message = gTxt('article_deleted').' ('.$id.')';
            } else {
                $message = array(gTxt('adi_matrix_article_delete_fail'),E_ERROR);
            }
        }

        // regenerate articles list & count array again?
        if (($step == 'update') || ($step == 'delete')) {
            $adi_matrix_articles = $this->get_articles($matrix_index,$offset,$pageby,$where,$sortq);
            $total = safe_count('textpattern',"$where");
        }

        if ($this->debug) {
            $matrix_page_id_list = implode(',',array_keys($adi_matrix_articles)); // list if all article ids on this page
            echo "<b>Article ID list (on this page):</b> $matrix_page_id_list".br;
            $col = safe_column('ID','textpattern',$where.$sortq);
            $matrix_id_list = implode(',',array_keys($col));
            echo "<b>Article ID list (in matrix):</b> $matrix_id_list".br.br;
        }

        if ($this->dump) {
            // dump article data (all articles in matrix)
            $rows = safe_rows('*','textpattern',"ID IN (".implode(',',array_keys($adi_matrix_articles)).")");
            echo 'START ARTICLE MATRIX DUMP'.br;
            foreach ($rows as $i => $a) {
                foreach ($a as $f => $d)
                    echo htmlentities("$f=$d,");
                echo br;
            }
            echo 'END ARTICLE MATRIX DUMP'.br;
        }

        // generate page
        pagetop($adi_matrix_list[$matrix_index]['name'],$message);

        // output matrix table & input form
        $table = $this->matrix_table($adi_matrix_articles,$matrix_index,$page,$errors,$updates);
        $tags = array('<input', '<textarea', '<select'); // tags which indicate that a save button is deserved
        $save_button = false;

        foreach ($tags as $tag) {
            $save_button = $save_button || strpos($table,$tag);
        }

        $class = 'adi_matrix_matrix';

        if ($adi_matrix_list[$matrix_index]['scroll']) {
            $class .= ' adi_matrix_scroll';
        }

        $class .= ' txp-list';
        echo form(
            tag($adi_matrix_list[$matrix_index]['name'],'h1')
            .'<div class="scroll_box">'
            .startTable('list','',$class)
            .$table
            .endTable()
            .'</div>'
            .(empty($adi_matrix_articles) ?
                graf(tag(gTxt('no_articles_recorded'),'em'),' class="adi_matrix_none"')
                : ''
            )
            .($save_button ?
                tag(
                    hInput('page',$page) // pass on paging
                    .fInput("submit", "do_something", gTxt('save'), "publish")
                    .eInput("adi_matrix_matrix_".$matrix_index).sInput("update"),
                    'div',
                    ' class="adi_matrix_button"'
                )
                : ''
            )
            .tag(
                graf(
                    gTxt('adi_matrix_default_sort')
                    .sp.sp
                    .elink(
                        $event
                        ,''
                        ,'reset_sort'
                        ,1
                        ,$sort_options[$adi_matrix_list[$matrix_index]['sort']]
                            .', '
                            .$sort_dirs[$adi_matrix_list[$matrix_index]['dir']]
                            .', '
                            .$sort_types[$adi_matrix_list[$matrix_index]['sort_type']]
                        ,'','','' // to override TXP 4.5 default title "Edit"
                    )
                    .br
                    .gTxt('adi_matrix_sort_type')
                    .sp.sp
                    .strong(gTxt('adi_matrix_'.$sort_type))
                    .' / '
                    .elink(
                        $event
                        ,''
                        ,'sort_type'
                        ,($sort_type == 'numerical' ? 'alphabetical' : 'numerical')
                        ,gTxt(($sort_type == 'numerical' ? 'adi_matrix_alphabetical' : 'adi_matrix_numerical'))
                        ,'','','' // to override TXP 4.5 default title "Edit"
                    )
                )
                ,'div',' class="adi_matrix_matrix_prefs"')
            ,''
            ,''
            ,'post'
            ,$class
        );

        // flashing message
        if ($errors) {
            echo <<<END_SCRIPT
                <script type="text/javascript">
                <!--
                $(document).ready( function(){
                            $('#messagepane').fadeOut(800).fadeIn(800);
                            $('#messagepane').fadeOut(800).fadeIn(800);
                        } )
                // -->
                </script>
    END_SCRIPT;
        }

        // grand total
        echo graf(gTxt('adi_matrix_total_articles').sp.sp.$total, ' class="adi_matrix_grand_total"');

        // paging
        echo tag(
                // prev/next page buttons (if muliple pages)
                ($total > $pageby ? nav_form($event, $page, $num_pages, $sort, $dir, '', '', $total, $pageby, '') : '') // set empty step (to avoid confusion), coz default is "list"
                // "num articles per page" select
                .($this->is_txp470 ? Txp::get('\Textpattern\Admin\Paginator', $event, $step)->render($pageby) : pageby_form($event, $pageby))
                ,'div'
                ,' id="list_navigation" class="txp-navigation"'
            );

        if ($this->debug) {
            $this->debug($adi_matrix_articles, $matrix_index);
        }

    }

    // test if database table is present
    public function installed($table='adi_matrix')
    {
        $rs = safe_query("SHOW TABLES LIKE '".safe_pfx($table)."'");
        $a = nextRow($rs);

        if ($a) {
            return true;
        } else {
            return false;
        }
    }

    // install adi_matrix table in database
    public function install()
    {
        global $adi_matrix_cfs;

        $cfq = '';

        foreach ($adi_matrix_cfs as $index => $value) {
            $cfq .= ', `custom_'.$index.'` TINYINT(1) DEFAULT 0 NOT NULL';
        }

        $res = safe_query(
                "CREATE TABLE IF NOT EXISTS "
                .safe_pfx('adi_matrix')
                ." (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `ordinal` VARCHAR(32) DEFAULT 1 NULL,
                `name` VARCHAR(255) NOT NULL,
                `sort` VARCHAR(32) NOT NULL DEFAULT 'posted',
                `sort_type` VARCHAR(32) NOT NULL DEFAULT 'alphabetical',
                `dir` VARCHAR(32) NOT NULL DEFAULT 'desc',
                `user` VARCHAR(64) NOT NULL DEFAULT '',
                `privs` VARCHAR(16) NOT NULL DEFAULT '',
                `scroll` TINYINT(1) DEFAULT 0 NOT NULL,
                `footer` TINYINT(1) DEFAULT 0 NOT NULL,
                `title` TINYINT(1) DEFAULT 0 NOT NULL,
                `publish` TINYINT(1) DEFAULT 0 NOT NULL,
                `show_section` TINYINT(1) DEFAULT 0 NOT NULL,
                `cf_links` VARCHAR(128) NOT NULL DEFAULT '',
                `tab` VARCHAR(16) NOT NULL DEFAULT 'content',
                `criteria_section` VARCHAR(128) NOT NULL DEFAULT '',
                `criteria_category` VARCHAR(128) NOT NULL DEFAULT '',
                `criteria_status` INT(2) NOT NULL DEFAULT '4',
                `criteria_author` VARCHAR(64) NOT NULL DEFAULT '',
                `criteria_keywords` VARCHAR(255) NOT NULL DEFAULT '',
                `criteria_timestamp` VARCHAR(16) NOT NULL DEFAULT 'any',
                `criteria_expiry` INT(2) NOT NULL DEFAULT '0',
                `criteria_descendent_cats` TINYINT(1) DEFAULT 0 NOT NULL,
                `criteria_condition` VARCHAR(255) NOT NULL DEFAULT '',
                `status` TINYINT(1) DEFAULT 0 NOT NULL,
                `keywords` TINYINT(1) DEFAULT 0 NOT NULL,
                `article_image` TINYINT(1) DEFAULT 0 NOT NULL,
                `category1` VARCHAR(128) NOT NULL DEFAULT '',
                `category2` VARCHAR(128) NOT NULL DEFAULT '',
                `posted` TINYINT(1) DEFAULT 0 NOT NULL,
                `expires` TINYINT(1) DEFAULT 0 NOT NULL,
                `section` TINYINT(1) DEFAULT 0 NOT NULL
                "
                .$cfq
                .");"
                ,$this->debug
            );

        // Install prefs.
        $plugprefs = $this->get_prefs();

        foreach ($plugprefs as $name => $opts) {
            // $this->get_pref() sets the given pref if provided a value.
            $this->get_pref($name, $opts['value']);
        }

        return $res;
    }

    // uninstall adi_matrix
    public function uninstall()
    {
        // delete table
        $res = safe_query("DROP TABLE ".safe_pfx('adi_matrix').";",$this->debug);
        // delete preferences
        $res = $res && safe_delete('txp_prefs',"name LIKE 'adi_matrix_%'",$this->debug);
        // delete textpack
        $res = $res && safe_delete('txp_lang',"owner = 'adi_matrix'",$this->debug);

        return $res;
    }

    // a matter of life & death
    // $event:  "plugin_lifecycle.adi_matrix"
    // $step:   "installed", "enabled", disabled", "deleted"
    // TXP 4.5: upgrade/reinstall only triggers "installed" event (now have to manually detect whether upgrade required)
    public function lifecycle($event,$step)
    {
        $result = '?';

        // set upgrade flag if upgrading/reinstalling in TXP 4.5+
        $upgrade = (($step == "installed") && $this->installed());

        if ($step == 'enabled') {
            $result = $upgrade = $this->install();
        } elseif ($step == 'deleted') {
            $result = $this->uninstall();
        }

        if ($upgrade) {
            $result = $result && $this->upgrade(true);
        }

        if ($this->debug) {
            echo "Event=$event Step=$step Result=$result Upgrade=$upgrade";
        }
    }

    // check/perform upgrade
    public function upgrade($do_upgrade=false)
    {
        $upgrade_required = false;
        // version 0.2
        $rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'article_image'",$this->debug); // find out if column exists
        $a = nextRow($rs);
        $v0_2 = empty($a);
        $upgrade_required = $upgrade_required || $v0_2;
        // version 0.3
        $rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'criteria_timestamp'",$this->debug);
        $a = nextRow($rs);
        $v0_3t = empty($a);
        $upgrade_required = $upgrade_required || $v0_3t;
        // version 0.3
        $rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'criteria_expiry'",$this->debug);
        $a = nextRow($rs);
        $v0_3e = empty($a);
        $upgrade_required = $upgrade_required || $v0_3e;
        // version 1.0
        $rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'scroll'",$this->debug);
        $a = nextRow($rs);
        $v1_0 = empty($a);
        $upgrade_required = $upgrade_required || $v1_0;
        // version 1.1
        $rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'footer'",$this->debug);
        $a = nextRow($rs);
        $v1_1 = empty($a);
        $upgrade_required = $upgrade_required || $v1_1;
        // version 2.0
        $rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'title'",$this->debug);
        $a = nextRow($rs);
        $v2_0 = empty($a);
        $upgrade_required = $upgrade_required || $v2_0;
        // version 2.1
        $rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'ordinal'",$this->debug);
        $a = nextRow($rs);
        $v2_1 = empty($a);
        $upgrade_required = $upgrade_required || $v2_1;
        // version 3.0
        $ipmethod = safe_field('event', 'txp_prefs', "name = 'adi_matrix_article_highlighting'", $this->debug);
        $v3_0 = $ipmethod === 'adi_matrix_admin';
        $upgrade_required = $upgrade_required || $v3_0;

        if ($do_upgrade && $upgrade_required) {
            $res = true;
            if ($v0_2) {
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `article_image` TINYINT(1) DEFAULT 0 NOT NULL",$this->debug);
            }
            if ($v0_3t) {
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `criteria_timestamp` VARCHAR(16) NOT NULL DEFAULT 'any'",$this->debug);
            }
            if ($v0_3e) {
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `criteria_expiry` INT(2) NOT NULL DEFAULT '0'",$this->debug);
            }
            if ($v1_0) {
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `scroll` TINYINT(1) DEFAULT 0 NOT NULL",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `category1` VARCHAR(128) NOT NULL DEFAULT ''",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `category2` VARCHAR(128) NOT NULL DEFAULT ''",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `dir` VARCHAR(32) NOT NULL DEFAULT '1'",$this->debug);
                // convert old `sort` to new `sort` & `dir`
                //  OLD sort=1 (Posted desc)    ->  NEW sort=1, dir=1
                //  OLD sort=2 (Posted asc)     ->  NEW sort=1, dir=2
                //  OLD sort=3 (Title)          ->  NEW sort=2, dir=''
                //  OLD sort=4 (LastMod desc)   ->  NEW sort=3, dir=1
                //  OLD sort=5 (LastMod asc)    ->  NEW sort=3, dir=2
                $res = $res && safe_update('adi_matrix',"`dir` = '1'","`sort` IN ('1','4')",$this->debug);
                $res = $res && safe_update('adi_matrix',"`dir` = '2'","`sort` IN ('2','5')",$this->debug);
                $res = $res && safe_update('adi_matrix',"`sort` = '1'","`sort` = '2'",$this->debug);
                $res = $res && safe_update('adi_matrix',"`sort` = '2'","`sort` = '3'",$this->debug);
                $res = $res && safe_update('adi_matrix',"`sort` = '3'","`sort` IN ('4','5')",$this->debug);
            }
            if ($v1_1) {
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `footer` TINYINT(1) DEFAULT 0 NOT NULL",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `sort_type` VARCHAR(32) NOT NULL DEFAULT 'alphabetical'",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `posted` TINYINT(1) DEFAULT 0 NOT NULL",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `expires` TINYINT(1) DEFAULT 0 NOT NULL",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `criteria_descendent_cats` TINYINT(1) DEFAULT 0 NOT NULL",$this->debug);
                // one day sort will be sorted
                //  OLD sort=1, NEW sort='posted'
                //  OLD sort=2, NEW sort='title',
                //  OLD sort=3, NEW sort='lastmod',
                //  OLD sort=4, NEW sort='expires',
                $res = $res && safe_update('adi_matrix',"`sort` = 'posted'","`sort` = '1'",$this->debug);
                $res = $res && safe_update('adi_matrix',"`sort` = 'title'","`sort` = '2'",$this->debug);
                $res = $res && safe_update('adi_matrix',"`sort` = 'lastmod'","`sort` = '3'",$this->debug);
                $res = $res && safe_update('adi_matrix',"`sort` = 'expires'","`sort` = '4'",$this->debug);
                // sort_type "+ 0 asc/desc" now "numerical"
                $res = $res && safe_update('adi_matrix',"`sort_type` = 'alphabetical'","`dir` IN ('1','2')",$this->debug);
                $res = $res && safe_update('adi_matrix',"`sort_type` = 'numerical'","`dir` IN ('3','4')",$this->debug);
                // sort direction
                $res = $res && safe_update('adi_matrix',"`dir` = 'desc'","`dir` IN ('1','4')",$this->debug);
                $res = $res && safe_update('adi_matrix',"`dir` = 'asc'","`dir` IN ('2','3')",$this->debug);
            }
            if ($v2_0) {
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `title` TINYINT(1) DEFAULT 0 NOT NULL",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `publish` TINYINT(1) DEFAULT 0 NOT NULL",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `show_section` TINYINT(1) DEFAULT 0 NOT NULL",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `cf_links` VARCHAR(128) NOT NULL DEFAULT ''",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `tab` VARCHAR(16) NOT NULL DEFAULT 'content'",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `section` TINYINT(1) DEFAULT 0 NOT NULL",$this->debug);
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `criteria_condition` VARCHAR(255) NOT NULL DEFAULT ''",$this->debug);
                $res = $res && safe_delete('txp_prefs',"name='adi_matrix_article_limit'",$this->debug); // made redundant by paging
            }
            if ($v2_1) {
                $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." ADD `ordinal` VARCHAR(32) DEFAULT 1 NULL AFTER `id`",$this->debug);
            }
            if ($v3_0) {
                $allprefs = $this->get_prefs();

                foreach ($allprefs as $pref => $opts) {
                    $res = $res && safe_update('txp_prefs', "event='adi_matrix', type='".(empty($opts['type']) ? PREF_PLUGIN : $opts['type']), "name='" . doSlash($pref) . "'");
                }
            }

            return $res;
        } else {
            // report back only
            return $upgrade_required;
        }
    }

    // downgrade to previous version - 2.0 to 1.1/1.2 only
    public function downgrade()
    {
        $res = true;
        $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `title`",$this->debug);
        $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `publish`",$this->debug);
        $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `show_section`",$this->debug);
        $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `cf_links`",$this->debug);
        $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `tab`",$this->debug);
        $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `section`",$this->debug);
        $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `criteria_condition`",$this->debug);
        $res = $res && safe_query("ALTER TABLE ".safe_pfx("adi_matrix")." DROP `ordinal`",$this->debug);

        return $res;
    }

    // generate a tree of parent/child relationships
    public function cat_tree($list,$parent='root')
    {
        $return = array();

        foreach ($list as $cat) {
            if ($cat['parent'] == $parent) {
                $return[$cat['name']] = $this->cat_tree($list,$cat['name']);
            }
        }

        return $return;
    }

    // find all descendents of a given parent
    public function cat_descendents($tree,$parent=NULL,$found=false)
    {
        $return = array();

        foreach ($tree as $name => $children) {
            if ($found) {
                $return[] = $name;
            }

            $return = array_merge($return, $this->cat_descendents($children,$parent,(($name == $parent) OR ($found))));
        }

        return $return;
    }

    // create categories array, indexed by cat name containing pertinent information
        // $getTree_array is array of arrays:
        //    'id' => 'xx',
        //    'name' => 'category-name',
        //    'title' => 'Category Title',
        //    'level' => 0,  (in category hierarchy)
        //    'children' => 2,  (no. of children)
        //    'parent' => 'root',   (name of parent)
    public function get_categories($getTree_array)
    {
        $cat_tree = $this->cat_tree($getTree_array);

        $categories = array();

        foreach ($getTree_array as $this_cat) {
            $categories[$this_cat['name']]['parent'] = $this_cat['parent'];
            $categories[$this_cat['name']]['children'] = $this->cat_descendents($cat_tree,$this_cat['name']);
        }

        return $categories;
    }

    // generate section popup list for admin settings table
    // where 'true' not supported on MySQL 4.0.27 (OK in MySQL 5+), so use 1=1
    public function section_popup($select_name,$value,$list='')
    {
        $blank_allowed = true;
        $where = "name != 'default'";

        // set up where condition if section list supplied
        if ($list) {
            $a = explode(',',$list);

            foreach ($a as $i => $v) {
                $a[$i] = "'$v'";
            }

            $where .= 'AND name in ('.implode(',',$a).')';
            $blank_allowed = false;
        }

        $rs = safe_column('name', 'txp_section', $where);

        if ($rs) {
            return selectInput($select_name, $rs, $value, $blank_allowed);
        }

        return false;
    }

    // generate section checkboxes
    public function section_checkboxes($field_name,$value)
    {
        $section_list = explode(',',$value);
        $out = '';

        $rs = safe_column('name', 'txp_section', "name != 'default'");

        if ($rs) {
            foreach ($rs as $section) {
                $out .= tag(checkbox($field_name.'[]',$section,(array_search($section,$section_list) !== false ? '1' : '0')).sp.$section,'label');
            }

            return $out;
        }

        return false;
    }

    // generate category popup list for admin settings table
    public function category_popup($select_name,$value,$admin=true)
    {
        $rs = getTree('root','article');

        if ($rs) {
            if ($admin) {
                /* create wildcards (wildcats?) */
                $wildcard_list = array('no_category','any_category','one_category','two_categories','any_parent_category','any_child_category');
                foreach (array_reverse($wildcard_list) as $wildcard) {
                    /* add to front of array & renumber */
                    array_unshift($rs,array('id' => '0', 'name' => '!'.$wildcard.'!', 'title' => gTxt('adi_matrix_'.$wildcard), 'level' => '0', 'children' => '0', 'parent' => 'root'));
                }
            }

            return treeSelectInput($select_name,$rs,$value,'',35);
        }

        return tag(gTxt('no_categories_exist'),'em');
    }

    // generate status popup list for admin settings table
    public function status_popup($select_name,$value)
    {
        $opts = $this->get_statuses();

        return selectInput($select_name, $opts, $value, true);
    }

    // generate timestamp popup list for admin settings table
    public function timestamp_popup($select_name, $value)
    {
        $opts = array(
            'any' => gTxt('adi_matrix_time_any'),
            'past' => gTxt('adi_matrix_time_past'),
            'future' => gTxt('adi_matrix_time_future'),
        );

        return selectInput($select_name, $opts, $value, false);
    }

    // generate expiry timestamp popup list for admin settings table
    public function expiry_popup($select_name, $value)
    {
        $opts = array(
            0 => '',
            1 => gTxt('adi_matrix_no_expiry'),
            2 => gTxt('adi_matrix_has_expiry'),
            3 => gTxt('expired'),
        );

        return selectInput($select_name, $opts, $value, false);
    }

    // generate user/author popup list for admin settings table
    public function user_popup($select_name,$value,$wildcard=false)
    {
        static $allusers = array();

        if (empty($allusers)) {
            $allusers = safe_column('name', 'txp_users', '1=1');
        }

        if ($allusers) {
            if ($wildcard) { /* create wildcard */
                $logged_in_user = array('!logged_in_user!' => gTxt('adi_matrix_logged_in_user'));
                $allusers = $logged_in_user + $allusers; /* add to front of array */
            }

            return selectInput($select_name, $allusers, $value, true);
        }

        return false;
    }

    // generate privs popup list for admin settings table
    public function privs_popup($select_name,$value)
    {
        global $txp_groups;

        // to get: 1 => 'publisher', 2 => 'managing_editor' etc
        $matrix_groups = $txp_groups;

        // lose index zero (none) - gets us a blank select option too!
        unset($matrix_groups[0]);

        foreach ($matrix_groups as $index => $group) {
            // to get: 1 => 'Publisher', 2 => 'Managing Editor' etc in the language 'de jour'
            $matrix_groups[$index] = gTxt($group);
        }

        return selectInput($select_name, $matrix_groups, $value, true);
    }

    // generate tab popup list for admin settings table
    public function tab_popup($select_name,$value)
    {
        $tabs = array(
            'content' => gTxt('tab_content'),
            'start' => gTxt('tab_start'),
        );

        return selectInput($select_name, $tabs, $value);
    }

    // date/time input fields - code stolen from include/txp_article.php
    public function timestamp_input($name,$datetime,$ts,$type='posted')
    {
        if ($datetime == '0000-00-00 00:00:00') {
            $ts = 0;
        }

        $class = ' '.$type;

        if ($type == 'posted') {
            $class .= ' created';
        }

        $out =
            tag(
                $this->matrix_tsi($name.'[year]','%Y',$ts,'',$type)
                .' /'
                .$this->matrix_tsi($name.'[month]','%m',$ts,'',$type)
                .' /'
                .$this->matrix_tsi($name.'[day]','%d',$ts,'',$type)
                ,'div'
                ,' class="date'.$class.'"'
            )
            .tag(
                $this->matrix_tsi($name.'[hour]','%H',$ts,'',$type)
                .' :'
                .$this->matrix_tsi($name.'[minute]','%M',$ts,'',$type)
                .' :'
                .$this->matrix_tsi($name.'[second]','%S',$ts,'',$type)
                .($type == 'posted' ? br.tag(gTxt('adi_matrix_reset'),'label',' class="reset_time-now"').sp.checkbox($name.'[reset_time]','1','0') : '')
                ,'div'
                ,' class="time'.$class.'"'
            );

        return $out;
    }

    // date/time item input - adapted from txplib_forms.php tsi()
    public function matrix_tsi($name,$datevar,$time,$tab='',$type='')
    {
        preg_match_all('/.*\[(.*)\]$/',$name,$matches); // to get 'year' etc from article_x[posted][year]
        $short_name = $matches[1][0];

        if ($type == 'expires') {
            $short_name = 'exp_'.$short_name;
        }

        $size = ($short_name == 'year' or $short_name == 'exp_year') ? 4 : 2;
        $s = ($time == 0) ? '' : safe_strftime($datevar, $time); // convert DB time to TXP date/time

        return
            n
            .'<input type="text" name="'.$name
            .'" value="'
            .$s
            .'" size="'
            .$size
            .'" maxlength="'
            .$size
            .'" class="edit '
            .$short_name
            .'"'
            .(empty($tab) ? '' : ' tabindex="'.$tab.'"')
            .' title="'
            .gTxt('article_'.$short_name)
            .'" />';
    }

    // matrix delete button [X]
    public function delete_button($matrix_list,$matrix_index)
    {
        $event = 'adi_matrix_admin';
        $step = 'delete';

        if ($matrix_index == 'new') {
            // don't want delete button
            return sp;
        } else {
            if ($this->is_txp460) {
                return
                    href(
                        span('Delete',' class="ui-icon ui-icon-close"')
                        ,array(
                            'event' => $event,
                            'step' => $step,
                            'matrix' => $matrix_index,
                            '_txp_token'    => form_token(),
                        )
                        ,array(
                            'class'       => 'dlink destroy',
                            'title'       => gTxt('delete'),
                            'data-verify' => gTxt('confirm_delete_popup'),
                        )
                    );
            } else {
                return
                    '<a href="?event='.$event.a.'step='.$step.a.'matrix='.$matrix_index
                    .'" class="dlink" title="'.gTxt('delete').'" onclick="return verify(\''
                    .gTxt('confirm_delete_popup')
                    .'\')">'
                    .'&#215;'
                    .'</a>';
            }
        }
    }

    // stick a matrix in the bin
    public function delete($matrix_index)
    {
        $res = safe_delete('adi_matrix', "`id`=$matrix_index", $this->debug);
        return $res;
    }

    // analyse $_POST & update settings
    public function update_settings()
    {
        global $adi_matrix_cfs;

        $res = false;

        foreach ($_POST as $index => $value) {
            $data = doArray($value,'doStripTags'); // strip out monkey business

            $this_index = explode('_',$index);

            if ($this_index[0] == 'matrix') {
                $matrix_index = $this_index[1];

                // adjustments
                if ($data['publish']) {
                    // if publish, then get title edit for free
                    $data['title'] = '1';
                }

                if (isset($data['section'])) {
                    // if section selected as data, then switch off show_section
                    $data['show_section'] = '0';
                }

                // sort
                if ($data['sort'] == '') {
                    $sortq = "sort='desc', ";
                } else {
                    $sortq = "sort='".doSlash($data['sort'])."', ";
                }

                // section
                if (array_key_exists('criteria_section',$data)) {
                    $criteria_sectionq = "criteria_section='".implode(',',$data['criteria_section'])."', ";
                } else {
                    $criteria_sectionq = "criteria_section='', ";
                }

                // criteria status
                if ($data['criteria_status'] == '') {
                    $data['criteria_status'] = '0'; // needs to be an integer for STRICTly (zero ignored in select field)
                }

                // status
                if (array_key_exists('status',$data)) {
                    $statusq = 'status=1, ';
                } else {
                    $statusq = 'status=0, ';
                }

                // keywords
                if (array_key_exists('keywords',$data)) {
                    $keywordsq = 'keywords=1, ';
                } else {
                    $keywordsq = 'keywords=0, ';
                }

                // article image
                if (array_key_exists('article_image',$data)) {
                    $article_imageq = 'article_image=1, ';
                } else {
                    $article_imageq = 'article_image=0, ';
                }

                // category
                if (array_key_exists('category1',$data)) {
                    $categoryq = 'category1=1, ';
                } else {
                    $categoryq = 'category1=0, ';
                }

                if (array_key_exists('category2',$data)) {
                    $categoryq .= 'category2=1, ';
                } else {
                    $categoryq .= 'category2=0, ';
                }

                // posted
                if (array_key_exists('posted',$data)) {
                    $postedq = 'posted=1, ';
                } else {
                    $postedq = 'posted=0, ';
                }

                // expires
                if (array_key_exists('expires',$data)) {
                    $expiresq = 'expires=1, ';
                } else {
                    $expiresq = 'expires=0, ';
                }

                // title
                if (array_key_exists('title',$data)) {
                    $titleq = 'title=1, ';
                } else {
                    $titleq = 'title=0, ';
                }

                // section
                if (array_key_exists('section',$data)) {
                    $sectionq = 'section=1, ';
                } else {
                    $sectionq = 'section=0, ';
                }

                // custom field
                $cfq = '';

                foreach ($adi_matrix_cfs as $index => $cf_name) {
                    $custom_x = 'custom_'.$index;

                    if (array_key_exists($custom_x,$data)) {
                        $cfq .= "custom_".$index."='1', ";
                    } else {
                        $cfq .= "custom_".$index."='0', ";
                    }
                }

                // category
                if (!array_key_exists('criteria_category',$data)) {
                    // in case there're no categories defined
                    $data['criteria_category'] = '';
                }

                if (preg_match('/!.*!/',$data['criteria_category'])) {
                    // disable descendent cats option with wildcards
                    unset($data['criteria_descendent_cats']);
                }

                if (array_key_exists('criteria_descendent_cats',$data)) {
                    $criteria_descendent_catsq = 'criteria_descendent_cats=1, ';
                } else {
                    $criteria_descendent_catsq = 'criteria_descendent_cats=0, ';
                }

                $matrix_sql_set =
                    "name='".doSlash($data['name'])."', "
                    .$sortq
                    ."ordinal='".doSlash($data['ordinal'])."', "
                    ."dir='".doSlash($data['dir'])."', "
                    ."sort_type='".doSlash($data['sort_type'])."', "
                    ."user='".doSlash($data['user'])."', "
                    ."privs='".doSlash($data['privs'])."', "
                    ."scroll='".doSlash($data['scroll'])."', "
                    ."footer='".doSlash($data['footer'])."', "
                    ."publish='".doSlash($data['publish'])."', "
                    ."show_section='".doSlash($data['show_section'])."', "
                    ."cf_links='".doSlash($data['cf_links'])."', "
                    ."tab='".doSlash($data['tab'])."', "
                    .$criteria_sectionq
                    ."criteria_status=".doSlash($data['criteria_status']).", " // no quotes for status coz it's an integer
                    ."criteria_author='".doSlash($data['criteria_author'])."', "
                    ."criteria_keywords='".doSlash($data['criteria_keywords'])."', "
                    ."criteria_timestamp='".doSlash($data['criteria_timestamp'])."', "
                    ."criteria_expiry='".doSlash($data['criteria_expiry'])."', "
                    ."criteria_condition='".doSlash($data['criteria_condition'])."', "
                    .$titleq
                    .$statusq
                    .$sectionq
                    .$keywordsq
                    .$article_imageq
                    .$postedq
                    .$expiresq
                    .$cfq
                    .$categoryq
                    .$criteria_descendent_catsq
                    ."criteria_category='".doSlash($data['criteria_category'])."' ";

                if ($matrix_index == 'new') {  // add new matrix to the mix
                    if (!empty($data['name'])) { // but don't add a blank one
                        $res = safe_insert(
                            'adi_matrix',
                            $matrix_sql_set
                            ,$this->debug
                        );
                    }
                } else { // update existing matrix
                    $res = safe_upsert(
                        'adi_matrix',
                        $matrix_sql_set
                        ,"id='$matrix_index'"
                        ,$this->debug
                    );
                }
            }
        }

        return $res;
    }

    // generate form fields for existing & new matrixes
    public function admin_table($matrix_list, $matrix_cfs, $selected='')
    {
        global $adi_matrix_cfs,$prefs;

        $sort_options = $this->get_sort_options();
        $sort_types = $this->get_sort_types();
        $sort_dirs = $this->get_sort_dirs();

        $out = '';

        // existing matrixes followed by empty fields for a new one
        foreach ($matrix_list as $matrix_index => $matrix) {
            $cf_checkboxes = '';

            foreach ($matrix_cfs as $index => $cf_name) {
                $custom_x = 'custom_'.$index;
                $cf_checkboxes .= graf(tag(checkbox("matrix_".$matrix_index."[$custom_x]",1,$matrix[$custom_x]).span(sp.$cf_name,'label'),'label',' class="adi_matrix_checkbox"'));
            }

            $cf_td = tag($cf_checkboxes, 'div', ' class="adi_matrix_field adi_matrix_custom_field"');
            $url = '?event=adi_matrix_matrix_'.$matrix_index;

            if ($matrix_index == 'new') {
                $view_link = ' ';
            } else {
                $view_link =
                    '[<a href="?event=adi_matrix_matrix_'.$matrix_index.'" title="'.gTxt('view').'" class="adi_matrix_view_link"><span>'.gTxt('view').'</span></a>]';
            }

            $out .= tag(
                    // matrix settings
                    tag($this->delete_button($matrix_list,$matrix_index),'div', array('class' => 'adi_matrix_admin_delete'))
                    .tag(
                        hed(gTxt('adi_matrix_heading'). sp . $view_link, 3)
                        .graf(tag(gTxt('name'),'label').finput("text","matrix_".$matrix_index."[name]",$matrix['name']))
                        .graf(tag(gTxt('adi_matrix_order'),'label').finput("text","matrix_".$matrix_index."[ordinal]",$matrix['ordinal']))
                        .graf(tag(gTxt('adi_matrix_sort'),'label').selectInput("matrix_".$matrix_index."[sort]",$sort_options,$matrix['sort'],false))
                        .graf(tag(gTxt('adi_matrix_sort_direction'),'label').selectInput("matrix_".$matrix_index."[dir]",$sort_dirs,$matrix['dir'],false))
                        .graf(tag(gTxt('adi_matrix_sort_type'),'label').selectInput("matrix_".$matrix_index."[sort_type]",$sort_types,$matrix['sort_type'],false))
                        .graf(tag(gTxt('adi_matrix_user'),'label').$this->user_popup("matrix_".$matrix_index."[user]",$matrix['user']))
                        .graf(tag(gTxt('privileges'),'label').$this->privs_popup("matrix_".$matrix_index."[privs]",$matrix['privs']))
                        .graf(
                            span(gTxt('adi_matrix_scroll'))
                            .tag(
                                radio("matrix_".$matrix_index."[scroll]",'0',($matrix['scroll'] == '0'))
                                .sp
                                .gTxt('no')
                                ,'label'
                            )
                            .tag(
                                radio("matrix_".$matrix_index."[scroll]",'1',($matrix['scroll'] == '1'))
                                .sp
                                .gTxt('yes')
                                ,'label'
                            )
                        )
                        .graf(
                            span(gTxt('adi_matrix_footer'))
                            .tag(
                                radio("matrix_".$matrix_index."[footer]",'0',($matrix['footer'] == '0'))
                                .sp
                                .gTxt('no')
                                ,'label'
                            )
                            .sp.sp
                            .tag(
                                radio("matrix_".$matrix_index."[footer]",'1',($matrix['footer'] == '1'))
                                .sp
                                .gTxt('yes')
                                ,'label'
                            )
                        )
                        .graf(
                            span(gTxt('adi_matrix_show_section'))
                            .tag(
                                radio("matrix_".$matrix_index."[show_section]",'0',($matrix['show_section'] == '0'))
                                .sp
                                .gTxt('no')
                                ,'label'
                            )
                            .sp.sp
                            .tag(
                                radio("matrix_".$matrix_index."[show_section]",'1',($matrix['show_section'] == '1'))
                                .sp
                                .gTxt('yes')
                                ,'label'
                            )
                        )
                        .graf(
                            span(gTxt('adi_matrix_cf_links'))
                                .tag(
                                radio("matrix_".$matrix_index."[cf_links]",'',(empty($matrix['cf_links']))) // will be comma list one day
                                .sp
                                .gTxt('no')
                                ,'label'
                            )
                            .sp.sp
                            .tag(
                                radio("matrix_".$matrix_index."[cf_links]",'article_image',(!empty($matrix['cf_links']))) // will be comma list one day
                                .sp
                                .gTxt('yes')
                                ,'label'
                            )
                            ,' style="display:none"'
                        )
                        .graf(
                            span(gTxt('publish'))
                            .tag(
                                radio("matrix_".$matrix_index."[publish]",'0',($matrix['publish'] == '0'))
                                .sp
                                .gTxt('no')
                                ,'label'
                            )
                            .sp.sp
                            .tag(
                                radio("matrix_".$matrix_index."[publish]",'1',($matrix['publish'] == '1'))
                                .sp
                                .gTxt('yes')
                                ,'label'
                            )
                        )
                        .graf(tag(gTxt('adi_matrix_tab'),'label').$this->tab_popup("matrix_".$matrix_index."[tab]",$matrix['tab']))
                        ,'div', ' class="adi_matrix_field"'
                    )
                    // article selection
                    .tag(
                        hed(gTxt('adi_matrix_article_selection'), 3)
                        .gTxt('section').br
                        .tag($this->section_checkboxes("matrix_".$matrix_index."[criteria_section]",$matrix['criteria_section']),'div',' class="adi_matrix_multi_checkboxes"')
                        .graf(tag(gTxt('category'),'label').$this->category_popup("matrix_".$matrix_index."[criteria_category]",$matrix['criteria_category']))
                        .graf(tag(checkbox("matrix_".$matrix_index."[criteria_descendent_cats]",1,$matrix['criteria_descendent_cats']).sp.gTxt('adi_matrix_include_descendent_cats'),'label',' class="adi_matrix_label2"'))
                        .graf(tag(gTxt('status'),'label').$this->status_popup("matrix_".$matrix_index."[criteria_status]",$matrix['criteria_status']))
                        .graf(tag(gTxt('author'),'label').$this->user_popup("matrix_".$matrix_index."[criteria_author]",$matrix['criteria_author'],true))
                        .graf(tag(gTxt('keywords'),'label').finput("text","matrix_".$matrix_index."[criteria_keywords]",$matrix['criteria_keywords']))
                        .graf(tag(gTxt('timestamp'),'label').$this->timestamp_popup("matrix_".$matrix_index."[criteria_timestamp]",$matrix['criteria_timestamp']))
                        .graf(tag(gTxt('adi_matrix_expiry'),'label').$this->expiry_popup("matrix_".$matrix_index."[criteria_expiry]",$matrix['criteria_expiry']))
                        .graf(tag(gTxt('adi_matrix_custom_condition'),'label').finput("text","matrix_".$matrix_index."[criteria_condition]",$matrix['criteria_condition']))
                        ,'div', ' class="adi_matrix_field"'
                    )
                    // article data
                    .tag(
                        hed(gTxt('adi_matrix_article_data'), 3)
                        .tag(
                        tag(
                            graf(tag(checkbox("matrix_".$matrix_index."[status]",1,$matrix['status']).span(sp.gTxt('status')),'label',' class="adi_matrix_checkbox"'))
                            .graf(tag(checkbox("matrix_".$matrix_index."[keywords]",1,$matrix['keywords']).span(sp.gTxt('keywords')),'label',' class="adi_matrix_checkbox"'))
                            .graf(tag(checkbox("matrix_".$matrix_index."[article_image]",1,$matrix['article_image']).span(sp.gTxt('article_image')),'label',' class="adi_matrix_checkbox"'))
                            .graf(tag(checkbox("matrix_".$matrix_index."[category1]",1,$matrix['category1']).span(sp.gTxt('category1')),'label',' class="adi_matrix_checkbox"'))
                            .graf(tag(checkbox("matrix_".$matrix_index."[category2]",1,$matrix['category2']).span(sp.gTxt('category2')),'label',' class="adi_matrix_checkbox"'))
                            .graf(tag(checkbox("matrix_".$matrix_index."[posted]",1,$matrix['posted']).span(sp.gTxt('posted')),'label',' class="adi_matrix_checkbox"'))
                            .graf(tag(checkbox("matrix_".$matrix_index."[expires]",1,$matrix['expires']).span(sp.gTxt('expires')),'label',' class="adi_matrix_checkbox"'))
                            .graf(tag(checkbox("matrix_".$matrix_index."[title]",1,$matrix['title']).span(sp.gTxt('title')),'label',' class="adi_matrix_checkbox"'))
                            .graf(tag(checkbox("matrix_".$matrix_index."[section]",1,$matrix['section']).span(sp.gTxt('section')),'label',' class="adi_matrix_checkbox"'))
                            ,'div', ' class="adi_matrix_field"'
                        )
                        .$cf_td
                        , 'div', array('class' => 'adi_matrix_data_block'))
                    , 'div')
                , 'div', array('id' => 'matrix_id_'.$matrix_index, 'class' => 'adi_matrix_row'));
        }

        return $out;
    }

    /**
     * Fetch the admin-side prefs panel link.
     */
    public function prefs_link()
    {
        return '?event=prefs#prefs_group_'.$this->event;
    }

    /**
     * Jump to the prefs panel from the Plugin options link.
     *
     * @return HTML Page sub-content.
     */
    public function options()
    {
        $link = $this->prefs_link();

        header('Location: ' . $link);
    }

    // Prefs handling javascript.
    public function inject_prefs_js($evt, $stp)
    {
        $js = '';

        if ($evt === 'prefs') {
            $js = script_js(<<<EOS
let block = $(".adi_matrix_tinymce");
let grpOn = $("#adi_matrix_tiny_mce-1");
let grpOff = $("#adi_matrix_tiny_mce-0");

if (block.length) {
    if (grpOff.prop("checked")) {
        block.hide();
    } else {
        block.show();
    }

    grpOff.click(function () {
        block.hide();
    });

    grpOn.click(function () {
        block.show();
    });
}
EOS
            , false, true);
        }

        return $js;
    }

    public function get_prefs()
    {
        $tiny_mce_config_val = <<<EOCFG
language : "en",
theme : "advanced",
plugins : "safari,pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
theme_advanced_buttons1 : "pagebreak,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
theme_advanced_buttons3 : "tablecontrols",
theme_advanced_toolbar_location : "top",
theme_advanced_toolbar_align : "left",
theme_advanced_statusbar_location : "bottom",
theme_advanced_resizing : true,
extended_valid_elements: "style[*]",
width: "600",
height: "400",
EOCFG;

        // default preferences
        $plugprefs = array(
            'adi_matrix_article_highlighting' => array(
                'value' => '1', 'html' => 'yesnoradio', 'position' => 20,
            ),
            'adi_matrix_article_tooltips'     => array(
                'value' => '1', 'html' => 'yesnoradio', 'position' => 30,
            ),
            'adi_matrix_display_id'           => array(
                'value' => '0', 'html' => 'yesnoradio', 'position' => 10,
            ),
            'adi_matrix_input_field_tooltips' => array(
                'value' => '0', 'html' => 'yesnoradio', 'position' => 40,
            ),
            'adi_matrix_jquery_ui'            => array(
                'value' => '../scripts/jquery-ui.js', 'html' => 'text_input', 'position' => 110, 'collection' => 'adi_matrix_tinymce',
            ),
            'adi_matrix_jquery_ui_css'        => array(
                'value' => '../scripts/jquery-ui.css', 'html' => 'text_input', 'position' => 120, 'collection' => 'adi_matrix_tinymce',
            ),
            'adi_matrix_tiny_mce'             => array(
                'value' => '0', 'html' => 'yesnoradio', 'position' => 100,
            ),
            'adi_matrix_tiny_mce_type'        => array(
                'value' => 'custom', 'html' => 'text_input', 'position' => 0, 'type' => PREF_HIDDEN,
            ),
            'adi_matrix_tiny_mce_dir'         => array(
                'value' => '../scripts/tiny_mce', 'html' => 'text_input', 'position' => 130, 'collection' => 'adi_matrix_tinymce',
            ),
            'adi_matrix_tiny_mce_config'      => array(
                'value' => $tiny_mce_config_val, 'html' => 'text_area', 'position' => 140, 'collection' => 'adi_matrix_tinymce',
            ),
        );

        return $plugprefs;
    }

    // read or set pref
    public function get_pref($name,$value=NULL,$private=false)
    {
        global $prefs;

        $matrix_prefs = $this->get_prefs();

        if ($value === NULL) {
            // return pref value
            // may be dynamically generated pref
            if (isset($matrix_prefs[$name]['value'])) {
                return get_pref($name,$matrix_prefs[$name]['value']);
            } else {
                return get_pref($name);
            }
        } else {
            // set pref
            if (array_key_exists($name, $matrix_prefs)) {
                $html = $matrix_prefs[$name]['html'];
            } else {
                $html = 'text_input';
            }

            $evt = empty($matrix_prefs[$name]['collection']) ? 'adi_matrix' : array('adi_matrix', $matrix_prefs[$name]['collection']);
            $type = empty($matrix_prefs[$name]['type']) ? PREF_PLUGIN : $matrix_prefs[$name]['type'];
            $res = set_pref($name, $value, $evt, $type, $html, (empty($matrix_prefs[$name]['position']) ? 0 : $matrix_prefs[$name]['position']), $private);
            $prefs[$name] = get_pref($name, $value, true); //??? JUST USE THIS LINE?

            return $res;
        }
    }

    // TinyMCE implementation in a modal window
    public function tiny_mce_custom()
    {
        $jquery_ui = $this->get_pref('adi_matrix_jquery_ui');
        $tiny_mce_dir = $this->get_pref('adi_matrix_tiny_mce_dir');
        $tiny_mce_config = $this->get_pref('adi_matrix_tiny_mce_config');

        $title = gTxt('edit');
        $ok = gTxt('adi_matrix_ok');
        $cancel = gTxt('adi_matrix_cancel');

        $script = script_js($jquery_ui, TEXTPATTERN_SCRIPT_URL);
        $script .= script_js($tiny_mce_dir.'/tiny_mce.js', TEXTPATTERN_SCRIPT_URL);
        $script .= script_js(<<<END_SCRIPT
//<![CDATA[
    $(document).ready(function(){

        var i = 0;
        $('.adi_matrix_matrix .glz_text_area_field textarea').each(function(){
            // hide textareas
            $(this).css({display:"none"});
            // assign unique class
            i++;
            $(this).addClass('mceNoEditor tie' + i);
            // create div containing textarea contents (with same unique class)
            var text = $(this).val();
            $(this).after('<div class="tie_div tie' + i + '">' + text + '<\/div>');

        });

        $('.adi_matrix_matrix div.tie_div').click(function(){

            // get unique class (actually the last class - potentially dodgy?)
            var thisClass = $(this).attr('class').split(' ').slice(-1);
            // get corresponding textarea
            var thisTextarea = $('textarea.' + thisClass);
            // get textarea name attribute (e.g. article_2[custom_6])
            // WILL NEED TO SET THIS INFO IN THE DOM IN ADVANCE - TEXTAREA TITLE ATTR?
            // var thisName = $(thisTextarea).attr('name');

            $('#dialog').remove();
            $('body').append('<div id="dialog" \/>');
            $('#dialog').dialog({
                autoOpen: false,
                bgiframe: true,
                resizable: false,
                width: 700,
                position: ['center',35],
                overlay: { backgroundColor: '#000', opacity: 0.6 },
                open: function(e, ui){

                },
                beforeclose: function(event, ui) {
                    tinyMCE.get('editor').remove();
                    $('#editor').remove();
                }

            });

            $('#dialog').dialog('option', 'title', '$title');
            $('#dialog').dialog('option', 'modal', true);
            $('#dialog').dialog('option', 'buttons', {
                $cancel: function() {
                            $(this).dialog('close');
                },
                $ok: function() {
                        var content = tinyMCE.get('editor').getContent();
                        // feed edited contents into textarea
                        $(thisTextarea).val(content);
                        // feed edited contents into div
                        $('div.' + thisClass).html(content);
                        $(this).dialog('close');
                }
            });

            $('#dialog').html('<textarea name="editor" id="editor"><\/textarea>');
            $('#dialog').dialog('open');
            tinyMCE.init({
                mode : "specific_textareas",
                editor_deselector : "mceNoEditor",
                // start of user configurable options
                $tiny_mce_config
                // end of user-configurable options
                setup : function(ed) {
                    ed.onInit.add(function(ed) {
                        tinyMCE.get('editor').setContent($(thisTextarea).val());
                        tinyMCE.execCommand('mceRepaint');
                    });
                }

            });
            return false;
        });
    });
//]]>
END_SCRIPT
        );

        echo $script;
    }

    // jQuery UI CSS for TinyMCE modal window
    public function tiny_mce_style()
    {
        $jquery_ui_css = $this->get_pref('adi_matrix_jquery_ui_css');
        echo '<link href="'.$jquery_ui_css.'" type="text/css" rel="stylesheet" />';
    }

    // adi_matrix admin tab
    public function matrix_admin($event, $step)
    {
        global $adi_matrix_cfs,$prefs,$txp_permissions, $txp_user;

        $message = '';
        $installed = $this->installed();
        $matrix_prefs = $this->get_prefs();

        if ($installed) {
            $upgrade_required = $this->upgrade();

            if ($upgrade_required) {
                $message = array(gTxt('adi_matrix_upgrade_required'),E_WARNING);
            } else {
                // custom field musical chairs
                $cfs_fiddled = false;

                // add additional custom fields that may have suddenly appeared (glz_cfs: custom_11+)
                foreach ($adi_matrix_cfs as $index => $value) {
                    $rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'custom_$index'",$this->debug); // find out if column exists
                    $a = nextRow($rs);

                    if (empty($a)) {
                        safe_query("ALTER TABLE ".safe_pfx('adi_matrix')." ADD `custom_$index` TINYINT(1) DEFAULT 0 NOT NULL",$this->debug);
                        $cfs_fiddled = true;
                    }
                }

                // remove custom fields that may have been deleted in glz_cfs
                $rs = safe_query('SHOW FIELDS FROM '.safe_pfx('adi_matrix')." LIKE 'custom%'",$this->debug);
                $current_cfs = array();

                while ($a = nextRow($rs)) { // get list of cf indexes from adi_matrix
                    $index = substr($a['Field'],7); // strip 'custom_' from 'custom_x'
                    $current_cfs[$index] = true;
                }

                foreach ($current_cfs as $index => $value) {
                    if (!array_key_exists($index,$adi_matrix_cfs)) {
                        safe_query("ALTER TABLE ".safe_pfx('adi_matrix')." DROP COLUMN `custom_$index`",$this->debug);
                        $cfs_fiddled = true;
                    }

                }

                if ($cfs_fiddled) {
                    $message = gTxt('adi_matrix_cfs_modified');
                }
            }
        } else {
            $message = array(gTxt('adi_matrix_not_installed'),E_ERROR);
        }

        // admin $step aerobics
        if ($step == 'update') {
            $result = $this->update_settings();
            $result ? $message = gTxt('adi_matrix_updated') : $message = array(gTxt('adi_matrix_update_fail'),E_ERROR);
        } elseif ($step == 'delete') {
            $matrix_index = gps('matrix');
            $result = $this->delete($matrix_index);
            $result ? $message = gTxt('adi_matrix_deleted') : $message = array(gTxt('adi_matrix_delete_fail'),E_ERROR);
        }

        // generate page
        pagetop(gTxt('adi_matrix_admin'),$message);

        $installed = $this->installed();

        if ($installed && !$upgrade_required) {
            $adi_matrix_list = $this->read_settings(false);

            // tack 'new' index onto end of $matrix_list (field defaults for adding new matrix)
            $adi_matrix_list['new'] = array(
                'name' => '',
                'ordinal' => '',
                'sort' => 'posted',
                'dir' => 'desc',
                'sort_type' => 'alphabetical',
                'user' => $txp_user,
                'privs' => '',
                'scroll' => '0',
                'footer' => '0',
                'title' => '0',
                'publish' => '0',
                'show_section' => '0',
                'cf_links' => '',
                'tab' => '0',
                'criteria_section' => '',
                'criteria_category' => '',
                'criteria_descendent_cats' => '0',
                'criteria_status' => '0',
                'criteria_author' => '',
                'criteria_keywords' => '',
                'criteria_timestamp' => '',
                'criteria_expiry' => '',
                'criteria_condition' => '',
                'status' => '0',
                'keywords' => '0',
                'article_image' => '0',
                'category1' => '0',
                'category2' => '0',
                'posted' => '0',
                'expires' => '0',
                'section' => '0'
            );

            foreach ($adi_matrix_cfs as $index => $value) {
                // add custom fields to $matrix_list['new']
                $adi_matrix_list['new']['custom_'.$index] = '0';
            }

            // @todo pref to control if it selects last edited, or always defaults to new?

            $matrix_defined = array_combine(array_keys($adi_matrix_list), array_column($adi_matrix_list, 'name'));
            $matrix_select = selectInput('matrix_id', $matrix_defined, 'new', false, false, 'matrix_id');

            // output table & input form
            echo $matrix_select . n .
                    fInput('submit', array(
                        'name' => 'do_something',
                        'class' => 'navlink',
                        'form' => 'adi_matrix_admin_form',
                        ),
                        gTxt('save'))
                .form(
                    $this->admin_table($adi_matrix_list,$adi_matrix_cfs)
                    .eInput('adi_matrix_admin')
                    .sInput('update')
                    ,''
                    ,''
                    ,'post'
                    ,'adi_matrix_admin'
                    ,''
                    ,'adi_matrix_admin_form'
                );
        }

        if ($this->debug) {
            echo "<b>Event:</b> ".$event.", <b>Step:</b> ".$step.br.br;
            echo '<b>$_POST:</b>';
            dmp($_POST);

            if ($installed) {
                $sort_options = $this->get_sort_options();

                echo '<b>PREFS:</b>'.br;
                foreach ($matrix_prefs as $name => $this_pref)
                    echo $name.' = '.$this->get_pref($name).br;
                echo br;
                echo '<b>$adi_matrix_list:</b>';
                dmp($this->read_settings());
                echo '<b>$adi_matrix_sort_options:</b>';
                dmp($sort_options);
                echo '<b>$adi_matrix_cfs:</b>';
                dmp($adi_matrix_cfs);
                echo '<b>glz_custom_fields plugin:</b> is';

                if (!$this->has_glz_cf) {
                    echo ' NOT';
                }
                echo ' installed'.br;
            }
        }

    }

    // article status code translation
    public function get_statuses()
    {
        $opts = array(
            1 => gTxt('draft'),
            2 => gTxt('hidden'),
            3 => gTxt('pending'),
            4 => gTxt('live'),
            5 => gTxt('sticky'),
        );

        return $opts;
    }

    // article sort options
    public function get_sort_options()
    {
        global $adi_matrix_cfs;

        $opts = array(
            'posted' => gTxt('posted'),
            'title' => gTxt('title'),
            'id' => gTxt('id'),
            'lastmod' => gTxt('article_modified'),
            'expires' => gTxt('expires'),
            'status' => gTxt('status'),
            'keywords' => gTxt('keywords'),
            'article_image' => gTxt('article_image'),
            'category1' => gTxt('category1'),
            'category2' => gTxt('category2'),
            'section' => gTxt('section'),
        );

        // add custom fields to sort options
        foreach ($adi_matrix_cfs as $index => $value) {
            $opts['custom_'.$index] = $adi_matrix_cfs[$index];
        }

        return $opts;
    }

    // article sort types
    public function get_sort_types()
    {
        $types = array(
            'alphabetical' => gTxt('adi_matrix_alphabetical'),
            'numerical' => gTxt('adi_matrix_numerical'),
        );

        return $types;
    }

    // article sort directions
    public function get_sort_dirs()
    {
        $dirs = array(
            'desc' => gTxt('descending'),
            'asc' => gTxt('ascending'),
        );

        return $dirs;
    }
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---

h1. *adi_matrix* - Multi-article update tabs

This plugin provides a way of viewing and updating multiple articles from a single TXP admin tab.

Matrixes give you a summary view of multiple articles, where you can make changes to selected data & update them all in one go.

Two new tabs do the work:

* adi_matrix admin tab under Extensions
* article matrix tab(s) under Contents or Home

The admin tab defines the matrixes and the article matrix tabs display the required articles with their data.

h2. *Installation*

Installation of *adi_matrix* adds a new table to your Textpattern database which should not interfere with anything else.

*adi_matrix* is designed to make changes to groups of article.  I suggest that you make backups before installation of this plugin and during initial testing on your site.  I can thoroughly recommend "rss_admin_db_manager":http://forum.textpattern.com/viewtopic.php?id=10395 to do database backups before installation.

h2. *adi_matrix admin tab*

This is where you set up the article matrixes.  There are three aspects to this:

h3. Matrix appearance

Here you can:

* give the matrix a name (which will be used to list it under the Contents tab)
* specify the order in which articles should be listed
* specify whether a single user or all users are to get access to the matrix
* specify whether access to the matrix is based on privileges or not
* specify which tab to display the matrix under
* permit users to add/delete articles
* define how the matrix is laid out

h3. Article selection criteria

By default all articles will be listed in the matrix, but you can narrow it down according to:

* section
* category
* article status
* author
* keywords
* posted & expires timestamps
* custom WHERE clause condition

h3. Article data display

This is where you define what data the user can see and change. Article that can be viewed & updated in matrixes:

* article status
* custom fields
* article image
* keywords
* categories
* posted & expires timestamps
* title
* section

h3. Preferences

* maximum number of articles to be listed
* display article ID
* article title highlighting (indicate future or expired articles)
* article title tooltips (show ID, posted & expires timestamps in tooltip)
* input field tooltips (show contents of input field in a tooltip)

h2. *Getting started*

A new matrix can be added in *adi_matrix* admin tab simply by entering its details into the blank spaces.  As a minimum, a matrix name needs to be provided.

Once a matrix has been defined, it's settings can be changed at any time.  The new matrix will seen under the Contents or Home tabs after you have visited at least one other TXP tab (a hop is required so that the the tab contents are refreshed).

h2. *Article matrixes*

The matrix tabs show a number of articles, with their associated "data". If you are the article author or have sufficient overriding privileges then you can make changes to the data & update all articles with a single click.

Note that only articles where you have actually changed anything will be updated - together with their __Last Modified Date__ and __Author__.

*adi_matrix* respects all the standard restrictions on who can make changes to articles - based on authorship & privilege level.

h2. *glz_custom_fields*

*adi_matrix* will automatically detect if *glz_custom_fields* is installed and should play nicely.

h2. *TinyMCE*

If *glz_custom_fields* is installed you have the opportunity to use "TinyMCE":http://www.tinymce.com/ to edit textarea custom fields.  Note that TinyMCE must be installed separately.  To use it with *adi_matrix*, switch it on in the admin tab and fill in the configuration details.

h2. *Uninstalling adi_matrix*

To uninstall *adi_matrix*, simply go to the Plugins tab and delete it.  No articles will be harmed in the process.

h2(adi_extras). *Additional information*

p(adi_extras). Support and further information can be obtained from the "Textpattern support forum":http://forum.textpattern.com/viewtopic.php?id=35972.

# --- END PLUGIN HELP ---
-->
<?php
}
?>