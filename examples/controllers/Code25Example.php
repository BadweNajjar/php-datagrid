<?php
namespace App\Controllers\Examples;

use App\Controllers\BaseController;
use DataGrid\DataGrid;

/**
 * PHP DataGrid :: Sample #2-5 (code) - using PHP DataGrid for displaying some statistical data
 *
 * Auto-generated CI4 controller wrapper around examples/code_2_5_example.php.
 * Drop into app/Controllers/Examples/ and route to it.
 */
class Code25Example extends BaseController
{
    public function index()
    {
        // Ensure session is started (DataGrid uses _SESSION for export, paging, etc.)
        service('session');
        ?>
<?php
    $year = isset($_POST['selected_year']) ? (int)$_POST['selected_year'] : "2009";
?>
<!doctype html>
<html>
  <head>
	<meta charset="utf-8">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta name='keywords' content='php grid, php datagrid, php data grid, datagrid sample, datagrid php, datagrid, grid php, datagrid in php, data grid in php, free php grid, free php datagrid, datagrid paging' />
    <meta name='description' content='PHP DataGrid - using DataGrid for displaying some statistical data' />
    <meta name="author" content=" - PHP DataGrid">
    <meta name="generator" content="PHP DataGrid">
    <title>PHP DataGrid :: Sample #2-5 (code) - using PHP DataGrid for displaying some statistical data</title>
  </head>
<body>
<table width="550px" border=0 align="center">
<tr valign='top'>
    <td align='left'>
        <span style="font-size:16px;font-weight:bold;">Using DataGrid for displaying statistics</span><br>
        <a href=index.php>Back to Index</a>
    </td>
    <td align='right'>
        <form name="frmDownloadStats" action="code_2_5_example.php" method="post">
        <div class='default_dg_caption'>
            &nbsp;&nbsp;&nbsp;
            <select name="selected_year">
                <option <?php echo (($year=="2008") ? "selected" : "");?> value="2008">2008</option>
                <option <?php echo (($year=="2009") ? "selected" : "");?> value="2009">2009</option>
                <option <?php echo (($year=="2010") ? "selected" : "");?> value="2010">2010</option>
                <option <?php echo (($year=="2011") ? "selected" : "");?> value="2011">2011</option>
                <option <?php echo (($year=="2012") ? "selected" : "");?> value="2012">2012</option>
            </select>
            &nbsp;&nbsp;&nbsp;
            <input type="submit" name="btnSubmit" value="Show" />
        </div>
        </form>
    </td>
</tr>
</table>
<br>

<center>
<?php

    ################################################################################
    ## +---------------------------------------------------------------------------+
    ## | 1. Creating & Calling:                                                    |
    ## +---------------------------------------------------------------------------+
    ##  *** define a relative (virtual) path to datagrid.class.php file
    ##  *** (relatively to the current file)
    ##  *** RELATIVE PATH ONLY ***
    $db = \Config\Database::connect();

    ////////////////////////////////////////////////////////////////////////////////
    //
    // TOTAL
    //
    ////////////////////////////////////////////////////////////////////////////////

    ################################################################################
    ##  *** set needed options and create a new class instance
      $debug_mode = false;        /* display SQL statements while processing */
      $messaging = true;          /* display system messages on a screen */
      $unique_prefix = "prd_";    /* prevent overlays - must be started with a letter */
      $dgrid = new DataGrid($debug_mode, $messaging, $unique_prefix);
      $dgrid->SetCssClass("pink");
      $dgrid->DisplayLoadingImage("true");
      $dgrid->AllowPaging(false, "", "");

    ##  *** set data source with needed options
    ##  *** put a primary key on the first place
      $sql = "SELECT
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-01-01' AND demo_downloads.download_date < '".$year."-02-01') as month1,
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-02-01' AND demo_downloads.download_date < '".$year."-03-01') as month2,
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-03-01' AND demo_downloads.download_date < '".$year."-04-01') as month3,
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-04-01' AND demo_downloads.download_date < '".$year."-05-01') as month4,
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-05-01' AND demo_downloads.download_date < '".$year."-06-01') as month5,
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-06-01' AND demo_downloads.download_date < '".$year."-07-01') as month6,
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-07-01' AND demo_downloads.download_date < '".$year."-08-01') as month7,
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-08-01' AND demo_downloads.download_date < '".$year."-09-01') as month8,
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-09-01' AND demo_downloads.download_date < '".$year."-10-01') as month9,
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-10-01' AND demo_downloads.download_date < '".$year."-11-01') as month10,
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-11-01' AND demo_downloads.download_date < '".$year."-12-01') as month11,
            (SELECT COUNT(*) FROM demo_downloads WHERE demo_downloads.download_date >= '".$year."-12-01' AND demo_downloads.download_date < '".($year+1)."-01-01') as month12
        FROM demo_downloads
        GROUP BY month1, month2, month3, month4 ";

      $default_order = array("download_date"=>"DESC");
      $dgrid->DataSource($db, $sql, $default_order);

    ##  *** "byFieldValue"=>"fieldName" - make the field to be a link to edit mode page
     $modes = array(
         "add"     =>array("view"=>false, "edit"=>false, "type"=>"link"),
         "edit"    =>array("view"=>false, "edit"=>false,  "type"=>"link", "byFieldValue"=>""),
         "details" =>array("view"=>false, "edit"=>false, "type"=>"link"),
         "delete"  =>array("view"=>false, "edit"=>false,  "type"=>"image")
     );
     $dgrid->SetModes($modes);
    ##  *** set DataGrid caption
     $dg_caption = "Statistical data for ".$year." Year";
     $dgrid->SetCaption($dg_caption);
     $dgrid->AllowPrinting(false);
     $dgrid->AllowSorting(false);
     $dgrid->AllowHighlighting(false);

    ## +---------------------------------------------------------------------------+
    ## | 6. View Mode Settings:                                                    |
    ## +---------------------------------------------------------------------------+
    ##  *** set view mode table properties
     $vm_table_properties = array("width"=>"444px");
     $dgrid->SetViewModeTableProperties($vm_table_properties);
     $max_value = "15";
     $vm_columns = array(
        "month1"  =>array("header"=>"Jan", "type"=>"barchart",  "align"=>"center", "width"=>"", "wrap"=>"nowrap", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"0", "middle_color"=>"", "middle_value"=>"50", "maximum_color"=>"", "maximum_value"=>$max_value, "display_type"=>"vertical"),
        "month2"  =>array("header"=>"Feb", "type"=>"barchart",  "align"=>"center", "width"=>"", "wrap"=>"nowrap", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"0", "middle_color"=>"", "middle_value"=>"50", "maximum_color"=>"", "maximum_value"=>$max_value, "display_type"=>"vertical"),
        "month3"  =>array("header"=>"Mar", "type"=>"barchart",  "align"=>"center", "width"=>"", "wrap"=>"nowrap", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"0", "middle_color"=>"", "middle_value"=>"50", "maximum_color"=>"", "maximum_value"=>$max_value, "display_type"=>"vertical"),
        "month4"  =>array("header"=>"Apr", "type"=>"barchart",  "align"=>"center", "width"=>"", "wrap"=>"nowrap", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"0", "middle_color"=>"", "middle_value"=>"50", "maximum_color"=>"", "maximum_value"=>$max_value, "display_type"=>"vertical"),
        "month5"  =>array("header"=>"May", "type"=>"barchart",  "align"=>"center", "width"=>"", "wrap"=>"nowrap", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"0", "middle_color"=>"", "middle_value"=>"50", "maximum_color"=>"", "maximum_value"=>$max_value, "display_type"=>"vertical"),
        "month6"  =>array("header"=>"Jun", "type"=>"barchart",  "align"=>"center", "width"=>"", "wrap"=>"nowrap", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"0", "middle_color"=>"", "middle_value"=>"50", "maximum_color"=>"", "maximum_value"=>$max_value, "display_type"=>"vertical"),
        "month7"  =>array("header"=>"Jul", "type"=>"barchart",  "align"=>"center", "width"=>"", "wrap"=>"nowrap", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"0", "middle_color"=>"", "middle_value"=>"50", "maximum_color"=>"", "maximum_value"=>$max_value, "display_type"=>"vertical"),
        "month8"  =>array("header"=>"Aug", "type"=>"barchart",  "align"=>"center", "width"=>"", "wrap"=>"nowrap", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"0", "middle_color"=>"", "middle_value"=>"50", "maximum_color"=>"", "maximum_value"=>$max_value, "display_type"=>"vertical"),
        "month9"  =>array("header"=>"Sep", "type"=>"barchart",  "align"=>"center", "width"=>"", "wrap"=>"nowrap", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"0", "middle_color"=>"", "middle_value"=>"50", "maximum_color"=>"", "maximum_value"=>$max_value, "display_type"=>"vertical"),
        "month10"  =>array("header"=>"Oct", "type"=>"barchart", "align"=>"center", "width"=>"", "wrap"=>"nowrap", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"0", "middle_color"=>"", "middle_value"=>"50", "maximum_color"=>"", "maximum_value"=>$max_value, "display_type"=>"vertical"),
        "month11"  =>array("header"=>"Nov", "type"=>"barchart", "align"=>"center", "width"=>"", "wrap"=>"nowrap", "visible"=>"true", "on_js_event"=>"", "field"=>"", "value_sign"=>"", "minimum_color"=>"", "minimum_value"=>"0", "middle_color"=>"", "middle_value"=>"50", "maximum_color"=>"", "maximum_value"=>$max_value, "display_type"=>"vertical"),
     );
     $dgrid->SetColumnsInViewMode($vm_columns);

    ## +---------------------------------------------------------------------------+
    ## | 8. Bind the DataGrid:                                                     |
    ## +---------------------------------------------------------------------------+
    ##  *** bind the DataGrid and draw it on the screen
      $dgrid->Bind();
      //ob_end_flush();
    ##
    ################################################################################

?>
</center>

</body>
</html>
<?php
    }
}