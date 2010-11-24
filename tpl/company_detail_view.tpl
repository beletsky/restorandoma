<h1>Данные компании</h1>
{ERRORS}
<form class="frmData" action="" name="MemberAddForm">
    <table>
      <tr>
        <td><label>Организационно-правовая форма</label></td>
        <td><select name="form[OrgType]" disabled>{ORG_TYPES_OPTIONS}</select></td>
      </tr>
      <tr>
        <td><label>Наименование организации</label></td>
        <td>{NAME}</td>
      </tr>
      <tr>
        <td><label>Веб-сайт</label></td>
        <td>{URL}</td>
      </tr>
      <tr>
        <td><label>Телефон</label></td>
        <td>{PHONE}</td>
      </tr>
      <tr>
        <td><label>Ближайшая станция метро</label></td>
        <td>{METRO}</td>
      </tr>
      <tr>
        <td><label>Точный адрес доставки</label></td>
        <td>{ADRESS}</td>
      </tr>
      <tr>
        <td><label>Приблизительное количество<br>сотрудников, которые будут<br>заказывать обед</label></td>
        <td>{EMLOY_NUM}</td>
      </tr>
      <tr>
        <td colspan="2">
            <table class="tblPayment">
<!-- BEGIN pay_type -->
              <tr>
                <td width="240"><label>{PAY_TITLE} </label></td>
                <td style="text-align:left;"><input type="radio" name="form[PaymentType]" value="{PAY_VAL}" {CHECKED} disabled></td>
              </tr>
<!-- END pay_type -->
            </table>
        </td>
      </tr>
      <tr>
        <td><label>Желаемое время доставки</label></td>
        <td><select name="form[DeliveryTime]" disabled>{DELIV_TIME_OPTIONS}</select></td>
      </tr>
      <tr>
        <td><span class="red"></td>
        <td class="tdBtn">
            <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="/user_yourcompany/a/edit_proc" class="a01e">Изменить данные <b>&raquo;</b></a></td></tr></table>
        </td>
      </tr>
    </table>
</form>
