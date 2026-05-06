<?php
namespace DataGrid\Export;

/**
 * ExportManager — dispatches export requests to the appropriate exporter.
 *
 * Reads `export` / `export_type` request variables and delegates to one of
 * `WordExporter`, `ExcelExporter`, `PdfExporter`, or `XmlExporter`. Extracted
 * from `DataGrid::ExportTo()` during the Phase 3 refactor.
 *
 * @package DataGrid\Export
 * @since   8.6.0
 */
class ExportManager
{
    /** @var \DataGrid\DataGrid */
    private $grid;

    public function __construct($grid)
    {
        $this->grid = $grid;
    }

    public function Dispatch()
    {
        $g = $this->grid;
        if($g->isDemo) return false;

        $req_export    = $g->GetVariableAlpha('export');
        $export_type   = $g->GetVariableAlpha('export_type');
        $req_page_size = (isset($_REQUEST[$g->uniquePrefix.'page_size'])) ? (int)$_REQUEST[$g->uniquePrefix.'page_size'] : $g->reqPageSize;

        if($g->exportAll){
            $page_size = $g->rowUpper;
        }else{
            $page_size = ($g->rowLower + $req_page_size);
        }

        if($req_export != true) return;

        if($export_type == 'pdf'){
            (new PdfExporter($g))->Render($page_size);
            return;
        }

        @chmod($g->directory.$g->exportingDirectory, 0777);
        $fe = @fopen($g->directory.$g->exportingDirectory.'export.'.$export_type, 'w+');
        if(!$fe){
            echo '<label class="'.$g->cssClass.'_dg_error_message no_print">'.$g->lang['file_opening_error'];
            if($g->debug) echo ' <strong>'.$g->directory.$g->exportingDirectory.'export.'.$export_type.'</strong>';
            else echo '<br />'.$g->lang['turn_on_debug_mode'];
            echo '</lable>';
            exit;
        }

        if($export_type == 'doc' || $export_type == 'csv'){
            // write BOM
            if(fwrite($fe, "\xEF\xBB\xBF") == FALSE){
                echo $g->lang['file_writing_error'].' (export.'.$export_type.')';
                exit;
            }
        }

        if($export_type == 'xml'){
            $export_output = (new XmlExporter($g))->Build($fe, $page_size);
        }elseif($export_type == 'xls'){
            $export_output = (new ExcelExporter($g))->Build($fe, $page_size, 'xls');
        }elseif($export_type == 'doc'){
            $export_output = (new WordExporter($g))->Build($page_size);
        }else{
            $export_output = (new ExcelExporter($g))->Build($fe, $page_size, 'csv');
        }

        if($export_type != 'csv'){
            if(fwrite($fe, $export_output) == FALSE){
                echo $g->lang['file_writing_error'].' (export.'.$export_type.')';
                exit;
            }
        }

        @fclose($fe);
        @chmod($g->directory.$g->exportingDirectory, 0744);
        echo $g->ExportDownloadFile('export.'.$export_type);
    }
}
