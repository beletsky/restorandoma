{PAGE_TEXT}
<!-- BEGIN error_block -->
<p>{ERRORS}</p>
<!-- END error_block -->
<!-- BEGIN date_status -->
<h3><b>���� ������:</b> {DATE_STRING}<br><b>������:</b> {STATUS}</h3>
<!-- END date_status -->
<table class="tblPersonal">
    <tr>
        <th width="10%" height="40" valign="top" class="first" nowrap>���</th>
        <th width="80%" height="40" valign="top" nowrap>������������</th>
        <th width="10%" valign="top" nowrap>����</th>
        <th width="10%" valign="top" class="last" nowrap>���-��</th>
    </tr>

<!-- BEGIN main_row -->
<!-- BEGIN header_row -->
    <tr><td colspan="4"><h3>{HEADER}</h3></td></tr>
<!-- END header_row -->
<!-- BEGIN simple_row -->
    <tr>
        <td valign="top" nowrap>{WEIGHT} ��.</td>
        <td valign="top">{TITLE}</td>
        <td valign="top" nowrap>{PRICE} ���.</td>
        <td valign="top" nowrap>{NUM} ��.</td>
        <input type="hidden" name="form[OrderListL][{ID_MEAL}][price]" value="{PRICE}">
        <input type="hidden" name="form[OrderListL][{ID_MEAL}][main]" value="{NUM}">
    </tr>
<!-- END simple_row -->
<!-- BEGIN sep_row -->
<!-- END sep_row -->
<!-- END main_row -->
    <tr class="total">
        <td valign="top" colspan="2" nowrap>����� �� �����</td>
        <td valign="top" nowrap><b>{SUMM} ���.</b></td>
        <td valign="top" nowrap></td>
    </tr>
</table>
<!-- BEGIN no_change_block -->
<table class="frmData">
<tr>
    <td valign="top" nowrap><label>����������� � ������:</label></td>
    <td width="100%" valign="top"><textarea name="form[Comments]" DISABLED>{COMMENTS}</textarea></td>
</tr>
</table>
<!-- END no_change_block -->
<!-- BEGIN update_block -->
<form class="frmRegistration" action="/user_companyorders/a/confirm_comp_order/show_date/{SHOW_DATE}" name="OrderForm" method="post">
<!--
    <input type="hidden" name="PageCode" value="user_companyorders">
    <input type="hidden" name="a" value="confirm_comp_order">
    <input type="hidden" name="show_date" value="{SHOW_DATE}">
-->
    <input type="hidden" name="form[IDClientUser]" value="{IDCLIENTUSER}">
    <input type="hidden" name="form[IDClietnComp]" value="{IDCLIENTCOMP}">
    <table>
        <tr>
            <td><label>����������� � ������:</label></td>
            <td><textarea class="txtArea" cols="" rows="" name="form[Comments]">{COMMENTS}</textarea></td>
        </tr>
        <tr>
            <td></td>
            <td class="tdBtn" style="text-align: right;">
                <table style="float:right;" width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="right" class="u05g_1" nowrap><a href="#" onclick="javascript:document.forms.OrderForm.submit(); return false;" class="a01e" >��������� ����� <b>&raquo;</b></a></td></tr></table>
            </td>
        </tr>
    </table>
</form>
<!-- END update_block -->

<table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="/user_companyorders.html" class="a01e"><b>&laquo;</b> ��������� � ������ ������� ��������</a></td></tr></table>
