{PAGE_TEXT}
<form class="frmData" action="" name="MemberAddForm">
    <table>
      <tr>
        <td width=240><label>�������</label></td>
        <td>{NAME}</td>
      </tr>
      <tr>
        <td><label>���</label></td>
        <td>{FNAME}</td>
      </tr>
      <tr>
        <td><label>��������</label></td>
        <td>{ONAME}</td>
      </tr>
      <tr>
        <td><label>���� ��������</label></td>
        <td>{BDATE}</td>
      </tr>
      <tr>
        <td><label>���������</label></td>
        <td>{POSITION}</td>
      </tr>
      <tr>
        <td><label>����� ����������� �����</label></td>
        <td>{MAIL}</td>
      </tr>
      <tr>
        <td><label>������</label></td>
        <td>{GRP_NAME}</td>
      </tr>
      <tr>
        <td><label>�������</label></td>
        <td><input type="checkbox" name="form[Status]" {STATUS} DISABLED></td>
      </tr>
      <tr>
        <td><label>�����������</label></td>
        <td>{COMMENTS}</td>
      </tr>
      <tr>
        <td></td>
        <td class="tdBtn">
          <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="/user_employlist/a/edit_user_proc/form[IDClientUser]/{IDCLIENTUSER}" class="a01e">������������� ������ <b>&raquo;</b></a></td></tr></table>
          <p><a href="/user_employlist.html">��������� � ������ �����������</a></p>
        </td>
      </tr>
    </table>
</form>
