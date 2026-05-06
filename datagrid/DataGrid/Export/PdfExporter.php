<?php
namespace DataGrid\Export;

/**
 * PdfExporter — renders the grid to PDF using the bundled tFPDF library.
 *
 * Honors the configured layout (tabular vs. columnar) and column widths.
 * Extracted from `DataGrid::ExportToPdf()` during the Phase 3 refactor.
 *
 * @package DataGrid\Export
 * @since   8.6.0
 */
class PdfExporter
{
    /** @var \DataGrid\DataGrid */
    private $grid;

    public function __construct($grid)
    {
        $this->grid = $grid;
    }

    public function Render($page_size = 0)
    {
        $g = $this->grid;
        $header = [];
        $data = [];

        if($g->layouts[$g->layoutType] == '0'){
            $type = 'tabular';
        }elseif($g->layouts[$g->layoutType] == '1'){
            $type = 'columnar';
        }else{
            $type = 'tabular';
        }

        if($type == 'tabular'){
            for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                $c = $g->sortedColumns[$c_sorted];
                $field_name = $g->GetFieldName($c);
                if($g->CanViewField($field_name)){
                    $value = $g->GetHeaderName($field_name, true).$g->GetExportHeaderTooltip($field_name, 'view');
                    $header[] = $g->GetHeaderName($value, true);
                }
            }
            for($r = $g->rowLower; (($r >=0 && $g->rowUpper >=0) && ($r < $g->rowUpper) && ($r < $page_size)); $r++){
                $row = $g->dgFetchRow($g->dataSet);
                $data_content = '';
                for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                    $c = $g->sortedColumns[$c_sorted];
                    $row_c = isset($row[$c]) ? $row[$c] : '';
                    $field_name = $g->GetFieldName($c);
                    if($g->CanViewField($field_name)){
                        if($g->IsForeignKey($field_name)){
                            $data_content .= str_replace("\t", '', $g->GetForeignKeyInput($row[$g->GetFieldOffset($g->primaryKey)], $field_name, $row_c, 'view'));
                        }else{
                            $fp_tooltip = $g->GetFieldProperty($field_name, 'tooltip', 'view');
                            $data_content .= ($fp_tooltip) ? strip_tags($row_c) : str_replace("\t", '', strip_tags($g->GetFieldValueByType($row_c, $c, $row)));
                        }
                        if($c_sorted < $g->sortedColumnsSize - 1) $data_content .= ';';
                    }
                }
                $data[] = explode(';', chop(strip_tags($data_content)));
            }
        }else{
            $header[] = $g->lang['field'];
            $header[] = $g->lang['field_value'];
            for($r = $g->rowLower; (($r >=0 && $g->rowUpper >=0) && ($r < $g->rowUpper) && ($r < $page_size)); $r++){
                $row = $g->dgFetchRow($g->dataSet);
                for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                    $data_content = '';
                    $c = $g->sortedColumns[$c_sorted];
                    $row_c = isset($row[$c]) ? $row[$c] : '';
                    $field_name = $g->GetFieldName($c);
                    if($g->CanViewField($field_name)){
                        $data_content = $g->GetHeaderName($field_name, true).$g->GetExportHeaderTooltip($field_name, 'edit').';';
                        if($g->IsForeignKey($field_name)){
                            $data_content .= str_replace("\t", '', $g->GetForeignKeyInput($row[$g->GetFieldOffset($g->primaryKey)], $field_name, $row_c, 'view'));
                        }else{
                            $fp_type = $g->GetFieldProperty($field_name, 'type', 'edit', 'normal');
                            $fp_save_as = $g->GetFieldProperty($field_name, 'save_as', 'edit', 'normal');
                            if($g->IsDate($field_name)){
                                $data_content .= str_replace("\t", '', $g->MyDate($row_c, $fp_type));
                            }else{
                                $data_content .= ($fp_type == 'file' && $fp_save_as = 'blob') ? '' : str_replace("\t", '', $row_c);
                            }
                        }
                        $data[] = explode(';', chop(strip_tags($data_content)));
                    }
                }
                $data[] = '';
            }
        }

        if(!defined('FPDF_FONTPATH')) define('FPDF_FONTPATH', $g->directory.'modules/tfpdf/font/');
        include_once($g->directory.'modules/tfpdf/tfpdf.php');
        $pdf = new \tFPDF();
        $pdf->AddPage();
        $pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
        $pdf->SetFont('DejaVu','',12);
        $pdf->FancyTable($header, $data, $type, $g->caption);
        $pdf->Output($g->directory.$g->exportingDirectory.'export.pdf', '', $g->debug);
        echo $g->ExportDownloadFile('export.pdf');
    }
}
