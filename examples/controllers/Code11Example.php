<?php
namespace App\Controllers\Examples;

use App\Controllers\BaseController;
use DataGrid\DataGrid;

/**
 * PHP DataGrid :: Sample #1-1 (code)
 *
 * Auto-generated CI4 controller wrapper around examples/code_1_1_example.php.
 * Drop into app/Controllers/Examples/ and route to it.
 */
class Code11Example extends BaseController
{
    public function index()
    {
        // Ensure session is started (DataGrid uses _SESSION for export, paging, etc.)
        service('session');
        ?>
<!doctype html>
<html>
  <head>
	<meta charset="utf-8">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta name='keywords' content='php grid, php datagrid, php data grid, datagrid sample, datagrid php, datagrid, grid php, datagrid in php, data grid in php, free php grid, free php datagrid, datagrid paging' />
    <meta name='description' content='PHP DataGrid - using PHP DataGrid for displaying some statistical data' />
    <meta name="author" content=" - PHP DataGrid">
    <meta name="generator" content="PHP DataGrid">
    <title>PHP DataGrid :: Sample #1-1 (code)</title>
  </head>
<body style="padding:10px">
<?php

  ################################################################################
  ## +---------------------------------------------------------------------------+
  ## | 1. Creating & Calling:                                                    |
  ## +---------------------------------------------------------------------------+
  ##  *** define a relative (virtual) path to datagrid.class.php file
  ##  *** (relatively to the current file)
  ##  *** RELATIVE PATH ONLY ***
    $db = \Config\Database::connect();

    ob_start();
  ##  *** set needed options
    $debug_mode = false;
    $messaging = true;
    $unique_prefix = "f_";
    $dgrid = new DataGrid($debug_mode, $messaging, $unique_prefix);

  ##  *** set data source with needed options
  ##  *** put a primary key on the first place
    $sql=" SELECT "
    ." demo_countries.id, "
    ." demo_countries.name, "
    ." demo_countries.description, "
    ." demo_countries.picture_url, "
    ." FORMAT(demo_countries.population, 0) as population, "
    ." CASE WHEN demo_countries.is_democracy = 1 THEN 'Yes' ELSE 'No' END as is_democracy "
    ."FROM demo_countries ";
    $default_order = array("name"=>"ASC");
    $dgrid->DataSource($db, $sql, $default_order);

    $dg_caption = '<b>Simplest PHP DataGrid</b> - <a href=index.php>Back to Index</a>';
    $dgrid->SetCaption($dg_caption);

  ## +---------------------------------------------------------------------------+
  ## | 6. View Mode Settings:                                                    |
  ## +---------------------------------------------------------------------------+
  ##  *** set columns in view mode
    $dgrid->SetAutoColumnsInViewMode(true);

  ## +---------------------------------------------------------------------------+
  ## | 7. Add/Edit/Details Mode settings:                                        |
  ## +---------------------------------------------------------------------------+
  ##  ***  set settings for edit/details mode
    $table_name = "demo_countries";
    $primary_key = "id";
    $condition = "";
    $dgrid->SetTableEdit($table_name, $primary_key, $condition);
    $dgrid->SetAutoColumnsInEditMode(true);

  ## +---------------------------------------------------------------------------+
  ## | 8. Bind the DataGrid:                                                     |
  ## +---------------------------------------------------------------------------+
  ##  *** set debug mode & messaging options
    $dgrid->Bind();
    ob_end_flush();

?>
</body>
</html>
<?php
    }
}