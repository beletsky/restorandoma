<?php
################################################################################
#                                                                              #
#   PhpSiteLib. ���������� ��� ������� ���������� ������                       #
#                                                                              #
#   Copyright (�) 2002, Ilya Blagorodov (blagorodov.ru)                        #
#                                                                              #
#   psl_utils.inc.php (String, Date and Time utilities)                        #
#   ������� �������� �����, �������������� �������                             #
#                                                                              #
################################################################################
/* 
     ������� ��������, ��� ������� ������ ��������:
   bool string_is_email (string string)    - ������� email
   bool string_is_login (string string)    - �������, ����� ��� ������ "������ �������"
   bool string_is_int (string string)      - ����� ������ (������� �������������)
   bool string_is_float (string string)    - ������ � ��. ������ (���. �����. � E)
   bool string_is_id (string string)       - ID (� ��), �.�. 
   bool string_is_date (string string)     - ����� � ������� 'dd.mm.yy[yy]'
   bool string_is_time (string string)     - �������� � ������� 'hh:mm[:ss]'
   bool string_is_datetime (string string) - ����� � �������� 'dd.mm.yy[yy] hh:mm[:ss]'
   
     ������� �������������� �����:
   string string_to_html (string string)   - ������ htmlspecialchars � �������� ��������� ������� ������� �� �� ���� (�������� { })
   string string_to_money (string string)  - ��������������� ������ � ������ � ������ ������� xx xxx.xx (��� ������ �����)
   string string_to_html_special(string string, string tags = '<a><b><i><u><p>', bool donl2br = true) 
                                                         - ������� ���� tags, ��������� \n � <br> (���� donl2br)

     ������� �������������� ��� �� (ANSI SQL):
   string sql_to_date (string string)      - ���� � ������� ANSI SQL � 'dd.mm.yyyy'
   string sql_to_datestr (string string)   - ���� � ������� ANSI SQL � 'dd ���_�����_�������� yyyy'
   string sql_to_datetime (string string)  - ���� � ������� ANSI SQL � 'dd.mm.yyyy hh:mm:ss'
   string sql_to_datetime_nosec (string string) - ���� � ������� ANSI SQL � 'dd.mm.yyyy hh:mm'
   string sql_to_time (string string, bool withSeconds = true)      - ������� � ������� ANSI SQL � 'hh:mm:ss'
      int sql_to_ts (string string)        - ���� � ������� ANSI SQL � UNIX timestamp
   string date_to_sql (string string)      - ���� � ������� 'dd.mm.yy[yy]' � ANSI SQL
   string datetime_to_sql (string string)  - ���� � ������� 'dd.mm.yy[yy] hh:mm[:ss]' � ANSI SQL
   
     ������� �������������� ��� �� (unix timestamp):
   string ts_to_date (int timestamp)       - ���� � ������� � timestamp � ���� 'dd.mm.yyyy'
   string ts_to_time (int timestamp)       - ���� � ������� � timestamp �� ����� 'hh:mm:ss'
   string ts_to_datetime (int timestamp)   - ���� � ������� � timestamp � ���� � ����� 'dd.mm.yyyy hh:mm:ss'
   string ts_to_sql (int timestamp)        - ���� � ������� � timestamp � ���� � ����� ANSI SQL
   
     ������� ��� ������ � ������������ (���������� ���� � ����������� �� �����):
   string get_month_name (int month)       - ���������� �������� �������� ������ � ���������� ���������� (� ������� �����)
   string get_month_name_low (int month)   - ���������� �������� �������� ������ � ���������� ���������� (� ���. �����)
   string get_word_form (int number,       - �����. ����� � ���������� ���������� ��� ������ ����� (number)
                         array words)        words - ������ �� �������, �������� ("������", "������", "�������")
   
     ������� ��� ������ � ��������:
   float get_micro_time (void)             - ���������� timestamp � �������������� (��������, ��� �������� ��������)
   float get_sql_days (string date_start,  - ���������� ���������� ����, ��������� � ���� date_start �� ���� date_end. ���� � SQL ������� (��� �������!)
                       string date_end)
   float get_sql_months (string date_start,- ���������� ���������� �������, ��������� � ���� date_start �� ���� date_end. ���� � SQL ������� (��� �������!)
                         string date_end)
   
     ������� ������������� ��� ��������� �� ��������� � ��������
   string nav_draw_bar (int page_num,      - ���������� ������������� ������
                    int rec_cnt,
                    int in_page,
                    string url)
   string nav_get_limit (int page_num,     - ���������� ������ "LIMIT ..." ��� SQL ��������, ���� $in_page < 0, �� ����� (���)
                     int rec_cnt,
                     int in_page)
                     
     ������ �������
   string get_select_options(mixed selectedKey, array options, bool useHtmlspecialchars = true) 
                                                         - ���������� ������ � ������ <OPTION>, ���������������� ������� options
                                                           selectedKey ����� ���� �������� ��������

      int fputcsv(int fp, array array, string separator) - ��������� � ���� fp csv-������ �� ������� array, ����������� separator'��
   string sputcsv(array array, string separator)         - ������� � ���������� csv-������ �� ������� array, ����������� separator'��
   string get_user_ip()                                  - ���������� IP ����� ������������
   string get_user_host()                                - ���������� ����� ������������ � ���� ������ (����)
   
     bool send_html_mail(string mailto, string subj,     - ���������� HTML ������ �� ������ mailto � ����� subj � ����� body.
                    string body, string headers = '', 
                    string charset = 'win-1251')
   
*/
function string_is_email($s) {
    return preg_match('/^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}$/i', $s) ? true : false;
}
function string_is_login($s) {
    return preg_match("/^[a-z0-9@\._\-]+$/i", $s) ? true : false;
}
function string_is_int($s) {
    return preg_match('/^[-+]?\d+$/', $s) ? true : false;
}
function string_is_float($s) {
    return preg_match('/^[-+]?\d*\.?\d+(e[-+]?\d+)?$/i', $s) ? true : false;
}
function string_is_id($s) { 
    return string_is_int($s) && $s > 0; 
}
function string_is_date($s) {
    return preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{1,4})$/', $s, $m) && checkdate($m[2], $m[1], $m[3]) ? true : false;
}
function string_is_time($s) {
    return preg_match('/^([0-1]?\d|2[0-3]):([0-5]?\d)(:([0-5]?\d))?$/', $s) ? true : false;
}
function string_is_datetime($s) {
    return preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{1,4})\s+([0-1]?\d|2[0-3]):([0-5]?\d)(:([0-5]?\d))?$/', $s, $m) && 
           checkdate($m[2], $m[1], $m[3]) ? true : false;
}
function sql_to_date ($s) {
    return preg_match('/^(\d+)-(\d+)-(\d+)/', $s, $m) ? $m[3].'.'.$m[2].'.'.$m[1] : '';
}
function sql_to_datestr ($s) {
    return preg_match('/^(\d+)-(\d+)-(\d+)/', $s, $m) ? $m[3].' '.get_month_name_low($m[2]).' '.$m[1] : '';
}
function sql_to_time ($s, $withSeconds = true) {
    return preg_match('/(\d+):(\d+):(\d+)/', $s, $m) ? $m[1].':'.$m[2].($withSeconds ? ':'.$m[3] : '') : '';
}
function sql_to_datetime ($s) {
    return preg_match('/^(\d+)-(\d+)-(\d+)\s+((\d+):(\d+):(\d+))$/', $s, $m) ? $m[3].'.'.$m[2].'.'.$m[1].' '.$m[4] : '';
}
function sql_to_datetime_nosec ($s) {
    return preg_match('/^(\d+)-(\d+)-(\d+)\s+(\d+):(\d+):(\d+)$/', $s, $m) ? $m[3].'.'.$m[2].'.'.$m[1].' '.$m[4].':'.$m[5] : '';
}
function sql_to_ts ($s) {
    if (preg_match('/^(\d+)-(\d+)-(\d+)\s+((\d+):(\d+):(\d+))$/', $s, $m)) {
        return mktime($m[5], $m[6], $m[7], $m[2], $m[3], $m[1]);
    } elseif (preg_match('/^(\d+)-(\d+)-(\d+)/', $s, $m)) {
        return mktime(0, 0, 0, $m[2], $m[3], $m[1]);
    } else {
        return 0;
    }
}
function date_to_sql ($s) {
    return preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{1,4})$/', $s, $m) && checkdate($m[2], $m[1], $m[3]) 
           ? $m[3].'-'.$m[2].'-'.$m[1] : false;
}
function datetime_to_sql ($s) {
    return preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{1,4})(\s+([0-1]?\d|2[0-3]):([0-5]?\d)(:([0-5]?\d))?)?$/', $s, $m) && 
           checkdate($m[2], $m[1], $m[3]) ? trim($m[3].'-'.$m[2].'-'.$m[1].' '.$m[4]) : false;
}
function ts_to_date($ts) {
    return date('d.m.Y', $ts);
}
function ts_to_time($ts) {
    return date('H:i:s', $ts);
}
function ts_to_datetime($ts){
    return date('d.m.Y H:i:s', $ts);
}
function ts_to_sql($ts) {
    return date('Y-m-d H:i:s', $ts);
}
function get_word_form($v, $w) {
    $s = '';
    if (is_array($w)) { 
        $last = substr($v, ($l = strlen($v)) - 1, 1);
        if ($l >= 2) $last2 = substr($v, $l - 2, 2);
        if ((isset($last2) && $last2 >= 11 && $last2 <= 14) || $last >= 5 || $last == 0) $s = $w[2];
        elseif ($last == 1) $s = $w[0];
        elseif ($last > 1 && $last < 5) $s = $w[1];
    }
    return $s;
}
function get_month_name($m) {
    $a = array('������', '�������', '�����', '������', '���', '����', '����', '�������', '��������', '�������', '������', '�������');
    return $m > 0 && $m < 13 ? $a[$m - 1] : '';
}
function get_month_name_low($m) {
    $a = array('������', '�������', '�����', '������', '���', '����', '����', '�������', '��������', '�������', '������', '�������');
    return $m > 0 && $m < 13 ? $a[$m - 1] : '';
}
function get_micro_time() { 
    list($usec, $sec) = explode(' ', microtime()); 
    return ((float)$usec + (float)$sec); 
} 
function get_sql_days($date_start, $date_end) {
    return (sql_to_ts($date_end) - sql_to_ts($date_start)) / 86400;
}
function get_sql_months($date_start, $date_end) {
    if (preg_match('/^(\d+)-(\d+)-(\d+)/', $date_start, $m1) && preg_match('/^(\d+)-(\d+)-(\d+)/', $date_end, $m2)) {
        $sy = $m1[1]; $sm = $m1[2]; $sd = $m1[3];
        $ey = $m2[1]; $em = $m2[2]; $ed = $m2[3];
        return ($em - $sm) + ($ey - $sy) * 12 + ($ed - $sd) / 30;
    } else {
        return 0;
    }
}
function get_select_options($selected_key, $options, $useHtmlspecialchars = true) {
    $r = '';
    if (is_array($options)) 
        foreach ($options as $k => $v) {
            $r .= '<option value="' . $k . '"';
            if ((!is_array($selected_key) && $selected_key == $k) || (is_array($selected_key) && in_array($k, $selected_key)))
                $r .= ' selected';
            $r .= '>' . ($useHtmlspecialchars ? string_to_html($v) : $v);
        }
    return $r;
}


function nav_draw_bar($page, $total, $in_page, $url, $links = 20) {
//    if ($in_page == 0) $in_page = 1;
    $page = _nav_get_page($page, $total, $in_page);

    if ($total > 0 && $in_page && intval(($total - 1) / $in_page) > 0) {
        $start = $page - intval($links / 2); 
        $end = $start + $links - 1;
        if ($start < 0) {
            $start = 0;
            $end = $start + $links - 1;
        };
        $end1 = intval(($total - 1) / $in_page);
        if ($end > $end1 && $start > $end - $end1) {
            $end = $end1;
            $start = $end - $links + 1;
        } elseif ($end > $end1) {
            $end = $end1;
            $start = 0;
        };
        
        if ($start > 0)     $nav_panel[] = "<a href=\"" . $url . "0\" title=\"� ������\">|&lt;&lt;</a>";
        if ($page > $start) $nav_panel[] = "<a href=\"$url". ($page - 1) . "\" title=\"�����\">&lt;</a>";
        
        for ($a = $start; $a <= $end; $a++) 
                            $nav_panel[] = $a == $page ? "<b>" . ($a + 1) . "</b>" : "<a href=\"$url$a\">". ($a + 1). "</a>";
                            
        if ($page < $end)   $nav_panel[] = "<a href=\"$url" . ($page + 1) . "\" title=\"������\">&gt;</a>";
        if ($end < $end1)   $nav_panel[] = "<a href=\"$url" . $end1 . "\" title=\"� �����\">&gt;&gt;|</a>";
        
        return implode(" | ", $nav_panel);
    };
}

function nav_get_limit ($page, $total, $in_page) {
    if ($in_page <= 0) return '';
    $page = _nav_get_page($page, $total, $in_page);
    if ($total > 0) {
      if (intval($total / $in_page) == 0)
        return '';
      elseif ($page > 0)
        return ' LIMIT '. ($page * $in_page). ", $in_page";
      else
        return " LIMIT $in_page";
    } else {
      return '';
    }
}

function _nav_get_count_limit($page, $total, $in_page) {
    return _nav_get_page($page, $total, $in_page) * $in_page;
}
  
function _nav_get_page($page, $total, $in_page) {
//    if ($in_page == 0) $in_page = 1;
    if ($page < 0 || !$in_page) {
        return 0;
    } elseif ($total > 0) {
        $max = $total / $in_page;
        $max = intval($max) == $max ? intval($max)-1 : intval($max);
        return $page > $max ? $max : $page;
    } else {
      return 0;
    }
}

function string_to_html($s) {
    return str_replace('}', '&#125;', str_replace('{', '&#123;', htmlspecialchars($s)));
}

function string_to_html_special($s, $tags = '<a><b><i><u><p>', $donl2br = true) {
    $s = str_replace('{', '&#123;', strip_tags($s, $tags));
    return $donl2br ? nl2br($s) : $s;
}

function string_to_money($s) {
    return string_is_float($s) ? number_format(round($s, 2), 2, '.', ' ') : $s;
}

/*function fputcsv($fp, $array, $deliminator = ',') {
    return fputs($fp, sputcsv($array, $deliminator));
}*/

function sputcsv($array, $deliminator = ',') { 
    $line = ""; 
    if (!is_array($array)) return -1;
    foreach ($array as $val) { 
        $val = str_replace("\r\n", "\n", $val); 
        if (ereg("[$deliminator\"\n\r]", $val)) $val = '"' . str_replace('"', '""', $val) . '"'; 
        $line .= $val . $deliminator; 
    }
    $line = substr($line, 0, (strlen($deliminator) * -1)); 
    $line .= "\n"; 
    return $line; 
}

function get_user_ip() {
    global $HTTP_SERVER_VARS;
    return isset($HTTP_SERVER_VARS['REMOTE_ADDR']) ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : '';
}

function get_user_host() {
    return gethostbyaddr(get_user_ip());
}

function send_html_mail($mailto, $subj, $body, $headers = '', $charset = 'win-1251') {
    if ($charset == '') $charset = 'win-1251';
    return @mail($mailto, $subj, $body, $headers . "MIME-Version: 1.0\nContent-Type: text/html; charset: $charset");
}


$_1_2[1]="���� ";
$_1_2[2]="��� ";

$_1_19[1]="���� ";
$_1_19[2]="��� ";
$_1_19[3]="��� ";
$_1_19[4]="������ ";
$_1_19[5]="���� ";
$_1_19[6]="����� ";
$_1_19[7]="���� ";
$_1_19[8]="������ ";
$_1_19[9]="������ ";
$_1_19[10]="������ ";

$_1_19[11]="���������� ";
$_1_19[12]="���������� ";
$_1_19[13]="���������� ";
$_1_19[14]="������������ ";
$_1_19[15]="���������� ";
$_1_19[16]="����������� ";
$_1_19[17]="���������� ";
$_1_19[18]="������������ ";
$_1_19[19]="������������ ";

$des[2]="�������� ";
$des[3]="�������� ";
$des[4]="����� ";
$des[5]="��������� ";
$des[6]="���������� ";
$des[7]="��������� ";
$des[8]="���������� ";
$des[9]="��������� ";

$hang[1]="��� ";
$hang[2]="������ ";
$hang[3]="������ ";
$hang[4]="��������� ";
$hang[5]="������� ";
$hang[6]="�������� ";
$hang[7]="������� ";
$hang[8]="��������� ";
$hang[9]="��������� ";

$namerub[1]="����� ";
$namerub[2]="����� ";
$namerub[3]="������ ";

$nametho[1]="������ ";
$nametho[2]="������ ";
$nametho[3]="����� ";

$namemil[1]="������� ";
$namemil[2]="�������� ";
$namemil[3]="��������� ";

$namemrd[1]="�������� ";
$namemrd[2]="��������� ";
$namemrd[3]="���������� ";

$kopeek[1]="������� ";
$kopeek[2]="������� ";
$kopeek[3]="������ ";


function semantic($i,&$words,&$fem,$f) {
    global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd;
    $words="";
    $fl=0;
    if($i >= 100) {
        $jkl = intval($i / 100);
        $words.=$hang[$jkl];
        $i%=100;
    }
    if($i >= 20) {
        $jkl = intval($i / 10);
        $words.=$des[$jkl];
        $i%=10;
        $fl=1;
    }
    switch($i) {
        case 1: $fem=1; break;
        case 2:
        case 3:
        case 4: $fem=2; break;
        default: $fem=3; break;
    }
    if( $i ){
        if( $i < 3 && $f > 0 ){
            if ( $f >= 2 ) {
                $words.=$_1_19[$i];
            } else {
                $words.=$_1_2[$i];
            }
        } else {
            $words.=$_1_19[$i];
        }
    }
}


function num2str($L) {
    global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd, $kopeek;

    $s=" ";
    $s1=" ";
    $s2=" ";
    $kop=intval( ( $L*100 - intval( $L )*100 ));
    $L=intval($L);
    if($L>=1000000000) {
        $many=0;
        semantic(intval($L / 1000000000),$s1,$many,3);
        $s.=$s1.$namemrd[$many];
        $L%=1000000000;
    }
    if($L >= 1000000) {
        $many=0;
        semantic(intval($L / 1000000),$s1,$many,2);
        $s.=$s1.$namemil[$many];
        $L%=1000000;
        if($L==0) {
            $s.="������ ";
        }
    }
    if($L >= 1000) {
        $many=0;
        semantic(intval($L / 1000),$s1,$many,1);
        $s.=$s1.$nametho[$many];
        $L%=1000;
        if($L==0) {
            $s.="������ ";
        }
    }
    if($L != 0) {
        $many=0;
        semantic($L,$s1,$many,0);
        $s.=$s1.$namerub[$many];
    }
    if($kop > 0) {
        $many=0;
        semantic($kop,$s1,$many,1);
        $s.=$s1.$kopeek[$many];
    } else {
        $s.=" 00 ������";
    }
    return $s;
}
function create_date_selcet($name,$years,$date_in, $start_year=2000, $class_name = '') {
    $ret = '';
    $year_sel = '';
    $days_sel = '';
    $cur_day =  0;
    $cur_month = 0;
    $cur_year = 0;

    $months_sel = '';
    if(string_is_date($date_in)) {
        $ar = split('\.',$date_in);
        $cur_day = $ar[0];
        $cur_month = $ar[1];
        $cur_year = $ar[2];
    }
    $go_i = $years < 32 ? 32 : $years;
    for($i=1;$i<$go_i;$i++) {
        if($i<= $years) $n = $i + $start_year-1;
        $sel_day   = ($i == $cur_day) ? 'selected' : '';
        $sel_month = ($i == $cur_month) ? 'selected' : '';
        $sel_year  = ($n == $cur_year) ? 'selected' : '';
        if($i < 32) $days_sel .= '<option value="'.$i.'" '.$sel_day.'>'.$i.'</option>';
        if($i < 13) $months_sel .= '<option value="'.$i.'" '.$sel_month.'>'.$i.'</option>';
        if($i<= $years) $year_sel .= '<option value="'.$n.'" '.$sel_year.'>'.$n.'</option>';
    }
    $class_val = $class_name != '' ? 'class = "'.$class_name.'" ' : '';
    $ret .= '<select name='.$name.'[day] '.$class_val.'>'.$days_sel.'</select>';
    $ret .= '<select name='.$name.'[month] '.$class_val.'>'.$months_sel.'</select>';
    $ret .= '<select name='.$name.'[year] '.$class_val.'>'.$year_sel.'</select>';
    return $ret;
}

function get_date_from_select($in_ar) {
    $ret = '';
    if (is_array($in_ar)) {
        $ar = $in_ar;
        $ret = $ar['day'].'.'.$ar['month'].'.'.$ar['year'];
    }
    return $ret;
}

?>