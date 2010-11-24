<!-- Блок текста с картинкой -->
<h1>{DATE}</h1>
<table width="100%" cellspacing="0" cellpadding="0">
<tr>
    <th width="10%" valign="top" nowrap>Вес</th>
    <th width="80%" valign="top" nowrap>Наименование</th>
    <th width="10%" valign="top" nowrap>Цена</th>
</tr>
<!-- BEGIN main_row -->
    <!-- BEGIN header_row -->
    <tr class="subheader"><td colspan="3"><h2>{HEADER}</h2></td></tr>
    <!-- END header_row -->
    <!-- BEGIN simple_row -->
    <tr>
        <td valign="top" nowrap align="center">{WEIGHT} гр.</td>
        <td valign="top">{TITLE}</td>
        <td valign="top" nowrap align="right">{PRICE} руб.</td>
    </tr>
    <!-- END simple_row -->
    <!-- BEGIN sep_row -->
    <!-- END sep_row -->
<!-- END main_row -->
</table>
