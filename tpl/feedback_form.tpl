{TEXT}
<!-- Форма обращения за рекламой-->
<form class="frmRegistration" action="/responses/a/send_feedback" name="FeedbackForm" method="post">
<!--                
    <input type=hidden name="PageCode" value="responses">
    <input type=hidden name="a" value="send_feedback">
-->                
    
    <table width="100%" cellspacing="0" cellpadding="0">
    <!-- BEGIN error_block -->
      <tr>
        <td></td>
        <td colspan="1" class="red" style="padding-left:12px;font-size:12px;text-align:left;">{SEND_ERRORS}</td>
      </tr>
    <!-- END error_block -->
      <tr>
        <td><label>Контактное лицо</label></td>
        <td><input type="text" class="inpTxt" name="form[person]" value="{PERSON}"></td>
      </tr>
      <tr>
        <td><label>Электронный адрес:<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[email]" value="{EMAIL}"></td>
      </tr>
      <tr>
        <td><label>Ваше предложение:</label></td>
        <td><textarea class="txtArea" cols="" rows="" name="form[message]">{MESSAGE}</textarea></td>
      </tr>
      <tr>
        <td><span class="red"><i>* - поля, обязательные для заполнения</i></span></td>
        <td class="tdBtn">
            <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="#" class="a01e" onclick="document.FeedbackForm.submit(); return false;">Отправить сообщение</a></td></tr></table>
        </td>
      </tr>
    </table>
</form>
