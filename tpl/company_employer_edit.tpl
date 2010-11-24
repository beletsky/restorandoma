{PAGE_TEXT}
<form class="frmRegistration" action="/user_employlist/a/{A_TYPE}" name="MemberAdd" method="post">
<!--
  <input type="hidden" name="PageCode" value="user_employlist">
  <input type="hidden" name="a" value="{A_TYPE}">
-->
  <input type="hidden" name="form[IDClientComp]" value={IDCLIENTCOPM}>
  <input type="hidden" name="form[IDClientUser]" value={IDCLIENTUSER}>
  <input type="hidden" name="form[UserLogin]" value="{LOGIN}">
  <input type="hidden" name="form[UserPwd]" value="{PWD}">

  <table>
<!-- BEGIN errors -->
      <tr>
        <td></td>
        <td colspan="1" class="red" style="padding-left:12px;font-size:12px;text-align:left;">{REG_ERRORS}</td>
      </tr>
<!-- END errors -->
      <tr>
        <td><label>Фамилия<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[Name]" value="{NAME}"></td>
      </tr>
      <tr>
        <td><label>Имя<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[FName]" value="{FNAME}"></td>
      </tr>
      <tr>
        <td><label>Отчество</label></td>
        <td><input type="text" class="inpTxt" name="form[OName]" value="{ONAME}"></td>
      </tr>
      <tr>
        <td><label>День рождения</label></td>
        <td style="text-align:left;">{BDATE_SELECT}</td>
      </tr>
      <tr>
        <td><label>Должность</label></td>
        <td><input type="text" class="inpTxt" name="form[Position]" value="{POSITION}"></td>
      </tr>
      <tr>
        <td><label>Адрес электронной почты<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[Mail]" value="{MAIL}"></td>
      </tr>
      <tr>
        <td width="240"><label>Статус</label></td>
        <td><select class="sltTxt" name="form[GroupCode]">{GROUP_OPTIONS}</select></td>
      </tr>
      <tr>
        <td><label>Активность</label></td>
        <td style="text-align:left;"><input type="checkbox" class="inpTxt" name="form[Status]" {STATUS} style="width:auto;"></td>
      </tr>
      <tr>
        <td><label>Комментарии</label></td>
        <td><textarea class="txtArea" cols="" rows="" name="form[Comments]">{COMMENTS}</textarea></td>
      </tr>
      <tr>
        <td><span class="red"><i>* - поля, обязательные для заполнения</i></span></td>
        <td class="tdBtn">
          <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="#" class="a01e" onclick="javascript:document.MemberAdd.submit(); return false;">Сохранить <b>&raquo;</b></a></td></tr></table>
          <p><a href="/user_employlist.html">Вернуться к списку сотрудников</a></p>
        </td>
      </tr>
  </table>
</form>
