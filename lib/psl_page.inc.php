<?php
################################################################################
#                                                                              #
#   PhpSiteLib. ���������� ��� ������� ���������� ������                       #
#                                                                              #
#   Copyright (�) 2002, Ilya Blagorodov (blagorodov.ru)                        #
#                                                                              #
#   psl_page.inc.php                                                           #
#   �������� ������� � �������� ������� � ���������.                           #
#   ������ ��������� ���������� � ���������, �������� � ���������.             #
#                                                                              #
################################################################################
/*

    bool IsPageAccessible(string pageCode = '')       - �������� �� �������� � ����� (���� �� ���� ������ ��������)
    bool IsResourceAccessible(string resourceCode)    - �������� �� ������
    
   array GetMenuOf(string parentCode = '')            - ������� ������ ����, ������������� parentCode � ��������� �������� ������������
     int GetMenuCodeByPageCode(string pageCode)       - ������� ��� ���� �� ���������� ���� ��������
  
     int GetErrno()                                   - ��������� ������ (���)
     int GetError()                                   - ��������� ������ (���������)

*/

define ('E_PSL_PAGE_OK',         0);
define ('E_PSL_PAGE_GRPID',      1);
define ('E_PSL_PAGE_RESCODE',    2);
define ('E_PSL_PAGE_GRPCODE',    3);
define ('E_PSL_PAGE_PAGECODE',   4);
define ('E_PSL_PAGE_MENUCODE',   5);

class PslPage {
 
    # ���������
    var $mThisPage      = '';                // ��� ������� ��������
    
    var $mResTbl        = 'dwResources';       // �������. ��� �������
    var $mResId         = 'ID_Resource';     // �������. ID
    var $mResCode       = 'ResourceCode';    // �������. ���
    var $mResName       = 'ResourceName';    // �������. ������������
    
    var $mPageTbl       = 'dwPages';           // ��������. ��� �������
    var $mPageId        = 'ID_Page';         // ��������. ID
    var $mPageCode      = 'PageCode';        // ��������. ���
    var $mPageName      = 'PageName';        // ��������. ������������
    var $mPagePath      = 'PagePath';        // ��������. ���� �� ��������
    
    var $mPageResTbl    = 'dwPageResources';   // ������� �������. ��� �������
    var $mPageResIdPage = 'ID_Page';         // ������� �������. ID ��������
    var $mPageResIdRes  = 'ID_Resource';     // ������� �������. ID �������
    
    var $mGrpResTbl     = 'dwGroupResources';  // �������, ��������� �������. ��� �������
    var $mGrpResIdGrp   = 'ID_Group';        // �������, ��������� �������. ID ������
    var $mGrpResIdRes   = 'ID_Resource';     // �������, ��������� �������. ID �������
    
    var $mGrpTbl        = 'dwGroups';          // ������. �������� �������
    var $mGrpId         = 'ID_Group';        // ������. ID
    var $mGrpCode       = 'GroupCode';       // ������. ���
    
    var $mMenuTbl       = 'dwMenu';            // ������ ����. ��� �������
    var $mMenuId        = 'ID_Menu';         // ������ ����. ID ������
    var $mMenuParent    = 'ID_Parent';       // ������ ����. ����������� �������
    var $mMenuPage      = 'ID_Page';         // ������ ����. ������ �� ��������, � ������� �������� �����
    var $mMenuCode      = 'MenuCode';        // ������ ����. ��� ������
    var $mMenuName      = 'MenuName';        // ������ ����. ��� ������
    var $mMenuOrder     = 'MenuOrder';       // ������ ����. ������������������ ������
    
    # public:
    var $mDb;                          // ������ ������� � ��
    var $mUser;                        // ������ �������� ������������ PslUser
    
    # private (don't change it manually!):
    var $_Errno = E_PSL_PAGE_OK;
    var $_Menu = array();
    var $_MenuLoaded = false;
    
    function IsPageAccessible($pageCode = '') {
        $this->_CheckDb();
        $this->_CheckUser();
        $this->_CheckPage();
        $this->_FlushError();
        
        $group_id = $this->mUser->GetGroupId();
        if ($pageCode == '') $pageCode = $this->mThisPage;
        
        if (!string_is_id($group_id)) {
            $this->_Errno = E_PSL_PAGE_GRPID;
            return false;
            
        } else {
            $q = 'select count(*) from ' . $this->mPageTbl . ' p'.
                 ' inner join ' . $this->mPageResTbl . ' pr on pr.' . $this->mPageResIdPage . ' = p.' . $this->mPageId .
                 ' inner join ' . $this->mGrpResTbl . ' gr on gr.' . $this->mGrpResIdRes . ' = pr.' . $this->mPageResIdRes .
                 ' where gr.' . $this->mGrpResIdGrp . ' = ' . $group_id . ' and p.' . $this->mPageCode . ' = "' . $pageCode . '"';
            $this->mDb->Query($q);

            if ($this->mDb->NextRecord())
                return $this->mDb->F(0) ? true : false;
            else
                return false;
                 
        }
    }
    
    function IsResourceAccessible($resCode) {
        $this->_CheckDb();
        $this->_CheckUser();
        $this->_FlushError();
        
        $group_id = $this->mUser->GetGroupId();
        if (!string_is_login($resCode)) {
            $this->_Errno = E_PSL_PAGE_RESCODE;
            return false;
            
        } elseif (!string_is_id($group_id)) {
            $this->_Errno = E_PSL_PAGE_GRPID;
            return false;

        } else {
            $q = 'select count(*) from ' . $this->mResTbl . ' r'.
                 ' inner join ' . $this->mGrpResTbl . ' gr on gr.' . $this->mGrpResIdRes . ' = r.' . $this->mResId .
                 ' where gr.' . $this->mGrpResIdGrp . ' = ' . $group_id .
                 ' and r.' . $this->mResCode . ' = "' . addslashes($resCode) . '"';
            $this->mDb->Query($q);

            if ($this->mDb->NextRecord())
                return $this->mDb->F(0) ? true : false;
            else
                return false;

        }
    }
    
    function GetMenuOf($parentCode = '') {
        $this->_LoadMenu();
        $this->_FlushError();
        
        // ������� ������ ��������� ����, ������������� $parentCode
        $r = !string_is_login($parentCode) ? $this->_Menu : $this->_FindItem($parentCode, $this->_Menu);
        
        return $this->GetErrno() == E_PSL_PAGE_OK ? $r : false;
    }
    
    function GetMenuCodeByPageCode($pageCode) {
        $this->_CheckDb();
        $this->_FlushError();
        $r = '';
        if (string_is_login($pageCode)) {
            $q = 'select m.' . $this->mMenuCode . ' from ' . $this->mMenuTbl . ' m inner join ' . $this->mPageTbl . 
                 ' p on p.' . $this->mPageId . ' = m.' . $this->mMenuPage . ' and p.' . $this->mPageCode . ' = "' . 
                 addslashes($pageCode) . '"';
            $this->mDb->Query($q);
            $r = $this->mDb->NextRecord() ? $this->mDb->F(0) : '';
        }
        return $r;
    }

    function GetErrno() {
        return $this->_Errno;
    }
    
    function GetError() {
        $e = array(E_PSL_PAGE_OK         => '��������� ��� ������',
                   E_PSL_PAGE_GRPID      => '�������� ID ������ �������� ������������',
                   E_PSL_PAGE_RESCODE    => '�������� ��� �������',
                   E_PSL_PAGE_GRPCODE    => '�������� ��� ������',
                   E_PSL_PAGE_PAGECODE   => '�������� ��� ��������',
                   E_PSL_PAGE_MENUCODE   => '�������� ��� ����',
                  );
        return in_array($this->_Errno, array_keys($e)) ? $e[$this->_Errno] : '����������� ������';
    }
    
    # ��������, ���������� �� ��
    function _CheckDb() {
        if (!isset($this->mDb)) die ('PslPage: �� ������ ���������� ��!');
    }
    
    function _CheckUser() {
        if (!isset($this->mUser)) die ('PslPage: �� ������ ���������� PslUser!');
    }
    
    function _CheckPage() {
        if (!string_is_login($this->mThisPage)) die ('PslPage: �� ���������� ��� ������� ��������');
    }
    
    function _FlushError() {
        $this->_Errno = E_PSL_PAGE_OK;
    }
    
    function _GetGrpId($grpCode) {
        if (!string_is_login($grpCode)) {
            $this->_Errno = E_PSL_PAGE_GRPCODE;
            return false;
        } else {
            $q = 'select ' . $this->mGrpId . ' from ' . $this->mGrpTbl . ' where ' . $this->mGrpCode . ' = "' . $grpCode . '"';
            $this->mDb->Query($q);
            if ($this->mDb->NextRecord()) {
                return $this->mDb->F(0);
            } else {
                $this->_Errno = E_PSL_PAGE_GRPCODE;
                return false;
            }
        }
    }
    
    function _GetResId($resCode) {
        if (!string_is_login($resCode)) {
            $this->_Errno = E_PSL_PAGE_RESCODE;
            return false;
        } else {
            $q = 'select ' . $this->mResId . ' from ' . $this->mResTbl . ' where ' . $this->mResCode . ' = "' . $resCode . '"';
            $this->mDb->Query($q);
            if ($this->mDb->NextRecord()) {
                return $this->mDb->F(0);
            } else {
                $this->_Errno = E_PSL_PAGE_RESCODE;
                return false;
            }
        }
    }
    
    function _GetPageId($pageCode) {
        if (!string_is_login($pageCode)) {
            $this->_Errno = E_PSL_PAGE_PAGECODE;
            return false;
        } else {
            $q = 'select ' . $this->mPageId . ' from ' . $this->mPageTbl . ' where ' . $this->mPageCode . ' = "' . $pageCode . '"';
            $this->mDb->Query($q);
            if ($this->mDb->NextRecord()) {
                return $this->mDb->F(0);
            } else {
                $this->_Errno = E_PSL_PAGE_PAGECODE;
                return false;
            }
        }
    }
    
    function _GetMenuId($menuCode) {
        if (!string_is_login($menuCode)) {
            $this->_Errno = E_PSL_PAGE_MENUCODE;
            return false;
        } else {
            $q = 'select ' . $this->mMenuId . ' from ' . $this->mMenuTbl . ' where ' . $this->mMenuCode . ' = "' . $menuCode . '"';
            $this->mDb->Query($q);
            if ($this->mDb->NextRecord()) {
                return $this->mDb->F(0);
            } else {
                $this->_Errno = E_PSL_PAGE_MENUCODE;
                return false;
            }
        }
    }
    
    function _IsGrpResExists($grpCode, $resCode) {
        $grp_id = $this->_GetGrpId($grpCode);
        $res_id = $this->_GetResId($resCode);
        if (!$this->GetErrno()) {
            $q = 'select * from ' . $this->mGrpResTbl . ' where ' . $this->mGrpResIdGrp . ' = ' . $grp_id . 
                 ' and ' . $this->mGrpResIdRes . ' = ' . $res_id;
            $this->mDb->Query($q);
            if ($this->mDb->Nf()) $this->_Errno = E_PSL_PAGE_GRXSTS;
        }
        return $this->GetErrno() == E_PSL_PAGE_GRXSTS ? true : false;
    }
    
    function _IsPageResExists($pageCode, $resCode) {
        $page_id = $this->_GetPageId($pageCode);
        $res_id = $this->_GetResId($resCode);
        if (!$this->GetErrno()) {
            $q = 'select * from ' . $this->mPageResTbl . ' where ' . $this->mPageResIdPage . ' = ' . $page_id . 
                 ' and ' . $this->mPageResIdRes . ' = ' . $res_id;
            $this->mDb->Query($q);
            if ($this->mDb->Nf()) $this->_Errno = E_PSL_PAGE_PRXSTS;
        }
        return $this->GetErrno() == E_PSL_PAGE_PRXSTS ? true : false;
    }
    
    function _LoadMenu() {
    
        if (!$this->_MenuLoaded) {
        
            // ������� ��� ���� �� ���� � �������� ��� �� ID_Parent
            $q = 'select distinct m.*, p.' . $this->mPageCode . ', p.' . $this->mPagePath . ' from ' . $this->mMenuTbl . ' m' .
                 ' inner join ' . $this->mPageTbl . ' p on p.' . $this->mPageId . ' = m.' . $this->mMenuPage .
                 ' inner join ' . $this->mPageResTbl . ' pr on pr.' . $this->mPageResIdPage . ' = p.' . $this->mPageId .
                 ' inner join ' . $this->mGrpResTbl . ' gr on gr.' . $this->mGrpResIdRes . ' = pr.' . $this->mPageResIdRes . 
                 ' and gr.' . $this->mGrpResIdGrp . ' = ' . $this->mUser->GetGroupId() .
                 ' order by m.' . $this->mMenuParent . ', m.' . $this->mMenuOrder;
            $this->mDb->Query($q);
            $menuArray = array();
            $codesArray = array('NULL' => 'NULL');
            while ($this->mDb->NextRecord()) {
                $menuArray[$this->mDb->F($this->mMenuParent) ? $this->mDb->F($this->mMenuParent) : 'NULL'][] = $this->mDb->mRecord;
                $codesArray[$this->mDb->F($this->mMenuCode)] = $this->mDb->F($this->mMenuId);
            }
            
            // �������� 
            $this->_Menu = $this->_LoadMenuLevel($menuArray, $codesArray);
            
            // ������� ������ ����� �������, ����������� � ��������� ������� ���� � ����
            $this->_CollectPageCodes($this->_Menu, $a);
            $this->_MenuLoaded = true;
        }
        
    }
    
    function _LoadMenuLevel($menuArray, $codesArray, $parentCode = '') {
        $this->_CheckDb();
        $this->_FlushError();
        
        $r = array();
        if ($parentCode != '' && !$this->_GetMenuId($parentCode)) {
            $this->_Errno = E_PSL_PAGE_MENUCODE;
        } else {
            if (isset($codesArray[$parentCode ? $parentCode : 'NULL']) && 
                isset($menuArray[$codesArray[$parentCode ? $parentCode : 'NULL']]) && 
                is_array($menuArray[$codesArray[$parentCode ? $parentCode : 'NULL']])) {
                
                foreach ($menuArray[$codesArray[$parentCode ? $parentCode : 'NULL']] as $m) $r[] = $m;
                
            }
                
            foreach ($r as $k => $v) $r[$k]['_children'] = $this->_LoadMenuLevel($menuArray, $codesArray, $v[$this->mMenuCode]);
        }
        
        return $r;
    }
    
    # ������������ ����������� ����� �� ������ ����, ���� ������� �� ����, 
    # ���������� ������ ���������, ���������� ��� ���� � ����� $parentCode
    function _FindItem($searchCode, $parentArray) {
        $item = false;
        if (is_array($parentArray) || string_is_login($searchCode))
            foreach ($parentArray as $k => $v)
                if (is_array($v)) {
                    if (isset($v[$this->mMenuCode]) && $v[$this->mMenuCode] == $searchCode)
                        $item = $v['_children'];
                    else
                        $item = $this->_FindItem($searchCode, $v['_children']);
                    if (is_array($item)) break;
                }
        return $item;
    }
    
    # ����������� ����������� ����� ������ ���� � �������� ���� ���� �������, ����������� � ������� ����
    function _CollectPageCodes(&$menu, &$codesArray) {
        if (!is_array($codesArray)) $codesArray = array();
        if (!is_array($menu)) return $codesArray;
        foreach ($menu as $k => $v) {
            if (isset($v[$this->mPageCode]) && string_is_login($v[$this->mPageCode])) $menu[$k]['_codes'][] = $v[$this->mPageCode];
            $this->_CollectPageCodes($menu[$k]['_children'], $menu[$k]['_codes']);
            $codesArray = array_merge($codesArray, $menu[$k]['_codes']);
        }
    }
    
}
?>