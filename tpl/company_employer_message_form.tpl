<h1>Рассылка сообщения сотрудникам</h1>
<form class="frmRegistration" action="" name="MessageForm" method="Post">
  <input type="hidden" name="form[MailsList]" value="{MAILS_LIST}">
  <input type="hidden" name="form[FIO]" value="{FIO}">
  <input type="hidden" name="form[FI]" value="{FI}">
  <input type="hidden" name="form[I]" value="{I}">
  <input type="hidden" name="a" value="{SEND_MESSAGE_ACTION}">
  <input type="hidden" name="PageCode" value="user_employlist">
  <input type="hidden" name="form[IDClientUser]" value="{CLIENT_USER_ID}">

  <table>
<!-- BEGIN error_block -->
      <tr>
        <td></td>
        <td colspan="1" class="red" style="padding-left:12px;font-size:12px;text-align:left;">{SEND_ERRORS}</td>
      </tr>
<!-- END error_block -->
      <tr>
        <td><label>Тема<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[Subject]" value="{SUBJECT}"></td>
      </tr>
      <tr>
        <td><label>Текст сообщения<span class="red">*</span></label></td>
        <td><textarea class="txtArea" cols="" rows="" name="form[Message]">{MESSAGE}</textarea></td>
      </tr>
      <tr>
          <td colspan="2" valign="top">
              <!-- BEGIN one_user_text -->
              Внимание! Для подстановки в текст сообщения<br>"{FIO}" используйте - (ФИО),<br>"{FI}" - (ФИ),<br>"{I}" - (И),<br>"{DATA}" - (Дата)
              <!-- END one_user_text -->
              <!-- BEGIN many_users_text -->
              Внимание! Для подстановки в текст сообщения<br>"{DATA}" используйте - (Дата)
              <!-- END many_users_text -->
          </td>
      </tr>
      <tr>
        <td><span class="red"><i>* - поля, обязательные для заполнения</i></span></td>
        <td class="tdBtn">
          <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="#" onclick="javascript:document.MessageForm.submit(); return false;" class="a01e">{BUTTON_TEXT}<b>&raquo;</b></a></td></tr></table>
        </td>
      </tr>
  </table>
</form>
