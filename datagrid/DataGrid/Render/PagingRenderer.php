<?php
namespace DataGrid\Render;

use DataGrid\Helper;

/**
 * PagingRenderer — renders the page-size selector and page-number navigation.
 *
 * Splits paging UI into a "first part" (page-size selector + record range)
 * and a "second part" (numbered page links + prev/next). Extracted from
 * `DataGrid::PagingFirstPart()` / `DataGrid::PagingSecondPart()` during the
 * Phase 7 refactor.
 *
 * @package DataGrid\Render
 * @since   8.6.0
 */
class PagingRenderer
{
    /** @var \DataGrid\DataGrid */
    private $grid;

    public function __construct($grid)
    {
        $this->grid = $grid;
    }

    public function FirstPart()
    {
        $g = $this->grid;
        // (1) if we got a wrong number of page -> set page=1
        $req_page_num = '';
        $req_page_size = $g->GetVariableInt('page_size');
        $req_p = $g->GetVariableInt('p');
        if(!empty($req_page_size)) $g->reqPageSize = $req_page_size;
        if(!$g->reqPageSize) $g->reqPageSize = 1;
        if($req_p != '') $req_page_num  = $req_p;
        
        if(is_numeric($req_page_num)){
            if($req_page_num > 0) $g->pageCurrent = $req_page_num;
            else $g->pageCurrent = 1;
        }else{
            $g->pageCurrent = 1;
        }
        // (2) set pagesTotal & pageCurrent vars for paging
        if($g->rowsTotal > 0){
            if(is_float($g->rowsTotal / $g->reqPageSize)){
                $g->pagesTotal = intval(($g->rowsTotal / $g->reqPageSize) + 1);
            }else{
                $g->pagesTotal = intval($g->rowsTotal / $g->reqPageSize);
            }
        }else{
            $g->pagesTotal = 0;
        }   
        if($g->pageCurrent > $g->pagesTotal) $g->pageCurrent = $g->pagesTotal;        
    }

    public function SecondPart($lu_paging = false, $upper_br = false, $lower_br = false, $type = '1')
    {
        $g = $this->grid;
        // (4) display paging line
        if(!$g->isPrinting) {$a_tag = 'a';} else {$a_tag = 'span';};
        $text = '';
        $horizontal_align = ($g->tblAlign[$g->mode] == 'center') ? 'margin-left:auto;margin-right:auto;' : '';
        $table_align      = ($g->tblAlign[$g->mode] == 'center') ? 'align="center"' : '';
        if($g->pagesTotal >= 1){
            $href_string = '';
            $g->SetUrlString($href_string, '', 'sorting', '', '&');
            $href_string = str_replace('&', '&amp;', $href_string);
            $href_string .= '&amp;'.$g->uniquePrefix.'page_size='.$g->reqPageSize;
            //[#0012 - 3] - start
            // new code: suggested by kalak
            $href_string = $g->AddArrayParams($href_string);
            //[#0012 - 3] - end
            if($lu_paging['results'] || $lu_paging['pages'] || $lu_paging['page_size']){
                if($upper_br) $text .= '';  
                $text .= $g->nl.'<form name="frmPaging'.$g->uniquePrefix.$type.'" id="frmPaging'.$g->uniquePrefix.$type.'" action="" style="margin:0px auto;padding:0px;">';
                $text .= '<table class="'.$g->cssClass.'_dg_paging_table" dir="'.$g->direction.'" style="height:7px;width:'.$g->tblWidth[$g->mode].';'.$horizontal_align.'" '.$table_align.' border="0">';
                $text .= '<tr>';
                $text .= '<td align="'.$lu_paging['results_align'].'" class="dg_nowrap">';
                if($lu_paging['results']){
                    $text .= ' '.$g->lang['results'].': ';
                    if(($g->pageCurrent * $g->reqPageSize) <= $g->rowsTotal) $total = ($g->pageCurrent * $g->reqPageSize);
                    else $total = $g->rowsTotal;
                    $text .= number_format(($g->pageCurrent * $g->reqPageSize - $g->reqPageSize + 1), 0, '', ',').' - '.number_format($total, 0, '', ',');
                    $text .= ' '.$g->lang['of'].' ';
                    $text .= number_format($g->rowsTotal, 0, '', ',').' ';
                }            
                $text .= '</td>';
                $text .= '<td align="'.$lu_paging['pages_align'].'" class="dg_nowrap">';
            
                if($lu_paging['pages']){
                    $text .= '<table class="'.$g->cssClass.'_dg_paging_table" border="0" style="padding:0px;margin:0px;border:0px;" dir="'.$g->direction.'"><tr>';
                    $text .= '<td>';
                    $text .= ' '.$g->lang['pages'].': ';
                    // dropdown paging option                
                    if($g->dropdownPaging === true){                        
                        $paging_array = Helper::GetPageSelections($g->rowsTotal, $g->pageCurrent, $g->pagesTotal);
                        $ddl_onchange = 'javascript:'.$g->uniquePrefix.'_doPostBack(\'paging\',\'\',\''.$href_string.'&amp;'.$g->uniquePrefix.'p=\'+this.value)';
                        $text .= $g->DrawDropDownList($g->uniquePrefix.'dropdownPaging'.$type, '', $paging_array, $g->pageCurrent, '', '', '', '', 'onchange="'.$ddl_onchange.'"');
                        $text .= '</td><td>';
                    }
                    $href_prev1 = $href_prev2 = $href_first = '';
                    if($g->pageCurrent > 1){
                        $href_prev1 = 'href="javascript:void(\'page=previous\');" onclick="javascript:'.$g->uniquePrefix.'_doPostBack(\'paging\',\'\',\''.$href_string.'&amp;'.$g->uniquePrefix.'p='.($g->pageCurrent - 1).'\')"';
                        $href_prev2 = 'href="javascript:void(\'page=previous\');" onclick="javascript:'.$g->uniquePrefix.'_doPostBack(\'paging\',\'\',\''.$href_string.'&amp;'.$g->uniquePrefix.'p='.$g->pageCurrent.'\')"';
                        $href_first = 'href="javascript:void(\'page=first\');" onclick="javascript:'.$g->uniquePrefix.'_doPostBack(\'paging\',\'\',\''.$href_string.'&amp;'.$g->uniquePrefix.'p=1\')"';
                    }
                    $text .= ' <'.$a_tag.(($href_first != '') ? ' title="'.htmlspecialchars($g->lang['first']).'" class="'.$g->cssClass.'_dg_p_a no_underline"' : ' class="'.$g->cssClass.'_dg_p_a_empty"').' '.$href_first.'>'.$g->firstArrow.'</'.$a_tag.'>';
                    if($g->pageCurrent > 1) $text .= ' <'.$a_tag.(($href_prev1 != '') ? ' class="'.$g->cssClass.'_dg_p_a no_underline" title="'.htmlspecialchars($g->lang['previous']).'"' : ' class="'.$g->cssClass.'_dg_p_a_empty"').' '.$href_prev1.'>'.$g->previousArrow.'</'.$a_tag.'>';
                    else $text .= ' <'.$a_tag.(($href_prev2 != '') ? ' class="'.$g->cssClass.'_dg_p_a no_underline" title="'.htmlspecialchars($g->lang['previous']).'"' : ' class="'.$g->cssClass.'_dg_p_a_empty"').' '.$href_prev2.'>'.$g->previousArrow.'</'.$a_tag.'>';
                    $text .= ' ';
                    $text .= '</td><td>';
                    $low_window_ind = $g->pageCurrent - 3;
                    $high_window_ind = $g->pageCurrent + 3;
                    if($low_window_ind > 1){ $start_index = $low_window_ind; $text .= '...'; }
                    else $start_index = 1;
                    if($high_window_ind < $g->pagesTotal) $end_index = $high_window_ind;
                    else $end_index = $g->pagesTotal;
                    for($ind=$start_index; $ind <= $end_index; $ind++){
                        $href_middle = 'javascript:'.$g->uniquePrefix.'_doPostBack(\'paging\',\'\',\''.$href_string.'&amp;'.$g->uniquePrefix.'p='.$ind.'\')';
                        if($ind == $g->pageCurrent) $text .= ' <'.$a_tag.' class="'.$g->cssClass.'_dg_p_a dg_underline" style="text-decoration:underline;" title="'.htmlspecialchars($g->lang['current']).'" href="javascript:void(\'page='.$ind.'\');" onclick="'.$href_middle.'"><b>'.$ind.'</b></'.$a_tag.'>'; 
                        else $text .= ' <'.$a_tag.' class="'.$g->cssClass.'_dg_p_a" href="javascript:void(\'page='.$ind.'\');" onclick="'.$href_middle.'">'.$ind.'</'.$a_tag.'>';
                        if($ind < $g->pagesTotal) $text .= '<span class="'.$g->cssClass.'_dg_p_comma">,</span> ';
                        else $text .= ' ';
                    }
                    if($high_window_ind < $g->pagesTotal) $text .= '...';
                    $href_next1 = $href_next2 = $href_last = '';
                    if($g->pageCurrent < $g->pagesTotal){
                        $href_next1 = 'href="javascript:void(\'page=next\');" onclick="javascript:'.$g->uniquePrefix.'_doPostBack(\'paging\',\'\',\''.$href_string.'&amp;'.$g->uniquePrefix.'p='.($g->pageCurrent + 1).'\')"';
                        $href_next2 = 'href="javascript:void(\'page=next\');" onclick="javascript:'.$g->uniquePrefix.'_doPostBack(\'paging\',\'\',\''.$href_string.'&amp;'.$g->uniquePrefix.'p='.$g->pageCurrent.'\')"';
                        $href_last  = 'href="javascript:void(\'page=last\');" onclick="javascript:'.$g->uniquePrefix.'_doPostBack(\'paging\',\'\',\''.$href_string.'&amp;'.$g->uniquePrefix.'p='.$g->pagesTotal.'\')"';
                    }
                    $text .= '</td><td>';
                    if($g->pageCurrent < $g->pagesTotal) $text .= ' <'.$a_tag.(($href_next1 != '') ? ' class="'.$g->cssClass.'_dg_p_a no_underline" title="'.htmlspecialchars($g->lang['next']).'"' : ' class="'.$g->cssClass.'_dg_p_a_empty"').' '.$href_next1.'>'.$g->nextArrow.'</'.$a_tag.'>';
                    else $text .= ' <'.$a_tag.(($href_next2 != '') ? ' class="'.$g->cssClass.'_dg_p_a no_underline" title="'.htmlspecialchars($g->lang['next']).'"' : ' class="'.$g->cssClass.'_dg_p_a_empty"').' '.$href_next2.'>'.$g->nextArrow.'</'.$a_tag.'>';
                    $text .= ' <'.$a_tag.(($href_last != '') ? ' class="'.$g->cssClass.'_dg_p_a no_underline" title="'.htmlspecialchars($g->lang['last']).'"' : ' class="'.$g->cssClass.'_dg_p_a_empty"').' '.$href_last.'>'.$g->lastArrow.'</'.$a_tag.'>';
                    $text .= '</td>';
                    $text .= '</tr></table>';
                }
            
                $text .= '</td>';
                $text .= '<td align="'.$lu_paging['page_size_align'].'" class="dg_nowrap">';            
                if($lu_paging['page_size']){
                    $text .= ' '.$g->lang['page_size'].': ';
                    //req_sort_type
                    $url_page_size = ($g->GetVariableInt('page_size') != '') ? $g->GetVariableInt('page_size') : $g->reqPageSize;
                    $href_string = $g->RemoveParameterFromURL($href_string, '&amp;'.$g->uniquePrefix.'page_size', $url_page_size);
                    $text .= $g->DrawDropDownList('page_size'.$type, '_doPostBack(\'page_resize\',\'\',\''.$href_string.'&amp;'.$g->uniquePrefix.'page_size=\'+document.frmPaging'.$g->uniquePrefix.$type.'.page_size'.$type.'.value)', $g->arrPages, $g->reqPageSize);
                }            
                $text .= '</td>';
                $text .= '</tr>';            
                $text .= '</table>';
                $text .= '</form>'.$g->nl;
                if($lower_br) $text .= ''; //<br />
            }
            echo $text;
        }
    }
}