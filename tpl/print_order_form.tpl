<!-- Блок текста с картинкой -->
<h1>{DATE}</h1>
<table width="100%" cellspacing="0" cellpadding="0">
<tr>
    <th width="10%" height="40" valign="center" nowrap>Вес</th>
    <th width="80%" height="40" valign="center" nowrap>Наименование</th>
    <th width="10%" valign="center" nowrap>Цена</th>
    <!-- BEGIN client -->
    <th width="10%" valign="center">{CLIENT_NAME}</th>
    <!-- END client -->
</tr>
<!-- BEGIN main_row -->
    <!-- BEGIN header_row -->
    <tr class="subheader"><td colspan="999"><h2>{HEADER}</h2></td></tr>
    <!-- END header_row -->
    <!-- BEGIN simple_row -->
    <tr>
        <td valign="top" nowrap align="center">{WEIGHT} гр.</td>
        <td valign="top">{TITLE}</td>
        <td valign="top" nowrap align="center">{PRICE} руб.</td>
        <!-- BEGIN simple_row_cell -->
        <td valign="top" nowrap class="outlined">&nbsp;</td>
        <!-- END simple_row_cell -->
    </tr>
    <!-- END simple_row -->
    <!-- BEGIN sep_row -->
    <!-- END sep_row -->
<!-- END main_row -->
</table>
