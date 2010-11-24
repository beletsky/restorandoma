{TEXT}
<!-- Форма обращения за рекламой-->
<form class="frmRegistration" action="/advert/a/send_advert_question" name="AdvertForm" method="post">
<!--                
<input type=hidden name="PageCode" value="advert">
<input type=hidden name="a" value="send_advert_question">
-->                
    <table>
<!-- BEGIN error_block -->
      <tr>
        <td></td>
        <td colspan="1" class="red" style="padding-left:12px;font-size:12px;text-align:left;">{SEND_ERRORS}</td>
      </tr>
<!-- END error_block -->
      <tr>
        <td><label>Контактное лицо:</label></td>
        <td><input type="text" class="inpTxt" name="form[person]" value="{PERSON}"></td>
      </tr>
      <tr>
        <td><label>Электронный адрес:<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[email]" value="{EMAIL}"></td>
      </tr>
      <tr>
        <td><label>Что Вас интересует:</label></td>
        <td><textarea class="txtArea" cols="" rows="" name="form[message]">{MESSAGE}</textarea></td>
      </tr>
      <tr>
        <td><span class="red"><i>* - поля, обязательные для заполнения</i></span></td>
        <td class="tdBtn"><table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a class="a01e" onclick="document.AdvertForm.submit(); return false;">Отправить</a></td></tr></table></td>
      </tr>
    </table>
</form>
<!-- !Форма обращения за рекламой-->
