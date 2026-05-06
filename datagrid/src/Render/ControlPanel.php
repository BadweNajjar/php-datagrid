<?php
namespace DataGrid\Render;

use DataGrid\Helper;

/**
 * ControlPanel — renders the top-of-grid control bar.
 *
 * Includes the caption, add-record button, multi-row operation selector,
 * print / export buttons and language selector. Extracted from `DataGrid`
 * during the Phase 7 refactor.
 *
 * @package DataGrid\Render
 * @since   8.6.0
 */
class ControlPanel
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
        $req_export = $g->GetVariableAlpha('export');
        $req_mode   = $g->GetVariableAlpha('mode');        
        $myRef_window = $g->uniquePrefix.'myRef';
        
        if($g->filteringAllowed || $g->exportingAllowed || $g->printingAllowed || !empty($g->navigationBar)){
            $margin_bottom = 'margin-bottom:'.(($g->layoutType == 'edit') ? '7px;' : '5px;');
            echo '<table border="0" class="tblToolBar" align="center" style="margin-left:auto;margin-right:auto;'.$margin_bottom.'" width="'.$g->tblWidth[$g->mode].'" cellspacing="1" cellpadding="1">';
            echo '<tr><td align="left">';
            if($g->navigationBar != '') echo $g->navigationBar.' ';
            if($g->mode == 'edit') echo '<label class="'.$g->cssClass.'_dg_label">'.$g->lang['required_fields_msg'].'</label>';
            echo '</td>';        
            if($g->filteringAllowed && $g->filteringHideIconAllowed && (($g->mode != 'edit') && ($g->mode != 'details'))){
                echo '<td class="dg_exi_td dg_nowrap">';
				$hide_display = ''; 
				$unhide_display = 'display:none;';
				$g->SetFilteringState('not empty', $hide_display, $unhide_display);
                if(!$g->isPrinting){
                    echo '<a id="'.$g->uniquePrefix.'a_hide" style="cursor:pointer;'.$hide_display.'" onclick="return _dgHideUnHideFiltering(\'hide\', \''.$g->uniquePrefix.'\');"><img class="dg_opacity" src="'.$g->publicUrl.'images/search_hide.gif'.'" alt="'.htmlspecialchars($g->lang['hide_search']).'" title="'.htmlspecialchars($g->lang['hide_search']).'" /></a>';
                    echo '<a id="'.$g->uniquePrefix.'a_unhide" style="cursor:pointer;'.$unhide_display.'" onclick="return _dgHideUnHideFiltering(\'unhide\', \''.$g->uniquePrefix.'\');"><img aclass="dg_opacity" src="'.$g->publicUrl.'images/search_unhide.gif'.'" alt="'.htmlspecialchars($g->lang['unhide_search']).'" title="'.htmlspecialchars($g->lang['unhide_search']).'" /></a>';
                }
                echo '</td>'.$g->nl;
            }
            if(!$g->hideGridBeforeSearch || ($g->hideGridBeforeSearch && $g->onSubmitFilter != '')){
                if($g->exportingAllowed && $req_mode != 'add'){
                    if((($req_export == '') || !$g->isPrinting) && ($g->isPrinting == '')){
                        if($g->arrExportingTypes['csv'] == true){
                            echo '<td class="dg_exi_td">';
                            echo '<a style="cursor:pointer;" onclick="javascript:'.((!$g->isDemo) ? $g->uniquePrefix.'_doPostBack(\'export\',\'\',\''.$g->urlString.'&amp;'.$g->uniquePrefix.'export_type=csv\');' : '_dgBlockedInDemo();').'" class="'.$g->cssClass.'_dg_a">';
                            echo '<img class="dg_opacity" src="'.$g->publicUrl.'images/csv.gif'.'" alt="'.htmlspecialchars($g->lang['export_to_excel']).'" title="'.htmlspecialchars($g->lang['export_to_excel']).' (csv)" />';
                            echo '</a></td>'.$g->nl;
                        }
                        if($g->arrExportingTypes['xls'] == true){
                            echo '<td class="dg_exi_td">';
                            echo '<a style="cursor:pointer;" onclick="javascript:'.((!$g->isDemo) ? $g->uniquePrefix.'_doPostBack(\'export\',\'\',\''.$g->urlString.'&amp;'.$g->uniquePrefix.'export_type=xls\');' : '_dgBlockedInDemo();').'" class="'.$g->cssClass.'_dg_a">';
                            echo '<img class="dg_opacity" src="'.$g->publicUrl.'images/xls.gif'.'" alt="'.htmlspecialchars($g->lang['export_to_excel']).'" title="'.htmlspecialchars($g->lang['export_to_excel']).' (xls)" />';
                            echo '</a></td>'.$g->nl;                          
                        }
                        if($g->arrExportingTypes['pdf'] == true){
                            echo '<td class="dg_exi_td">';
                            echo '<a style="cursor:pointer;" onclick="javascript:'.((!$g->isDemo) ? $g->uniquePrefix.'_doPostBack(\'export\',\'\',\''.$g->urlString.'&amp;'.$g->uniquePrefix.'export_type=pdf\');' : '_dgBlockedInDemo();').'" class="'.$g->cssClass.'_dg_a">';
                            echo '<img class="dg_opacity" src="'.$g->publicUrl.'images/pdf.gif'.'" alt="'.htmlspecialchars($g->lang['export_to_pdf']).'" title="'.htmlspecialchars($g->lang['export_to_pdf']).'" />';
                            echo '</a></td>'.$g->nl;
                        }
                        if($g->arrExportingTypes['xml'] == true){
                            echo '<td class="dg_exi_td">';
                            echo '<a style="cursor:pointer;" onclick="javascript:'.((!$g->isDemo) ? $g->uniquePrefix.'_doPostBack(\'export\',\'\',\''.$g->urlString.'&amp;'.$g->uniquePrefix.'export_type=xml\');' : '_dgBlockedInDemo();').'" class="'.$g->cssClass.'_dg_a">';
                            echo '<img class="dg_opacity" src="'.$g->publicUrl.'images/xml.gif'.'" alt="'.htmlspecialchars($g->lang['export_to_xml']).'" title="'.htmlspecialchars($g->lang['export_to_xml']).'" />';
                            echo '</a></td>'.$g->nl;                                                      
                        }
                        if($g->arrExportingTypes['doc'] == true){
                            echo '<td class="dg_exi_td">';
                            echo '<a style="cursor:pointer;" onclick="javascript:'.((!$g->isDemo) ? $g->uniquePrefix.'_doPostBack(\'export\',\'\',\''.$g->urlString.'&amp;'.$g->uniquePrefix.'export_type=doc\');' : '_dgBlockedInDemo();').'" class="'.$g->cssClass.'_dg_a">';
                            echo '<img class="dg_opacity" src="'.$g->publicUrl.'images/doc.gif'.'" alt="'.htmlspecialchars($g->lang['export_to_word']).'" title="'.htmlspecialchars($g->lang['export_to_word']).'" />';
                            echo '</a></td>'.$g->nl;
                        }
                    }else{
                        echo '<td class="dg_exi_td"></td>'.$g->nl;
                    }                
                }
                if($g->printingAllowed && $req_mode != 'add' && $g->rowsTotal > 0){
                    if(($req_export == '') && !$g->isPrinting){
                        if($g->methodPostBack == 'ajax'){
                            $print_curr_url = $g->CombineUrl($g->GetVariableAlpha('mode'), $g->GetVariableAlphaNumeric('rid'));
                            $g->SetUrlString($print_curr_url, 'filtering', 'sorting', 'paging');
                        }else $print_curr_url = $g->urlString;
                        if($g->methodPostBack == 'ajax') $print_curr_url = str_replace('&', '&amp;', $print_curr_url);
                        echo '<td class="dg_exi_td">';
                        if($g->printType == 'new') echo '<a href="javascript:_dgPopupPrint(\''.$g->uniquePrefix.'\',\''.$g->cssClass.'\')" class="'.$g->cssClass.'_dg_a"><img class="dg_opacity" src="'.$g->publicUrl.'images/print.gif'.'" alt="'.htmlspecialchars($g->lang['printable_view']).'" title="'.htmlspecialchars($g->lang['printable_view']).'" /></a>';
                        else echo '<a style="cursor:pointer;" onclick="javascript:'.$g->uniquePrefix.'_doPostBack(\'print\',\'\',\''.$print_curr_url.'\');" class="'.$g->cssClass.'_dg_a"><img class="dg_opacity" src="'.$g->publicUrl.'images/print.gif'.'" alt="'.htmlspecialchars($g->lang['printable_view']).'" title="'.htmlspecialchars($g->lang['printable_view']).'" /></a>';
                        echo '</td>'.$g->nl;
                    }else{
                        echo '<td align="right"><a style="cursor:pointer;" onclick="window:print();" class="'.$g->cssClass.'_dg_a no_print" title="'.htmlspecialchars($g->lang['print_now_title']).'">'.$g->lang['print_now'].'</a></td>'.$g->nl;
                    }
                }
            }
            if($g->filteringAllowed && $g->refreshPageIconAllowed && ($g->mode == 'view') && ($req_mode != 'update') && ($req_mode != 'delete') && ($req_mode != 'clone')){
                if(!$g->isPrinting){
                    $href_string = ($g->methodPostBack == 'ajax') ? 'javascript:'.$g->uniquePrefix.'_doAjaxRequest(\''.(($g->QUERY_STRING!='') ? '?'.$g->QUERY_STRING : '').'\');' : 'window.location.href=self.location;';
                    echo '<td class="dg_exi_td"><a style="cursor:pointer" onclick="'.str_replace('&', '&amp;', $href_string).'" class="'.$g->cssClass.'_dg_a"><img class="dg_opacity" src="'.$g->publicUrl.'images/refresh.gif'.'" alt="'.htmlspecialchars($g->lang['refresh_page']).'" title="'.htmlspecialchars($g->lang['refresh_page']).'" /></a></td>';
                }
            }        
            echo '</tr>';
            echo '</table>';
        }else{
            if($g->mode == 'edit'){
                $margin_bottom = ($g->layoutType == 'edit') ? 'margin-bottom:7px;' : 'margin-bottom:5px;';
                echo '<table border="0" class="tblToolBar" style="margin-left:auto;margin-right:auto;'.$margin_bottom.'" width="'.$g->tblWidth[$g->mode].'" cellspacing="1" cellpadding="1">';
                echo '<tr><td align="left"><label class="'.$g->cssClass.'_dg_label">'.$g->lang['required_fields_msg'].'</label></td></tr>';
                echo '</table>'; 
            }
        }
    }
}