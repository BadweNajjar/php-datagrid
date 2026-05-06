<?php
namespace DataGrid\Filter;

use DataGrid\Helper;

/**
 * FilterBuilder — builds the filter form HTML.
 *
 * Stores the rendered HTML on the grid's `filteringOutput` property so the
 * existing `DrawFiltering()` can echo it. Extracted from
 * `DataGrid::PrepareFiltering()` during the Phase 6 refactor; behavior is
 * intentionally byte-identical to the legacy inline implementation.
 *
 * @package DataGrid\Filter
 * @since   8.6.0
 */
class FilterBuilder
{
    /** @var \DataGrid\DataGrid */
    private $grid;

    public function __construct($grid)
    {
        $this->grid = $grid;
    }

    public function Build()
    {
        $g = $this->grid;

        $selSearchType = $g->GetVariableInt('_ff_selSearchType');
        $req_mode = $g->GetVariableAlpha('mode');
        $cols = 0;
        $output = '';

        if (($req_mode == 'add') || ($req_mode == 'edit') || ($req_mode == 'details')) return false;

        if ($g->filteringAllowed) {
            $g->SetFilteringState();
            $horizontal_align = ($g->tblAlign[$g->mode] == 'center') ? 'margin-left:auto;margin-right:auto;' : '';
            if ($g->layouts['filter'] == '2') {
                $searchset_width = ($g->isPrinting) ? '93%' : '100%';
            } else {
                $searchset_width = '100%';
            }
            $output .= '<table id="'.$g->uniquePrefix.'searchset" style="margin-bottom:7px;'.$horizontal_align.' '.$g->hideDisplay.'" width="'.$searchset_width.'"><tr><td style="text-align:center;">'.$g->nl;
            if (!$g->isPrinting) {
                if ($g->layouts['filter'] == '2') {
                    $output .= '<div class="'.$g->cssClass.'_dg_fieldset" style="'.$horizontal_align.'width:'.$g->tblWidth['view'].';">'.$g->nl;
                } else {
                    $output .= '<fieldset class="'.$g->cssClass.'_dg_fieldset" dir="'.$g->direction.'" style="'.$horizontal_align.'width:'.$g->tblWidth['view'].';">'.$g->nl;
                    $output .= '<legend class="'.$g->cssClass.'_dg_legend">'.$g->lang['search_d'].'</legend>'.$g->nl;
                }
            }
            $internal_form_margin = ($g->layouts['filter'] == '2') ? 'margin:0px;padding:0px;' : 'margin:10px;';
            $internal_table_width = ($g->layouts['filter'] == '2') ? '100%' : $g->tblWidth[$g->mode];

            $output .= '<form name="frmFiltering'.$g->uniquePrefix.'" id="frmFiltering'.$g->uniquePrefix.'" action="" method="'.(($g->methodPostBack == 'get') ? 'get' : 'post').'" style="'.$internal_form_margin.'">'.$g->nl;
            $output .= $g->SaveHttpGetVars(false);
            $output .= '<table dir="'.$g->direction.'" class="'.$g->cssClass.'_dg_filter_table" border="0" id="filterTbl'.$g->uniquePrefix.'" style="margin-left:auto;margin-right:auto;" width="'.$internal_table_width.'" cellspacing="1" cellpadding="1">'.$g->nl;
            if ($g->layouts['filter'] == '0' || $g->layouts['filter'] == '2') $output .= '<tr>'.$g->nl;

            foreach ($g->arrFilterFields as $fldName => $fldValue) {

                $fp_on_js_event     = $g->GetFieldProperty($fldName, 'on_js_event', 'filter', 'normal');
                $fp_calendar_type   = $g->GetFieldProperty($fldName, 'calendar_type', 'filter', 'normal');
                $fp_width           = $g->GetFieldProperty($fldName, 'width', 'filter', 'normal');
                $fp_autocomplete    = $g->GetFieldProperty($fldName, 'autocomplete', 'filter', 'normal');
                $fp_handler         = $g->GetFieldProperty($fldName, 'handler', 'filter', 'normal');
                $fp_maxresults      = $g->GetFieldProperty($fldName, 'maxresults', 'filter', 'normal');
                $fp_shownoresults   = $g->GetFieldProperty($fldName, 'shownoresults', 'filter', 'normal');
                $fp_multiple        = $g->GetFieldProperty($fldName, 'multiple', 'filter', 'normal');
                $fp_multiple_size   = $g->GetFieldProperty($fldName, 'multiple_size', 'filter', 'normal', '4');
                $fp_default         = $g->GetFieldProperty($fldName, 'default', 'filter', 'normal', '');
                $fp_table           = trim($g->GetFieldProperty($fldName, 'table', 'filter', 'normal', ''));
                $fp_placeholder     = $g->GetFieldProperty($fldName, 'placeholder', 'filter', 'normal', '');
                $placeholder        = (!empty($fp_placeholder)) ? ' placeholder="'.$fp_placeholder.'"' : '';

                $multiple_parameters = ($fp_multiple) ? $multiple_parameters = 'multiple size="'.$fp_multiple_size.'"' : '';
                $fp_shownoresults = (empty($fp_shownoresults)) ? 'false' : 'true';
                $field_width = ($fp_width != '') ? 'width:'.$fp_width.';' : '';
                $fp_field_type = $g->GetFieldProperty($fldName, 'field_type', 'filter', 'normal');
                if ($fp_field_type != '') $fp_field_type = '_fo_'.$fp_field_type;

                if ($g->layouts['filter'] == '1') $output .= '<tr valign="middle">'.$g->nl;
                $fldValue_fields = isset($fldValue['field']) ? explode(',', $fldValue['field']) : [];
                $table_field_name = str_replace('.', '_d_', $fp_table).'_'.$fldValue_fields[0];

                $field_name_in_url = $g->uniquePrefix.'_ff_'.$table_field_name.$fp_field_type;

                if (isset($_REQUEST[$field_name_in_url]) AND ($_REQUEST[$field_name_in_url] != '')) {
                    if (!is_array($_REQUEST[$field_name_in_url])) $filter_field_value = stripcslashes($g->GetVariable($field_name_in_url, false));
                    else $filter_field_value = $_REQUEST[$field_name_in_url];
                } else {
                    $filter_field_value = '';
                }

                $filter_field_value_html = str_replace('"', '&#034;', $filter_field_value);
                $filter_field_value_html = str_replace("'", '&#039;', $filter_field_value_html);
                if (!is_array($filter_field_value)) $filter_field_value_html = urldecode($filter_field_value_html);
                $filter_field_operator =  $table_field_name.'_operator';

                $operator_name_in_url = $g->uniquePrefix.'_ff_'.$filter_field_operator.$fp_field_type;

                $output .= '<td ';
                if ($g->layouts['filter'] == '1') {
                    $output .= 'align="'.(($g->direction == 'rtl') ? 'left' : 'right').'" style="width:50%">'.$fldName;
                    $output .= '</td><td>'.$g->nbsp.'</td><td>';
                    $cols += 3;
                } elseif ($g->layouts['filter'] == '0' || $g->layouts['filter'] == '2') {
                    if ($g->tabularColumns != '') {
                        if ($g->layouts['filter'] == '0') {
                            $output .= 'align="'.(($g->direction == 'rtl') ? 'right' : 'left').'"';
                        } else $output .= 'align="'.(($g->direction == 'rtl') ? 'left' : 'right').'"';
                    } elseif ($g->layouts['filter'] == '2') {
                        $output .= 'align="'.(($g->direction == 'rtl') ? 'right' : 'left').'" style="padding-left:2px;padding-right:3px;" nowrap="nowrap"';
                    } else {
                        $output .= 'align="center"';
                    }
                    $output .= '> '.$fldName.': ';
                    $cols += 1;
                } else {
                    $output .= 'align="'.(($g->direction == 'rtl') ? 'left' : 'right').'" style="width:50%">'.$fldName;
                    $output .= '</td>'.$g->nl;
                    $output .= '<td>'.$g->nbsp.'</td>'.$g->nl;
                    $output .= '<td>';
                    $cols += 2;
                }
                if (isset($fldValue['show_operator']) && $fldValue['show_operator'] != false && $fldValue['show_operator'] != 'false') {
                    if (!$g->isPrinting) {
                        if (isset($_REQUEST[$operator_name_in_url]) && $_REQUEST[$operator_name_in_url] != '') {
                            $filter_operator = $g->RemoveBadChars($_REQUEST[$operator_name_in_url]);
                        } elseif (isset($fldValue['default_operator']) && in_array($fldValue['default_operator'], $g->arrFilteringOperators)) {
                            $filter_operator = $g->RemoveBadChars($fldValue['default_operator']);
                        } else {
                            $filter_operator = '=';
                        }
                        $onchange_filter_field = $g->PrepareFilterJsOnChange($fldValue['field'], $fp_table, $fp_field_type, 'operator');
                        $output .= '<select class="'.$g->cssClass.'_dg_select" name="'.$g->uniquePrefix.'_ff_'.$filter_field_operator.$fp_field_type.'" id="'.$g->uniquePrefix.'_ff_'.$filter_field_operator.$fp_field_type.'"'.$onchange_filter_field.'>';
                        $output .= '<option value="="'.(($filter_operator == '=')? ' selected="selected"' : '').'>'.$g->lang['='].'</option>';
                        $output .= '<option value="&gt;"'.(($filter_operator == '>')? ' selected="selected"' : '').'>'.$g->lang['>'].'</option>';
                        $output .= '<option value="&gt;="'.(($filter_operator == '>=')? ' selected="selected"' : '').'>'.$g->lang['>='].'</option>';
                        $output .= '<option value="&lt;"'.(($filter_operator == '<')? ' selected="selected"' : '').'>'.$g->lang['<'].'</option>';
                        $output .= '<option value="&lt;="'.(($filter_operator == '<=')? ' selected="selected"' : '').'>'.$g->lang['<='].'</option>';
                        $output .= '<option value="!="'.(($filter_operator == '!=')? ' selected="selected"' : '').'>'.$g->lang['!='].'</option>';
                        $output .= '<option value="like"'.(($filter_operator == 'like')? ' selected="selected"' : '').'>'.$g->lang['like'].'</option>';
                        $output .= '<option value="'.urlencode('like%').'"'.((urldecode($filter_operator) == 'like%')? ' selected="selected"' : ''). '>'.$g->lang['like%'].'</option>';
                        $output .= '<option value="'.urlencode('%like').'"'.((urldecode($filter_operator) == '%like')? ' selected="selected"' : '').'>'.$g->lang['%like'].'</option>';
                        $output .= '<option value="'.urlencode('%like%').'"'.((urldecode($filter_operator) == '%like%')? ' selected="selected"' : '').'>'.$g->lang['%like%'].'</option>';
                        $output .= '<option value="not like"'.(($filter_operator == 'not like')? ' selected="selected"' : '').'>'.$g->lang['not_like'].'</option>';
                        $output .= '</select>';
                    } else {
                        $output .= (isset($_REQUEST[$operator_name_in_url])) ? '['.$_REQUEST[$operator_name_in_url].']' : '';
                    }
                } else {
                    if (isset($fldValue['default_operator']) && in_array($fldValue['default_operator'], $g->arrFilteringOperators)) {
                        $filter_operator = urlencode($fldValue['default_operator']);
                    } else {
                        $filter_operator = '=';
                    }
                    $output .= '<input type="hidden" name="'.$g->uniquePrefix.'_ff_'.$filter_field_operator.$fp_field_type.'" id="'.$g->uniquePrefix.'_ff_'.$filter_field_operator.$fp_field_type.'" value="'.$filter_operator.'" />';
                }
                if ($g->layouts['filter'] == '1') {
                    $output .= '</td>'.$g->nl.'<td>'.$g->nbsp.'</td>'.$g->nl;
                    $output .= '<td style="width:50%" align="'.(($g->direction == 'rtl') ? 'right' : 'left').'">';
                    $cols += 2;
                } elseif ($g->layouts['filter'] == '0') {
                    $output .= '<br />';
                } elseif ($g->layouts['filter'] == '2') {
                    // nothing
                } else {
                    $output .= '</td>'.$g->nl.'<td>'.$g->nbsp.'</td>'.$g->nl;
                    $output .= '<td style="width:50%" align="'.(($g->direction == 'rtl') ? 'right' : 'left').'">';
                    $cols += 2;
                }
                $ff_type = (isset($fldValue['type'])) ? $fldValue['type'] : '';
                $ff_view_type = (isset($fldValue['view_type'])) ? $fldValue['view_type'] : '';
                if (!$g->isPrinting) {
                    $table_field_name = str_replace('.', '_d_', $fp_table);
                    switch ($ff_type) {
                        case 'textbox':
                            $onchange_filter_field = $g->PrepareFilterJsOnChange($fldValue['field'], $fp_table, $fp_field_type);
                            $count = 0;
                            foreach ($fldValue_fields as $fldValue_field) {
                                if ($count++ == 0) {
                                    $dgAutoSuggest_function = $dgAutoSuggest_function_text = '';
                                    if ($fp_autocomplete == 'true' || $fp_autocomplete === true) {
                                        $dgAutoSuggest_function = '_dgAutoSuggest(\''.$g->publicUrl.$fp_handler.'\',\''.(int)$fp_maxresults.'\','.$fp_shownoresults.',\''.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.'\');';
                                        $dgAutoSuggest_function_text = 'onfocus="'.$dgAutoSuggest_function.'"';
                                    }
                                    if ($g->onSubmitFilter == '' && $fp_default != '') $filter_field_value_html = $fp_default;
                                    $output .= '<input class="'.$g->cssClass.'_dg_textbox" style="'.$field_width.'" type="text" value="'.$filter_field_value_html.'" name="'.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.$fp_field_type.'" id="'.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.$fp_field_type.'" '.$dgAutoSuggest_function_text.' '.$onchange_filter_field.' '.$fp_on_js_event.$placeholder.' />';
                                    if ($fp_autocomplete == 'true' || $fp_autocomplete === true) {
                                        $g->jsCode[] = $dgAutoSuggest_function;
                                    }
                                } else {
                                    $filter_field_operator =  $table_field_name.'_'.$fldValue_field.'_operator';
                                    $output .= '<input type="hidden" name="'.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.'" id="'.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.'" value="'.$filter_field_value_html.'" />';
                                    $output .= '<input type="hidden" name="'.$g->uniquePrefix.'_ff_'.$filter_field_operator.$fp_field_type.'" id="'.$g->uniquePrefix.'_ff_'.$filter_field_operator.$fp_field_type.'" value="'.$filter_operator.'" />';
                                }
                            }
                            break;
                        case 'enum':
                        case 'dropdownlist':
                            $field_ddl_name = $g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue['field'];
                            $field_ddl_name .= ($fp_multiple) ? '[]' : '';
                            $tag_id = $g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue['field'].$fp_field_type;
                            $radio_count = '0';

                            if ($ff_view_type != 'radiobutton') $output .= ' <select class="'.$g->cssClass.'_dg_select" style="'.$field_width.'" name="'.$field_ddl_name.$fp_field_type.'" id="'.$tag_id.'" '.$fp_on_js_event.' '.$multiple_parameters.'>';
                            if ($ff_view_type != 'radiobutton' && !$fp_multiple) $output .= '<option value="">-- '.$g->lang['any'].' --</option>';

                            if (isset($fldValue['source']) && is_array($fldValue['source'])) {
                                foreach ($fldValue['source'] as $val => $opt) {
                                    $output .= ($ff_view_type != 'radiobutton') ? '<option value="'.$val.'" ' : '<input class="'.$g->cssClass.'_dg_radiobutton" type="radio" name="'.$field_ddl_name.$fp_field_type.'" id="'.$tag_id.$radio_count.'" value="'.$val.'" ';
                                    if ($filter_field_value != '') {
                                        if ($filter_field_value == $val) $output .= ($ff_view_type != 'radiobutton') ? 'selected="selected"' : 'checked="checked"';
                                    } elseif ($g->onSubmitFilter == '' && $fp_default != '') {
                                        if ($val == $fp_default) $output .= ($ff_view_type != 'radiobutton') ? 'selected="selected"' : 'checked="checked"';
                                    }
                                    $output .= ($ff_view_type != 'radiobutton') ? '>'.$opt.'</option>' : '/><label for="'.$tag_id.$radio_count.'" class="'.$g->cssClass.'_dg_label">'.$opt.'</label>';
                                    $radio_count++;
                                }
                            } else {
                                if (isset($fldValue['condition']) && trim($fldValue['condition']) !== '') {
                                    $where = $fldValue['condition'];
                                } else {
                                    $where = ' 1=1 ';
                                }
                                $fp_field_view = $g->GetFieldProperty($fldName, 'field_view', 'filter', 'normal');
                                $fp_show_count = $g->GetFieldPropertyBool($fldName, 'show_count', 'filter', false, false);
                                $order_by_field = (isset($fldValue['order_by_field']) && $fldValue['order_by_field'] != '') ? $fldValue['order_by_field'] : $fldValue['field'];
                                $order_type     = ((Helper::ConvertCase((isset($fldValue['order_type']) ? $fldValue['order_type'] : ''),'lower',$g->langName) == 'desc')?'DESC':'ASC');
                                if ($fp_field_view !== '') {
                                    if ($fp_show_count) {
                                        $sql = 'SELECT '.$fldValue['field'].', '.$fp_field_view.', COUNT('.$fldValue['field'].') as cnt FROM '.$fp_table.' WHERE '.$where.' GROUP BY '.$fldValue['field'].' ORDER BY '.$order_by_field.' '.$order_type.' ';
                                    } else {
                                        $sql = 'SELECT DISTINCT '.$fldValue['field'].' '.(($fldValue['field'] != $fp_field_view) ? ', '.$fp_field_view : '').' FROM '.$fp_table.' WHERE '.$where.' ORDER BY '.$order_by_field.' '.$order_type.' ';
                                    }
                                    $dSet = $g->dgQuery($sql, 'select', 'Retrieve data for filtering fields');
                                    if ($g->CheckIsError($dSet)) {
                                        $g->AddErrors($dSet);
                                    } else {
                                        while ($row = $g->dgFetchRow($dSet)) {
                                            $selected = '';
                                            $ff_name = $fp_field_view;
                                            if (preg_match('/ as /i', strtolower($ff_name))) $ff_name = substr($ff_name, strpos(strtolower($ff_name), ' as ')+4);
                                            if ((is_array($filter_field_value) && in_array($row[$fldValue['field']], $filter_field_value)) ||
                                              (!is_array($filter_field_value) && ($row[$fldValue['field']] == $filter_field_value))) {
                                                if ($filter_field_value != '') $selected = ($ff_view_type != 'radiobutton') ? ' selected="selected"' : ' checked="checked"';
                                            } elseif ($g->onSubmitFilter == '' && $fp_default != '') {
                                                if ($fp_default == $row[$fldValue['field']]) $selected = ($ff_view_type != 'radiobutton') ? ' selected="selected"' : ' checked="checked"';
                                            }
                                            if ($fp_show_count) {
                                                $option_text = $row[$ff_name].' ('.$row['cnt'].')';
                                            } else {
                                                $option_text = $row[$ff_name];
                                            }
                                            if ($ff_view_type != 'radiobutton') $output .= '<option'.$selected.' value="'.$row[$fldValue['field']].'">'.$option_text.'</option>';
                                            else $output .= '<input'.$selected.' class="'.$g->cssClass.'_dg_radiobutton" type="radio" name="'.$field_ddl_name.$fp_field_type.'" id="'.$tag_id.$radio_count.'" value="'.$row[$fldValue['field']].'" /><label for="'.$tag_id.$radio_count.'" class="'.$g->cssClass.'_dg_label">'.$option_text.'</label>';
                                            $radio_count++;
                                        }
                                    }
                                } else {
                                    if ($fp_show_count) {
                                        $sql = 'SELECT '.$fldValue['field'].', COUNT('.$fldValue['field'].') as cnt FROM '.$fp_table.' WHERE '.$where.' GROUP BY '.$fldValue['field'].' ORDER BY '.$order_by_field.' '.$order_type.' ';
                                    } else {
                                        $sql = 'SELECT DISTINCT '.$fldValue['field'].' FROM '.$fp_table.' WHERE '.$where.' ORDER BY '.$order_by_field.' '.$order_type.' ';
                                    }
                                    $dSet = $g->dgQuery($sql, 'select', 'Retrieve data for filtering fields');
                                    if ($g->CheckIsError($dSet)) {
                                        $g->AddErrors($dSet);
                                    } else {
                                        while ($row = $g->dgFetchRow($dSet)) {
                                            $selected = '';
                                            if (!is_array($filter_field_value)) $filter_field_value = urldecode($filter_field_value);
                                            if ((is_array($filter_field_value) && in_array($row[$fldValue['field']], $filter_field_value)) ||
                                              (!is_array($filter_field_value) && ($row[$fldValue['field']] === $filter_field_value))) {
                                                if ($filter_field_value != '') $selected = ($ff_view_type != 'radiobutton') ? ' selected="selected"' : ' checked="checked"';
                                            } elseif ($g->onSubmitFilter == '' && $fp_default != '') {
                                                if ($fp_default == $row[$fldValue['field']]) $selected = ($ff_view_type != 'radiobutton') ? ' selected="selected"' : ' checked="checked"';
                                            }
                                            if ($fp_show_count) {
                                                $option_text = $row[$fldValue['field']].' ('.$row['cnt'].')';
                                            } else {
                                                $option_text = $row[$fldValue['field']];
                                            }
                                            if ($ff_view_type != 'radiobutton') $output .= '<option'.$selected.' value="'.$row[$fldValue['field']].'">'.$option_text.'</option>';
                                            else $output .= '<input'.$selected.' class="'.$g->cssClass.'_dg_radiobutton" type="radio" name="'.$field_ddl_name.$fp_field_type.'" id="'.$tag_id.$radio_count.'" value="'.$row[$fldValue['field']].'" /><label for="'.$tag_id.$radio_count.'" class="'.$g->cssClass.'_dg_label">'.$option_text.'</label>';
                                            $radio_count++;
                                        }
                                    }
                                }
                                //[#0039] - without this line datagrid doesn't work with ddl in filtering fields for oci8
                                if (in_array($g->dgGetDbDriverType(), ['oci', 'oci8'])) $g->BindDataSource($g->dbHandler, $g->sqlView);
                            }
                            if ($ff_view_type != 'radiobutton') $output .= '</select>';
                            break;
                        case 'calendar':
                            $fldValue_fields = str_replace(' ', '', $fldValue['field']);
                            $fldValue_fields = explode(',', $fldValue_fields);
                            $date_format = isset($fldValue['date_format']) ? $fldValue['date_format'] : '';
                            if (!in_array($date_format, ['date', 'datedmy', 'datemdy', 'datetime', 'time'])) $date_format = 'date';
                            $onchange_filter_field = '';

                            if (preg_match('/datetime/i', $date_format)) $maxlength = '19';
                            elseif (preg_match('/time/i', $date_format)) $maxlength = '8';
                            else $maxlength = '10';

                            $count = 0;
                            foreach ($fldValue_fields as $fldValue_field) {
                                if ($count++ > 0) { $onchange_filter_field .= 'onchange="document.getElementById(\''.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.'\').value=this.value;"'; }
                            }
                            $count = 0;
                            foreach ($fldValue_fields as $fldValue_field) {
                                if ($count++ == 0) {
                                    $title_format = $g->GetDateFormatForFilteringCal($date_format);
                                    if ($g->onSubmitFilter == '' && $fp_default != '') $filter_field_value = $fp_default;
                                    $output .= '<input placeholder="'.$title_format.'" type="text" class="'.$g->cssClass.'_dg_textbox" style="'.$field_width.'" value="'.Helper::StripQuotes($filter_field_value).'" name="'.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.$fp_field_type.'" id="'.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.$fp_field_type.'" maxlength="'.$maxlength.'" '.$onchange_filter_field.' '.$fp_on_js_event.' />';
                                } else {
                                    $filter_field_operator = $table_field_name.'_'.$fldValue_field.'_operator';
                                    $output .= '<input type="hidden" name="'.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.'" id="'.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.'" value="'.$filter_field_value.'" />';
                                    $output .= '<input type="hidden" name="'.$g->uniquePrefix.'_ff_'.$filter_field_operator.$fp_field_type.'" id="'.$g->uniquePrefix.'_ff_'.$filter_field_operator.$fp_field_type.'" value="'.$filter_operator.'" />';
                                }
                            }
                            if ($fp_calendar_type == 'floating') {
                                $if_format = $g->GetDateFormatForFloatingCal($date_format);
                                $show_time = ($date_format == 'datetime' || $date_format == 'time') ? 'true' : 'false';
                                $textbox_id = $g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.$fp_field_type;
                                $g->jsCode[] = 'Calendar.setup({firstDay : '.$g->weekStartingDay.', inputField : \''.$textbox_id.'\', ifFormat : \''.$if_format.'\', showsTime : '.$show_time.', button : \'img_'.$textbox_id.'\'});';
                                $output .= '<img id="img_'.$textbox_id.'" src="'.$g->publicUrl.'images/cal.gif" alt="'.htmlspecialchars($g->lang['set_date']).'" title="'.htmlspecialchars($g->lang['set_date']).'" align="top" style="cursor:pointer;margin:3px;margin-left:6px;margin-right:6px;border:0px;" ';
                                if ($g->methodPostBack == 'ajax') $output .= 'onmouseover="javascript:'.$g->uniquePrefix.'_doOpenFloatingCalendar(\''.$textbox_id.'\', \''.$if_format.'\', '.$show_time.');"';
                                $output .= ' />'.$g->nl;
                            } else {
                                $output .= '<a class="'.$g->cssClass.'_dg_a2" href="javascript:_dgOpenCalendar(\''.(($g->ignoreBaseTag) ? $g->HTTP_HOST.'/' : '').$g->publicUrl.'\',\'separator='.$g->dtSeparator.'\',\'frmFiltering'.$g->uniquePrefix.'\',\'\',\''.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue_field.$fp_field_type.'\',\''.$date_format.'\')"><img src="'.$g->publicUrl.'images/cal.gif" alt="'.htmlspecialchars($g->lang['set_date']).'" title="'.htmlspecialchars($g->lang['set_date']).'" align="top" style="border:0px;margin:3px 6px;" /></a>'.$g->nbsp;
                            }
                            break;
                        default:
                            $output .= '<input class="'.$g->cssClass.'_dg_textbox" type="text" value="'.$filter_field_value_html.'" name="'.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue['field'].'" id="'.$g->uniquePrefix.'_ff_'.$table_field_name.'_'.$fldValue['field'].'" />';
                            break;
                    }
                } else {
                    $output .= $filter_field_value;
                }
                $output .= '</td>'.$g->nl;
                if ($g->layouts['filter'] == '1') $output .= '</tr>'.$g->nl;
                elseif (($g->layouts['filter'] == '0' || $g->layouts['filter'] == '2') && $g->tabularColumns != '' && $g->tabularColumns > 0) {
                    if ($cols % $g->tabularColumns == 0) $output .= '</tr><tr>'.$g->nl;
                }
            }
            if ($g->layouts['filter'] == '2') $cols++;
            if ($g->layouts['filter'] == '0') $output .= '</tr>'.$g->nl;
            if ($g->layouts['filter'] != '2') $output .= '<tr><td '.(($cols > 0) ? 'colspan="'.$cols.'"' : '').' style="height:6px" align="center"></td></tr>'.$g->nl;
            if ($g->layouts['filter'] != '2') $output .= '<tr>';
            $output .= '<td '.(($g->layouts['filter'] != '2' && $cols > 0) ? 'colspan="'.$cols.'"' : '').' align="'.(($g->layouts['filter'] == '2') ? (($g->direction == 'rtl') ? 'left' : 'right') : 'center').'"'.(($g->layouts['filter'] == '2') ? ' width="55%"' : '').'>';
            if (count($g->arrFilterFields) > 1) {
                if ($g->showSearchType) { $output .= $g->lang['search_type'].': '; }
                if (!$g->isPrinting) {
                    if ($g->showSearchType) {
                        $output .= '<select class="'.$g->cssClass.'_dg_select" name="'.$g->uniquePrefix.'_ff_selSearchType" id="'.$g->uniquePrefix.'_ff_selSearchType">';
                        $output .= '<option value="0" '.((($selSearchType != '') && ($selSearchType == 0)) ? 'selected="selected"' : '').'>'.$g->lang['and'].'</option>';
                        $output .= '<option value="1" '.(($selSearchType == 1) ? 'selected="selected"' : '').'>'.$g->lang['or'].'</option>';
                        $output .= '</select>';
                    } else {
                        $output .= '<input type="hidden" name="'.$g->uniquePrefix.'_ff_selSearchType" id="'.$g->uniquePrefix.'_ff_selSearchType" value="0" />';
                    }
                } else {
                    if (($selSearchType != '') && ($selSearchType == 0)) {
                        $output .= '[and]';
                    } elseif ($selSearchType == 1) {
                        $output .= '[or]';
                    } else {
                        $output .= '[none]';
                    }
                }
            }
            if (!$g->isPrinting) {
                if ($g->onSubmitFilter != '') {
                    $output .= ' <input class="'.$g->cssClass.'_dg_button" type="button" value="'.$g->lang['reset'].'" onclick="javascript:'.$g->uniquePrefix.'_doPostBack(\'reset\');" /> ';
                }
                $output .= ' <input class="'.$g->cssClass.'_dg_button" style="margin-left:5px;margin-right:5px;" type="submit" name="'.$g->uniquePrefix.'_ff_onSUBMIT_FILTER" id="'.$g->uniquePrefix.'_ff_onSUBMIT_FILTER" value="'.$g->lang['search'].'" /> ';
            }
            $output .= '</td>'.$g->nl.'</tr>'.$g->nl;
            if ($g->layouts['filter'] != '2') $output .= '<tr><td '.(($cols > 0) ? 'colspan="'.$cols.'"' : '').' style="height:5px" align="center"></td></tr>'.$g->nl;
            $output .= $g->TblClose(false);
            $output .= '</form>'.$g->nl;
            if (!$g->isPrinting) {
                if ($g->layouts['filter'] == '2') {
                    $output .= '</div>'.$g->nl;
                } else {
                    $output .= '</fieldset>'.$g->nl;
                }
            }
            $output .= '</td></tr></table>'.$g->nl;
        }
        $g->filteringOutput = $output;
    }
}
