{PAGE_TEXT}
<table class="tblPersonal">
<tr>
    <th align="center" width="15%" height="40" valign="top" nowrap class="first">Дата</th>
    <th align="center" width="25%" valign="top" nowrap>Сумма</th>
    <th align="center" width="35%" valign="top" nowrap>Вид операции</th>
    <th align="center" width="25%" valign="top" nowrap class="last">Остаток</th>
</tr>
<!-- BEGIN account_op_item -->
<tr>
    <td width="15%" align="center" valign="top">{OP_DATE}</td>
    <td width="25%" align="right" valign="top">{OP_SUMM} руб.</td>
    <td width="35%" align="center" valign="top">{OP_TYPE}</td>
    <td width="25%" align="right" valign="top">{AC_LEFT} руб.</td>
</tr>
<!-- END account_op_item -->
<tr class="total">
    <td colspan="3" valign="bottom">Всего начислено за период:</td>
    <td align="right" valign="bottom"><b>{TOTAL_ADD} руб.</b></td>
</tr>
<tr class="total">
    <td colspan="3" valign="bottom">Всего списано за период:</td>
    <td align="right" valign="bottom"><b>{TOTAL_MINUS} руб.</b></td>
</tr>
<tr class="total">
    <td colspan="3" valign="bottom"><b>Текущий остаток на счете:</b></td>
    <td align="right" valign="bottom"><b>{CUR_LEFT} руб.</b></td>
</tr>
</table>
