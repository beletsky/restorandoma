<h1>{HEADER}</h1>
{TEXT}
<!-- Форма обращения за рекламой-->
<form class="frmRegistration" action="/user_question/a/send_user_question" name="AdvertForm" method="post">
<!--
    <input type=hidden name="PageCode" value="user_question">
    <input type=hidden name="a" value="send_user_question">
-->        
    <table>
      <tr>
        <td><label>Контактное лицо:</label></td>
        <td><input type="text" class="inpTxt" name="Person" value="{PERSON}"></td>
      </tr>
      <tr>
        <td><label>Электронный адрес:</label></td>
        <td><input type="text" class="inpTxt" name="Email" value="{EMAIL}"></td>
      </tr>
      <tr>
        <td><label>Ваш вопрос:</label></td>
        <td><textarea class="txtArea" cols="" rows="" name="Message">{MESSAGE}</textarea></td>
      </tr>
      <tr>
        <td></td>
        <td class="tdBtn">
            <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="#" class="a01e" onclick="document.AdvertForm.submit(); return false;">Отправить <b>&raquo;</b></a></td></tr></table>
        </td>
      </tr>
    </table>
</form>
<!-- !Форма обращения за рекламой-->
