<script language="JavaScript1.2">
function Init()
{
    VarA = new Array('ItemNum[]','ItemPrice[]', 'ItemID[]','SummOut','Summ','OrderForm');
    for (i = 0; i < VarA.length; i++)
    {
        if (VarA[i].indexOf('[]') != -1)
        {
            eval("if (document.getElementsByName('" + VarA[i] + "')) {" + VarA[i].replace(/\[\]$/, "") + "_ = document.getElementsByName('" + VarA[i] + "')};");
        }
        else
        {
            eval("if (document.getElementById('" + VarA[i] + "')) {" + VarA[i] + "_ = document.getElementById('" + VarA[i] + "')};");
        }
    }
    Calc();
}

function Calc()
{
    ItemSummTemp = 0;
    for (i = 0; i < ItemNum_.length; i++)
    {
        ItemSummTemp += p(ItemNum_[i].value)*p(
        ItemPrice_[i].value);
    }
        
    Summ_.value = ItemSummTemp;
    SummOut_.innerHTML = ItemSummTemp;
}

function OrderSubmit()
{
    El = OrderForm_;
    if (SummOut_.innerHTML != "0")
        document.forms.OrderForm.submit();
    else
        alert('����������, �������� �����');
}

window.onload = Init;
</script>


{PAGE_TEXT}

<form class="frmMenu" action="/confirm_order/a/edit_order" name="OrderForm" id="OrderForm" method="post" >
<input type="hidden" name="form[OrderDate]" value={SHOW_DATE}>
<input type="hidden" name="form[IDClientUser]" value={IDCLIENTUSER}>
<input type="hidden" name="form[IDOrder]" value="{IDORDER}">
<!--
<input type="hidden" name="PageCode" value="confirm_order">
<input type="hidden" name="a" value="edit_order">
-->

<!-- BEGIN main_row -->
<h3>{HEADER}</h3>
<!-- ���� ����� -->
<table>
<!-- BEGIN simple_row -->
<tr>
<!--            <td width="20" valign="top" nowrap><input type="checkbox" name="ItemStatus[]" onclick="Calc();" class="ic01" checked></td> -->
    <td width="100%" valign="top">{TITLE}<br /><b>�����:</b> {WEIGHT} ��.<br /><b>������:</b> {INGREDIENTS}</td>
    <td valign="top" nowrap>{PRICE} ���.<input type="hidden" name="ItemPrice[]" value="{PRICE}"></td>
    <td valign="top" nowrap>���-��</td>
    <td width="30" valign="top" nowrap><input type="text" name="ItemNum[]" onchange="Calc();" value="{NUM}" style="width:30px;"></td>
    <input type="hidden" name="ItemID[]" value="{CODE}">
</tr>
<!-- END simple_row -->
</table>
<!-- !���� ����� -->
<!-- END main_row -->


<!-- ���� ������ � ��������� -->
<table class="frmData">
<tr>
    <td valign="top" colspan="2" nowrap>����� �� ����� - <b><span id="SummOut">0</span> ���.</b><input type="hidden" id="Summ" value="0"></td>
</tr>
<tr>
    <td align="right" valign="top">
        <!-- ������ -->
        <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a onClick="javascript:OrderSubmit(); return false;" href="#" class="a01e">{BTN_CAPTION} <b>&raquo;</b></b></a></td></tr></table>
        <!-- !������ -->
    </td>
</tr>
</table>
<!-- !���� ������ � ��������� -->
</form>
