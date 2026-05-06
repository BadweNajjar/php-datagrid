<?php
namespace DataGrid\Render;

use DataGrid\Helper;

/**
 * TabularRenderer — renders the grid in tabular (one-row-per-record) layout.
 *
 * Handles header, body and per-row action cells, including the inline edit /
 * delete / details forms. Extracted from `DataGrid` during the Phase 7
 * refactor.
 *
 * @package DataGrid\Render
 * @since   8.6.0
 */
class TabularRenderer
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
        $req_mode         = $g->GetVariableAlpha('mode');
        $horizontal_align = ($g->tblAlign[$g->mode] == 'center') ? 'margin-left:auto;margin-right:auto;' : '';
        $table_align      = ($g->tblAlign[$g->mode] == 'center') ? 'align="center"' : '';
        $req_sort_field   = '';
       
        $g->ExportTo();
        $g->ShowCaption($g->caption);
        $g->DisplayMessages();
        $g->DrawControlPanel();
        
        if($g->mode != 'edit') $g->DrawFiltering();   
        if(($req_mode !== 'add') || ($req_mode == '')) $g->PagingFirstPart();  
        if($g->pagingAllowed) $g->PagingSecondPart($g->arrUpperPaging, false, true, 'Upper');
        $side_column_allowed = (($g->ModeAllowed('details') || $g->ModeAllowed('delete')) && $g->controlsDisplayingType == 'grouped');

        // clear array of move row info
        if(isset($_SESSION)) unset($_SESSION[$g->uniquePrefix.'-move']);
        
        //prepare summarize columns array
        foreach($g->columnsViewMode as $key => $val){        
            $fp_summarize = $g->GetFieldPropertyBool($key, 'summarize', 'view', false, false);
            if($fp_summarize) $g->arrSummarizeColumns[$key] = ['sum_time'=>'00:00', 'sum'=>0, 'count'=>0, 'max'=>0, 'min'=>'', 'dif1'=>0, 'dif2'=>9];
        }

        if($g->isLoadingImageEnabled && $g->methodPostBack != 'ajax') echo '<div id="'.$g->uniqueRandomPrefix.'loading_image" class="dg_loading_image">'.$g->lang['loading_data'].' <img src="'.$g->include_path.'images/loading.gif" alt="'.htmlspecialchars($g->lang['loading_data']).'" /></div>'.$g->nl;
        
        // Hide grid before search
        $g->DrawControlButtonsJS();
        if($g->HideDataGrid()) return true;

        // draw add link-button cell        
        if($g->ModeAllowed('add', $g->mode, 'show_button') && isset($g->modes['add']['show_add_button']) && ($g->modes['add']['show_add_button'] == 'outside')){
            echo '<table class="'.$g->cssClass.'_dg_tbl_outside" dir="'.$g->direction.'" border="0" '.$table_align.' style="'.$horizontal_align.'" width="'.$g->tblWidth[$g->mode].'">';
            echo '<tr>';
            echo '<td align="'.(($g->direction == 'ltr') ? 'left' : 'right').'"><b>';
            $g->DrawModeButton('add', 'javascript:'.$g->uniquePrefix.'_doPostBack("add","'.Helper::EncodeParameter('-1', $g->safeMode).'","'.$g->urlString.'");', $g->lang['add_new'], $g->lang['add_new_record'], 'add.gif', "''", false, '', '', false, false);
            echo '</b></td>';
            echo '</tr>';
            echo '</table>';
            $g->modes['add'][$g->mode] = false;
        }

        if($g->scrollingOption && $g->mode == 'view'){
            if($g->browserName == 'MSIE') echo '<table cellpadding="0" cellspacing="0" border="0" width="'.$g->tblWidth[$g->mode].'" align="'.$g->tblAlign[$g->mode].'"><tr><td>'.$g->nl;
            else echo '<div class="dg_scroll_outer" style="width:'.$g->tblWidth[$g->mode].'">';
        }        
        $g->TblOpen();        
        
        // *** START DRAWING HEADERS -------------------------------------------
        $g->TblHeadOpen();
        $g->RowOpen('');

            // draw multi-row checkboxes header
            if(($g->isMultirowAllowed) && ($g->rowsTotal > 0)){                
                $g->MainColOpen('center', 0, 'nowrap', '26px', $g->cssClass.'_dg_th');
                echo '&nbsp;';
                $g->MainColClose();
            }            
           
            $button_mode = (($g->isWarning && $req_mode == 'update') ? 'view' : $g->mode); /* to prevent error on unique field warnings */
            if($g->ModeAllowed('add', $button_mode, 'show_button')){
                $g->MainColOpen('center', 0, 'nowrap', '8%', $g->cssClass.'_dg_th_normal');
                $g->DrawModeButton('add', 'javascript:'.$g->uniquePrefix.'_doPostBack("add","'.Helper::EncodeParameter('-1', $g->safeMode).'","'.$g->urlString.'");', $g->lang['add_new'], $g->lang['add_new_record'], 'add.gif', "''", false, '', '', false, false);                        
                $g->MainColClose();
            }else{
                if($g->ModeAllowed('edit') || $side_column_allowed){
                    $g->MainColOpen('center',0,'nowrap', '2%', $g->cssClass.'_dg_th_normal'); echo $g->nbsp; $g->MainColClose();                
                }
            }
            if(($g->rowsNumeration)){
                $g->MainColOpen('center',0,'nowrap', '4%'); echo $g->numerationSign; $g->MainColClose();                
            }

            // draw column headers in add mode
            if(($g->rid == -1) && ($req_mode == 'add')){
                foreach($g->columnsEditMode as $key => $val){                    
                    if($g->GetFieldProperty($key, 'type') != 'hidden' && !preg_match('/delimiter/i', $key)){
                        $g->MainColOpen('center',0);
                        echo '<b>'.$g->GetHeaderName($key).'</b>';                        
                        $g->MainColClose();                        
                    }
                }
            }else{
                if(!$g->rowUpper) $g->sortingAllowed = false;
                $req_sort_field    = $g->GetVariableAlphaNumeric('sort_field');
                $req_sort_field_by = $g->GetVariableAlphaNumeric('sort_field_by');
                $req_sort_type     = $g->GetVariableAlpha('sort_type');    
                if($req_sort_field){
                    if(strtolower($req_sort_type) == 'desc'){
                        $sort_sign = '&#9660;'; 
                        $sort_title = $g->lang['descending'];
                    }else{
                        $sort_sign = '&#9650;'; 
                        $sort_title = $g->lang['ascending'];
                    }
                }
                if($g->mode === 'view'){
                    // draw column headers in view mode                    
                    for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                        // get current column's index (offset)
                        $c = $g->sortedColumns[$c_sorted];
                        $field_name = $g->GetFieldName($c);
                        
                        $fp_sort_by = $g->GetFieldProperty($field_name, 'sort_by', 'view');
                        $fp_sort_type = $g->GetFieldProperty($field_name, 'sort_type', 'view');
                        $fp_show_on_print = $g->GetFieldPropertyBool($field_name, 'show_on_print', 'view', true, true);
                        $print_class = (!$fp_show_on_print) ? ' dg-no-print' : '';

                        $fp_sort_by_offset = $g->GetFieldOffset($fp_sort_by);
                        if($fp_sort_by != '' && $fp_sort_by_offset >= 0){
                            $sort_field_by = $fp_sort_by_offset+1;                            
                        }else{
                            if($fp_sort_by != '' && $fp_sort_by_offset == '-1') $g->AddWarning('', '', 'Check carefully \'sort_by\' attribute for field \''.$field_name.'\', it may has a wrong data!');
                            $sort_field_by = ''; 
                        }
                        
                        if($g->CanViewField($field_name)){ 
                            $fp_wrap  = $g->GetFieldProperty($field_name, 'wrap', 'view', 'lower', $g->wrap);
                            $fp_width = $g->GetFieldProperty($field_name, 'width', 'view'); if($fp_width == 'X%|Xpx') $fp_width = '';
                            $field_titile_tooltip = $g->lang['sort'];
                            $fp_sortable = $g->GetFieldPropertyBool($field_name, 'sortable', 'view', true, true);
                            $fp_header_align = $g->GetHeaderAlign($field_name);
                            if($g->sortingAllowed && !$g->isPrinting && $req_sort_field && Helper::IsInteger($req_sort_field) && ($c == ($req_sort_field -1))){
                            	$th_css_class = $g->cssClass.'_dg_th_selected';
                            } else {
                            	$th_css_class = $g->cssClass.'_dg_th';
                            };
                            $g->MainColOpen($fp_header_align, 0, $fp_wrap, $fp_width, $th_css_class.$print_class);
                            if($g->sortingAllowed && $fp_sortable){
                                $href_string = ''; 
                                $g->SetUrlString($href_string, '', '', 'paging'); // removed filtering for _doPostBack
                                if(isset($_REQUEST[$g->uniquePrefix.'sort_type']) && $_REQUEST[$g->uniquePrefix.'sort_type'] == 'asc') $sort_type='desc';
                                else $sort_type = 'asc';
                                if(!$g->isPrinting){
                                    $href_string .= $g->amp.$g->uniquePrefix.'sort_field='.($c+1).$g->amp.$g->uniquePrefix.'sort_field_by='.$sort_field_by.$g->amp.$g->uniquePrefix.'sort_field_type='.$fp_sort_type.$g->amp.$g->uniquePrefix.'sort_type=';
                                    // prepare sorting order by field's type 
                                    if($req_sort_field && Helper::IsInteger($req_sort_field) && ($c == ($req_sort_field -1))){
                                        $href_string .= $sort_type;
                                    }else{
                                        if($g->IsDate($field_name, $g->mode)){ $href_string .= 'desc'; }
                                        else{ $href_string .= 'asc'; }                                        
                                    }
                                    
                                    //[#0012 - 1] - start
                                    /// old code: $href_string = str_replace('&', '&amp;', str_replace('&amp;', '&', $href_string));
                                    //[#0012 - 1] - end
                                    
                                    //[#0012 - 2] - start
                                    // new code: suggested by kalak
                                    $href_string = $g->AddArrayParams($href_string);
                                    //[#0012 - 2] - end

                                    $href_string = 'javascript:'.$g->uniquePrefix.'_doPostBack(\'sort\',\'\',\''.$href_string.'\');';
                                    echo '<a class="'.$g->cssClass.'_dg_a_header" href="javascript:void(\'sort\');" onclick="'.$href_string.'" title="'.htmlspecialchars($field_titile_tooltip).'" ';
                                    echo '><b>'.$g->GetHeaderName($field_name).'</b> ';
                                    if($req_sort_field && Helper::IsInteger($req_sort_field) && ($c == ($req_sort_field -1))){
                                        echo $g->nbsp.'<span title="'.htmlspecialchars($sort_title).'" class="dg_sort_arrow">'.$sort_sign.'</span>';
                                    }
                                    echo '</a>';
                                }else{
                                    echo '<b>'.$g->GetHeaderName($field_name).'</b>';                            
                                }
                            }else{                                
                                echo '<b>'.$g->GetHeaderName($field_name).'</b> ';                        
                            }
                            if(!preg_match('/selected/i', $th_css_class)) echo $g->PrepareTooltip($field_name, 'view');
                            $g->MainColClose();
                        }else{
                            // find problematic fields
                            $f_count = 0;
                            foreach($g->columnsViewMode as $f_key => $f_val){
                                if($c_sorted == $f_count++){
                                    $g->AddWarning('', '', 'Field <b>'.$f_key.'</b>, used in the list of fields in View Mode was not found in SELECT SQL! Please, check carefully your code syntax and field name, it may be case sensitive!');
                                    break;
                                }
                            }                            
                        }
                    }//for
                }elseif($g->mode === 'edit'){
                    foreach($g->columnsEditMode as $key => $val){
                        if($g->GetFieldProperty($key, 'type') != 'hidden'){
                            if($g->CanViewField($key) && !preg_match('/delimiter/i', $key)){
                                $g->MainColOpen('center',0);
                                echo '<b>'.$g->GetHeaderName($key).'</b>';                        
                                $g->MainColClose();                                
                            }
                        }                        
                    }
                }            
            }
            // draw details/delete headers
            if($g->ModeAllowed('details', '', 'show_button') && $g->controlsDisplayingType != 'grouped'){
                $g->MainColOpen('center',0,'nowrap', '6%', $g->cssClass.'_dg_th_normal');echo $g->lang['view'];$g->MainColClose();
            }                        
            if($g->ModeAllowed('delete', '', 'show_button') && $g->controlsDisplayingType != 'grouped'){
                $g->MainColOpen('center',0,'nowrap', '6%', $g->cssClass.'_dg_th_normal');echo $g->lang['delete'];$g->MainColClose();
            }            
        $g->RowClose();
        $g->TblHeadClose();        
        // *** END HEADERS -----------------------------------------------------

        //if we add a new row on linked tabular view mode table (mode 0 <-> 0)
        $quick_exit = false;        
        if((isset($_REQUEST[$g->uniquePrefix.'mode']) && ($_REQUEST[$g->uniquePrefix.'mode'] == 'add')) && ($g->rowLower == 0) && ($g->rowUpper == 0)){
            $g->rowUpper = 1;
            $quick_exit = true;
        }        

        // *** START DRAWING ROWS ----------------------------------------------
        $first_field_name = '';
        $curr_url = '';
        $c_curr_url = '';

        $g->TbodyOpen();
        for($r = $g->rowLower; (($r >=0 && $g->rowUpper >=0) && ($r < $g->rowUpper) && ($r < ($g->rowLower + $g->reqPageSize))); $r++){            
            // add new row (ADD MODE)
            if(($r == $g->rowLower) && ($g->rid == -1) && ($req_mode == 'add')){
                $g->RowOpen($r, $g->rowColor[4]); /* highlight selected row */
                $main_td_color=$g->rowColor[3];
                $curr_url = $g->CombineUrl('update', Helper::EncodeParameter(-1, $g->safeMode, false), $g->amp);
                $g->SetUrlString($c_curr_url, 'filtering', 'sorting', 'paging', $g->amp);                
                $curr_url .= $c_curr_url;
                $curr_url .= $g->amp.$g->uniquePrefix.'new=1';
                echo '<form name="'.$g->uniquePrefix.'frmEditRow" id="'.$g->uniquePrefix.'frmEditRow" method="post" action="'.(($g->methodPostBack == 'get') ? $curr_url : '').'"'.$g->enctype.'>'.$g->nl;
                echo '<input type="hidden" name="'.$g->uniquePrefix.'_operation_randomize_code" value="'.$g->GetRandomString(20).'" />'.$g->nl;
                $g->PrintHiddenFieldsFromUrl($curr_url);
                $g->SetEditFieldsFormScript($curr_url);
                // draw multi-row empty cell
                if(($g->isMultirowAllowed) && (!$g->isError)){$g->ColOpen('center',0,'nowrap',$g->rowColor[0], $g->cssClass.'_dg_td');echo $g->nbsp;$g->ColClose();}                            
                $buttons  = $g->DrawModeButton('edit', 'javascript:'.$g->uniquePrefix.'sendEditFields();', $g->lang['create'], $g->lang['create_new_record'], 'update.gif', "''", false, '&nbsp', '', true, true, 'create');
                $buttons .= $g->DrawModeButton('cancel', 'javascript:'.$g->uniquePrefix.'verifyCancel("-1","'.$g->amp.$g->uniquePrefix.'new=1")', $g->lang['cancel'], $g->lang['cancel'], 'cancel.gif', "''", false, $g->nbsp, '', true);
                if($buttons){ $g->ColOpen('center',0,'nowrap',$main_td_color, $g->cssClass.'_dg_td_main'); echo $buttons; $g->ColClose(); }                
                $hidden_fields = '';
                foreach($g->columnsEditMode as $key => $val){
                    if($g->GetFieldProperty($key, 'type') != 'hidden' && !preg_match('/delimiter/i', $key)){
                        $g->ColOpen('left',0,'nowrap');
                        if($g->IsForeignKey($key)){
                            echo $g->nbsp.$g->GetForeignKeyInput(-1, $key, '-1', 'edit').$g->nbsp;
                        }else{
                            echo $g->GetFieldValueByType('', 0, '', $key);
                        }
                        $g->ColClose();                    
                    }else{
                        $hidden_fields .= $g->GetFieldValueByType('', 0, '', $key);
                    }
                }
                
                if($g->ModeAllowed('delete')) $g->ColOpen('center',0,'nowrap');echo '';$g->ColClose();                
                echo $hidden_fields;
                echo '</form>';                
                $g->RowClose();                
            }
                            
            //if we add a new row on linked tabular view mode table (mode 0 <-> 0) 
            if($quick_exit == true){
                $g->TblClose();
                if($g->isLoadingImageEnabled && $g->methodPostBack != 'ajax') echo $g->ScriptOpen().'document.getElementById("'.$g->uniqueRandomPrefix.'loading_image").style.display="none";'.$g->ScriptClose();                
                if(($g->firstFieldFocusAllowed) && ($first_field_name != '')) echo $g->ScriptOpen().'_dgSetFocus(document.forms["'.$g->uniquePrefix.'frmEditRow"]'.$g->GetFieldRequiredType($first_field_name).$first_field_name.');'.$g->ScriptClose();                
                return;            
            }
            
            $row = $g->dgFetchRow($g->dataSet);
            if($g->dgGetDbDriverType() == 'ibm' && $row[0] == '') break;
            if(($g->mode == 'edit') && $g->GetFieldOffset($g->primaryKey) != '-1' && (intval($g->rid) == intval($row[$g->GetFieldOffset($g->primaryKey)]))){
                $g->RowOpen($r, $g->rowColor[4]); /* highlight selected row */
            }else{
                if($r % 2 == 0) $g->RowOpen($r, $g->rowColor[0]);
                else $g->RowOpen($r, $g->rowColor[1]);
            }
            if($r % 2 == 0) $main_td_color=$g->rowColor[2];
            else $main_td_color=$g->rowColor[3];
            
            // draw multi-row row checkboxes
            if($g->isMultirowAllowed){
                $g->ColOpen('center',0,'nowrap','','');                
                $disable = $g->isPrinting ? 'disabled' : '';
                $checkbox_value = isset($row[$g->GetFieldOffset($g->primaryKey)]) ? $row[$g->GetFieldOffset($g->primaryKey)] : '0';
                echo '<input onclick="onMouseClickRow(\''.$g->uniquePrefix.'\',\''.$r.'\',\''.$g->rowColor[5].'\',\''.$g->rowColor[1].'\',\''.$g->rowColor[0].'\')" type="checkbox" name="'.$g->uniquePrefix.'checkbox_'.$r.'" id="'.$g->uniquePrefix.'checkbox_'.$r.'" value="';
                echo Helper::EncodeParameter($checkbox_value, $g->safeMode, false);
                echo '" '.$disable.' />';
                $g->ColClose();                
            }
            
            // draw mode buttons
            if($g->ModeAllowed('edit') || $side_column_allowed){
                if(($g->mode == 'edit') && $g->GetFieldOffset($g->primaryKey) != '-1' && (intval($g->rid) == intval($row[$g->GetFieldOffset($g->primaryKey)]))){
                    $curr_url = $g->CombineUrl('update', Helper::EncodeParameter($row[$g->GetFieldOffset($g->primaryKey)], $g->safeMode, false), $g->amp);
                    $g->SetUrlString($c_curr_url, 'filtering', 'sorting', 'paging', $g->amp);
                    $curr_url .= $c_curr_url;
                    echo '<form name="'.$g->uniquePrefix.'frmEditRow" id="'.$g->uniquePrefix.'frmEditRow" method="post" action="'.(($g->methodPostBack == 'get') ? $curr_url : '').'"'.$g->enctype.'>'.$g->nl;
                    echo '<input type="hidden" name="'.$g->uniquePrefix.'_operation_randomize_code" value="'.$g->GetRandomString(20).'" />'.$g->nl;
                    $g->PrintHiddenFieldsFromUrl($curr_url);
                    $g->SetEditFieldsFormScript($curr_url);                    
                    $g->ColOpen('center',0,'nowrap',$main_td_color, $g->cssClass.'_dg_td_main');
                    $g->DrawModeButton('edit', 'javascript:'.$g->uniquePrefix.'sendEditFields();', $g->lang['update'], $g->lang['update_record'], 'update.gif', "''", false, ' ', '', false, true, 'update');
                    $g->DrawModeButton('cancel', 'javascript:'.$g->uniquePrefix.'_doPostBack("cancel","'.Helper::EncodeParameter($row[$g->GetFieldOffset($g->primaryKey)], $g->safeMode).'");', $g->lang['cancel'], $g->lang['cancel'], "cancel.gif", "''", false, $g->nbsp, '');
                    $g->ColClose();
                }else{                    
                    if(in_array($g->dgGetDbDriverType(), ['oci', 'oci8', 'odbc'])){
                        $row_id = $row[0];
                    }else{
                        $row_id = isset($row[$g->GetFieldOffset($g->primaryKey)]) ? $row[$g->GetFieldOffset($g->primaryKey)] : $g->GetFieldOffset($g->primaryKey);                        
                    }
                    $curr_url = $g->CombineUrl('edit', $row_id);
                    $g->SetUrlString($curr_url, 'filtering', 'sorting', 'paging');                                            
                    if(isset($_REQUEST[$g->uniquePrefix.'new']) && (isset($_REQUEST[$g->uniquePrefix.'new']) == 1)){
                        $curr_url .= $g->amp.$g->uniquePrefix.'new=1';
                    }
                    // by field Value - link on Edit mode page
                    if(isset($g->modes['edit']['byFieldValue']) && ($g->modes['edit']['byFieldValue'] != '')){
                        if($g->GetFieldOffset($g->modes['edit']['byFieldValue']) == '-1'){
                            if($g->debug){
                                $g->ColOpen(($g->direction == 'rtl')?'right':'left',0,'nowrap',$main_td_color, $g->cssClass.'_dg_td_main');
                                echo $g->nbsp.$g->lang['wrong_field_name'].' - '.$g->modes['edit']['byFieldValue'].$g->nbsp;
                            }else{
                                $g->ColOpen('center',0,'nowrap',$main_td_color, $g->cssClass.'_dg_td_main');                                    
                                $g->DrawModeButton('edit', 'javascript:'.$g->uniquePrefix.'_doPostBack("edit","'.Helper::EncodeParameter($row_id, $g->safeMode).'");', $g->lang['edit'], $g->lang['edit_record'], "edit.gif", "''", false, $g->nbsp, '');
                            }
                        }else{
                            $g->ColOpen(($g->direction == 'rtl')?'right':'left',0,'nowrap',$main_td_color, $g->cssClass.'_dg_td_main');
                            echo $g->nbsp.'<a class="'.$g->cssClass.'_dg_a_header" href="'.$curr_url.'">'.$row[$g->GetFieldOffset($g->modes['edit']['byFieldValue'])].'</a>'.$g->nbsp;
                        }                            
                    }else{
                        $g->ColOpen('center',0,'nowrap',$main_td_color, $g->cssClass.'_dg_td_main', '8%');
                        $g->DrawModeButton('edit', 'javascript:'.$g->uniquePrefix.'_doPostBack("edit","'.Helper::EncodeParameter($row_id, $g->safeMode).'","'.$g->urlString.'");', $g->lang['edit'], $g->lang['edit_record'], "edit.gif", "''", false, $g->nbsp, ''); 
                    }
                }
                $row_id = isset($row[$g->GetFieldOffset($g->primaryKey)]) ? $row[$g->GetFieldOffset($g->primaryKey)] : $g->GetFieldOffset($g->primaryKey);
                if($g->controlsDisplayingType == 'grouped') $g->DrawControlButtons($row_id, 'grouped', (int)$g->ModeAllowed('edit', '', 'show_button'));
                if($g->ModeAllowed('edit') || ($g->controlsDisplayingType == 'grouped')) $g->ColClose();                                            
            }else{
                if($g->ModeAllowed('add')){  
                    $g->ColOpen('center',0,'nowrap',$g->rowColor[2], $g->cssClass.'_dg_td_main');$g->ColClose();                    
                }
            }
            
            if($g->rowsNumeration){
                $page_num = (($g->GetVariableInt('p') != '') ? $g->GetVariableInt('p') : '1');        
                $rows_numeration = (($page_num - 1) * $g->reqPageSize) + ($r + 1);
                $g->ColOpen('center',0,'nowrap'); echo '<label class="'.$g->cssClass.'_dg_label">'.$rows_numeration.'</label>'; $g->ColClose();
            }

            // draw column data
            $hidden_fields = '';
            for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                // get current column's index (offset)
                $c = $g->sortedColumns[$c_sorted];
                $col_align = $g->GetFieldAlign($c, $row, $g->mode);
                $c_field_name = $g->GetFieldName($c);
                $fp_wrap = $g->GetFieldProperty($c_field_name, 'wrap', 'view', 'lower', $g->wrap);
                $print_class = (!$g->GetFieldPropertyBool($c_field_name, 'show_on_print', 'view', true, true)) ? 'dg-no-print' : '';

                $row_c = isset($row[$c]) ? $row[$c] : '';
                if(($g->mode === 'view') && ($g->CanViewField($c_field_name))){
                    if($req_sort_field == $c+1){
                        $g->ColOpen($col_align, 0, $fp_wrap, (($r % 2 == 0) ? $g->rowColor[8] : $g->rowColor[9]), $print_class.' '.$g->cssClass.'_dg_td_selected'); 
                    }else{
                        $g->ColOpen($col_align, 0, $fp_wrap, '', $print_class);
                    }
                    $field_value = $g->GetFieldValueByType($row_c, $c, $row);
                    $fp_summarize = $g->GetFieldPropertyBool($c_field_name, 'summarize', 'view', false, false);
                    $fp_summarize_function = strtoupper($g->GetFieldProperty($c_field_name, 'summarize_function', 'view'));
                    if(!in_array($fp_summarize_function, ['SUM_TIME', 'SUM', 'AVG', 'MAX', 'MIN', 'DIFF', 'PERCENT_DIFF'])) $fp_summarize_function = $g->summarizeFunction;
                    $fp_on_item_created = $g->GetFieldProperty($c_field_name, 'on_item_created', 'view');
                    
                    if($fp_summarize){
                        // customized working with field value
                        if(function_exists($fp_on_item_created)){
                            //ini_set('allow_call_time_pass_reference', true);
                            $curr_value = str_replace(',', '', $fp_on_item_created($row_c));
                        }else{
                            if($g->IsEnum($c_field_name, 'view')) {
                                if (is_array($g->columnsViewMode[$c_field_name]['source'])) {
                                    $row_c = isset($g->columnsViewMode[$c_field_name]['source'][$row_c]) ? $g->columnsViewMode[$c_field_name]['source'][$row_c] : $row_c;
                                }
                            }
                            $curr_value = str_replace(',', '', $row_c);
                        }
                        $curr_value = strip_tags($curr_value);
                        if($fp_summarize_function == 'SUM_TIME'){ $g->arrSummarizeColumns[$c_field_name]['sum_time'] = Helper::SumTimes($g->arrSummarizeColumns[$c_field_name]['sum_time'],  $curr_value); }
                        elseif($fp_summarize_function == 'SUM' || $fp_summarize_function == 'AVG'){ $g->arrSummarizeColumns[$c_field_name]['sum'] += $curr_value; }
                        elseif($fp_summarize_function == 'MAX'){ if($curr_value > $g->arrSummarizeColumns[$c_field_name]['max']) $g->arrSummarizeColumns[$c_field_name]['max'] = $curr_value; }
                        elseif($fp_summarize_function == 'MIN'){
                            if($g->arrSummarizeColumns[$c_field_name]['min'] == '') $g->arrSummarizeColumns[$c_field_name]['min'] = $curr_value;
                            elseif($curr_value < $g->arrSummarizeColumns[$c_field_name]['min']) $g->arrSummarizeColumns[$c_field_name]['min'] = $curr_value;
                        }elseif($fp_summarize_function == 'DIFF' || $fp_summarize_function == 'PERCENT_DIFF'){
                            $field_1 = $g->GetFieldProperty($c_field_name, 'summarize_field_1', 'view');
                            $field_2 = $g->GetFieldProperty($c_field_name, 'summarize_field_2', 'view');
                            $g->arrSummarizeColumns[$c_field_name]['dif1'] += !empty($row[$field_1]) ? $row[$field_1] : 0;
                            $g->arrSummarizeColumns[$c_field_name]['dif2'] += !empty($row[$field_2]) ? $row[$field_2] : 0;
                        }
                        // [#0030 under check - 09.11.10] fix for summarize functions
                        ///if($curr_value != '' && intval($curr_value) != '0')....
                        $g->arrSummarizeColumns[$c_field_name]['count']++;
                    }
                    echo $field_value;
                    $g->ColClose();                    
                }elseif($g->mode === 'edit'){
                    if($g->CanViewField($c_field_name)){
                        if($g->GetFieldProperty($c_field_name, 'type') == 'hidden'){
                            // don't show hidden fields, even if 'visible'=>'true'
                            $hidden_fields .= $g->GetFieldValueByType('', 0, '', $c_field_name);
                        }else{ 
                            if($first_field_name == '' && !preg_match('/delimiter/i', $c_field_name)) $first_field_name = $c_field_name;
                            if(intval($g->rid) === intval($row[$g->GetFieldOffset($g->primaryKey)])){
                                $g->ColOpen($col_align, 0, $fp_wrap);
                                if($g->IsForeignKey($c_field_name)){                                                
                                    echo $g->nbsp.$g->GetForeignKeyInput($row[$g->GetFieldOffset($g->primaryKey)], $c_field_name, $row_c, 'edit').$g->nbsp;
                                }else{
                                    echo $g->GetFieldValueByType($row_c, $c, $row); 
                                }
                                $g->ColClose();                                
                            }else{
                                if($g->GetFieldProperty($c_field_name, 'type') != 'hidden'){
                                    $g->ColOpen($col_align, 0, $fp_wrap);
                                    if($g->IsForeignKey($c_field_name)){
                                        echo $g->nbsp.$g->GetForeignKeyInput($row[$g->GetFieldOffset($g->primaryKey)], $c_field_name, $row_c,'view').$g->nbsp;
                                    }elseif($g->IsEnum($c_field_name)){
                                        if(is_array($g->columnsEditMode[$c_field_name]['source'])){
                                           echo $g->nbsp.(isset($g->columnsEditMode[$c_field_name]['source'][$row_c]) ? $g->columnsEditMode[$c_field_name]['source'][$row_c] : $row_c).$g->nbsp;
                                        }else{
                                           echo $g->GetFieldValueByType($row_c, $c, $row, '', '', 'view');
                                        }
                                    }else{
                                        if($g->GetFieldPropertyBool($c_field_name, 'hide', 'view', false, false)){
                                            echo $g->nbsp.'******'.$g->nbsp;
                                        }else{
                                            echo $g->GetFieldValueByType($row_c, $c, $row, '', '', 'view');
                                        }                                
                                    }                                                                 
                                    $g->ColClose();                                
                                }
                            }                            
                        }                        
                    }
                }
            }
            if (in_array($g->dgGetDbDriverType(), ['oci', 'oci8', 'odbc'])) {
                $row_id = $row[0];
            }else{
                $row_id = isset($row[$g->GetFieldOffset($g->primaryKey)]) ? $row[$g->GetFieldOffset($g->primaryKey)] : $g->GetFieldOffset($g->primaryKey);                        
            }
            if($g->controlsDisplayingType != 'grouped') $g->DrawControlButtons($row_id);

            if(($g->mode == 'edit') && ($g->GetFieldOffset($g->primaryKey) != '-1') && (intval($g->rid) == intval($row[$g->GetFieldOffset($g->primaryKey)]))){ echo $hidden_fields.'</form>'; }
            $g->RowClose();
        }
        // *** END ROWS --------------------------------------------------------        
        
        // draw summarizing row
        $g->TbodyClose();        
        if($r != $g->rowLower){ $g->DrawSummarizeRow(); }
        $g->TblClose();              
        if($g->scrollingOption && $g->mode == 'view'){
            if($g->browserName == 'MSIE') echo '</td></tr></table>'.$g->nl;
            else echo '</div>';    
        }
        
        // draw empty table       
        if($r == $g->rowLower){ $g->NoDataFound(); }
        
        $g->DrawMultiRowBar($r);  // draw multi-row row footer cell

        if($g->pagingAllowed) $g->PagingSecondPart($g->arrLowerPaging, true, true, 'Lower');
        
        if($g->isLoadingImageEnabled && $g->methodPostBack != 'ajax') echo $g->ScriptOpen().'document.getElementById("'.$g->uniqueRandomPrefix.'loading_image").style.display="none";'.$g->ScriptClose();        
        if(($g->firstFieldFocusAllowed) && ($first_field_name != '')) echo $g->ScriptOpen().'_dgSetFocus(document.'.$g->uniquePrefix.'frmEditRow.'.$g->GetFieldRequiredType($first_field_name).$first_field_name.');'.$g->ScriptClose();
    }
}