{PAGE_TEXT}
<form class="frmRegistration" action="/{PAGE_CODE}/a/{ACTION}" name="OrderSearchForm" method="post">
<!--
<input type="hidden" name="PageCode" value="{PAGE_CODE}">
<input type="hidden" name="a" value="{ACTION}">
-->        
<table width="100%" cellspacing="0" cellpadding="0">
<!-- BEGIN errors -->
      <tr>
        <td></td>
        <td colspan="1" class="red" style="padding-left:12px;font-size:12px;text-align:left;">{ARCHIVE_ERRORS}</td>
      </tr>
<!-- END errors -->
      <tr>
        <td><label>Начиная с даты</label></td>
        <td>{START_DATE_SELECT}</td>
      </tr>
      <tr>
        <td><label>И заканчивая датой</label></td>
        <td>{END_DATE_SELECT}</td>
      </tr>
      <tr>
        <td><span class="red"><i>* - поля, обязательные для заполнения</i></span></td>
        <td class="tdBtn">
          <table width="1" height="27" cellspacing="0" cellpadding="0" class="u01h"><tr><td align="center" class="u05g_1" nowrap><a href="#" class="a01e" onclick="document.OrderSearchForm.submit(); return false;">Искать <b>&raquo;</b></a></td></tr></table>
        </td>
      </tr>
</table>
</form>
