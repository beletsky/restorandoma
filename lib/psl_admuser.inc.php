<?php
################################################################################
#                                                                              #
#   PhpSiteLib. ���������� ��� ������� ���������� ������                       #
#                                                                              #
#   Copyright (�) 2002, Ilya Blagorodov (blagorodov.ru)                        #
#                                                                              #
#   psl_admuser.inc.php                                                        #
#   ����������������� �������������.                                           #
#                                                                              #
################################################################################
/*

   array GetList(string groupCode = '',               - ���������� ������ ������������� ������ group ��� ���� (���� ''), 
                 string sortField = '',                 ��������������� �� sortfld ������� � started ������ 
                    int started = 0,                    � ������� limit �������
                    int limit = 0)
                           
     int GetCount(string group = '')                  - ���-�� ������������� � ������ group
   
     int GetErrno()                                   - ��������� ������ (���)
     int GetError()                                   - ��������� ������ (���������)

*/

define ('E_PSL_ADMUSER_OK',         0);

class PslAdminUser {
 
    # ���������
    var $mUsersTable   = 'Users';      // ������� � ��������������
    var $mIdField      = 'ID_User';    // ���� � ID ������������
    var $mLoginField   = 'UserLogin';  // ���� � �������
    var $mPwdField     = 'UserPwd';    // ���� � �������
    var $mDeletedField = 'UserDeleted';// ���� � ������ "������", ���� �����, �� �� ������������
    var $mPwdCrypt     = false;        // ���������� ������ � ������� ������� PASSWORD
    
    var $mGroupsTable  = 'dwGroups';     // ������� ����� �������������, ���� �����, �� ������ �� ������������
    var $mUGroupField  = 'ID_Group';   // ���� � ����. ������������� �� ������� �� ������
    var $mGIdField     = 'ID_Group';   // ���� ID � ������� ����� (�� ���. ���� ������)
    var $mGCodeField   = 'GroupCode';  // ���� � ����� ������ � ������� ����� (�������� � ���������)
    var $mGCodeAlias   = 'GroupCode';  // ��������, �� �������� ����. ������ � ���� ������ � ���������
    
    # public:
    var $mDb;                          // ������ ������� � ��
    
    # private (don't change it manually!):
    var $_Errno = E_PSL_ADMUSER_OK;
    
    
    function GetCount($groupCode = '') {
        $this->_CheckDb();
        $this->_Errno = E_PSL_ADMUSER_OK;
        
        $q = 'select count(u.*) from ' . $this->mUsersTable . ' u';
        if ($this->mGroupsTable != '' && $groupCode != '')
            $q .= ' inner join ' . $this->mGroupsTable . ' g on g.' . $this->mGIdField . ' = u.' . 
                  $this->mUGroupField . ' where g.' . $this->mGCodeField . ' = "' . addslashes($groupCode) . '"';
        $this->mDb->Query($q);
        return $this->mDb->NextRecord() ? $this->mDb->f(0) : 0;
    }
    
    function GetList(groupCode = '', sortField = '', started = 0, limit = 0) {
        $this->_CheckDb();
        $this->_Errno = E_PSL_ADMUSER_OK;

        $q  = 'select u.*';
        if ($this->mGroupsTable != '') $q .= ', g.' . $this->mGCodeField . ' as ' . $this->mGCodeAlias;
        $q .= ' from ' . $this->mUsersTable . ' u';
        $q .= ' inner join ' . $this->mGroupsTable . ' g on g.' . $this->mGIdField . ' = u.' . $this->mUGroupField
        if ($this->mGroupsTable != '' && $groupCode != '') 
          $q .= ' where g.' . $this->mGCodeField . ' = "' . addslashes($groupCode) . '"';
        $this->mDb->Query($q);
    
    
    }
    
    function GetErrno() {
        return $this->_Errno;
    }
    
    function GetError() {
        $e = array(E_PSL_ADMUSER_OK         => '��������� ��� ������',
                  );
        return in_array($this->_Errno, array_keys($e)) ? $e[$this->_Errno] : '����������� ������';
    }
    
    # ��������, ���������� �� ��
    function _CheckDb() {
        if (!isset($this->mDb)) die ('PslAdminUser: �� ������ ���������� ��!');
    }
    
}
?>