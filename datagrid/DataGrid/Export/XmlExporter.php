<?php
namespace DataGrid\Export;

/**
 * XmlExporter — builds XML output.
 *
 * Emits one `<rowN>` element per record, with one child element per visible
 * column. Extracted from `DataGrid::ExportToXml()` during the Phase 3
 * refactor.
 *
 * @package DataGrid\Export
 * @since   8.6.0
 */
class XmlExporter
{
    /** @var \DataGrid\DataGrid */
    private $grid;

    public function __construct($grid)
    {
        $this->grid = $grid;
    }

    public function Build($fe, $page_size = 0)
    {
        $g = $this->grid;
        $content = '<?xml version="1.0" encoding="UTF-8" ?>';
        $content .= '<page>';
        for($r = $g->rowLower; (($r >=0 && $g->rowUpper >=0) && ($r < $g->rowUpper) && ($r < $page_size)); $r++){
            $row = $g->dgFetchRow($g->dataSet);
            $content .= '<row'.$r.'>';
            for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                $c = $g->sortedColumns[$c_sorted];
                $row_c = isset($row[$c]) ? $row[$c] : '';
                $field_name = $g->GetFieldName($c);
                if($g->CanViewField($field_name)){
                    $header_name = $field_name;
                    $content .= '<'.$header_name.$g->GetExportHeaderTooltip($field_name, $g->mode, 'xml').'>';
                    if($g->IsForeignKey($field_name)){
                        $content_temp = str_replace(',', '', $g->GetForeignKeyInput($row[$g->GetFieldOffset($g->primaryKey)], $field_name, $row_c, 'view'));
                    }else{
                        $fp_type = $g->GetFieldProperty($field_name, 'type', 'edit', 'normal');
                        $fp_save_as = $g->GetFieldProperty($field_name, 'save_as', 'edit', 'normal');
                        if($g->IsDate($field_name)){
                            $content_temp = $g->MyDate($row_c, $fp_type);
                        }else{
                            $content_temp = ($fp_type == 'file' && $fp_save_as = 'blob') ? '' : strip_tags($row_c);
                        }
                    }
                    $content .= htmlentities($content_temp, ENT_COMPAT, 'UTF-8');
                    $content .= '</'.$header_name.'>';
                }
            }
            $content .= '</row'.$r.'>';
        }
        $content .= '</page>';
        return $content;
    }
}
