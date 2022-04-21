console.log("Mr Faiz");
$(document).ready(function () {
  // alert('ay');
  // $("#search").change(function (event) {
  // });
  $("#product_name").on("change", function () {
    console.log("hhhh");
    let product_name = $('#product_name').val();
    // console.log(product_name);
    $.ajax({
      url: "http://localhost:8080/login/getvariation",
      data: {
        "id": product_name
      },
      method: "POST",
      dataType: "JSON"

    }).done(function (data) {
      // console.log(data);
      for (var i = 0; i < data.length; i++) {
        // console.log(data[i]);
        var options = "";
        if (data[i].added_variants) {
          console.log("j");
          // console.log(data[i].added_variants);
          var added_variants = Array(data[i].added_variants);
          console.log(added_variants.length);
          // for(var k=0; k<= added_variants.length; k++){
          for (k in added_variants) {
            console.log("h");
            console.log(added_variants[k].color);
            for (j in added_variants[k]) {
              options += "<option value='" + added_variants[k][j] + "'>" + added_variants[k][j] + "</option>";
            }
            option_names = "";
          }
        }
      }
      $("#variation_name").append(options);
    })
    // $("#search").css("background-color", "pink");
    // let sel1 = $("#search").val();
    // console.log($("#search").val());


  });

});