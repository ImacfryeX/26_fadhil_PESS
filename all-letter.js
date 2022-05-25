$(function(){
  $("input[name='conactNo']").on('input', function () {
    $(this).val($(this).val().replace(/[^0-9]/g, ''));
  });
});