<h1>Редактирование данных компании</h1>
<form class="frmRegistration" action="/user_yourcompany/a/update_company" name="RegisterForm" method="post">
<!--
<input type=hidden name="PageCode" value="user_yourcompany">
<input type=hidden name="a" value="update_company">
-->
<input type=hidden name="form[IDClientComp]" value="{IDCLIENTCOMP}">

  <table>
<!-- BEGIN error_block -->
      <tr>
        <td></td>
        <td colspan="1" class="red" style="padding-left:12px;font-size:12px;text-align:left;">{UPDATE_ERRORS}</td>
      </tr>
<!-- END error_block -->
      <tr>
        <td width="240"><label>Организационно-правовая форма<span class="red">*</span></label></td>
        <td><select class="sltTxt" name="form[OrgType]">{ORG_TYPES_OPTIONS}</select></td>
      </tr>
      <tr>
        <td><label>Наименование организации<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[Name]" value="{NAME}"></td>
      </tr>
      <tr>
        <td><label>Веб-сайт</label></td>
        <td><input type="text" class="inpTxt" name="form[WebUrl]" value="{URL}"></td>
      </tr>
      <tr>
        <td><label>Телефон<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[Phone]" value="{PHONE}"></td>
      </tr>
      <tr>
        <td><label>Ближайшая станция метро<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[Metro]" value="{METRO}"></td>
      </tr>
      <tr>
        <td><label>Точный адрес доставки<span class="red">*</span></label></td>
        <td><textarea class="txtArea" cols="" rows="" name="form[Adress]">{ADRESS}</textarea></td>
      </tr>
      <tr>
        <td><label>Приблизительное количество<br>сотрудников, которые будут<br>заказывать обед</label></td>
        <td><input type="text" class="inpTxt" name="form[EmployersNum]" value="{EMLOY_NUM}"></td>
      </tr>
      <tr>
        <td colspan="2">
            <table class="tblPayment">
<!-- BEGIN pay_type -->
              <tr>
                <td width="240"><label>{PAY_TITLE} </label></td>
                <td style="text-align:left;"><input type="radio" name="form[PaymentType]" value="{PAY_VAL}" {CHECKED}></td>
              </tr>
<!-- END pay_type -->
              <tr>
                <td width="240"></td>
                <td class="tdDownload"><a href="/menu/dogovor.doc"><img src="/images/btn_download.gif" alt="Скачать договор" width="129" height="25"></a></td>
              </tr>
            </table>
        </td>
      </tr>
      <tr>
        <td><label>Желаемое время доставки<span class="red">*</span></label></td>
        <td><select class="sltTxt" name="form[DeliveryTime]">{DELIV_TIME_OPTIONS}</select></td>
      </tr>
      <tr>
        <td><span class="red"><i>* - поля, обязательные для заполнения</i></span></td>
        <td class="tdBtn">
            <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="#" class="a01e" onclick="javascript:document.RegisterForm.submit(); return false;">Сохранить данные <b>&raquo;</b></a></td></tr></table>
        </td>
      </tr>
  </table>
</form>
