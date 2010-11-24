{PAGE_TEXT}<br>
<table class="tblPersonal">
<tr>
    <th width="10%" height="40" valign="top" class="first" nowrap>№</th>
    <th width="40%" valign="top" nowrap>ФИО</th>
    <th width="30%" valign="top" nowrap>Сумма на счете</th>
    <th width="20%" valign="top" class="last" nowrap>Статус</th>
</tr>
<!-- BEGIN one_employ -->
<tr>
    <td valign="top">{NUMBER}</td>
    <td valign="top">
        <p><a href="{UPDATE_USER_URL}">{EMPLOY_NAME}</a></p>
        <p style="font-size:12px;"><img src="/images/icn02d_1.gif" width="12" height="9" align="absbottom" alt="" style="margin-right:5px;"><a href="{SEND_MESSAGE_URL}">Написать</a></p></td>
    <td valign="top">{SUMM} руб.<br>
        <p style="font-size:12px;"><img src="/images/icn02d_1.gif" width="12" height="9" align="absbottom" alt="" style="margin-right:5px;"><a href="{ADD_AMOUNT_URL}">Пополнить</a></p>
    </td>
    <td valign="top">{STATUS}</td>
</tr>
<!-- END one_employ -->
</table>
