{PAGE_TEXT}
<!-- BEGIN error_block -->
<p>{ERRORS}</p>
<!-- END error_block -->
<!-- BEGIN date_status -->
<h3><b>Дата заказа:</b> {DATE_STRING}<br><b>Статус:</b> {STATUS}</h3>
<!-- END date_status -->
<form class="frmMenu" action="" name="OrderForm">
    <input type="hidden" name="form[IDClientUser]" value="{IDCLIENTUSER}">
    
    <table class="tblPersonal">
    <tr>
        <th width="10%" height="40" valign="top" class="first" nowrap>Вес</th>
        <th width="80%" height="40" valign="top" nowrap>Наименование</th>
        <th width="10%" valign="top" nowrap>Цена</th>
        <th width="10%" valign="top" class="last" nowrap>Кол-во</th>
    </tr>
    <!-- BEGIN main_row -->
    <!-- BEGIN header_row -->
    <tr><td colspan="4"><h3>{HEADER}</h3></td></tr>
    <!-- END header_row -->
    <!-- BEGIN simple_row -->
    <tr>
        <td valign="top" nowrap>{WEIGHT} гр.</td>
        <td valign="top">{TITLE}</td>
        <td valign="top" nowrap>{PRICE} руб.</td>
        <td valign="top" nowrap>{NUM} шт.</td>
        <input type="hidden" name="form[OrderListL][{ID_MEAL}][price]" value="{PRICE}">
        <input type="hidden" name="form[OrderListL][{ID_MEAL}][main]" value="{NUM}">
    </tr>
    <!-- END simple_row -->
    <!-- BEGIN sep_row -->
    <!-- END sep_row -->
    <!-- END main_row -->
    <tr class="total">
        <td valign="top" colspan="2" nowrap>Заказ на сумму</td>
        <td valign="top" nowrap><b>{SUMM} руб.</b></td>
        <td valign="top" nowrap></td>
    </tr>
    </table>

    <table class="frmData" style="margin-top:20px;">
    <!-- BEGIN update_block -->
    <tr>
        <td valign="top">
            <!-- Кнопка -->
            <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="/menu/form[IDOrder]/{IDORDER}/show_date/{SHOW_DATE}" class="a01e"><b>&laquo;</b> Изменить заказ</a></td></tr></table>
            <!-- !Кнопка -->
        </td>
        <!-- BEGIN send_order_button -->
        <td align="right" valign="top">
            <!-- Кнопка -->
            <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="/user_myorders/a/confirm_order/form[IDOrder]/{IDORDER}" class="a01e" >Отправить заказ <b>&raquo;</b></a></td></tr></table>
            <!-- !Кнопка -->
        </td>
        <!-- END send_order_button -->
    </tr>
    <!-- END update_block -->
    </table>
    
</form>
<!-- BEGIN return_button -->
<!-- Кнопка -->
<table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="/user_myorders.html" class="a01e"><b>&laquo;</b> Вернуться к списку моих заказов</a></td></tr></table>
<!-- !Кнопка -->
<!-- END return_button -->
<!-- BEGIN return_employ_list_button -->
<!-- Кнопка -->
<table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="/user_companyorders/a/comp_ord_employ/show_date/{SHOW_DATE}" class="a01e"><b>&laquo;</b> Вернуться к списку заказов сотрудников</a></td></tr></table>
<!-- !Кнопка -->
<!-- END return_employ_list_button -->
<!-- BEGIN return_user_account_button -->
<!-- Кнопка -->
<table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="/user_account.html" class="a01e"><b>&laquo;</b> Вернуться к списку операций по личному счету</a></td></tr></table>
<!-- !Кнопка -->
<!-- END return_user_account_button -->
