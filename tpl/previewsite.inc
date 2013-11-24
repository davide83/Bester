<div id="contentlayer" class="content-layer">
<h1><!-- #MainTitle --><!-- MainTitle# --></h1>

<div class="betlayer">
<!-- #Text --><!-- Text# -->

<label><!-- #Date --><!-- Date# --></label>
<form action="" method="post" name="editor">

</div>
<div class="betlayer">
<form action="" method="post" name="editor">

<label for="title">Titel:</label><br>
<input name="title" value="<!-- #FieldTitle --><!-- FieldTitle# -->" size="40" type="text"><br><br>

<script language="javascript" type="text/javascript" src="../js/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
tinyMCE.init({
	mode : "textareas"
});
</script>
<label for="text">Eintrag:</label><br>
<textarea name="text" cols="55" rows="15"><!-- #FieldText --><!-- FieldText# --></textarea>
<br />
<input name="preview" value="preview" type="submit">
<input name="insert" value="insert" type="submit">
</form>
</div>
</div>
</div>
<div class="right-layer">