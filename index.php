<?php
include 'dbh.php';
?>




<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Expense Sheet</title>
  <link rel="stylesheet" href="stylesheet.css">
  <link rel="shortcut icon" href="#">
  <!-- Bootstrap css 4.0.0 -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <!-- jQuery 3.2.1 -->
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <!-- Popper js  1.12.9 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <!-- Bootstrap js 4.0.0 -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <!-- google sheets api  -->
  <script src="api.js" type="text/javascript"></script>
  <script async defer src="https://apis.google.com/js/api.js"
    onload="this.onload=function(){};handleClientLoad()"
    onreadystatechange="if (this.readyState === 'complete') this.onload()">
  </script>

</head>
<body>
  <header>
    <div class="container">
          <div class="row">
            <div class="col offset-11">
              <button type="button" class="btn btn-outline-primary" id="signin-button" onclick="handleSignInClick()">Sign in to Google</button>
            </div>
        </div>
    </div>
    <div class="container" id="calculation_container">
        <div class="row header_wrapper">
          <div class="col-3 offset-4" id="total_expense">
            <input type="text" class="form-control expense_all" placeholder="Total Amount">
          </div>
        </div>
    </div>
  </header>


<div class="container">
    <nav aria-label="Page navigation example">
      <ul class="pagination">
        <li class="page-item" id="previous-page">
          <a class="page-link" href="javascript:void(0)" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
            <span class="sr-only">Previous</span>
          </a>
        </li>
        <div id="page-wrapper">
        <li class="page-item current-page active"><a class="page-link" href="javascript:void(0)">1</a></li>
       </div>
        <li class="page-item" id="next-page">
          <a class="page-link" href="javascript:void(0)" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
            <span class="sr-only">Next</span>
          </a>
        </li>
      </ul>
    </nav>
</div>



  <div class="container" id="input_container">
    <div class="row input_wrapper">
      <div class="col" id="input_date">
        <input type="text" class="form-control input_user_date" placeholder="Date">
      </div>
      <div class="col" id="input_category">
        <datalist id="list_category">
        </datalist>
        <input type="text" class="form-control input_user_category" placeholder="Category" list="list_category">
      </div>
      <div class="col-4" id="input_description">
        <input type="text" class="form-control input_user_description" placeholder="Description">
      </div>
      <div class="col" id="input_value">
        <input type="text" class="form-control input_user_value" placeholder="Value">
      </div>
      <div class="col" id="input_submit">
        <button type="button" class="btn btn-outline-primary">Submit</button>
      </div>
    </div>
  </div>


  <div class="container" id="output_container">
    <?php
        $sql = "SELECT * FROM expenses LIMIT 3";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)){
            echo "<p>";
            echo $row['date'];
            echo $row['category'];
            echo $row['description'];
            echo $row['price'];
            echo "</p>";
          }
        } else {
          echo "There are no data!";
        }
  ?>
  <!-- comment -->
  </div>

  <div class="container-fluid" id="category_output_container">
      <div class="row no-gutters justify-content-center category_output_wrapper">
        <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="Toolbar with button groups" id="unique_category_wrapper">
        </div>
      </div>
  </div>

</body>


<script>

let counter = $("div[class*='output_wrapper_']").length;
let cols = '';

let date_val = $('.input_user_date');
let category_val = $('.input_user_category');
let description_val = $('.input_user_description');
let value_val = $('.input_user_value');

let unique_category_wrapper = $("div[id*='unique_category_wrapper']");
let rendered_category = $("div[id*='category_list_']");

let pageWrapper = $('#page-wrapper');
let limitPerPage = 10;
//let currentPage = Math.ceil(counter / limitPerPage);

$(document).ready(function(){
  enterKeySubmit();

          $('#input_submit').on('click', ()=>{
            createOption();
            resetFocus();
            createNewRow();
            clearInputField();
            calculateTotalCost();
            createCategory(isUniqueCategory(isAllCategory()));
            generatePagination();

          });

});



function calculateTotalCost(){
  var totalCost = 0;
  $(document).find("input[name*='expense']").each(function (){
    totalCost += +$(this).val();
  })
  $("input[class*='expense_all']").val(totalCost);
}


const resetFocus = () => {
  $('.input_user_date').focus();
}


const enterKeySubmit = () => {
  $('#input_submit').keyup((event)=>{
    if (event.which === 13) {
      $('#input_submit').click();
    }
  })
}


const createOption = () => {
  let input_new_option = $('.input_user_category').val();
  let add_new_option = $('<option>', {
    "value" : input_new_option
  })
  $('#list_category').append(add_new_option);
}

const refreshCategory = () => {
  $(document).find("div[id*='category_list_']").each(()=>{
    $("div[id*='category_list_']").remove();
  })
}


const createCategory = (arr) => {

  refreshCategory();
  let category_counter = arr.length;

  if (category_counter > 0) {

    for (let i = 0; i < category_counter; i++) {

      let category_col = $('<div>', {
        "class" : "input-group",
        "id" : "category_list_" + category_counter
      })

      let category_name_wrapper = $('<div>', {
        "class" : "input-group-prepend"
      })

      let category_name = $('<div>', {
        "class" : "input-group-text",
        "id" : "btnGroupAddon2"
      }).text(arr[i])

      let category_expense = $('<input>', {
        "type" : "text",
        "class" : "form-control",
        "placeholder" : "category total",
        "aria-label" : "category total",
        "aria-describedly" : "btnGroupAddon2"
      })

      unique_category_wrapper.append(category_col);
      category_name_wrapper.append(category_name);
      category_col.append(category_name_wrapper);
      category_col.append(category_expense);
    }

  } else {
    return false;
  }
}

const isAllCategory = () => {
  //if ($("input[class*='input_user_category']").val()) {
    //alert('this is empty')

    let selected_category = $("input[name*='selected_category']");
    let empty_array = [];

    for (let i = 0; i < selected_category.length; i++) {
          empty_array.push($(selected_category[i]).val());
    }
    return empty_array;

//  }

}

const isUniqueCategory = (arr) => {
  let uniqueCategory = [...new Set(arr)];
  return uniqueCategory;
}

const clearInputField = () => {
  date_val.val('');
  category_val.val('');
  description_val.val('');
  value_val.val('');
}

const createNewRow = () => {

  let outputWrapper = $('<div>', {
    "class" : "row output_wrapper_"+ counter
  }).css("margin-bottom","5px");

  let first_col = $('<div>', {
    "class" : "col",
    "id" : "input_date_" + counter
  })

  let second_col = $('<div>', {
    "class" : "col",
    "id" : "input_category_" + counter
  })

  let third_col = $('<div>', {
    "class" : "col-6",
    "id" : "input_description_" + counter
  })

  let fourth_col = $('<div>', {
    "class" : "col",
    "id" : "input_value_" + counter
  })

  for (let col = 0; col < 4; col++){
    if (col =='0') {
      let date_input = $('<input>', {
        "type" : "text",
        "class" : "form-control",
        "placeholder" : "Date",
        "id" : counter + ':' + col,
        "value" : date_val.val()
      })
      first_col.append(date_input);
      outputWrapper.append(first_col);
    }
    else if (col == '1') {
      let category_input = $('<input>', {
        "type" : "text",
        "class" : "form-control",
        "placeholder" : "Category",
        "name" : "selected_category",
        "id" : counter + ':' + col,
        "value" : category_val.val()
      })
      second_col.append(category_input);
      outputWrapper.append(second_col);
    }
    else if (col == '2') {
      let description_input = $('<input>', {
        "type" : "text",
        "class" : "form-control",
        "placeholder" : 'Description',
        "id" : counter + ':' + col,
        "value" : description_val.val()
      })
      third_col.append(description_input);
      outputWrapper.append(third_col);
    }
    else if (col = '3') {
      let value_input = $('<input>', {
        "type" : "text",
        "class" : "form-control",
        "placeholder" : "Value",
        "name" : "expense",
        "id" : counter + ':' + col,
        "value" : value_val.val()
      })
      fourth_col.append(value_input);
      outputWrapper.append(fourth_col);
    }
  }

  $('#output_container').append(outputWrapper);
  counter = counter + 1;
}


 const generatePagination = () => {

    //let numberOfRows = counter;
    // let limitPerPage = 10;
    let totalPage = Math.ceil(counter / limitPerPage);
    let divisionVar = 1;

    if (totalPage !== 1) {

            if (counter % 10 == divisionVar) {
              divisionVar = divisionVar + 1;
              pageWrapper.append("<li class='page-item current-page'><a class='page-link' href='javascript:void(0)'>" + totalPage + "</a></li>");
            }
    }

    $("div[class*='output_wrapper_']:gt(" + (limitPerPage - 1) + ")").hide();
 }




 $(document).on("click", "li.current-page", function () {

   if ($(this).hasClass("active")) {
     return false;
   } else {
     let index = $(this).index() + 1;
     $(".pagination li").removeClass("active");
     $(this).addClass("active");
     $("div[class*='output_wrapper_'").hide();

      let grandTotal = limitPerPage * index;
      for (let i = grandTotal - limitPerPage; i < grandTotal; i++){
          $("div[class*='output_wrapper_']:eq(" + i + ")").show()
      }
   }
 })


 $("#next-page").on("click", function () {

    let index = $('.active').index() + 1;
    let totalPage = Math.ceil(counter / limitPerPage);

    if (index === totalPage) {
      return false;
    } else {
      index++;
      $(".pagination li").removeClass("active");
      $("div[class*='output_wrapper_'").hide();
       let grandTotal = limitPerPage * index;
      for (let i = grandTotal - limitPerPage; i < grandTotal; i++){
          $("div[class*='output_wrapper_']:eq(" + i + ")").show()
      }
       $("li.current-page:eq(" + (index - 1) + ")").addClass("active");
    }
 })

$("#previous-page").on("click", function () {

   let index = $('.active').index() + 1;
   let totalPage = Math.ceil(counter / limitPerPage);

   if (index === 1) {
     return false;
   } else {
     index--;
     $(".pagination li").removeClass("active");
     $("div[class*='output_wrapper_'").hide();
      let grandTotal = limitPerPage * index;
     for (let i = grandTotal - limitPerPage; i < grandTotal; i++){
         $("div[class*='output_wrapper_']:eq(" + i + ")").show()
     }
      $("li.current-page:eq(" + (index - 1) + ")").addClass("active");
   }
})


// const calculateCategory = () => {
//
// }

</script>
</html>
