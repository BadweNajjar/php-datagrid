<?php
namespace DataGrid\Export;

/**
 * ExcelExporter — builds CSV / XLS (BIFF) output.
 *
 * Streams either a UTF-8 CSV or a minimal BIFF-format XLS payload. Extracted
 * from `DataGrid::ExportToExcel()` during the Phase 3 refactor.
 *
 * @package DataGrid\Export
 * @since   8.6.0
 */
class ExcelExporter
{
    /** @var \DataGrid\DataGrid */
    private $grid;

    public function __construct($grid)
    {
        $this->grid = $grid;
    }

    public function Build($fe, $page_size = 0, $export_type = 'csv')
    {
        $g = $this->grid;
        $content = '';

        if($export_type == 'xls'){
            $content = pack('vvvvvv', 0x809, 0x08, 0x00,0x10, 0x0, 0x0);
        }

        if($g->layouts[$g->layoutType] == '0'){
            $arr_line = [];
            $cell_ind = 0;
            for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                $c = $g->sortedColumns[$c_sorted];
                $field_name = $g->GetFieldName($c);
                if($g->CanViewField($field_name)){
                    $value = $g->GetHeaderName($field_name, true).$g->GetExportHeaderTooltip($field_name, 'view');
                    if($export_type == 'xls'){
                        $size = strlen($value);
                        $content .= pack('v*', 0x0204, 8 + $size, 0, $cell_ind, 0x00, $size);
                        $content .= $value;
                        $cell_ind++;
                    }else{
                        $arr_line[] = $value;
                    }
                }
            }
            if($export_type == 'csv') fputcsv($fe, $arr_line);
            $row_ind = 1;
            for($r = $g->rowLower; (($r >=0 && $g->rowUpper >=0) && ($r < $g->rowUpper) && ($r < $page_size)); $r++){
                $arr_line = [];
                $cell_ind = 0;
                $row = $g->dgFetchRow($g->dataSet);
                for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                    $c = $g->sortedColumns[$c_sorted];
                    $row_c = isset($row[$c]) ? $row[$c] : '';
                    $field_name = $g->GetFieldName($c);
                    if($g->CanViewField($field_name)){
                        $fp_tooltip = $g->GetFieldProperty($field_name, 'tooltip', 'view');
                        $value = ($fp_tooltip) ? strip_tags($row_c) : str_replace(',', '', strip_tags($g->GetFieldValueByType($row_c, $c, $row)));
                        if($export_type == 'xls'){
                            $size = strlen($value);
                            $content .= pack('v*', 0x0204, 8 + $size, $row_ind, $cell_ind, 0x00, $size);
                            $content .= $value;
                            $cell_ind++;
                        }else{
                            $arr_line[] = $value;
                        }
                    }
                }
                $row_ind++;
                if($export_type == 'csv') fputcsv($fe, $arr_line);
            }
        }else{
            $row_ind = 1;
            for($r = $g->rowLower; (($r >=0 && $g->rowUpper >=0) && ($r < $g->rowUpper) && ($r < $page_size)); $r++){
                $row = $g->dgFetchRow($g->dataSet);
                for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                    $arr_line = [];
                    $c = $g->sortedColumns[$c_sorted];
                    $row_c = isset($row[$c]) ? $row[$c] : '';
                    $field_name = $g->GetFieldName($c);
                    if($g->CanViewField($field_name)){
                        $field_header = $g->GetHeaderName($field_name, true).$g->GetExportHeaderTooltip($field_name, 'edit');
                        $fp_type = $g->GetFieldProperty($field_name, 'type', 'edit', 'normal');
                        $fp_save_as = $g->GetFieldProperty($field_name, 'save_as', 'edit', 'normal');
                        if($export_type == 'xls'){
                            $size = strlen($field_header);
                            $content .= pack('v*', 0x0204, 8 + $size, $row_ind, 0, 0x00, $size);
                            $content .= $field_header;
                            $row_c = ($fp_type == 'file' && $fp_save_as == 'blob') ? '' : $row_c;
                            $size = strlen($row_c);
                            $content .= pack('v*', 0x0204, 8 + $size, $row_ind, 1, 0x00, $size);
                            $content .= $row_c;
                        }else{
                            $arr_line[] = $field_header;
                            if($g->IsForeignKey($field_name)){
                                $arr_line[] = str_replace(',', '', $g->GetForeignKeyInput($row[$g->GetFieldOffset($g->primaryKey)], $field_name, $row_c, 'view'));
                            }else{
                                if($g->IsDate($field_name)){
                                    $arr_line[] = str_replace(',', '', $g->MyDate($row_c, $fp_type));
                                }else{
                                    $arr_line[] = ($fp_type == 'file' && $fp_save_as = 'blob') ? '' : str_replace(',', '', strip_tags($row_c));
                                }
                            }
                        }
                        $row_ind++;
                    }
                    if($export_type == 'csv') fputcsv($fe, $arr_line);
                }
                if($export_type == 'xls'){
                    $content .= pack('v*', 0x0204, 8, $row_ind, 0, 0x00, 0);
                    $content .= pack('v*', 0x0204, 8, $row_ind, 1, 0x00, 0);
                    $row_ind++;
                }
            }
        }
        if($export_type == 'xls'){
            $content .= pack('vv', 0x0A, 0x00);
            return $content;
        }
        return null;
    }
}
