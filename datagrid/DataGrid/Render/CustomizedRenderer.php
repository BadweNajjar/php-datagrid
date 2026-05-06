<?php
namespace DataGrid\Render;

use DataGrid\Helper;

/**
 * CustomizedRenderer — renders the grid using a user-supplied HTML template.
 *
 * Substitutes column placeholders inside the configured view-mode template,
 * giving the application full control over the per-row markup. Extracted
 * from `DataGrid` during the Phase 7 refactor.
 *
 * @package DataGrid\Render
 * @since   8.6.0
 */
class CustomizedRenderer
{
    /** @var \DataGrid\DataGrid */
    private $grid;

    public function __construct($grid)
    {
        $this->grid = $grid;
    }

    public function Render()
    {
        $g = $this->grid;
        $req_mode = $g->GetVariableAlpha('mode');
        $r = '-1';
       
        $g->ExportTo();
        $g->ShowCaption($g->caption);
        $g->DrawControlPanel();        
        
        if(($g->mode != 'edit') && ($g->mode != 'details')) $g->DrawFiltering();   
        if(($req_mode !== 'add') || ($req_mode == '')) $g->PagingFirstPart();  
        $g->DisplayMessages();
        if($g->pagingAllowed) $g->PagingSecondPart($g->arrUpperPaging, false, true, 'Upper');
        if($g->rowLower == $g->rowUpper) echo '<br />';        

        if($g->isLoadingImageEnabled && $g->methodPostBack != 'ajax') echo '<div id="'.$g->uniqueRandomPrefix.'loading_image" class="dg_loading_image">'.$g->lang['loading_data'].' <img src="'.$g->include_path.'images/loading.gif" alt="'.htmlspecialchars($g->lang['loading_data']).'" /></div>'.$g->nl;
        
        // Hide grid before search
        $g->DrawControlButtonsJS();
        if($g->HideDataGrid()) return true;

        if(isset($g->templates[$g->layoutType]['header'])){
            // Add button
            $mode_button = $g->DrawModeButton('add', 'javascript:'.$g->uniquePrefix.'_doPostBack("add","'.Helper::EncodeParameter('-1', $g->safeMode).'","'.$g->urlString.'");', $g->lang['add_new'], $g->lang['add_new_record'], 'add.gif', "''", false, '', '', true);
            $template_header = str_replace('[ADD]', $mode_button, $g->templates[$g->layoutType]['header']);            

            // draw sortable headers
            if($g->sortingAllowed){
                $req_sort_field = $g->GetVariableAlphaNumeric('sort_field');
                $sort_type      = $g->GetVariableAlpha('sort_type');
                $page_size      = $g->GetVariableInt('page_size');
                $req_p          = $g->GetVariableInt('p');
                for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                    $c = $g->sortedColumns[$c_sorted];
                    $c_field_name = $g->GetFieldName($c);
                    if($req_sort_field && ($c == ($req_sort_field -1))){
                        if($sort_type == 'desc') $sort_type = 'asc';
                        else $sort_type = 'desc';
                    }elseif($sort_type == ''){
                        $sort_type = 'asc';    
                    }                    
                    $href_string = $g->amp.$g->uniquePrefix.'sort_field='.($c+1).$g->amp.$g->uniquePrefix.'sort_type='.$sort_type.$g->amp.$g->uniquePrefix.'page_size='.$page_size.$g->amp.$g->uniquePrefix.'p='.$req_p;
                    $href_string = 'javascript:'.$g->uniquePrefix.'_doPostBack(\'sort\',\'\',\''.$href_string.'\');';
                    $field_titile_tooltip = $g->lang['sort'];                    
                    $header_link = '<a class="'.$g->cssClass.'_dg_a_header" href="javascript:void(\'sort\');" onclick="'.$href_string.'" title="'.htmlspecialchars($field_titile_tooltip).'"><b>'.$g->GetHeaderName($c_field_name).'</b>';
                    $header_link .= $g->PrepareTooltip($c_field_name, 'view');
                    $template_header = str_replace('@'.$c_field_name.'@', $header_link, $template_header);                
                }                            
            }
            echo $template_header;
        }
        
        if($req_mode == 'add' || $req_mode == 'edit' || ($req_mode == 'update' && $g->rid == '-1')){
            $g->SetEditFieldsFormScript();
            //prepare action url for the form
            $combine_url_rid = ($g->multiRows > 1) ? $g->rid : Helper::EncodeParameter($g->rid, $g->safeMode, false);
            $curr_url = $g->CombineUrl('update', $combine_url_rid, $g->amp);
			$c_curr_url = '';
            $g->SetUrlString($c_curr_url, 'filtering', 'sorting', 'paging', $g->amp);
            $curr_url .= $c_curr_url;
            if(($req_mode == 'add') || ($req_mode == 'update' && $g->rid == '-1')){
                $curr_url .= $g->amp.$g->uniquePrefix.'new=1';
            }                                
            echo '<form name="'.$g->uniquePrefix.'frmEditRow" id="'.$g->uniquePrefix.'frmEditRow" method="post" action="'.(($g->methodPostBack == 'get') ? $curr_url : '').'"'.$g->enctype.'>'.$g->nl;
            echo '<input type="hidden" name="'.$g->uniquePrefix.'_operation_randomize_code" value="'.$g->GetRandomString(20).'" />'.$g->nl;
            $g->PrintHiddenFieldsFromUrl($curr_url);
        }
        
        if($req_mode == 'add' || ($req_mode == 'update' && $g->rid == '-1')){  
            if(isset($g->templates[$g->layoutType]['body'])) $template = $g->templates[$g->layoutType]['body'];
            else $template = $g->templates[$g->layoutType];
            foreach($g->columnsEditMode as $key => $val){
                if($g->IsForeignKey($key)){
                    $template = str_replace('{'.$key.'}', $g->GetForeignKeyInput(-1, $key, '-1', 'edit'), $template);
                }elseif(preg_match('/delimiter/i', $key)){
                    $template = str_replace('{'.$key.'}', $g->GetFieldProperty($key, 'inner_html'), $template); 
                }else{
                    $template = str_replace('{'.$key.'}', $g->GetFieldValueByType('', 0, '', $key), $template);
                }                                
            }            
            // Add button
            $mode_button = $g->DrawModeButton('edit', 'javascript:'.$g->uniquePrefix.'sendEditFields();', $g->lang['create'], $g->lang['create_new_record'], 'update.gif', "''", false, $g->nbsp, '', true);
            $template = str_replace('[CREATE]', $mode_button, $template);

            $param = $g->amp.$g->uniquePrefix.'new=1';
            $mode_button = $g->DrawModeButton('cancel', 'javascript:'.$g->uniquePrefix.'verifyCancel(\'-1\',\''.$param.'\')', $g->lang['cancel'], $g->lang['cancel'], 'cancel.gif', "''", false, $g->nbsp, '', true);
            $template = str_replace('[CANCEL]', $mode_button, $template);            
            $template = str_replace('[UPDATE]', '', $template);
            
            echo $template;
        }else{

            for($r = $g->rowLower; (($r >=0 && $g->rowUpper >=0) && ($r < $g->rowUpper) && ($r < ($g->rowLower + $g->reqPageSize))); $r++){                

                // draw column data
                $row = $g->dgFetchRow($g->dataSet);                
                
                if(isset($g->templates[$g->layoutType]['body'])) $template = $g->templates[$g->layoutType]['body'];
                else $template = $g->templates[$g->layoutType];
                
                $ind = ($g->GetFieldOffset($g->primaryKey) != -1) ? $g->GetFieldOffset($g->primaryKey) : 0;                
    
                // Rows numeration
                if($g->rowsNumeration){
                    $page_num = (($g->GetVariableInt('p') != '') ? $g->GetVariableInt('p') : '1');        
                    $rows_numeration = (($page_num - 1) * $g->reqPageSize) +  ($r + 1);
                    $template = str_replace('[ROWS_NUMERATION]', $rows_numeration, $template);
                }

                // Multi-row checkboxes
                if($g->isMultirowAllowed){
                    $disable = $g->isPrinting ? 'disabled' : '';
                    $checkbox_value = ($row[$g->GetFieldOffset($g->primaryKey)] != -1) ? $row[$g->GetFieldOffset($g->primaryKey)] : '0';
                    $multirow_checkbox = '<input type="checkbox" name="'.$g->uniquePrefix.'checkbox_'.$r.'" id="'.$g->uniquePrefix.'checkbox_'.$r.'" value="'.Helper::EncodeParameter($checkbox_value, $g->safeMode, false).'" '.$disable.' />';
                    $template = str_replace('[MULTIROW_CHECKBOX]', $multirow_checkbox, $template);
                }                
    
                // Add button
                $mode_button = $g->DrawModeButton('add', 'javascript:'.$g->uniquePrefix.'_doPostBack("add","'.Helper::EncodeParameter('-1', $g->safeMode).'","'.$g->urlString.'");', $g->lang['add_new'], $g->lang['add_new_record'], 'add.gif', "''", false, '', '', true);
                $template = str_replace('[ADD]', $mode_button, $template);
                $template = str_replace('[CREATE]', '', $template);
                
                // Edit button            
                $mode_button = $g->DrawModeButton('edit', 'javascript:'.$g->uniquePrefix.'_doPostBack("edit","'.Helper::EncodeParameter($row[$ind], $g->safeMode).'");', $g->lang['edit'], $g->lang['edit_record'], 'edit.gif', "''", false, $g->nbsp, '', true);
                $template = str_replace('[EDIT]', $mode_button, $template);
                
                // Details button            
                $mode_button = $g->DrawModeButton('details', 'javascript:'.$g->uniquePrefix.'_doPostBack("details","'.Helper::EncodeParameter($row[$ind], $g->safeMode).'","'.$g->urlString.'");', $g->lang['details'], $g->lang['view_details'], 'details.gif', "''", false, $g->nbsp, '', true);                        
                $template = str_replace('[DETAILS]', $mode_button, $template);
                
                // Back button            
                // [#0021-1] - ajax for details mode
                $href_string = '';
                if($g->methodPostBack == 'ajax'){
                    $g->SetUrlString($href_string, '', 'sorting', 'paging');
                    $href_string = $g->AddArrayParams($href_string);
                }
                $mode_button = $g->DrawModeButton('cancel', 'javascript:'.$g->uniquePrefix.'_doPostBack("back","'.Helper::EncodeParameter($row[$g->GetFieldOffset($g->primaryKey)], $g->safeMode).'","'.$href_string.'");', $g->lang['back'], $g->lang['back'], 'cancel.gif', "''", false, $g->nbsp, '', true, false);
                $template = str_replace('[BACK]', $mode_button, $template);
    
                // Delete button
                $mode_button = $g->DrawModeButton('delete', 'javascript:'.$g->uniquePrefix.'verifyDelete("'.Helper::EncodeParameter($row[$ind], $g->safeMode).'","");', $g->lang['delete'], $g->lang['delete_record'], 'delete.gif', "''", false, '', '', true);                        
                $template = str_replace('[DELETE]', $mode_button, $template);

                // Cancel button            
                $mode_button = $g->DrawModeButton('cancel', 'javascript:'.$g->uniquePrefix.'_doPostBack("cancel","'.Helper::EncodeParameter($row[$g->GetFieldOffset($g->primaryKey)], $g->safeMode).'");', $g->lang['cancel'], $g->lang['cancel'], 'cancel.gif', "''", false, $g->nbsp, '', true);
                $template = str_replace('[CANCEL]', $mode_button, $template);

                // Update button            
                $mode_button = $g->DrawModeButton('edit', 'javascript:'.$g->uniquePrefix.'sendEditFields();', $g->lang['update'], $g->lang['update_record'], 'update.gif', "''", false, $g->nbsp, '', true);
                $template = str_replace('[UPDATE]', $mode_button, $template);

                for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                    // get current column's index (offset)
                    $c = $g->sortedColumns[$c_sorted];
                    $c_field_name = $g->GetFieldName($c);
                    $row_c = isset($row[$c]) ? $row[$c] : '';
                    
                    if($c_field_name != '-1'){
                        if($g->IsForeignKey($c_field_name)){
                            $template = str_replace('{'.$c_field_name.'}', $g->GetForeignKeyInput($row[$g->GetFieldOffset($g->primaryKey)], $c_field_name, $row_c, (($req_mode == 'add' || $req_mode == 'edit') ? 'edit' : 'view')), $template);
                        }else{
                            $template = str_replace('{'.$c_field_name.'}', $g->GetFieldValueByType($row_c, $c, $row), $template);
                        }                                                        
                    }
                }
                // additional loop for finding 'delimiter' fields
                if($req_mode == 'edit' || $req_mode == 'details'){
                    foreach($g->columnsEditMode as $key => $val){
                        if(preg_match('/delimiter/i', $key)) $template = str_replace('{'.$key.'}', $g->GetFieldProperty($key, 'inner_html'), $template); 
                    }
                }
                echo $template;
            } // for           
        }
        
        if($req_mode == 'add' || $req_mode == 'edit'|| ($req_mode == 'update' && $g->rid == '-1')){
            echo '</form>';
        }
        
        if(isset($g->templates[$g->layoutType]['footer'])) echo $g->templates[$g->layoutType]['footer'];            

        // draw empty table       
        if($r == $g->rowLower){ $g->NoDataFound(); }
        if($g->pagingAllowed) $g->PagingSecondPart($g->arrLowerPaging, true, true, 'Lower');

        if($g->mode == 'view' || $req_mode == ''){
            $g->DrawMultiRowBar($r);  // draw multi-row footer cell
        }
        
        if($g->isLoadingImageEnabled && $g->methodPostBack != 'ajax') echo $g->ScriptOpen().'document.getElementById("'.$g->uniqueRandomPrefix.'loading_image").style.display=\'none\';'.$g->ScriptClose();       
    }
}