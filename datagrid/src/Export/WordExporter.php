<?php
namespace DataGrid\Export;

/**
 * WordExporter — builds Word-compatible HTML for export.
 *
 * Emits an HTML payload with Microsoft Office namespaces and inline `@page`
 * styles so MS Word will open it as a `.doc`. Extracted from
 * `DataGrid::ExportToWord()` during the Phase 3 refactor.
 *
 * @package DataGrid\Export
 * @since   8.6.0
 */
class WordExporter
{
    /** @var \DataGrid\DataGrid */
    private $grid;

    public function __construct($grid)
    {
        $this->grid = $grid;
    }

    public function Build($page_size = 0)
    {
        $g = $this->grid;

        $content = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
        <head><xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom></w:WordDocument></xml>
        <style type="text/css">
        @page {size:8.5in 11.0in; margin:0.5in 0.31in 0.42in 0.25in;}
        table{border-collapse:collapse;}
        table,th,td{border: 1px solid black;}
        th{background-color:#f1f2f3;}
        </style>
        </head>
        <body>';

        $content .= '<center><b>'.$g->caption.'</b></center><br>';
        $content .= '<table>';

        if($g->layouts[$g->layoutType] == '0'){
            $content .= '<tr>';
            for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                $c = $g->sortedColumns[$c_sorted];
                $field_name = $g->GetFieldName($c);
                if($g->CanViewField($field_name)){
                    $tooltip = (($g->GetExportHeaderTooltip($field_name,'view') != '') ? '<br><small>'.$g->GetExportHeaderTooltip($field_name,'view').'</small>' : '');
                    $content .= '<th>'.$g->GetHeaderName($field_name, true).$tooltip.'</th>';
                }
            }
            $content .= '</tr>';
            for($r = $g->rowLower; (($r >=0 && $g->rowUpper >=0) && ($r < $g->rowUpper) && ($r < $page_size)); $r++){
                $content .= '<tr>';
                $row = $g->dgFetchRow($g->dataSet);
                for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                    $c = $g->sortedColumns[$c_sorted];
                    $field_name = $g->GetFieldName($c);
                    if($g->CanViewField($field_name)){
                        $fp_tooltip = $g->GetFieldProperty($field_name, 'tooltip', 'view');
                        $value = ($fp_tooltip) ? strip_tags($row[$c]) : str_replace(',', '', strip_tags($g->GetFieldValueByType($row[$c], $c, $row)));
                        $content .= '<td>'.$value.'</td>';
                    }
                }
                $content .= '</tr>';
            }
        }else{
            $content .= '<tr><td>'.$g->lang['field'].'</td><td>'.$g->lang['field_value'].'</td></tr>';
            for($r = $g->rowLower; (($r >=0 && $g->rowUpper >=0) && ($r < $g->rowUpper) && ($r < $page_size)); $r++){
                $row = $g->dgFetchRow($g->dataSet);
                for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                    $c = $g->sortedColumns[$c_sorted];
                    $field_name = $g->GetFieldName($c);
                    if($g->CanViewField($field_name)){
                        $content .= '<tr>';
                        $field_header = $g->GetHeaderName($field_name, true).$g->GetExportHeaderTooltip($field_name, 'edit');
                        $fp_type = $g->GetFieldProperty($field_name, 'type', 'edit', 'normal');
                        $fp_save_as = $g->GetFieldProperty($field_name, 'save_as', 'edit', 'normal');
                        $content .= '<td>'.$field_header.'</td>';
                        $content .= '<td>';
                        if($g->IsForeignKey($field_name)){
                            $content .= str_replace(',', '', $g->GetForeignKeyInput($row[$g->GetFieldOffset($g->primaryKey)], $field_name, $row[$c], 'view'));
                        }else{
                            if($g->IsDate($field_name)){
                                $content .= str_replace(',', '', $g->MyDate($row[$c], $fp_type));
                            }else{
                                $content .= ($fp_type == 'file' && $fp_save_as = 'blob') ? '' : str_replace(',', '', strip_tags($row[$c]));
                            }
                        }
                        $content .= '</td>';
                        $content .= '</tr>';
                    }
                }
            }
        }
        $content .= '</table>';
        $content .= '</body></html>';
        return $content;
    }
}
