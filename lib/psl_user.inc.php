<?php
################################################################################
#                                                                              #
#   PhpSiteLib. ���������� ��� ������� ���������� ������                       #
#                                                                              #
#   Copyright (�) 2002, Ilya Blagorodov (blagorodov.ru)                        #
#                                                                              #
#   psl_user.inc.php                                                           #
#   ������ ��� ������ � ������� �������������. ����� ������������ ���������    #
#   �������� ������������.                                                     #
#                                                                              #
################################################################################
/*

    bool Start(void)                                  - ������������� �������� ������������. ���� true, �� �������
  
    bool SetLogin(string login)                       - ������������� ������� �����. ���� true, �� �������
  string GetLogin()                                   - ���������� ������� �����
  string GetGroup()                                   - ���������� ��� ������ (���� �������� ��������� �����)
     int GetGroupId()                                 - ���������� id ������ ������������
    bool SetGroup(string group_code)                  - ���������� ��� ������ (���� �������� ��������� �����)
     int GetId()                                      - �������� id �������� ������������
    bool IsAuthorized(void)                           - ��������� �� ������������?
  
   mixed Get(string paramName = '')                   - �������� �������� ��������� ��� ��� �� ����� �����������
    bool Set(mixed paramName, string paramValue = '') - ���������� �������� ��� ��� �� ����� �����������. ���� true, �� �������
  
    bool Login(string login, string pwd)              - ������� ������. ���� true, �� �������
    void Logout()                                     - ��������� ����� �� �������

     int GetErrno()                                   - ��������� ������ (���)
     int GetError()                                   - ��������� ������ (���������)

*/

define ('_PHPSITELIB_USER_LN', '_phpsitelibuser_login' . $_PHPSITELIB_USER_POSTFIX);
define ('_PHPSITELIB_USER_AN', '_phpsitelibuser_auth' . $_PHPSITELIB_USER_POSTFIX);
define ('E_PSL_USER_OK',         0);
define ('E_PSL_USER_STRLOGIN',   1);
define ('E_PSL_USER_STRPWD',     2);
define ('E_PSL_USER_NOTFOUND',   3);
define ('E_PSL_USER_NOLOGIN',    4);
define ('E_PSL_USER_NOTLOGGED',  5);
define ('E_PSL_USER_NOGP',       6);

class PslUser {
 
    # ���������
    var $mUsersTable   = 'dwUsers';      // ������� � ��������������
    var $mIdField      = 'ID_User';    // ���� � ID ������������
    var $mLoginField   = 'UserLogin';  // ���� � �������
    var $mPwdField     = 'UserPwd';    // ���� � �������
    var $mDeletedField = 'UserDeleted';// ���� � ������ "������", ���� �����, �� �� ������������
    var $mPwdCrypt     = false;        // ���������� ������ � ������� ������� PASSWORD
    var $mSecureDelay  = 3;            // ����� ������ �������� ��� �������� ������. ���� 0, �� �������� �� �����������
    
    var $mGroupsTable  = 'dwGroups';     // ������� ����� �������������, ���� �����, �� ������ �� ������������
    var $mUGroupField  = 'ID_Group';   // ���� � ����. ������������� �� ������� �� ������
    var $mGIdField     = 'ID_Group';   // ���� ID � ������� ����� (�� ���. ���� ������)
    var $mGCodeField   = 'GroupCode';  // ���� � ����� ������ � ������� ����� (�������� � ���������)
    var $mGCodeAlias   = 'GroupCode';  // ��������, �� �������� ����. ������ � ���� ������ � ���������
    var $mGAccessBackEnd  = 'GroupAccessBackEnd'; // ���� � ������� ����� - ���� ������� � ��������� �����
    var $mGAccessFrontEnd = 'GroupAccessFrontEnd'; // ���� � ������� ����� - ���� ������� � �����
    
    var $mAcceptGroups = array();      // ������ ����������� ����� ��� �����������
    
    var $mAutoAcceptGroups = false;    // �������������� ������� �����, ����������� � ����������� (������������ ���� GroupAccessBackEnd)
    var $mAccessBackEnd = false;       // ���� ������������ �������������� ������� �����, �� ��� ����� �������� �������� ������, � �������
    var $mAccessFrontEnd = false;      // ����������� �������������� ���� GroupAccessBackEnd � GroupAccessFrontEnd
    var $mCheckAccessBackEnd = true;   // ��������� ������� ������� � ������
    var $mCheckAccessFrontEnd = true;  // ��������� ������� ������� � �����
    
    # public:
    var $mDb;                          // ������ ������� � ��
    
    # private (don't change it manually!):
    var $_Fetched = false;
    var $_Auth = false;
    var $_Login = '';    
    var $_Id = 0;
    var $_Props = array();
    var $_Errno = E_PSL_USER_OK;
    
    function Start() {
        global $HTTP_SESSION_VARS;
        if (session_is_registered(_PHPSITELIB_USER_LN) && session_is_registered(_PHPSITELIB_USER_AN) && $HTTP_SESSION_VARS[_PHPSITELIB_USER_AN])
            $this->_Auth = $this->SetLogin($HTTP_SESSION_VARS[_PHPSITELIB_USER_LN]);
        return $this->_Auth;
    }

    function SetLogin($login) {
        if ($login != $this->_Login) return $this->_FetchProps(false, $login);
    }
    
    function GetLogin() {
        return $this->_Login;
    }
    
    function SetGroup($code) {
        return $this->Set($this->mGCodeAlias, $code);
    }
    
    function GetGroup() {
        return $this->Get($this->mGCodeAlias);
    }
    
    function GetGroupId() {
        return $this->Get($this->mUGroupField);
    }
    
    function GetId() {
        return $this->_Id;
    }
    
    function IsAuthorized() {
        return $this->_Auth;
    }
    
    function GetErrno() {
        return $this->_Errno;
    }
    
    function GetError() {
        $e = array(E_PSL_USER_OK         => '��������� ��� ������',
                   E_PSL_USER_STRLOGIN   => '����� �������� ������������ �������',
                   E_PSL_USER_STRPWD     => '������ �������� ������������ �������', 
                   E_PSL_USER_NOTFOUND   => '������ �� �������', 
                   E_PSL_USER_NOLOGIN    => '����� �� ���������', 
                   E_PSL_USER_NOTLOGGED  => '�� �����������', 
                   E_PSL_USER_NOGP       => '�� ���������� ��������� ��� ������ � ��������', #!
                  );
        return in_array($this->_Errno, array_keys($e)) ? $e[$this->_Errno] : '����������� ������';
    }
    
    function Get($paramName = '') {
        if (!$this->_Fetched) 
            if (!$this->_FetchProps())
                return false;
        return $paramName != '' ? $this->_Props[$paramName] : $this->_Props;
    }
    
    function Set($paramName, $paramValue = '') {
        $this->_CheckDb();
        
        // ����� ��� �������, ������� ����� ��������������
        $this->mDb->Lock($this->mGroupsTable != '' ? array($this->mUsersTable, $this->mGroupsTable) : $this->mUsersTable);
        
        // �������� ������������� �������� ������������
        if (!$this->_Exists($this->_Login)) return false;
        
        // ���� ������� �������� - �� ������, �� ������� ��� �������� ��� ������������� ���������
        if (!is_array($paramName)) $paramName = array($paramName => $paramValue);
        
        // ���������� ������� �� ������ � �������� ��
        $a = array();
        foreach ($paramName as $k => $v)
            if (string_is_login($k) && $k != $this->mGCodeAlias) {
                $v = addslashes($v);
                $a[] = $k == $this->mPwdField && $this->mPwdCrypt ? "$k = password('$v')" : "$k = '$v'";
            }
                    
        if (count($a)) {
            $q = 'update ' . $this->mUsersTable . 
                 ' set ' . implode(', ', $a) . 
                 ' where ' . $this->mLoginField . ' = "' . $this->_Login . '"';
            $this->mDb->Query($q);
        }
        
        // ���� � ���������� ���� ������ ������, �� ����������� ��:
        // ������� ID ������ � �������� ������������
        if ($this->mGroupsTable != '' && in_array($this->mGCodeAlias, array_keys($paramName)) && string_is_login($paramName[$this->mGCodeAlias])) {
        
            $q = 'select ' . $this->mGIdField . 
                 ' from ' . $this->mGroupsTable . 
                 ' where ' . $this->mGCodeField . ' = "' . $paramName[$this->mGCodeAlias] . '"';
            $this->mDb->Query($q);
            
            if ($this->mDb->NextRecord()) {
            
                $q = 'update ' . $this->mUsersTable . 
                     ' set ' . $this->mUGroupField . ' = ' . $this->mDb->F(0) . 
                     ' where ' . $this->mLoginField . ' = "' . $this->_Login . '"';
                $this->mDb->Query($q);
                
            }
        }
        
        $this->mDb->Unlock();
        
        $this->_Fetched = false;
        return true;
    }
    
    # �������� ������ � ������, ���� ��, �� �������, ����� ���������
    function Login($login, $pwd) {
        eval('global $' . _PHPSITELIB_USER_LN .', $' . _PHPSITELIB_USER_AN . ';');
        $this->Logout();
        if ($this->_FetchProps(true, $login, $pwd)) {
            $this->_Auth  = true;
//            eval('$' . _PHPSITELIB_USER_LN . ' = "' . $this->_Login . '";');
//            eval('$' . _PHPSITELIB_USER_AN . ' = "' . $this->_Auth . '";');
            session_register(_PHPSITELIB_USER_LN);
            session_register(_PHPSITELIB_USER_AN);
            $_SESSION[_PHPSITELIB_USER_LN] = $this->_Login;
            $_SESSION[_PHPSITELIB_USER_AN] = $this->_Auth;
        } elseif ($this->mSecureDelay) {
            sleep($this->mSecureDelay);
        }
        return $this->_Errno == E_PSL_USER_OK;
    }
    
    # ����� ���� ��������� � ���� � ������� ������
    function Logout() {
        global $HTTP_COOKIE_VARS;
        if (!$this->_Auth) {
            $this->_Errno = E_PSL_USER_NOTLOGGED;
        } else {
            eval('global $' . _PHPSITELIB_USER_LN .', $' . _PHPSITELIB_USER_AN . ';');
            session_unregister(_PHPSITELIB_USER_LN);
            session_unregister(_PHPSITELIB_USER_AN);
            unset($HTTP_COOKIE_VARS['admin_login']);
            $this->_Fetched = false;
            $this->_Auth = false;
            $this->_Login = '';
            $this->_Id = 0;
            $this->_Errno = E_PSL_USER_OK;
        }
        return $this->_Errno == E_PSL_USER_OK;
    }
    
    # ���� $login, �� ��������� ����� � �������. ���� �� �����, �����. false. ���� ��, �� ������������ ���. ����� � id
    function _FetchProps($check_pwd = false, $login = '', $pwd = '') {
        $this->_CheckDb();
        $this->_Fetched = false;
        $this->_Errno = E_PSL_USER_OK;
        $this->_FetchAcceptGroups();
        if ($login == '') $login = $this->_Login;
        
        if ($login == '')                         $this->_Errno = E_PSL_USER_NOLOGIN;
        if (!string_is_login($login))             $this->_Errno = E_PSL_USER_STRLOGIN;
        if ($pwd != '' && !string_is_login($pwd)) $this->_Errno = E_PSL_USER_STRPWD;
        
        if ($this->_Errno != E_PSL_USER_OK) return false;
        
        if ($this->mGroupsTable != '') {
            if ($this->mUGroupField == '' || $this->mGroupsTable == '' || $this->mGIdField == '' || 
                $this->mGCodeField == '' || $this->mGCodeAlias == '') {
            
                $this->_Errno = E_PSL_USER_NOGP;
                return false;
                
            } else {
            
                $gtbl  = $this->mGroupsTable; $ugrpid = $this->mUGroupField; $gid = $this->mGIdField;     
                $gcode = $this->mGCodeField;  $alias  = $this->mGCodeAlias;
            
            }
        }
        
        // ������������ ������ �������, ���������� �������
        $pwds = $this->mPwdCrypt ? 'password("' . $pwd . '")' : '"' . $pwd . '"';
                                        $q = 'select u.*';
        if ($this->mGroupsTable != '')  $q .= ", g.$gcode as $alias";
                                        $q .= ' from ' . $this->mUsersTable . ' u';
        if ($this->mGroupsTable != '')  $q .= " inner join $gtbl g on g.$gid = u.$ugrpid";
                                        $q .= ' where u.' . $this->mLoginField . ' = "' . $login . '"';
        if ($check_pwd && $pwds != '')  $q .= ' and u.' . $this->mPwdField . ' = ' . $pwds;
        if ($this->mDeletedField != '') $q .= ' and u.' . $this->mDeletedField . ' = 0';
        
        if ($this->mGroupsTable != '') {
            $ag = array();
            foreach ($this->mAcceptGroups as $g) $ag[] = '"' . addslashes($g) . '"';
            $q .= ' and g.' . $this->mGCodeField . ' in (' . (count($this->mAcceptGroups) ? implode(', ', $ag) : '""') . ')';
        }
       
        $this->mDb->Query($q);
        
        // ����������� ���������
        if (!$this->mDb->NextRecord()) {
            $this->_Errno = E_PSL_USER_NOTFOUND;
        } else {
            $this->_Props   = $this->mDb->mRecord;
            $this->_Login   = $login;
            $this->_Id      = $this->mDb->F($this->mIdField);
            $this->_Fetched = true;
        }
        return $this->_Errno == E_PSL_USER_OK;
    }
    
    # ��������, ���������� �� ��
    function _CheckDb() {
        if (!isset($this->mDb)) die ('PslUser: �� ������ ���������� ��!');
    }
    
    # �������� ������������� ������������ �� ������
    function _Exists($login) {
        $this->_CheckDb();
        if (!string_is_login($login)) {
            $this->_Errno = E_PSL_USER_NOLOGIN;
            return false;
        }
        $this->mDb->Query('select * from ' . $this->mUsersTable . ' where ' . $this->mLoginField . ' = "' . $login . '"');
        $this->_Errno = $this->mDb->Nf() ?  E_PSL_USER_OK : E_PSL_USER_NOTFOUND;
        return $this->_Errno == E_PSL_USER_OK;
    }
    
    function _FetchAcceptGroups() {
        $this->_CheckDb();
        if ($this->mAutoAcceptGroups) {
            $q = 'select ' . $this->mGCodeField . ' from ' . $this->mGroupsTable;
            if ($this->mCheckAccessBackEnd || $this->mCheckAccessFrontEnd) $q .= ' where ';
            if ($this->mCheckAccessBackEnd) $q .= $this->mGAccessBackEnd . ' = ' . ($this->mAccessBackEnd ? '1' : '0');
            if ($this->mCheckAccessBackEnd && $this->mCheckAccessFrontEnd) $q .= ' and ';
            if ($this->mCheckAccessFrontEnd) $q .= $this->mGAccessFrontEnd . ' = ' . ($this->mAccessFrontEnd ? '1' : '0');
            $this->mDb->Query($q);
            $this->mAcceptGroups = array();
            while ($this->mDb->NextRecord()) $this->mAcceptGroups[] = $this->mDb->F(0);
        }
    }

}
?>