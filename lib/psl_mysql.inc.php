<?php
################################################################################
#                                                                              #
#   PhpSiteLib. ���������� ��� ������� ���������� ������                       #
#                                                                              #
#   Copyright (�) 2002, Ilya Blagorodov (blagorodov.ru)                        #
#                                                                              #
#   psl_mysql.inc.php                                                          #
#   ����� PslMySQL: ������������ ������� � �� MySQL.                           #
#   ������������ ���� � ����� ���� ���������� ���������� PHPLIB:               #
#   PHPLIB Copyright (c) 1998-2000 NetUSE AG Boris Erdmann, Kristian Koehntopp #
#                                                                              #
################################################################################
/*

  void PslMySql(string query = '')                 - ���� �������� ������ �������, �� �� ����������
   int GetLinkId()                                 - �������� ������������� �������� ����������
   int GetQueryId()                                - �������� ������������� �������� �������
   int Connect(string db = '', string host = '',   - ��������� ����������� � ������� ������������� ����������.
               string user = '', string pwd = '')    ���� ��������� �� �������, ������������ ���� �������.
  void Free()                                      - ���������� �������
   int Query(string query)                         - ��������� ������. ���������� ������������� �������.
   int GetInsertId()                               - ���������� ��������� ����������� ID (��������� ���������� last_insert_id())
  bool NextRecord()                                - ������������ � ����. ������. ���������� true, ���� ���� ������ � ������
   int Lock(mixed tables, string mode = 'write')   - ����� �������. tables - ������ ��� ������. mode - ('write', 'read')
   int Unlock()                                    - ��������� UNLOCK TABLES. ���������� ������������� ����� �������
   int AffectedRows()                              - mysql_affected_rows() ��� ��������� �������
   int NumRows()                                   - mysql_num_rows()
   int NumFields()                                 - mysql_num_fields()
   int Nf()                                        - NumRows()
  void Np()                                        - print NumRows()
 mixed F(string fieldName)                         - ���������� ���������� ���� fieldName
  void P(string fieldName)                         - print ����������� ���� fieldName 

*/
class PslMySql {
  
    # ��������� ����������
    var $mHost     = '';
    var $mDatabase = '';
    var $mUser     = '';
    var $mPassword = '';
  
    # ���������
    var $mAutoFree     = false;     // �������������� ���������� mysql_free_result
    var $mDebug        = false;     // �������� ���������� ����������
    var $mErrHandler   = '';        // �������� ������� - ����������� ������ (������ ��������� ���� �������� - ����� ������)
    var $mHaltOnError  = 'yes';     // ����� ��������� ������: "yes"    (���������� ������ � ���������������), 
                                    //                         "no"     (�� �������� �� �������), 
                                    //                         "report" (���������� ������ � ���������� ����������)
  
    # ������ �� ���������� ������� ������ � ����� ������� ������
    var $mRecord = array();
    var $mRow;
  
    # ����� � ����� ������� ������
    var $mErrno = 0;
    var $mError = '';
  
    # Private...
    var $_LinkId  = 0;
    var $_QueryId = 0;
    var $_Query   = '';
  
  
    
    function PslMySql($query = '') {
        $this->Query($query);
    }
  
    function GetLinkId() {
        return $this->_LinkId;
    }
  
    function GetQueryId() {
        return $this->_QueryId;
    }
  
    function Connect($db = '', $host = '', $user = '', $pwd = '') {
    
        if ($db   == '') $db   = $this->mDatabase;
        if ($host == '') $host = $this->mHost;
        if ($user == '') $user = $this->mUser;
        if ($pwd  == '') $pwd  = $this->mPassword;
        
        if ($this->_LinkId == 0) {
      
            $this->_LinkId = mysql_connect($host, $user, $pwd);
            if (!$this->_LinkId) {
                $this->_Halt("connect($host, $user, \$pwd) failed.");
                return 0;
            }
          
            if (!@mysql_select_db($db, $this->_LinkId)) {
                $this->_Halt("cannot use database " . $db);
                return 0;
            }
            
            @mysql_query('set names \'cp1251\'', $this->_LinkId);
        }
      
        return $this->_LinkId;
    }
  
    function Free() {
        if(!$this->_QueryId) return;
        //@mysql_free_result($this->_QueryId);
        $this->_QueryId = 0;
    }
  
    function Query($query) {
    
        if ($query == '') return 0;
          
        $this->_Query = $query;
       
        if (!$this->Connect()) return 0;
       
        if ($this->_QueryId) $this->Free();
       
        if ($this->mDebug) print 'Debug: query = ' . $query . "<br>\n";
       
        $this->_QueryId = @mysql_query($query, $this->_LinkId);
        $this->mRow   = 0;
        $this->mErrno = mysql_errno();
        $this->mError = mysql_error();
        if (!$this->_QueryId) $this->_Halt("Invalid SQL: " . $query);
       
        return $this->_QueryId;
    }
    
    function GetInsertId() {
        return $this->_LinkId ? @mysql_insert_id($this->_LinkId) : 0;
    }
  
    function NextRecord() {
      
        if (!$this->_QueryId) {
            $this->_Halt("NextRecord called with no query pending.");
            return 0;
        }
      
        $this->mRecord = @mysql_fetch_array($this->_QueryId);
        $this->mRow++;
        $this->mErrno  = mysql_errno();
        $this->mError  = mysql_error();
      
        $stat = is_array($this->mRecord);
        if (!$stat && $this->mAutoFree) $this->Free();
        
        return $stat;
    }
  
    function Lock($tables, $mode = 'write') {
        $this->Connect();
        
        $query = "lock tables ";
        if (is_array($tables)) {
            foreach ($tables as $k => $v)
                $query .= $v . ' ' . ($k == 'read' && $k != 0 ? 'read' : $mode) . ', ';
            $query = substr($query, 0, -2);
        } else {
            $query .= $table . ' ' . $mode;
        }
        
        if ($this->mDebug) print 'Debug: query = ' . $query . "<br>\n";
      
        $res = @mysql_query($query, $this->_LinkId);
        if (!$res) {
            $this->_Halt("lock(" . (isset($table) ? $table : '') . ", $mode) failed.");
            return 0;
        }
        return $res;
    }
    
    function Unlock() {
        $this->connect();
       
        if ($this->mDebug) print("Debug: query = unlock tables<br>\n");
        $res = @mysql_query("unlock tables", $this->_LinkId);
        if (!$res) {
            $this->_Halt("unlock() failed.");
            return 0;
        }
        return $res;
    }
  
  
    function AffectedRows() {
        return @mysql_affected_rows($this->_LinkId);
    }
  
    function NumRows() {
        return @mysql_num_rows($this->_QueryId);
    }
  
    function NumFields() {
        return @mysql_num_fields($this->_QueryId);
    }

    function FetchArray() {
        return @mysql_fetch_array($this->_QueryId);
    }

    function FetchRow() {
        return @mysql_fetch_row($this->_QueryId);
    }
  
    function Nf() {
        return $this->NumRows();
    }
  
    function Np() {
        print $this->NumRows();
    }
  
    function F($fieldName) {
        return isset($this->mRecord[$fieldName]) ? $this->mRecord[$fieldName] : '';
    }
  
    function P($fieldName) {
        print $this->mRecord[$fieldName];
    }
  
    
    
    function _Halt($msg) {
        $this->mError = @mysql_error($this->_LinkId);
        $this->mErrno = @mysql_errno($this->_LinkId);
        
        if ($this->mErrHandler != '') {
            $text = "Database Error occured!\n\nQuery:\n" . $this->_Query . "\n\nError:\n" . $this->mError .
                    "\n\nError No:\n" . $this->mErrno;
            $f = $this->mErrHandler;
            $f($text);
        }
              
        if ($this->mHaltOnError == 'no') return;
        
        $this->_HaltMsg($msg);
        
        if ($this->mHaltOnError != 'report') die('Session halted.');
    }
  
    function _HaltMsg($msg) {
        print '<b>Database error:</b> ' . $msg . "<br>\n";
        print '<b>MySQL Error</b>: ' . $this->mErrno . ' (' . $this->mError . ")<br>\n";
    }
  
}
?>