{PAGE_TEXT}
<table class="tblPersonal">
<tr>
    <th width="10%" height="40" valign="top" class="first" nowrap>№</th>
    <th width="25%" height="40" valign="top" nowrap>Дата</th>
    <th width="40%" valign="top" nowrap>Сумма заказа</th>
    <th width="25%" valign="top" class="last" nowrap>Состояние</th>
</tr>
<!-- BEGIN one_order -->
<tr>
    <td valign="top">{ORDER_NUM}</td>
    <td valign="top">{DATE}</td>
    <td valign="top">
        <table class="tblPlain">
        <tr>
            <td valign="top"><label>Цена:</label></td>
            <td valign="top" nowrap>{PRICE} руб.</td>
        </tr>
        <tr>
            <td valign="top"><label>Доставка:</label></td>
            <td valign="top" nowrap>{DELIV_PRICE} руб.</td>
        </tr>
        <tr>
            <td valign="top"><b>Итого:</b></td>
            <td valign="top" nowrap>{TOTAL_PRICE} руб.</td>
        </tr>
        </table>
    </td>
    <td valign="top"><p><a href="{URL}">{STATUS}</a></p></td>
</tr>
<!-- END one_order -->
</table>
