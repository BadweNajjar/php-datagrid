<?php
namespace App\Controllers\Examples;

use App\Controllers\BaseController;
use DataGrid\DataGrid;

/**
 * PHP DataGrid :: Sample #2-9 (code) - All In One (all types of fields)
 *
 * Auto-generated CI4 controller wrapper around examples/code_2_9_example.php.
 * Drop into app/Controllers/Examples/ and route to it.
 */
class Code29Example extends BaseController
{
    public function index()
    {
        // Ensure session is started (DataGrid uses _SESSION for export, paging, etc.)
        service('session');
        ?>
<?php

    ## wee need this if we want to prevent FF sending double request
    header("content-type: text/html; charset=utf-8");

    ## uncomment, if your want to prevent "Web Page exired" message when use $submission_method = "post";
    ## (don't uncomment, if your export feature is active)
    // session_cache_limiter ('private, must-revalidate');
    ## uncomment, if your export feature (or movable rows) is active
    // session_start();

    ## +---------------------------------------------------------------------------+
    ## | 1. Creating & Calling:                                                    |
    ## +---------------------------------------------------------------------------+
    ##  *** define a relative (virtual) path to datagrid.class.php file
    ##  *** (relatively to the current file)
    ##  *** RELATIVE PATH ONLY ***
    $db = \Config\Database::connect();

    //  ob_start();
    ##  *** set needed options and create a new class instance
      $debug_mode = false;        /* display SQL statements while processing */
      $messaging = true;          /* display system messages on a screen */
      $unique_prefix = "aio_";    /* prevent overlays - must be started with a letter */
      $mode = isset($_REQUEST[$unique_prefix.'mode']) ? strip_tags($_REQUEST[$unique_prefix.'mode']) : '';
      $rid = isset($_REQUEST[$unique_prefix.'rid']) ? (int)$_REQUEST[$unique_prefix.'rid'] : '';

      $dgrid = new DataGrid($debug_mode, $messaging, $unique_prefix);
    ##  *** set encoding and collation (default: utf8/utf8_unicode_ci)
    /// $dg_encoding = "utf8";
    /// $dg_collation = "utf8_unicode_ci";
    /// $dgrid->SetEncoding($dg_encoding, $dg_collation);
    ##  *** set data source with required settings
    ##  *** 1. write all fields separated by commas(,) like: field1, field2 etc.. DON'T USE table.*
    ##  *** 2. write the primary key in the first place (MUST BE AUTO-INCREMENT NUMERIC!)
      $sql = "SELECT
                oio.id,
                oio.field_textbox,
                oio.field_textarea,
                oio.field_date,
                oio.field_money,
                oio.field_enum,
                oio.field_checkbox,
                oio.field_color,
                oio.field_percent,
                dc.name as field_foreign_key_name
              FROM demo_all_in_one oio
                LEFT OUTER JOIN demo_countries dc ON oio.field_foreign_key = dc.id";
      $default_order = array("id"=>"ASC");   /* Ex.: array("field_1"=>"ASC", "field_2"=>"DESC") */
      $dgrid->DataSource($db, $sql, $default_order);

    ## +---------------------------------------------------------------------------+
    ## | 2. General Settings:                                                      |
    ## +---------------------------------------------------------------------------+
    ## +-- PostBack Submission Method ---------------------------------------------+
    ##  *** defines postback submission method for DataGrid: AJAX, POST(default) or GET
      $postback_method = "post";
      $dgrid->SetPostBackMethod($postback_method);

    ## +-- Cache Settings ---------------------------------------------------------+
    ## *** make sure your cache/ dir has 755 (write) permissions
    ## *** define caching parameters: 1st - allow caching or not, 2nd - caching lifetime in minutes
    /// $dgrid->SetCachingParameters(true, 5);
    ## *** delete all caching pages (only if needed)
    /// $dgrid->DeleteCache();

    ## +-- Languages --------------------------------------------------------------+
    ##  *** set interface language (default - English)
      $dg_language = "en";
      $dgrid->SetInterfaceLang($dg_language);
    ##  *** set direction: "ltr" or "rtr" (default - "ltr")
    /// $direction = "ltr";
    /// $dgrid->SetDirection($direction);

    ## +-- Layouts, Templates & CSS -----------------------------------------------+
    ##  *** datagrid layouts: "0" - tabular(horizontal) - default, "1" - columnar(vertical), "2" - customized
    ##  *** use "view"=>"0" and "edit"=>"0" only if you work on the same tables
    ##  *** filter layouts: "0" - tabular(horizontal) - default, "1" - columnar(vertical), "2" - advanced(inline)
     $layouts = array("view"=>"0", "edit"=>"1", "details"=>"1", "filter"=>"2");
     $dgrid->SetLayouts($layouts);
    /// *** $mode_template = array("header"=>"", "body"=>"", "footer"=>"");
    /// @field_name_1@ - field header
    /// {field_name_1} - field value
    /// [ADD][CREATE][EDIT][DELETE][BACK][CANCEL][UPDATE][MULTIROW_CHECKBOX][ROWS_NUMERATION] - allowed elements and operations (must be placed in $template['body'] only)
    /// $view_template = "";
    /// $add_edit_template = "";
    /// $details_template = array("header"=>"", "body"=>"", "footer"=>"");
    /// $details_template['header'] = "";
    /// $details_template['body'] = "<table><tr><td>{field_name_1}</td><td>{field_name_2}</td></tr><tr><td>[BACK]</td></tr></table>";
    /// $details_template['footer'] = "";
    /// $dgrid->SetTemplates($view_template, $add_edit_template, $details_template);
    ##  *** set modes operations ("type" => "link|button|image")
    ##  *** "view" - view mode, "edit" - add/edit/details modes,
    ##  *** "byFieldValue"=>"fieldName" - make the field to be a link to edit mode page
    /// $modes = array(
    ///     "add"	  =>array("view"=>true, "edit"=>false, "type"=>"link",  "show_button"=>true, "show_add_button"=>"inside|outside"),
    ///     "edit"	  =>array("view"=>true, "edit"=>true,  "type"=>"link",  "show_button"=>true, "byFieldValue"=>""),
    ///     "details" =>array("view"=>true, "edit"=>false, "type"=>"link",  "show_button"=>true),
    ///     "delete"  =>array("view"=>true, "edit"=>true,  "type"=>"image", "show_button"=>true)
    /// );
    /// $dgrid->SetModes($modes);
    ##  *** set CSS class for datagrid
    ##  *** "default|blue|gray|green|pink|empty|x-blue|x-gray|x-green" or your own css style
      $css_class = "x-blue";
      $dgrid->SetCssClass($css_class);
    ##  *** set DataGrid caption
      $dg_caption = "All In One Example - <a href=index.php>Back to Index</a>";
      $dgrid->SetCaption($dg_caption);
    ##
    ## +-- Scrolling --------------------------------------------------------------+
    ##  *** allow scrolling on datagrid
    /// $scrolling_option = false;
    /// $dgrid->AllowScrollingSettings($scrolling_option);
    ##  *** set scrolling settings (optional)
    /// $scrolling_height = "100px"; /* ex.: "190px" or "190" */
    /// $dgrid->SetScrollingSettings($scrolling_height);
    ##
    ## +-- Multirow Operations ----------------------------------------------------+
    ##  *** allow multirow operations
     $multirow_option = true;
     $dgrid->AllowMultirowOperations($multirow_option);
     $multirow_operations = array(
         "edit"    => array("view"=>true),
         "details" => array("view"=>true),
         "clone"   => array("view"=>false),
         "delete"  => array("view"=>true),
    ///     "my_operation_name" => array("view"=>true, "flag_name"=>"my_flag_name", "flag_value"=>"my_flag_value", "tooltip"=>"Do something with selected", "image"=>"image.gif")
     );
     $dgrid->SetMultirowOperations($multirow_operations);
    ##
    ## +-- Passing parameters & setting up other DataGrids ------------------------+
    ##  *** set variables that used to get access to the page (like: my_page.php?act=34&id=56 etc.)
    /// $http_get_vars = array("act", "id");
    /// $dgrid->SetHttpGetVars($http_get_vars);
    ##  *** set other datagrid/s unique prefixes (if you use few datagrids on one page)
    ##  *** format (in which mode to allow processing of another datagrids)
    ##  *** array("unique_prefix"=>array("view"=>true|false, "edit"=>true|false, "details"=>true|false));
    /// $otherDatagrids = array("abcd_"=>array("view"=>true, "edit"=>true, "details"=>true));
    /// $dgrid->SetOtherDatagrids($otherDatagrids);

    ## +---------------------------------------------------------------------------+
    ## | 3. Printing & Exporting Settings:                                         |
    ## +---------------------------------------------------------------------------+
    ##  *** set printing option: true(default) or false
    /// $printing_option = true;
    /// $dgrid->AllowPrinting($printing_option);
    ##
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
     $dropdown_paging = false;
     $dgrid->AllowPaging($paging_option, $rows_numeration, $numeration_sign, $dropdown_paging);
    ##  *** set paging settings
     $bottom_paging = array("results"=>true, "results_align"=>"left", "pages"=>true, "pages_align"=>"center", "page_size"=>true, "page_size_align"=>"right");
     $top_paging = array();
     $pages_array = array("10"=>"10", "12"=>"12", "25"=>"25", "50"=>"50", "100"=>"100", "250"=>"250", "500"=>"500", "1000"=>"1000");
     $default_page_size = 12;
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
    ##  *** use "," (comma) if you want to make search by some words, for ex.: hello, bye, hi
    ##  *** you have to change search type to OR when you search multi-fields, for ex.: "first_name, last_name"
    ##  *** "field_type" (optional, for range search) may be "from" or "to"
    ##  *** "date_format" may be "date|datedmy|datemdy|datetime|time"
    ##  *** "default_operator" may be =|<|>|like|%like|like%|%like%|not like
    ##  *** "handler"=>"" - write here path relatively to DATAGRID_DIR (where datagrid.class.php is found)
    ##  *** "field_view"=>"fieldName_2" or "field_view"=>"CONCAT(first_name, ' ', last_name) as full_name"
    /// $fill_from_array = array("0"=>"No", "1"=>"Yes");  /* as "value"=>"option" */
     $filtering_fields = array(
        "Textbox"     => array("type"=>"textbox",  "table"=>"demo_all_in_one", "table_alias"=>"oio", "field"=>"field_textbox", "filter_condition"=>"", "show_operator"=>"false", "default_operator"=>"=", "case_sensitive"=>"false", "comparison_type"=>"string", "width"=>"120px", "on_js_event"=>"", "default"=>""),
        "Date"        => array("type"=>"calendar", "table"=>"demo_all_in_one", "table_alias"=>"oio", "field"=>"field_date", "filter_condition"=>"", "show_operator"=>"false", "default_operator"=>"=", "case_sensitive"=>"false", "comparison_type"=>"string", "width"=>"120px", "on_js_event"=>"", "default"=>"", "calendar_type"=>"popup|floating", "date_format"=>"date", "field_type"=>""),
        "Foreign Key" => array("type"=>"enum",     "table"=>"demo_countries", "table_alias"=>"dc", "field"=>"id", "filter_condition"=>"", "show_operator"=>"false", "default_operator"=>"=", "case_sensitive"=>"false", "comparison_type"=>"string|numeric|binary", "width"=>"", "on_js_event"=>"", "default"=>"", "source"=>"self", "field_view"=>"name", "order_by_field"=>"name", "order_type"=>"ASC", "condition"=>"", "show_count"=>false, "multiple"=>"false", "multiple_size"=>"4"),
     );
     $dgrid->SetFieldsFiltering($filtering_fields);
    ##  *** allow default filtering: default - false
    /// $default_filtering_option = true;
    /// $dgrid->AllowDefaultFiltering($default_filtering_option);

    ## +---------------------------------------------------------------------------+
    ## | 6. View Mode Settings:                                                    |
    ## +---------------------------------------------------------------------------+
    ##  *** set view mode table properties
    /// $vm_table_properties = array("width"=>"90%");
    /// $dgrid->SetViewModeTableProperties($vm_table_properties);
    ##  *** set columns in view mode
    ##  *** Ex.: "on_js_event"=>"onclick='alert(\"Yes!!!\");'"
    ##  ***      "barchart" : number format in SELECT SQL must be equal with number format of max_value
      $fill_from_array = array("0"=>"No", "1"=>"Yes", "2"=>"Don't know", "3"=>"My be"); /* as "value"=>"option" */
      $vm_columns = array(
        "field_textbox"  => array("header"=>"Textbox",  "type"=>"label", "align"=>"left", "width"=>"", "wrap"=>"wrap", "text_length"=>"-1", "header_tooltip"=>'Product Name', 'header_tooltip_type' => 'floating', "tooltip"=>"false", "tooltip_type"=>"floating", "case"=>"normal", "summarize"=>"false", "sort_type"=>"string", "sort_by"=>"", "visible"=>"true", "on_js_event"=>""),
        //"field_textarea" => array("header"=>"Textarea", "type"=>"label", "align"=>"left", "width"=>"", "wrap"=>"wrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal", "summarize"=>"false", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>""),
        "field_date"     => array("header"=>"Date",     "type"=>"label", "align"=>"center", "width"=>"90px", "wrap"=>"wrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"simple", "case"=>"normal", "summarize"=>"false", "sort_type"=>"string", "sort_by"=>"", "visible"=>"true", "on_js_event"=>""),
        "field_foreign_key_name" => array("header"=>"Foreign Key",  "type"=>"label", "align"=>"center", "width"=>"100px", "wrap"=>"wrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"simple", "case"=>"normal", "summarize"=>"false", "sort_type"=>"string", "sort_by"=>"", "visible"=>"true", "on_js_event"=>""),
        "field_enum"     => array("header"=>"Enum",     "type"=>"enum",  "align"=>"center", "width"=>"90px", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal", "summarize"=>"false", "sort_type"=>"string", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "source"=>$fill_from_array),
        "field_checkbox" => array("header"=>"Checkbox", "type"=>"checkbox", "align"=>"center", "width"=>"80px", "wrap"=>"wrap|nowrap", "sort_type"=>"numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "true_value"=>1, "false_value"=>0),
        "field_color"    => array("header"=>"Color",    "type"=>"color",    "align"=>"center", "width"=>"90px", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal", "sort_type"=>"string", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "view_type"=>"image"),
        "field_money"    => array("header"=>"Money",    "type"=>"money", "align"=>"right", "width"=>"80px", "wrap"=>"wrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal", "summarize"=>"true", "summarize_sign"=>"SUM$=", "sort_type"=>"numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "sign"=>"$", "sign_place"=>"before", "decimal_places"=>"2", "dec_separator"=>".", "thousands_separator"=>","),
        "field_percent"  => array("header"=>"Percent",  "type"=>"percent",  "align"=>"right", "width"=>"90px", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal", "summarize"=>"true", "summarize_sign"=>"AVG%=", "summarize_function"=>"AVG", "sort_type"=>"numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "decimal_places"=>"2", "dec_separator"=>"."),

    ///     "FieldName_2"=>array("header"=>"Name_B", "type"=>"image",      "align"=>"left", "width"=>"X%|Xpx", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "case"=>"normal|upper|lower|camel", "summarize"=>"false", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "target_path"=>"uploads/", "default"=>"", "image_width"=>"50px", "image_height"=>"30px", "linkto"=>"", "magnify"=>"false", "magnify_type"=>"popup|magnifier|lightbox", "magnify_power"=>"2"),
    ///     "FieldName_3"=>array("header"=>"Name_C", "type"=>"linktoview", "align"=>"left", "width"=>"X%|Xpx", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal|upper|lower|camel", "summarize"=>"false", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>""),
    ///     "FieldName_4"=>array("header"=>"Name_D", "type"=>"linktoedit", "align"=>"left", "width"=>"X%|Xpx", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal|upper|lower|camel", "summarize"=>"false", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>""),
    ///     "FieldName_5"=>array("header"=>"Name_E", "type"=>"linktodelete", "align"=>"left", "width"=>"X%|Xpx", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal|upper|lower|camel", "summarize"=>"false", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>""),
    ///     "FieldName_6"=>array("header"=>"Name_F", "type"=>"link",       "align"=>"left", "width"=>"X%|Xpx", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal|upper|lower|camel", "summarize"=>"false", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "field_key"=>"field_name_0", "field_key_1"=>"field_name_1", "field_data"=>"field_name_2", "rel"=>"", "title"=>"", "target"=>"_self", "href"=>"{0}"),
    ///     "FieldName_7"=>array("header"=>"Name_G", "type"=>"link",       "align"=>"left", "width"=>"X%|Xpx", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal|upper|lower|camel", "summarize"=>"false", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "field_key"=>"field_name_0", "field_key_1"=>"field_name_1", "field_data"=>"field_name_2", "rel"=>"", "title"=>"", "target"=>"_self", "href"=>"mailto:{0}"),
    ///     "FieldName_8"=>array("header"=>"Name_H", "type"=>"link",       "align"=>"left", "width"=>"X%|Xpx", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal|upper|lower|camel", "summarize"=>"false", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "field_key"=>"field_name_0", "field_key_1"=>"field_name_1", "field_data"=>"field_name_2", "rel"=>"", "title"=>"", "target"=>"_self", "href"=>"http://mydomain.com?act={0}&act={1}&code=ABC"),
    ///     "FieldName_9"=>array("header"=>"Name_I", "type"=>"linkbutton", "align"=>"left", "width"=>"X%|Xpx", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal|upper|lower|camel", "summarize"=>"false", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "field_key"=>"field_name_0", "field_key_1"=>"field_name_1", "field_data"=>"field_name_2", "href"=>"{0}"),
    ///     "FieldName_11"=>array("header"=>"Name_K", "type"=>"password",  "align"=>"left", "width"=>"X%|Xpx", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "tooltip"=>"false", "tooltip_type"=>"floating|simple", "case"=>"normal|upper|lower|camel", "summarize"=>"false", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "hide"=>"false"),
    ///     "FieldName_13"=>array("header"=>"Name_M", "type"=>"barchart",  "align"=>"left", "width"=>"X%|Xpx", "wrap"=>"wrap|nowrap", "text_length"=>"-1", "case"=>"normal|upper|lower|camel", "summarize"=>"false", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"", "middle_color"=>"", "middle_value"=>"", "maximum_color"=>"", "maximum_value"=>"100", "display_type"=>"vertical|horizontal"),
    ///
    ///     "FieldName_16"=>array("header"=>"Name_Q", "type"=>"object",    "align"=>"center", "width"=>"X%|Xpx", "height"=>"X%|Xpx", "sort_type"=>"string|numeric", "sort_by"=>"", "visible"=>"true", "on_js_event"=>""),
    ///     "FieldName_17"=>array("header"=>"Name_R", "type"=>"blob"),
      );
      $dgrid->SetColumnsInViewMode($vm_columns);
    ##  *** set auto-generated columns in view mode
    //  $auto_column_in_view_mode = false;
    //  $dgrid->SetAutoColumnsInViewMode($auto_column_in_view_mode);

    ## +---------------------------------------------------------------------------+
    ## | 7. Add/Edit/Details Mode Settings:                                        |
    ## +---------------------------------------------------------------------------+
    ##  *** set add/edit mode table properties
    /// $em_table_properties = array("width"=>"70%");
    /// $dgrid->SetEditModeTableProperties($em_table_properties);
    ##  *** set details mode table properties
    /// $dm_table_properties = array("width"=>"70%");
    /// $dgrid->SetDetailsModeTableProperties($dm_table_properties);
    ##  ***  define settings for add/edit/details modes
      $table_name  = "demo_all_in_one";
      $primary_key = "id";
    ##  for ex.: "table_name.field = ".$_REQUEST['abc_rid'];
      $condition   = "";
      $dgrid->SetTableEdit($table_name, $primary_key, $condition);
    ##  *** set columns in edit mode
      $em_columns = array(

        "id"             => array("header"=>"Label (ID)", "type"=>"label",  "title"=>"", "default"=>"", "visible"=>"true", "on_js_event"=>""),
        "field_textbox"  => array("header"=>"Textbox",  "type"=>"textbox",  "req_type"=>"ry", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"255", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>""),
        "field_textarea" => array("header"=>"Textarea", "type"=>"textarea", "req_type"=>"sy", "width"=>"410px", "title"=>"", "readonly"=>"false", "maxlength"=>"1024", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "edit_type"=>"simple", "resizable"=>"true", "upload_images"=>"false", "rows"=>"3", "cols"=>"50"),
        "field_date"     => array("header"=>"Date",     "type"=>"date",     "req_type"=>"st", "width"=>"187px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "calendar_type"=>"dropdownlist"),
        "field_datetime" => array("header"=>"DateTime", "type"=>"datetime", "req_type"=>"st", "width"=>"127px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "calendar_type"=>"floating", "show_seconds"=>"true"),
        "field_time"     => array("header"=>"Time",     "type"=>"time",     "req_type"=>"st", "width"=>"90px",  "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "calendar_type"=>"dropdownlist", "show_seconds"=>"true"),
        "field_image"    => array("header"=>"Image",    "type"=>"image",    "req_type"=>"st", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "target_path"=>"uploads/", "allow_image_updating"=>"false", "max_file_size"=>"100K", "image_width"=>"120px", "image_height"=>"90px", "resize_image"=>"false", "resize_width"=>"", "resize_height"=>"", "magnify"=>"true", "magnify_type"=>"magnifier", "magnify_power"=>"2", "file_name"=>"", "host"=>"local", "allow_downloading"=>"false", "allowed_extensions"=>""),
        "field_file"     => array("header"=>"File",     "type"=>"file",     "req_type"=>"st", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "target_path"=>"uploads/", "max_file_size"=>"100K", "file_name"=>"", "host"=>"local", "allow_downloading"=>"false", "allowed_extensions"=>""),
        "field_money"    => array("header"=>"Money",    "type"=>"money",    "req_type"=>"sn", "width"=>"80px",  "title"=>"", "readonly"=>"false", "maxlength"=>"8",  "default"=>"0", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "sign"=>"$", "sign_place"=>"before", "decimal_places"=>"2", "dec_separator"=>".", "thousands_separator"=>","),
        "delimiter_1"    => array("inner_html"=>"&#8226;&#8226;&#8226; Delimiter "),
        "field_foreign_key" =>array("header"=>"Foreign Key", "type"=>"foreign_key","req_type"=>"ri", "width"=>"210px", "title"=>"", "readonly"=>"false", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true"),
        "field_enum"          => array("header"=>"Enum",            "type"=>"enum", "req_type"=>"st", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "source"=>$fill_from_array, "view_type"=>"radiobutton", "elements_alignment"=>"horizontal|vertical", "multiple"=>"false", "multiple_size"=>"4"),
        "field_enum_multiple" => array("header"=>"Multiple Select", "type"=>"enum", "req_type"=>"sr", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "source"=>array("Vice"=>"Vice", "Current"=>"Current", "Candidate"=>"Candidate"), "view_type"=>"checkbox", 'elements_alignment'=>'vertical', "multiple"=>"true", "multiple_size"=>"4"),
        "field_password" => array("header"=>"Password", "type"=>"password", "req_type"=>"sp", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"20", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "hide"=>"false", "generate"=>"true", "cryptography"=>"true", "cryptography_type"=>"aes", "aes_password"=>"aes_password"),
        "validator"      => array("header"=>"Password Validator", "type"=>"validator", "req_type"=>"sv", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "visible"=>(($mode == "add") ? "true" : "false"), "on_js_event"=>"", "for_field"=>"field_password", "validation_type"=>"password"),

        "field_checkbox" => array("header"=>"Checkbox", "type"=>"checkbox", "req_type"=>"st", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "true_value"=>1, "false_value"=>0),
        "field_color"    => array("header"=>"Color",    "type"=>"color",    "req_type"=>"st", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "view_type"=>"picker", "save_format"=>"hexcodes"),
        "field_percent"  => array("header"=>"Percent",  "type"=>"percent",  "req_type"=>"rt", "width"=>"70px", "title"=>"", "readonly"=>"false", "maxlength"=>"4", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>"true", "on_js_event"=>"", "decimal_places"=>"2", "dec_separator"=>"."),
        "field_hidden"   => array("header"=>"Hidden",   "type"=>"hidden",   "req_type"=>"st", "default"=>@date("Y-m-d"), "value"=>"", "unique"=>"false", "visible"=>(($mode == "add") ? "false" : "true")),
        "field_link"     => array("header"=>"Link (add/edit modes)",     "type"=>"link",     "req_type"=>"st", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"http://", "unique"=>"false", "unique_condition"=>"", "visible"=>true, "on_js_event"=>""),

    ///     "FieldName_16" =>array("header"=>"Name_P", "type"=>"blob",       "req_type"=>"st", "readonly"=>"false"),
      );

      if($mode == "details"){
        $em_columns["field_link"] = array("header"=>"Link (details mode)",     "type"=>"link",     "req_type"=>"st", "width"=>"210px", "title"=>"", "readonly"=>"false", "maxlength"=>"-1", "default"=>"", "unique"=>"false", "unique_condition"=>"", "visible"=>true, "on_js_event"=>"", "field_key"=>"field_link", "field_data"=>"field_link", "rel"=>"", "title"=>"", "target"=>"_new", "href"=>"{0}");
      }

      $dgrid->SetColumnsInEditMode($em_columns);
    ##  *** set auto-generated columns in edit mode
    //  $auto_column_in_edit_mode = false;
    //  $dgrid->SetAutoColumnsInEditMode($auto_column_in_edit_mode);

    ## +---------------------------------------------------------------------------+
    ## | 8. Foreign Keys Settings:                                                 |
    ## +---------------------------------------------------------------------------+
    ##  *** set foreign keys for add/edit/details modes (if there are linked tables)
    ##  *** Ex.: "field_name"=>"CONCAT(field1,' ',field2) as field3"
    ##  *** Ex.: "condition"=>"TableName_1.FieldName > 'a' AND TableName_1.FieldName < 'c'"
    ##  *** Ex.: "on_js_event"=>"onclick='alert(\"Yes!!!\");'"
     $foreign_keys = array(
        "field_foreign_key" => array("table"=>"demo_countries", "field_key"=>"id", "field_name"=>"name", "view_type"=>"dropdownlist", "elements_alignment"=>"horizontal|vertical", "condition"=>"", "order_by_field"=>"name", "order_type"=>"ASC", "show_count"=>"", "on_js_event"=>""),
    ///     "ForeignKey_2"=>array("table"=>"TableName_2", "field_key"=>"FieldKey_2", "field_name"=>"FieldName_2", "view_type"=>"dropdownlist(default)|radiobutton|textbox|label", "elements_alignment"=>"horizontal|vertical", "condition"=>"", "order_by_field"=>"", "order_type"=>"ASC|DESC", "show_count"=>"", "on_js_event"=>"")
     );
     $dgrid->SetForeignKeysEdit($foreign_keys);
    ##
    ################################################################################

?>
<!doctype html>
<html>
  <head>
	<meta charset="utf-8">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta name='keywords' content='php grid, php datagrid, php data grid, datagrid sample, datagrid php, datagrid, grid php, datagrid in php, data grid in php, free php grid, free php datagrid, datagrid paging' />
    <meta name='description' content='PHP DataGrid - Customized layout in View Mode' />
    <meta name="author" content=" - PHP DataGrid">
    <meta name="generator" content="PHP DataGrid">
    <meta name="ROBOTS" content="All" />
    <meta name="revisit-after" content="7 days" />
    <title>PHP DataGrid :: Sample #2-9 (code) - All In One (all types of fields)</title>
    <?php
        ## put call of this method between HTML <HEAD> tags (recommended)
        $dgrid->WriteCssClass();
    ?>
</head>
<body style="padding:10px;">
<?php
    ################################################################################
    ## +---------------------------------------------------------------------------+
    ## | 9. Bind the DataGrid:                                                     |
    ## +---------------------------------------------------------------------------+
    ##  *** bind the DataGrid and draw it on the screen
    ##  *** you may use $dgrid->Bind(false) and then $dgrid->Show() to separate
    ##  *** binding and displaying id datagrid
        $dgrid->Bind();
    //  ob_end_flush();
    ################################################################################
?>
</body>
</html>
<?php
    }
}