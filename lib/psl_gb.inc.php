<?php
################################################################################
#                                                                              #
#   PhpSiteLib. ���������� ��� ������� ���������� ������                       #
#                                                                              #
#   Copyright (�) 2002, Ilya Blagorodov (blagorodov.ru)                        #
#                                                                              #
#   psl_gb.inc.php                                                             #
#   �������� �����. ���������� ���������, ����� �� ����������, ����� ��������� #
#                                                                              #
################################################################################
/*

    ���������������� �������:
    
     int GetTotalMsgCount()                           - �������� ����� ��������� � ������� �������� �����
   array GetMsgList()                                 - �������� ������ ��������� �� ������� �������� (��� ����)
  string GetNavBar(string url)                        - �������� ������������� ������. ������������ ������� nav_draw_bar (psl_utils)
     int PostMessage(array props)                     - ������� ��������� �� ����� �������� ������������ ($psl_user) 
                                                      - props - ������ (����� - ��� ���� � ������� Messages),
                                                      - ���������� ID ������������ ��������� ��� false, ���� ������

    ����������������� �������:                                                  
                                                      
     int GetGbCount()                                 - ���������� �������� ����
   array GetGbList(string sortField)                  - ������ �������� ����, ��������������� �� sortField
    bool AddGb(array props)                           - �������� �������� �����
    bool EditGb(int gbId, array props)                - ������������� �������� ����� � ID = gbId
    
    bool EditMsg(int msgId, array props)              - ������������� ��������� � ID = msgId
    
   array GetGbModerators(int gbId)                    - �������� ������ ID_User ����������� ��������� �����
    bool AddGbModerator(int gbId, int userId)         - �������� ���������� �������� �����
    bool DeleteGbModerator(int modId, int userId)     - ������� ���������� �������� ����� (���� userId = �������� ������������, �� �� ����)
    
   array GetGbSubscribers(int gbId)                   - �������� ������ (��� ����) ����������� ��������� �����
    bool AddGbSubscriber(int gbId, array props)       - �������� ���������� �������� �����
    bool EditGbSubscriber(int subscrId, array props)  - ������������� ���������� �������� �����
     int DelGbSubscriber(int subscrId)                - ������� ����������
    
                                                      
    ��������� �������:                                                  
                                                      
     int GetErrno()                                   - ��������� ������ (���)
     int GetError()                                   - ��������� ������ (���������)

*/

define ('E_PSL_GB_OK',       0);
define ('E_PSL_GB_POST',     1);
define ('E_PSL_GB_DBL',      2);
define ('E_PSL_GB_DENIED',   3);

class PslGb {

    # ���������� �������� ������ �� ������� ������������
    var $mCurCode;                     // ��� ������� �������� �����, ���� �����, �� �� ������������
    var $mMsgInPage = 25;              // ����� ��������� �� ��������, 0 - ���
    var $mPage = 0;                    // ����� ������� ��������
    var $mSortField;                   // ���������� ��� ������ ������ ���������
    
    var $mEmailTemplate = '';          // ������ ��� �������� HTML ������ � ����� ���������
    var $mEmailSubject = '';           // ���� ������ � ����� ���������
    var $mEmailHeaders = '';           // ��������� ������ � ����� ���������
    var $mEmailCharset = 'koi8-r';     // ��������� ������ � ����� ���������
    var $mEmailCharsetTplFld = 'CHARSET'; // ��� ���� � �������, � ������� ����� ������� charset
    
    var $mEmailConvertFrom = 'w';      // ��������� ��������� ���������
    var $mEmailConvertTo = 'k';        // ���������, � ������� ���������� ������
    
    var $mEmailConvertSpecial = true;  // ������������ ��� ������� ������ ������� string_to_html_special (��� ������ ���������)
    var $mEmailConvertTags = '';       // ����, ������� �� ���������� �� ������ ��������� ��� ������� ������
    var $mEmailConvertNl2Br = true;    // ��������������� \n => <br> � ������ ��������� ��� �������
    
    
    # ���������
    var $mGbTable     = 'Gb';              // ������ �������� ����. ��� �������
    var $mGbId        = 'ID_Gb';           // ������ �������� ����. ID
    var $mGbName      = 'GbName';          // ������ �������� ����. ��������
    var $mGbCode      = 'GbCode';          // ������ �������� ����. ���
    
    var $mGbMTable    = 'GbModerators';    // ���������� �������� ����. ��� �������
    var $mGbMIdGb     = 'ID_Gb';           // ���������� �������� ����. ID �������� �����
    var $mGbMIdM      = 'ID_User';         // ���������� �������� ����. ID ������������ - ����������
    
    var $mMsgTable    = 'GbMsg';           // ���������. ��� �������
    var $mMsgId       = 'ID_Msg';          // ���������. ID
    var $mMsgGb       = 'ID_Gb';           // ���������. ID �������� �����
    var $mMsgUser     = 'ID_User';         // ���������. ID �����������
    var $mMsgDate     = 'MsgDate';         // ���������. ���� � �����
    var $mMsgText     = 'MsgText';         // ���������. �����
    
    var $mGbSTable    = 'GbSubscribers';   // ���������� �� ��������. ��� ������� (���� �����, �� �������� ��������� �� ����.)
    var $mGbSId       = 'ID_GbSubscriber'; // ���������� �� ��������. ID 
    var $mGbSGb       = 'ID_Gb';           // ���������� �� ��������. ID �������� �����
    var $mGbSEmail    = 'SubscriberEmail'; // ���������� �� ��������. Email ����������
    
    var $mGbBTable    = 'GbBans';          // ���������� IP �� ������� ���������. ��� ������� (���� �����, �� �� �������� IP �� ��������������)
    var $mGbBId       = 'ID_GbBan';        // ���������� IP �� ������� ���������. ID
    var $mGbBGb       = 'ID_Gb';           // ���������� IP �� ������� ���������. ID �������� �����
    var $mGbBIP       = 'BanIP';           // ���������� IP �� ������� ���������. ������������� IP
    

    # public:
    var $mDb;                          // ������ ������� � ��
    var $mUser;                        // ������� ������������ (���� �����, �� �� ������������)
    
    
    # private (don't change it manually!):
    var $_Errno = E_PSL_GB_OK;
    
    
    function GetTotalMsgCount() {
        $this->_CheckDb();
        
        $q = 'select count(*) from ' . $this->mMsgTable;
        $gb = $this->_GetGbId();
        if (string_is_id($gb)) $q .= ' where ' . $this->mMsgGb . ' = ' . $gb;
        $this->mDb->Query($q);
        return $this->mDb->NextRecord() ? $this->mDb->F(0) : 0;
    }
    
    function GetNavBar($url, $page = '') {
        if ($page != '') $this->mPage = $page;
        return nav_draw_bar($this->mPage, $this->GetTotalMsgCount(), $this->mMsgInPage, $url);
    }
    
    function GetMsgList() {
        $this->_CheckDb();
        
        $q = 'select * from ' . $this->mMsgTable;
        $gb = $this->_GetGbId();
        if (string_is_id($gb)) $q .= ' where ' . $this->mMsgGb . ' = ' . $gb;
        if ($this->mSortField != '') $q .= ' order by ' . $this->mSortField;
        $q .= nav_get_limit($this->mPage, $this->GetTotalMsgCount(), $this->mMsgInPage);
        $this->mDb->Query($q);
        $r = array();
        while ($this->mDb->NextRecord()) $r[] = $this->mDb->mRecord;
        
        return $r;
    }
    
    function PostMessage($props) {
        $this->_CheckDb();
        
        // ������� ID_Gb, ���� ������ ��� ������� �����
        $gb = $this->_GetGbId();
        
        // ������� ID_User, ���� ������������ �������� ������������
        $user = isset($this->mUser) ? $this->mUser->GetId() : 0;
        
        // �������� - �������� �� ��� ���������
        if ($this->_MsgExists($gb, $user, $props)) return false;
        
        // ��������, ����� �� ����� ������� ������������ ������ � ��� �����
        if (!$this->_CanPost()) return false;
        
        // ����� ��������� � ����
        $q = 'insert ' . $this->mMsgTable . ' set ';
        $f = array();
        if ($this->mMsgGb != '' && string_is_id($gb))     $f[] = $this->mMsgGb . ' = ' . $gb;
        if ($this->mMsgUser != '' && string_is_id($user)) $f[] = $this->mMsgUser . ' = ' . $user;
        if ($this->mMsgDate != '') 
            $f[] = $this->mMsgDate . (in_array($this->mMsgDate, array_keys($props)) ? (' = "' . $props[$this->mMsgDate]) : ' = now()');
        foreach ($props as $k => $v)
            if ($k != $this->mMsgDate)
                $f[] = $k . ' = "' . addslashes($v) . '"';
                
        $q .= implode(', ', $f);
        $this->mDb->Query($q);
        if ($this->mDb->mErrno) $this->_Errno = E_PSL_GB_POST;
        
        // ���� ������ ��� ������ �� ��������, ������� ����������� ID � �������� ��������
        if ($this->_Errno == E_PSL_GB_OK) {
        	$props[$this->mMsgId] = $this->mDb->GetInsertId();
        	$this->_Mail($props);
        }
        
        return $this->_Errno == E_PSL_GB_OK;
    }
        
    function GetErrno() {
        return $this->_Errno;
    }
    
    function GetError() {
        $e = array(E_PSL_GB_OK         => '��������� ��� ������',
                   E_PSL_GB_POST       => '������ ������ ���������',
                   E_PSL_GB_DBL        => '����� ��������� ��� ��������',
                   E_PSL_GB_DENIED     => '������� ��������� � IP ' . get_user_ip() . ' ��������',
                  );
        return in_array($this->_Errno, array_keys($e)) ? $e[$this->_Errno] : '����������� ������';
    }
    
    # �������� ID_Gb
    function _GetGbId ($code = '') {
        $this->_CheckDb();
        
        if ($code == '') $code = $this->mCurCode;
        if (string_is_login($code)) {
            $q = 'select ' . $this->mGbId . ' from ' . $this->mGbTable . ' where ' . $this->mGbCode . ' = "' . addslashes($code) . '"';
            $this->mDb->Query($q);
            return $this->mDb->NextRecord() ? $this->mDb->F(0) : 0;
        } else {
            return 0;
        }
    }
    
    # ��������� ������������� ���������
    function _MsgExists($gb, $user, $props) {
        $this->_CheckDb();
        
        $q = 'select * from ' . $this->mMsgTable . ' where ';
        $f = array();
        if ($this->mMsgGb != '' && string_is_id($gb))     $f[] = $this->mMsgGb . ' = ' . $gb;
        if ($this->mMsgUser != '' && string_is_id($user)) $f[] = $this->mMsgUser . ' = ' . $user;
        if ($this->mMsgDate != '' && in_array($this->mMsgDate, array_keys($props)))
            $f[] = $this->mMsgDate . ' = "' . $props[$this->mMsgDate] . '"';
        foreach ($props as $k => $v)
            if ($k != $this->mMsgDate)
                $f[] = $k . ' = "' . addslashes($v) . '"';

        $q .= implode(' and ', $f);
        $this->mDb->Query($q);
        if ($this->mDb->Nf()) $this->_Errno = E_PSL_GB_DBL;
        return $this->_Errno != E_PSL_GB_OK;
    }
    
    # ���������, ����� �� ����� ������� ������������ ������� � ��������
    function _CanPost() {
        $this->_CheckDb();
        
        if ($this->mGbBTable != '') {
            $q = 'select * from ' . $this->mGbBTable . ' where ' . $this->mGbBGb . ' = ' . $this->_GetGbId() . 
                 ' and ' . $this->mGbBIP  . ' = "' . addslashes(get_user_ip()) . '"';
            $this->mDb->Query($q);
            if ($this->mDb->Nf()) $this->_Errno = E_PSL_GB_DENIED;
	    }        
        return $this->_Errno == E_PSL_GB_OK;
    }
    
    # �������� ��������� � ����� ��������
    function _Mail($props) {
        $this->_CheckDb();
        
        if ($this->mGbSTable) {
            $q = 'select ' . $this->mGbSEmail . ' from ' . $this->mGbSTable . ' where ' . $this->mGbSGb . ' = ' . $this->_GetGbId();
            $this->mDb->Query($q);
            while ($this->mDb->NextRecord()) $this->_MailOne($this->mDb->F(0), $props);
        }
        return $this->_Errno == E_PSL_GB_OK;
    }
    
    # ������� ������ ��������� � ����� ��������
    function _MailOne($email, $props) {
    	// ���� ���������, �������������� ������ ���������
    	if ($this->mEmailConvertSpecial) $props[$this->mMsgText] = string_to_html_special($props[$this->mMsgText], $this->mEmailConvertTags, $this->mEmailConvertNl2Br);
    	// ���������
	    if ($this->mEmailCharsetTplFld != '') $props[$this->mEmailCharsetTplFld] = $this->mEmailCharset;
	    
	    $props2 = array();
	    foreach ($props as $k => $v) $props2['{' . $k . '}'] = $v;
        $b = $this->mEmailTemplate != '' ? $this->_PrepareMailStr($this->mEmailTemplate, $props2) : '';
        if ($b == '') foreach ($props as $k => $v) $b .= $k . ' = ' . $v . '<br>';
        $s = $this->_PrepareMailStr($this->mEmailSubject != '' ? $this->mEmailSubject : 'New post', $props2);
        $h = $this->_PrepareMailStr($this->mEmailHeaders, $props2);
        
        $s = $this->_ConvertStr($s);
        $h = $this->_ConvertStr($h);
        
        send_html_mail($email, $s, $b, $h, $this->mEmailCharset);
    
        return $this->_Errno == E_PSL_GB_OK;
    }
    
    # ����������� �������� ����� ��������� � ������
    function _PrepareMailStr($str, $props) {
    	return str_replace(array_keys($props), $props, $str);
    }
    
    # ������������ ������ (���������)
    function _ConvertStr($str) {
    	return $this->mEmailConvertFrom != $this->mEmailConvertTo ? convert_cyr_string($str, $this->mEmailConvertFrom, $this->mEmailConvertTo) : $str;
    }
    
    # ��������, ���������� �� ��
    function _CheckDb() {
        if (!isset($this->mDb)) die ('PslGb: �� ������ ���������� ��!');
    }
        
}

?>