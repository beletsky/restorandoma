            <div class="menu_calendar">
                <div class="month">
                    <div class="month_inner">
<!-- BEGIN prev_month -->
                        <a href="{PREV_MONTH_URL}"><img src="/images/ico_next_month.gif" alt="&lt;" width="11" height="11" ></a>
<!-- END prev_month -->
                        {MONTH_NAME}
<!-- BEGIN next_month -->
                        <a href="{NEXT_MONTH_URL}"><img src="/images/ico_next_month.gif" alt="&gt;" width="11" height="11" ></a>
<!-- END next_month -->
                    </div>
                </div>
                <p class="arrow">Наше</p>
                <p class="calendar_header">меню</p>
                
                <table class="tbl_calendar" cellpadding="0" cellspacing="0">
<!-- BEGIN line -->
                  <tr>
<!-- BEGIN one_day --><!-- BEGIN old_day -->
                    <td>{DAY}</td>
<!-- END old_day --><!-- BEGIN active_day -->
                    <td><a href="{URL}">{DAY}</a></td>
<!-- END active_day --><!-- BEGIN new_day -->
                    <td><span class="red">{DAY}</span></td>
<!-- END new_day --><!-- END one_day -->
<!-- BEGIN br -->
<!-- END br -->
                  </tr>
<!-- END line -->
                </table>
            </div>

