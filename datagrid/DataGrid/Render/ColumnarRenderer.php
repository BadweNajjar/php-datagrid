<?php
namespace DataGrid\Render;

use DataGrid\Helper;

/**
 * ColumnarRenderer — renders the grid in columnar (one-column-per-record) layout.
 *
 * Used for compact presentations where each record becomes a column rather
 * than a row. Extracted from `DataGrid` during the Phase 7 refactor.
 *
 * @package DataGrid\Render
 * @since   8.6.0
 */
class ColumnarRenderer
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
        $req_mode = ($g->modeAfterUpdate == '') ? $g->GetVariableAlpha('mode') : $g->modeAfterUpdate;
        $hidden_fields = '';
        $r = 0; // rows counter
        $row = [];
		$c_curr_url = '';
        
        $g->ExportTo();
        $g->ShowCaption($g->caption);        
        $g->DrawControlPanel();
        
        if(($g->mode != 'edit') && ($g->mode != 'details')) $g->DrawFiltering();   
        if((($req_mode !== 'add') && ($req_mode !== 'details')) || ($req_mode == '')) $g->PagingFirstPart();  
        $g->DisplayMessages();          
        $g->DrawControlButtonsJS();    
      
        if($g->ModeAllowed('add', $g->mode, 'show_button')){
            $g->TblOpen();
            $g->RowOpen($r, $g->rowColor[0]);
                $g->MainColOpen('center',0,'nowrap', '', $g->cssClass.'_dg_th_normal dg_th_tlr');
                $g->DrawModeButton('add', 'javascript:'.$g->uniquePrefix.'_doPostBack("add","'.Helper::EncodeParameter('-1', $g->safeMode).'","'.$g->urlString.'");', $g->lang['add_new'], $g->lang['add_new'], "add.gif", "''", true, '', '', false, false);                        
                $g->MainColClose();
            $g->RowClose();
            $g->TblClose();                
        }

        if($g->pagingAllowed) $g->PagingSecondPart($g->arrUpperPaging, false, true, 'Upper');

        //prepare action url for the form
        $combine_url_rid = ($g->multiRows > 1) ? $g->rid : Helper::EncodeParameter($g->rid, $g->safeMode, false);
        $curr_url = $g->CombineUrl('update', $combine_url_rid, $g->amp);
        $g->SetUrlString($c_curr_url, 'filtering', 'sorting', 'paging', $g->amp);
        $curr_url .= $c_curr_url;
        /// if($req_mode === 'add'){ [#0032-2] fix for unique fields check
        if(($req_mode == 'add') || ($req_mode == 'update' && $g->rid == '-1')){
            $curr_url .= $g->amp.$g->uniquePrefix.'new=1';
        }

        if($g->isLoadingImageEnabled && $g->methodPostBack != 'ajax') echo '<div id="'.$g->uniqueRandomPrefix.'loading_image" class="dg_loading_image">'.$g->lang['loading_data'].' <img src="'.$g->include_path.'images/loading.gif" alt="'.htmlspecialchars($g->lang['loading_data']).'" /></div>'.$g->nl;
        echo '<form name="'.$g->uniquePrefix.'frmEditRow" id="'.$g->uniquePrefix.'frmEditRow" style="margin:10px auto;padding:0px;" method="post" action="'.(($g->methodPostBack == 'get') ? $curr_url : '').'"'.$g->enctype.'>'.$g->nl;
        echo '<input type="hidden" name="'.$g->uniquePrefix.'_operation_randomize_code" value="'.$g->GetRandomString(20).'" />'.$g->nl;
        $g->PrintHiddenFieldsFromUrl($curr_url);
        
        // draw hidden fields for Add Mode
        if($g->rid == -1){            
            foreach($g->columnsEditMode as $key => $val){
                if($g->GetFieldProperty($key, 'type') == 'hidden' && !$g->GetFieldProperty($key, 'visible')){
                    $hidden_fields .= $g->GetFieldValueByType('', 0, '', $key).$g->nl;
                }                
            }
        }

        if($g->drawTopButtons && $g->sortedColumnsSize > 5){
            $g->DrawColumnarButtons(0, $row, $c_curr_url, $req_mode, false);
        }        

        $g->TblOpen();
        // draw header
        $g->RowOpen($r);
        if($g->mode == 'view' && $g->modes['edit'][$g->mode] == '1'){
            // columnar layout in view mode
            $g->MainColOpen('center',0,'nowrap','10%', (($g->isPrinting) ? $g->cssClass.'_dg_td' : $g->cssClass.'_dg_th')); $g->MainColClose(); 
        }        
        $g->MainColOpen('center',0,'nowrap','32%', (($g->isPrinting) ? $g->cssClass.'_dg_td' : $g->cssClass.'_dg_th dg_th_tl')); echo '<b>'.(($g->fieldHeader != '') ? $g->fieldHeader : $g->lang['field']).'</b>'; $g->MainColClose(); 
        $g->MainColOpen('center',0,'nowrap','68%', (($g->isPrinting) ? $g->cssClass.'_dg_td' : $g->cssClass.'_dg_th dg_th_tr')); echo '<b>'.(($g->fieldHeaderValue != '') ? $g->fieldHeaderValue : $g->lang['field_value']).'</b>'; $g->MainColClose(); 
        $g->RowClose();        

        // set number of showing rows on the page
        if(($g->layouts['view'] == '0' || $g->layouts['view'] == '2') && ($g->layouts['edit'] == '1') && ($g->mode == 'edit')){
            $g->reqPageSize = ($g->multiRows > 0) ? $g->multiRows : 1;
        }elseif(($g->layouts['view'] == '0' || $g->layouts['view'] == '2') && ($g->layouts['edit'] == '1') && ($g->mode == 'details')){
            $g->reqPageSize = ($g->multiRows > 0) ? $g->multiRows : 1;
        }elseif(($g->layouts['view'] == '1') && ($g->layouts['edit'] == '1') && ($g->mode == 'edit')){
            $g->reqPageSize = 1;
        }elseif(($g->layouts['edit'] == '1') && ($g->mode == 'details')){
            $g->reqPageSize = 1;
        }         

        $first_field_name = ''; /* we need it to set a focus on this field */
        // draw rows in ADD MODE
        if($g->rid == -1){            
            foreach($g->columnsEditMode as $key => $val){
                if($g->GetFieldProperty($key, 'type') == 'hidden' && !$g->GetFieldProperty($key, 'visible')) { continue; } /* skip hidden fields */
                if(($first_field_name == '' && !preg_match('/delimiter/i', $key)) && (($g->mode === 'edit') || ($g->mode === 'add'))) $first_field_name = $key;
                if((int)$r % 2 == 0) $g->RowOpen($r, $g->rowColor[0]);
                else $g->RowOpen($r, $g->rowColor[1]);
                
                // prepare alignment for header and data
                $fp_header_align = $g->GetHeaderAlign($key, 'edit');
                $col_header_align = (!empty($fp_header_align)) ? $fp_header_align : (($g->direction == 'rtl') ? 'right' : 'left');
                $fp_align   = $g->GetFieldProperty($key, 'align');
                if($fp_align != '') $col_data_align = $fp_align;
                else $col_data_align = ($g->direction == 'rtl')?'right':'left';                    
                    
                if(preg_match('/delimiter/i', $key)){
                    $g->ColOpen(($g->direction == 'rtl')?'right':'left',2,'wrap');
                        echo $g->GetFieldProperty($key, 'inner_html');
                    $g->ColClose();
                }elseif($key == 'validator'){
                    $fp_for_field = $g->GetFieldProperty('validator', 'for_field');
                    $fp_header    = $g->GetFieldProperty('validator', 'header');
                    $fp_req_type  = $g->GetFieldProperty('validator', 'req_type');
                    // column's header
                    $g->ColOpen($col_header_align,0,'nowrap');                
                        echo $g->nbsp;echo '<b>'.$fp_header.'</b>';                        
                    $g->ColClose();
                    // column's data                    
                    $g->ColOpen($col_data_align,0,'nowrap');
                        echo $g->GetFieldValueByType('', 0, '', $fp_for_field, $fp_req_type);
                    $g->ColClose();
                }else{
                    if($g->CanViewField($key)){                    
                        // column's header
                        $g->ColOpen($col_header_align,0,'nowrap');                            
                            echo $g->nbsp;echo '<label for="'.$g->GetFieldRequiredType($key).$key.'"><b>'.$g->GetHeaderName($key).'</b></label>'.$g->PrepareTooltip($key, $g->mode);                        
                        $g->ColClose();
                        // column's data
                        $g->ColOpen($col_data_align,0,'nowrap');
                        if($g->IsForeignKey($key)){
                            echo $g->nbsp.$g->GetForeignKeyInput(-1, $key, '-1', 'edit').$g->nbsp;
                        }else{
                            echo $g->GetFieldValueByType('', 0, '', $key);
                        }
                        $g->ColClose();
                    }
                }
                $g->RowClose();
            }
        }
        
        // *** START DRAWING ROWS ----------------------------------------------
        for($r = $g->rowLower; (($g->rid != -1) && ($r < $g->rowUpper) && ($r < ($g->rowLower + $g->reqPageSize))); $r++){
            // draw space between rows
            if(($g->multiRows) > 0 && $r > $g->rowLower && $r > $g->rowLower){
                //$g->RowClose();
                $g->TblClose();
                $g->TblOpen('margin-top:12px;');
                // draw header
                $g->RowOpen($r); echo '<td height="0px" width="32%"></td><td height="0px" width="68%"></td>'; $g->RowClose();
            }
            $row = $g->dgFetchRow($g->dataSet);
            
            // draw column headers                     
            for($c_sorted = $g->colLower; $c_sorted < $g->sortedColumnsSize; $c_sorted++){
                // get current column's index (offset)
                $c = $g->sortedColumns[$c_sorted];
                
                // turn off highlighting if we use columnar layout in vew mode
                if($g->layoutType == 'view' && $g->layouts['view'] == '1'){
                    $g->isRowHighlightingAllowed = false;
                }            
                $c_field_name = $g->FieldNameByCase($g->GetFieldName($c));                
                // draw hidden fields for Edit Mode
                if($g->GetFieldProperty($c_field_name, 'type') == 'hidden' && !$g->GetFieldProperty($c_field_name, 'visible')){
                    if($g->multiRows > 0){
                        $rid_value = Helper::DecodeParameter($g->rids[$r], $g->safeMode);
                        $multirow_postfix = '_'.$rid_value;
                    }else{
                        $rid_value = $g->rid;
                        $multirow_postfix = '';
                    }
                    $hidden_fields .= $g->GetFieldValueByType($row[$c], $c, $row, '', '', '', $multirow_postfix).$g->nl;                    
                    continue;
                }

                if($r % 2 == 0) $g->RowOpen($r.$c_sorted, $g->rowColor[0]);
                else $g->RowOpen($r.$c_sorted, $g->rowColor[1]);

                // prepare alignment for header and data
                $fp_header_align = $g->GetHeaderAlign($c_field_name, 'edit');
                $col_header_align = (!empty($fp_header_align)) ? $fp_header_align : (($g->direction == 'rtl') ? 'right' : 'left');
                $fp_align   = $g->GetFieldProperty($c_field_name, 'align');
                if($fp_align != '') $col_data_align = $fp_align;
                else $col_data_align = ($g->direction == 'rtl') ? 'right' : 'left';                    
                if($g->CanViewField($c_field_name)){
                    if(($first_field_name == '' && !preg_match('/delimiter/i', $c_field_name)) && (($g->mode === 'edit') || ($g->mode === 'add'))) $first_field_name = $c_field_name;
                    // column headers
                    if($g->mode === 'view'){
                        // columnar layout for view mode
                        if($g->modes['edit'][$g->mode] == '1'){
                            $g->ColOpen($col_header_align,0,'nowrap');
                                if($c_sorted == $g->colLower){
                                    echo $g->DrawModeButton('edit', 'javascript:'.$g->uniquePrefix.'_doPostBack("edit","'.Helper::EncodeParameter($row[0], $g->safeMode).'");', $g->lang['edit'], $g->lang['edit_record'], "edit.gif", "''", false, $g->nbsp, '', true);
                                }
                            $g->ColClose();                                                
                        }
                        $g->ColOpen($col_header_align,0,'nowrap');                   
                        echo $g->nbsp;echo '<b>'.$g->GetHeaderName($c_field_name).'</b>'.$g->PrepareTooltip($c_field_name, $g->mode);                        
                        $g->ColClose();
                    }elseif($g->mode === 'edit'){
                        $g->ColOpen($col_header_align,0,'nowrap', '', '', '', 'padding-top:5px;vertical-align:top;');
                        echo $g->nbsp;echo '<label for="'.$g->GetFieldRequiredType($c_field_name).$c_field_name.'"><b>'.$g->GetHeaderName($c_field_name).'</b></label>'.$g->PrepareTooltip($c_field_name, $g->mode);
                        $g->ColClose();
                    }elseif($g->mode === 'details'){
                        $g->ColOpen($col_header_align,0,'nowrap');                   
                        echo $g->nbsp;echo '<b>'.$g->GetHeaderName($c_field_name).'</b>'.$g->PrepareTooltip($c_field_name, $g->mode);                        
                        $g->ColClose();
                    }
                    // column data 
                    if($g->mode === 'view'){
                        $fp_wrap = $g->GetFieldProperty($c_field_name, 'wrap', 'view');
                        $g->ColOpen($col_data_align, 0, $fp_wrap);
                            echo $g->GetFieldValueByType($row[$c], $c, $row);
                        $g->ColClose();                    
                    }elseif($g->mode === 'details'){
                        $g->ColOpen($col_data_align,0);
                        if($g->IsForeignKey($c_field_name)){
                            echo $g->GetForeignKeyInput($row[$g->GetFieldOffset($g->primaryKey)], $c_field_name, $row[$c],'view');
                        }else{
                            echo $g->GetFieldValueByType($row[$c], $c, $row);
                        }
                        $g->ColClose();
                    }elseif($g->mode === 'edit'){
                        // if we have multi-rows selected
                        // mr_2
                        if($g->multiRows > 0){
                            $rid_value = Helper::DecodeParameter($g->rids[$r], $g->safeMode);
                            $multirow_postfix = '_'.$rid_value;
                        }else{
                            $rid_value = $g->rid;
                            $multirow_postfix = '';
                        }
                        $ind = ($g->GetFieldOffset($g->primaryKey) != -1) ? $g->GetFieldOffset($g->primaryKey) : 0;
                        if(intval($rid_value) === intval($row[$ind])){
                            $g->ColOpen($col_data_align,0,'nowrap');
                            if($g->IsForeignKey($c_field_name)){
                                echo $g->nbsp.$g->GetForeignKeyInput($row[$ind], $c_field_name, $row[$c], 'edit', $multirow_postfix).$g->nbsp;
                            }else{
                                /// mr_3
                                echo $g->GetFieldValueByType($row[$c], $c, $row, '', '', '', $multirow_postfix);
                            }
                            $g->ColClose();
                        }else{
                            $g->ColOpen($col_data_align,0,'nowrap');
                            if($g->rid == -1){
                                // add new row                                    
                                if($g->IsForeignKey($c_field_name)){
                                    echo $g->nbsp.$g->GetForeignKeyInput(-1, $c_field_name, '-1', 'edit').$g->nbsp;
                                }else{
                                    echo $g->GetFieldValueByType('', $c, $row);
                                }                                    
                            }else{
                                if($g->IsForeignKey($c_field_name)){
                                    echo $g->nbsp.$g->GetForeignKeyInput($row[$g->GetFieldOffset($g->primaryKey)], $c_field_name, $row[$c], 'view', $multirow_postfix).$g->nbsp;
                                }else{
                                    /// mr_4
                                    echo $g->GetFieldValueByType($row[$c], $c, $row, '', '', '', $multirow_postfix);                                        
                                }                                    
                            }
                            $g->ColClose();
                        }
                    }
                }else{
                    $ind = 0;                        
                    // if we have multi-rows selected
                    // mr_22
                    if($g->multiRows > 0){
                        $rid_value = Helper::DecodeParameter($g->rids[$r], $g->safeMode);
                        $multirow_postfix = '_'.$rid_value;
                    }else{
                        $rid_value = $g->rid;
                        $multirow_postfix = '';
                    }
                    
                    foreach($g->columnsEditMode as $key => $val){
                        if($ind == $c_sorted){
                            if($g->mode != 'details'){
                                if($key == 'validator' && $g->GetFieldProperty('validator', 'visible')){ // customized rows (validator)
                                    $fp_for_field = $g->GetFieldProperty($key, 'for_field');
                                    $fp_header    = $g->GetFieldProperty($key, 'header');
                                    $fp_req_type  = $g->GetFieldProperty($key, 'req_type');
                                    $g->ColOpen($col_header_align,0,'nowrap');                   
                                        echo $g->nbsp;echo '<b>'.$fp_header.'</b>';                        
                                    $g->ColClose();
                                    $fp_wrap = $g->GetFieldProperty($c_field_name, 'wrap', 'view');
                                    $g->ColOpen($col_data_align, 0, $fp_wrap);
                                        $fp_for_field_offset = $g->GetFieldOffset($fp_for_field);
                                        if($fp_for_field_offset != '-1') echo $g->GetFieldValueByType($row[$fp_for_field_offset], $fp_for_field_offset, $row, '', $fp_req_type, '', $multirow_postfix);
                                    $g->ColClose();                    
                                }                                    
                            }
                            if(preg_match('/delimiter/i', $key)){ // customized rows (delimiter)                                
                                $g->ColOpen('',2,'wrap');
                                echo $g->GetFieldProperty($key, 'inner_html');
                                $g->ColClose();                                            
                            }
                        }
                        $ind++;
                    }
                }
                $g->RowClose();
            }// for            
        }
        // *** END DRAWING ROWS ------------------------------------------------
        
        $g->TblClose();
        echo '<br />';        
        if(($r == $g->rowLower) && ($g->rid != -1)){
            $g->NoDataFound();
            echo '<br /><center>';
            if($g->isPrinting){
                echo '<span class="'.$g->cssClass.'_dg_a"><b>'.$g->lang['back'].'</b></span>';                                        
            }else{
                echo '<a class="'.$g->cssClass.'_dg_a" href="javascript:history.go(-1);"><b>'.$g->lang['back'].'</b></a>';
            }                
            echo '</center>';        
        }else{            
            if($g->layoutType == 'view' && $g->layouts['view'] == '1'){
                // don't draw command buttons bar
            }else{
                if(($g->mode != 'details') && $g->ModeAllowed('edit')){
                    $g->SetEditFieldsFormScript();
                }
                $g->DrawColumnarButtons($r, $row, $c_curr_url, $req_mode);
            }
        }       
        echo $hidden_fields;        
        echo '</form>';
        if($g->isLoadingImageEnabled && $g->methodPostBack != 'ajax') echo $g->ScriptOpen().'document.getElementById("'.$g->uniqueRandomPrefix.'loading_image").style.display="none";'.$g->ScriptClose();
        
        if($g->pagingAllowed) $g->PagingSecondPart($g->arrLowerPaging, true, true, 'Lower');               
        if(($g->firstFieldFocusAllowed) && ($first_field_name != '')) echo $g->ScriptOpen().'_dgSetFocus(document.'.$g->uniquePrefix.'frmEditRow.'.$g->GetFieldRequiredType($first_field_name).$first_field_name.');'.$g->ScriptClose();                
    }
}