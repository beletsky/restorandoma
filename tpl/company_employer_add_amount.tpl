<h1>Пополнение личного счета {CLIENT_NAME}</h1>
<p>Сейчас на счете - <b>{SUMM} руб.</b></p>
<form class="frmRegistration" action="" name="MemberAddForm" method="post">
  <input type="hidden" name="form[IDClientUser]" value="{IDCLIENTUSER}">
  <input type="hidden" name="a" value="add_user_amount">
  <input type="hidden" name="PageCode" value="user_employlist">
  <table>
<!-- BEGIN errors -->
      <tr>
        <td></td>
        <td colspan="1" class="red" style="padding-left:12px;font-size:12px;text-align:left;">{ADD_ERRORS}</td>
      </tr>
<!-- END errors -->
      <tr>
        <td><label>Сумма для пополнения счета<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[Amount]"></td>
      </tr>
      <tr>
        <td><span class="red"><i>* - поля, обязательные для заполнения</i></span></td>
        <td class="tdBtn">
          <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a onclick="javascript:document.forms.MemberAddForm.submit(); return false;" href="#" class="a01e">Перечислить!</a></td></tr></table>
          <p><a href="/user_employlist.html">Вернуться к списку сотрудников</a></p>
        </td>
      </tr>
  </table>
</form>
