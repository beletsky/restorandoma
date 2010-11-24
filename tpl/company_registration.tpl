{PAGE_TEXT}
<form class="frmRegistration" action="/registration/a/register_comp" name="RegisterForm" method="post">
    <input type="hidden" name="form[Status]" value="checked">
    
    <table>
<!-- BEGIN errors -->
      <tr>
        <td></td>
        <td colspan="1" class="red" style="padding-left:12px;font-size:12px;text-align:left;">{REG_ERRORS}</td>
      </tr>
<!-- END errors -->
      <tr>
        <td width="240"><label>Организационно-правовая форма<span class="red">*</span></label></td>
        <td><select class="sltTxt" name="form[OrgType]">{ORG_TYPES_OPTIONS}</select></td>
      </tr>
      <tr>
        <td><label>Наименование организации<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[Name]" value="{NAME}"></td>
      </tr>
      <tr>
        <td><label>Фамилия<span class="red">*</span></label></td>
        <td><input type="text" class="inpTxt" name="form[NName]" value="{NNAME}"></td>
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
        <td style="text-align:left;padding-left:13px;">{BDATE_SELECT}</td>
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
        <td><label>Веб-сайт</label></td>
        <td><input type="text" class="inpTxt" name="form[WebUrl]" value="{WEBURL}"></td>
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
        <td><input type="text" class="inpTxt" name="form[EmployersNum]" value="{EMPLOYERSNUM}"></td>
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
        <td class="tdBtn"><input type="submit" class="btnReady" value="" title="Готово"></td>
      </tr>
</table>
</form>
