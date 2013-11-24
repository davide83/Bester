<div id="contentlayer" class="content-layer">
<link rel="shortcut icon" href="images/favicon.ico" />

<style type="text/css">@import url(/css/calendar-brown.css);</style>
<script type="text/javascript" src="/js/calendar.js"></script>
<script type="text/javascript" src="/js/calendar-en.js"></script>
<script type="text/javascript" src="/js/calendar-setup.js"></script>


<h1><!-- #MainTitle --><!-- MainTitle# --></h1>
<h2><!-- #Message1 --><!-- Message1# --></h2>
<form name="accountform" action="" method="post" enctype="multipart/form-data">

  <table>
    <tr>
      <td class="noborder"><label for="title"><!-- #Title --><!-- Title# --></label></td><td class="noborder"><input type="text" name="title" class="textfield" value="<!-- #InsTitle --><!-- InsTitle# -->"></td>
    </tr>
    <tr>
      <td class="noborder"><!-- #SubTitle --><!-- SubTitle# --></td><td class="noborder"><input type="text" name="subtitle" class="textfield" value="<!-- #InsSubTitle --><!-- InsSubTitle# -->"></td>
    </tr>
    <tr>
      <td class="noborder"><!-- #StartDate --><!-- StartDate# --></td><td class="noborder"><input type="text" name="startdate" id="startdate" class="textfield" value="<!-- #InsStartDate --><!-- InsStartDate# -->"><button type="reset" id="startdate_b" class="button" >select</button></td>
    </tr>    
    <tr>
      <td class="noborder"><!-- #EndDate --><!-- EndDate# --></td><td class="noborder"><input type="text" name="enddate" id="enddate" class="textfield" value="<!-- #InsEndDate --><!-- InsEndDate# -->"><button type="reset" id="enddate_b" class="button" >select</button></td>
    </tr> 
        <tr>
      <td class="noborder"><!-- #PosNr --><!-- PosNr# --></td>
      <td  class="noborder">
  <select name="posnr" size="1">
    <!-- #SelectItem -->
    <option value="<!-- #SelectItemValue --><!-- SelectItemValue# -->"  <!-- #ItemSelected --><!-- ItemSelected# -->><!-- #SelectItemName --><!-- SelectItemName# --></option>
    <!-- SelectItem# -->
  </select>
</td>
    </tr>  
  </table>

<p><!-- #BetImage --><!-- BetImage# --></p>
<p><input type="file" name="betimage" class="textfield" value="<!-- #InsPosNr --><!-- InsPosNr# -->"></p>
<!-- #HiddenFields --><!-- HiddenFields# -->

<input type="submit" name="<!-- #ButtonDeleteName --><!-- ButtonDeleteName# -->" class="button" value="<!-- #ButtonDeleteValue --><!-- ButtonDeleteValue# -->">
<input type="submit" name="<!-- #ButtonGoName --><!-- ButtonGoName# -->" class="button" value="<!-- #ButtonGoValue --><!-- ButtonGoValue# -->">
</form>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "startdate",      // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "startdate_b",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "enddate",      // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "enddate_b",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

</div>
</div>

<div id="rightlayer" class="right-layer">