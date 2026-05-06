<?php
namespace App\Controllers\Examples;

use App\Controllers\BaseController;
use DataGrid\DataGrid;

/**
 * PHP DataGrid :: Sample #2-7 (code) - Advanced PHP DG code with AJAX features
 *
 * Auto-generated CI4 controller wrapper around examples/code_2_7_example.php.
 * Drop into app/Controllers/Examples/ and route to it.
 */
class Code27Example extends BaseController
{
    public function index()
    {
        // Ensure session is started (DataGrid uses _SESSION for export, paging, etc.)
        service('session');
        ?>
<?php

 ################################################################################
 ## +---------------------------------------------------------------------------+
 ## | 1. Creating & Calling:                                                    |
 ## +---------------------------------------------------------------------------+
 ##  *** define a relative (virtual) path to datagrid.class.php file
 ##  *** (relatively to the current file)
 ##  *** RELATIVE PATH ONLY ***

    $unique_prefix = "prs_";    /* prevent overlays - must be started with a letter */
    $postback   = (isset($_REQUEST['postback']) && $_REQUEST['postback'] != "") ? strip_tags($_REQUEST['postback']) : "ajax";
    $mode       = isset($_REQUEST[$unique_prefix.'mode']) ? $_REQUEST[$unique_prefix.'mode'] : "";
    $region_id  = isset($_REQUEST["riyregion_id"]) ? $_REQUEST["riyregion_id"] : "";

    $db = \Config\Database::connect();

    ob_start();

 ##  *** set needed options and create a new class instance
  $debug_mode = false;        /* display SQL statements while processing */
  $messaging = true;          /* display system messages on a screen */
  $dgrid = new DataGrid($debug_mode, $messaging, $unique_prefix);
 ##  *** set encoding and collation (default: utf8/utf8_unicode_ci)
 /// $dg_encoding = "utf8";
 /// $dg_collation = "utf8_unicode_ci";
 /// $dgrid->SetEncoding($dg_encoding, $dg_collation);
 ##  *** set data source with needed options
 ##  *** put a primary key on the first place
    $sql=" SELECT
            demo_presidents.id,
            demo_presidents.name,
            demo_presidents.birth_date,
            demo_presidents.status,
            demo_presidents.favorite_color,
            demo_presidents.is_active,
            demo_presidents.region_id,
            demo_presidents.country_id,
            demo_countries.name as country_name
        FROM demo_presidents
            LEFT OUTER JOIN demo_countries ON demo_presidents.country_id=demo_countries.id ";
  $default_order = array("name"=>"ASC");
  $dgrid->DataSource($db, $sql, $default_order);

 ## +---------------------------------------------------------------------------+
 ## | 2. General Settings:                                                      |
 ## +---------------------------------------------------------------------------+
 ## +-- PostBack Submission Method ---------------------------------------------+
 ##  *** defines postback submission method for DataGrid: AJAX, POST or GET(default)
  $postback_method = "ajax";
  $dgrid->SetPostBackMethod($postback_method);
 ##  *** set interface language (default - English)
 /// $dg_language = "en";
 /// $dgrid->SetInterfaceLang($dg_language);
 ##  *** set direction: "ltr" or "rtr" (default - "ltr")
 /// $direction = "ltr";
 /// $dgrid->SetDirection($direction);
 ##  *** set layouts: "0" - tabular(horizontal) - default, "1" - columnar(vertical), "2" - customized
  $layouts = array("view"=>"0", "edit"=>"1", "details"=>"1", "filter"=>"2");
  $dgrid->SetLayouts($layouts);
 /// $details_template = "<table><tr><td>{field_name_1}</td><td>{field_name_2}</td></tr>...</table>";
 /// $dgrid->SetTemplates("","",$details_template);
 ##  *** set modes for operations ("type" => "link|button|image")
 ##  *** "view" - view mode | "edit" - add/edit/details modes
 ##  *** "byFieldValue"=>"fieldName" - make the field to be a link to edit mode page
 /// $modes = array(
 ///     "add"	  =>array("view"=>true, "edit"=>false, "type"=>"link", "show_add_button"=>"inside|outside"),
 ///     "edit"	  =>array("view"=>true, "edit"=>true,  "type"=>"link", "byFieldValue"=>""),
 ///     "details" =>array("view"=>true, "edit"=>false, "type"=>"link"),
 ///     "delete"  =>array("view"=>true, "edit"=>true,  "type"=>"image")
 /// );
 /// $dgrid->SetModes($modes);
 ##  *** allow scrolling on datagrid
 /// $scrolling_option = false;
 /// $dgrid->AllowScrollingSettings($scrolling_option);
 ##  *** set scrolling settings (optional)
 /// $scrolling_height = "200px";
 /// $dgrid->SetScrollingSettings($scrolling_height);
 ##  *** allow multirow operations
   $multirow_option = false;
   $dgrid->AllowMultirowOperations($multirow_option);
   $multirow_operations = array(
       "edit"  => array("view"=>true),
       "delete"  => array("view"=>true),
       "details" => array("view"=>true),
   );
   $dgrid->SetMultirowOperations($multirow_operations);
 ##  *** set CSS class for datagrid
 ##  *** "default" or "blue" or "gray" or "green" or "pink" or your own css file
   $css_class = "x-blue";
   $dgrid->SetCssClass($css_class);
 ##  *** set variables that used to get access to the page (like: my_page.php?act=34&id=56 etc.)
 // $http_get_vars = array("id");
 // $dgrid->SetHttpGetVars($http_get_vars);
 ##  *** set other datagrid/s unique prefixes (if you use few datagrids on one page)
 ##  *** format (in which mode to allow processing of another datagrids)
 ##  *** array("unique_prefix"=>array("view"=>true|false, "edit"=>true|false, "details"=>true|false));
 /// $otherDatagrids = array("abcd_"=>array("view"=>true, "edit"=>true, "details"=>true));
 /// $dgrid->SetOtherDatagrids($otherDatagrids);
 ##  *** set DataGrid caption
  $dg_caption = "Presidents (AJAX Enabled) - <a href='index.php'>Back to Index</a>";
  $dgrid->SetCaption($dg_caption);

 ## +---------------------------------------------------------------------------+
 ## | 3. Printing & Exporting Settings:                                         |
 ## +---------------------------------------------------------------------------+
 ##  *** set printing option: true(default) or false
  $printing_option = false;
  $dgrid->AllowPrinting($printing_option);
 ## +-- Exporting --------------------------------------------------------------+
 ##  *** initialize the session with session_start();
 ##  *** default exporting directory: tmp/export/
 /// $exporting_option = true;
 /// $export_all = false;
 /// $dgrid->AllowExporting($exporting_option, $export_all);
 /// $exporting_types = array('csv'=>'true', 'xls'=>'true', 'pdf'=>'true', 'xml'=>'true');
 /// $dgrid->AllowExportingTypes($exporting_types);

 ## +---------------------------------------------------------------------------+
 ## | 4. Sorting & Paging Settings:                                             |
 ## +---------------------------------------------------------------------------+
 ##  *** set sorting option: true(default) or false
 /// $sorting_option = true;
 /// $dgrid->AllowSorting($sorting_option);
 ##  *** set paging option: true(default) or false
  $paging_option = true;
  $rows_numeration = false;
  $numeration_sign = "N #";
  $dropdown_paging = true;
  $dgrid->AllowPaging($paging_option, $rows_numeration, $numeration_sign, $dropdown_paging);
 ##  *** set paging settings
  $bottom_paging = array("results"=>true, "results_align"=>"left", "pages"=>true, "pages_align"=>"center", "page_size"=>true, "page_size_align"=>"right");
  $top_paging = array();
  $pages_array = array("5"=>"5", "10"=>"10", "25"=>"25", "50"=>"50", "100"=>"100", "250"=>"250", "500"=>"500", "1000"=>"1000");
  $default_page_size = 10;
  $paging_arrows = array("first"=>"|&lt;&lt;", "previous"=>"&lt;&lt;", "next"=>"&gt;&gt;", "last"=>"&gt;&gt;|");
  $dgrid->SetPagingSettings($bottom_paging, $top_paging, $pages_array, $default_page_size, $paging_arrows);

 ## +---------------------------------------------------------------------------+
 ## | 5. Filter Settings:                                                       |
 ## +---------------------------------------------------------------------------+
 ##  *** set filtering option: true or false(default)
  $filtering_option = true;
  $show_search_type = true;
  $dgrid->AllowFiltering($filtering_option, $show_search_type);
 ##  *** set additional filtering settings
 /// $fill_from_array = array("0"=>"No", "1"=>"Yes");  /* as "value"=>"option" */
  $filtering_fields = array(
    "Name"       =>array("type"=>"textbox",  "table"=>"demo_presidents", "field"=>"name", "show_operator"=>"false", "default_operator"=>"like%", "case_sensitive"=>"false", "comparison_type"=>"string", "width"=>"130px", "on_js_event"=>""),
    "Birth Date" =>array("type"=>"calendar", "table"=>"demo_presidents", "field"=>"birth_date", "calendar_type"=>"floating", "date_format"=>"date", "show_operator"=>"false", "default_operator"=>"like%", "case_sensitive"=>"false", "comparison_type"=>"string", "width"=>"120px", "on_js_event"=>""),
    "Status"     =>array("type"=>"enum",     "table"=>"demo_presidents", "field"=>"status", "field_view"=>"", "filter_condition"=>"", "order"=>"ASC", "source"=>"self", "condition"=>"", "show_operator"=>"false", "default_operator"=>"=", "case_sensitive"=>"false", "comparison_type"=>"string", "width"=>"", "multiple"=>"false", "multiple_size"=>"4", "on_js_event"=>"", "view_type"=>"radiobutton"),
  );
  $dgrid->SetFieldsFiltering($filtering_fields);

 ## +---------------------------------------------------------------------------+
 ## | 6. View Mode Settings:                                                    |
 ## +---------------------------------------------------------------------------+
 ##  *** set view mode table properties
  $vm_table_properties = array("width"=>"75%");
  $dgrid->SetViewModeTableProperties($vm_table_properties);
 ##  *** set columns in view mode
 ##  *** Ex.: "on_js_event"=>"onclick='alert(\"Yes!!!\");'"
 ##  ***      "barchart" : number format in SELECT SQL must be equal with number format in max_value
  /// $fill_from_array = array("0"=>"Banned", "1"=>"Active", "2"=>"Closed", "3"=>"Removed"); /* as "value"=>"option" */
  $vm_columns = array(
    "name"       =>array("header"=>"Name",        "type"=>"label", "align"=>"left", "wrap"=>"wrap",   "text_length"=>"20", "case"=>"normal"),
    "birth_date" =>array("header"=>"Birth Date",  "type"=>"label", "align"=>"center", "header_tooltip"=>"Shows the birth date", "wrap"=>"nowrap", "text_length"=>"-1", "case"=>"normal"),
    "status"     =>array("header"=>"Status",      "type"=>"label", "align"=>"center", "header_tooltip"=>"Shows the current status", "wrap"=>"nowrap", "text_length"=>"30", "case"=>"normal"),
    "favorite_color" =>array("header"=>"Favorite Color",       "type"=>"color",     "align"=>"center", "width"=>"", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal|upper|lower|camel", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "view_type"=>"image"),
    "is_active"  =>array("header"=>"Active",      "type"=>"checkbox",  "align"=>"center", "width"=>"", "wrap"=>"nowrap", "sort_type"=>"numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "true_value"=>1, "false_value"=>0),
    "country_name" =>array("header"=>"Country",   "type"=>"label", "align"=>"center",  "wrap"=>"nowrap", "text_length"=>"30", "case"=>"normal"),
  );
  $dgrid->SetColumnsInViewMode($vm_columns);
 ##  *** set auto-generated columns in view mode
 //  $auto_column_in_view_mode = false;
 //  $dgrid->SetAutoColumnsInViewMode($auto_column_in_view_mode);

 ## +---------------------------------------------------------------------------+
 ## | 7. Add/Edit/Details Mode Settings:                                        |
 ## +---------------------------------------------------------------------------+
 ##  *** set add/edit mode table properties
    $em_table_properties = array("width"=>"75%");
    $dgrid->SetEditModeTableProperties($em_table_properties);
 ##  *** set details mode table properties
    $dm_table_properties = array("width"=>"60%");
    $dgrid->SetDetailsModeTableProperties($dm_table_properties);
 ##  ***  set settings for add/edit/details modes
    $table_name  = "demo_presidents";
    $primary_key = "id";
    $condition   = "";
    $dgrid->SetTableEdit($table_name, $primary_key, $condition);
 ##  *** set columns in edit mode
 /// $fill_from_array = array("0"=>"No", "1"=>"Yes", "2"=>"Don't know", "3"=>"My be"); /* as "value"=>"option" */
    $em_columns = array(
        "name"        =>array("header"=>"Name",       "type"=>"textbox",  "width"=>"140px", "req_type"=>"rt", "title"=>"Name"),
        "birth_date"  =>array("header"=>"Birth Date", "type"=>"date",     "req_type"=>"rt", "width"=>"80px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "calendar_type"=>"floating"),
        "work_phone"  =>array("header"=>"Work Phone", "type"=>"textbox",  "width"=>"140px", "req_type"=>"rx", "template"=>"(ddd)-ddd-dd-dd", "title"=>"Work Phone"),
        "status"      =>array("header"=>"Status",     "type"=>"enum",     "req_type"=>"st", "width"=>"210px", "title"=>"Status", "readonly"=>false, "maxlength"=>"-1", "default"=>"", "unique"=>false, "unique_condition"=>"", "on_js_event"=>"", "source"=>"self", "view_type"=>"dropdownlist"),
        "region_id"   =>array("header"=>"Region",     "type"=>"foreign_key","req_type"=>"ri", "width"=>"210px", "title"=>"", "readonly"=>"false", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true"),
        "country_id"  =>array("header"=>"Country",    "type"=>"foreign_key","req_type"=>"ri", "width"=>"210px", "title"=>"", "readonly"=>"false", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true"),
        "favorite_color" =>array("header"=>"Favorite Color",      "type"=>"color",    "req_type"=>"rt", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "view_type"=>"dropdownlist", "save_format"=>"hexcodes"),
        "is_active"   =>array("header"=>"Active",     "type"=>"checkbox", "req_type"=>"si", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"0", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "true_value"=>1, "false_value"=>0),

        "email"              =>array("header"=>"Email", "type"=>"textbox", "req_type"=>"re", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>false, "unique_condition"=>"", "visible"=>"true", "on_js_event"=>""),
        "validator_email"    =>array("header"=>"Re-enter Email", "type"=>"validator", "req_type"=>"sv", "width"=>"210px", "title"=>"Confirm Email", "readonly"=>"false", "maxlength"=>"50", "default"=>"", "visible"=>"true", "on_js_event"=>"", "for_field"=>"email", "validation_type"=>"email"),
        "password"           =>array("header"=>"Password", "type"=>"textbox", "req_type"=>"rp", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"25", "default"=>"", "unique"=>false, "unique_condition"=>"", "visible"=>"true", "on_js_event"=>""),
        "validator_password" =>array("header"=>"Please re-enter your password", "type"=>"validator", "req_type"=>"sv", "width"=>"210px", "title"=>"Confirm Password", "readonly"=>"false", "maxlength"=>"50", "default"=>"", "visible"=>"true", "on_js_event"=>"", "for_field"=>"password", "validation_type"=>"password"),
    );
    $dgrid->SetColumnsInEditMode($em_columns);
 ##  *** set auto-generated columns in edit mode
 //  $auto_column_in_edit_mode = false;
 //  $dgrid->SetAutoColumnsInEditMode($auto_column_in_edit_mode);
 ##  *** set foreign keys for add/edit/details modes (if there are linked tables)
   $foreign_keys = array(
    "region_id" =>array("table"=>"demo_regions", "field_key"=>"id", "field_name"=>"name", "view_type"=>"dropdownlist", "elements_alignment"=>"horizontal|vertical", "condition"=>"", "order_by_field"=>"", "order_type"=>"ASC", "on_js_event"=>"onchange='_dgFormAction(\"\", \"\", \"".$unique_prefix."\", \"".$dgrid->HTTP_URL."\", \"".$_SERVER['QUERY_STRING']."\", \"".$postback."\", \"".$mode."\")'", "show_count"=>"true"),
    "country_id"=>array("table"=>"demo_countries", "field_key"=>"id", "field_name"=>"name", "view_type"=>"dropdownlist", "elements_alignment"=>"horizontal|vertical", "condition"=>(($region_id != "") ? "region_id=".$region_id : ""), "order_by_field"=>"name", "order_type"=>"ASC", "on_js_event"=>"", "show_count"=>"true"),
   );
   $dgrid->SetForeignKeysEdit($foreign_keys);

?>
<!doctype html>
<html>
  <head>
	<meta charset="utf-8">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta name='keywords' content='php grid, php datagrid, php data grid, datagrid sample, datagrid php, datagrid, grid php, datagrid in php, data grid in php, free php grid, free php datagrid, datagrid paging' />
    <meta name='description' content='PHP DataGrid - AJAX features' />
    <meta name="author" content=" - PHP DataGrid">
    <meta name="generator" content="PHP DataGrid">
    <meta name="ROBOTS" content="All" />
    <meta name="revisit-after" content="7 days" />
    <title>PHP DataGrid :: Sample #2-7 (code) - Advanced PHP DG code with AJAX features</title>
    <?php
        ## call of this method between HTML <HEAD> elements
        $dgrid->WriteCssClass();
    ?>
</head>
<body style="padding:10px">
<?php
################################################################################
## +---------------------------------------------------------------------------+
## | 8. Bind the DataGrid:                                                     |
## +---------------------------------------------------------------------------+
##  *** bind the DataGrid and draw it on the screen
  $dgrid->Bind();
  ob_end_flush();

################################################################################

function my_format_date($last_login_time){
    $last_login = mktime(substr($last_login_time, 11, 2), substr($last_login_time, 14, 2),
                        substr($last_login_time, 17, 2), substr($last_login_time, 5, 2),
                        substr($last_login_time, 8, 2), substr($last_login_time, 0, 4));
    if($last_login_time != ""){
        return date("M d, Y g:i A", $last_login);
        //return substr(0, 4);
    }else return "";
}
?>
<?php
    }
}