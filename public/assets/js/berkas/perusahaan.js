import $globalVariable from "../base-function.js";
(function ($) {
    "use strict";

    $(".dropify").dropify();
    // $(".select2").select2();

    $(".btnSave").on("click", function (e) {
        let tabpane = $(".tab-pane.fade.active.show");
        let form = tabpane.find("form");
        $globalVariable.formAjax({
          form,
          callbackSuccess: function (response, status, jqxhr, form) {
            if (response.status) {
              $globalVariable.baseSwal('success', 'Berhasil', response.message, "success");
            } else {
              $globalVariable.baseSwal('danger', 'Gagal',response.message, "error");
            }
          }
        });
    });
    var pemisah, format1, format2, format3, format4;
    $('#PEMISAH').on('change', function(e){
      pemisah = $('#PEMISAH').find('option:selected').html();
      format1 = format($('#FORMAT1').val());
      format2 = format($('#FORMAT2').val());
      format3 = format($('#FORMAT3').val());
      format4 = format($('#FORMAT4').val());
      setContoh();
    })
    $('#FORMAT1').on('change', function(e){
      pemisah = $('#PEMISAH').find('option:selected').html();
      format1 = format($('#FORMAT1').val());
      format2 = format($('#FORMAT2').val());
      format3 = format($('#FORMAT3').val());
      format4 = format($('#FORMAT4').val());
      setContoh();

    })
    $('#FORMAT2').on('change', function(e){
      pemisah = $('#PEMISAH').find('option:selected').html();
      format1 = format($('#FORMAT1').val());
      format2 = format($('#FORMAT2').val());
      format3 = format($('#FORMAT3').val());
      format4 = format($('#FORMAT4').val());
      setContoh();

    })
    $('#FORMAT3').on('change', function(e){
      pemisah = $('#PEMISAH').find('option:selected').html();
      format1 = format($('#FORMAT1').val());
      format2 = format($('#FORMAT2').val());
      format3 = format($('#FORMAT3').val());
      format4 = format($('#FORMAT4').val());
      setContoh();

    })
    $('#FORMAT4').on('change', function(e){
      pemisah = $('#PEMISAH').find('option:selected').html();
      format1 = format($('#FORMAT1').val());
      format2 = format($('#FORMAT2').val());
      format3 = format($('#FORMAT3').val());
      format4 = format($('#FORMAT4').val());
      setContoh();
    })

    function setContoh(){
      $('#Contoh').val(format1+pemisah+format2+pemisah+format3+pemisah+format4);
    }

    function format(value){
      let fmt = '';
      if(value == 0){
        fmt = $('input[name="ALIAS"]').val();
      }else if(value == 1){
        fmt = 'LPB'
      }else if(value == 2){
        fmt = '0322'
      }else if(value == 3){
        fmt = '032022'
      }else if(value == 4){
        fmt = '00000'
      }else if(value == 5){
        fmt = '2203'
      }else if(value == 6){
        fmt = '202203'
      }
      return fmt
    }
})(jQuery);
