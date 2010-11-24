{PAGE_TEXT}
<form class="frmData" action="" name="MemberAddForm">
    <table>
      <tr>
        <td width=240><label>Фамилия</label></td>
        <td>{NAME}</td>
      </tr>
      <tr>
        <td><label>Имя</label></td>
        <td>{FNAME}</td>
      </tr>
      <tr>
        <td><label>Отчество</label></td>
        <td>{ONAME}</td>
      </tr>
      <tr>
        <td><label>День рождения</label></td>
        <td>{BDATE}</td>
      </tr>
      <tr>
        <td><label>Должность</label></td>
        <td>{POSITION}</td>
      </tr>
      <tr>
        <td><label>Адрес электронной почты</label></td>
        <td>{MAIL}</td>
      </tr>
      <tr>
        <td><label>Статус</label></td>
        <td>{GRP_NAME}</td>
      </tr>
      <tr>
        <td><label>Активен</label></td>
        <td><input type="checkbox" name="form[Status]" {STATUS} DISABLED></td>
      </tr>
      <tr>
        <td><label>Комментарии</label></td>
        <td>{COMMENTS}</td>
      </tr>
      <tr>
        <td></td>
        <td class="tdBtn">
          <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="/user_employlist/a/edit_user_proc/form[IDClientUser]/{IDCLIENTUSER}" class="a01e">Редактировать данные <b>&raquo;</b></a></td></tr></table>
          <p><a href="/user_employlist.html">Вернуться к списку сотрудников</a></p>
        </td>
      </tr>
    </table>
</form>
