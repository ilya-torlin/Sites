<?php
$con = new mysqli("127.0.0.1", "root", "wdaS7zCd", "first_db");
$message = $con->query("SELECT content FROM content_table")->fetch_object()->content;
$con->close();
echo "$message <br/>";
echo "Hello From Sites Folder! testwork.home";
?>
<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
  <script type="text/javascript">
  $(function() {
 $('form').submit(function(e) {
   var $form = $(this);
   var $that = $(this),
   formData = new FormData($form.get(0));
   $.ajax({
     type: $form.attr('method'),
     headers:{'Authorization' : 'Bearer nkQxsD5eS6qcpqIx'},
     url: 'http://api.testwork.home/files/post',
     contentType: false, // важно - убираем форматирование данных по умолчанию
      processData: false,
     //data: $form.serialize()
     data: formData
   }).done(function() {
     console.log('success');
   }).fail(function() {
     console.log('fail');
   });
   //отмена действия по умолчанию для кнопки submit
   e.preventDefault();
 });
});
  </script>
<form id="smartForm" action="" method=post enctype="multipart/form-data">
     <!--input type="text" name="filename" value="">
     <input type="text" name="content" value=""-->
<input name="file" type="file" />
<input class="smartButton" type="submit" value="ОТПРАВИТЬ" name="submit"/>
</form>
