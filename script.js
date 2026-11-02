$(document).ready(function () {

  // 1. Date of birth cannot be in the future
  var today = new Date().toISOString().split("T")[0];
  $("#dob").attr("max", today);

  // 2. Allow only digits in phone and PIN fields
  $("#phone, #pin").on("input", function () {
    this.value = this.value.replace(/\D/g, "");
  });

  // 3. Show age automatically when DOB is chosen
  $("#dob").on("change", function () {
    var dob = new Date(this.value);
    if (isNaN(dob)) { $("#age").text(""); return; }
    var now = new Date();
    var age = now.getFullYear() - dob.getFullYear();
    var m = now.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < dob.getDate())) { age--; }
    $("#age").text(age >= 0 ? "(Age: " + age + ")" : "");
  });

  // 4. Character counter for address
  $("#address").on("input", function () {
    $("#count").text(this.value.length);
  });

  // 5. Helpers to show and clear errors
  function showError(id, msg) {
    $("#" + id).addClass("invalid");
    $("#" + id + "-err").text(msg).show();
  }
  function clearError(id) {
    $("#" + id).removeClass("invalid");
    $("#" + id + "-err").hide();
  }

  // Remove the error as soon as the user fixes the field
  $("#reg-form input, #reg-form select, #reg-form textarea").on("input change", function () {
    var id = this.id || this.name;
    clearError(id);
    if (this.name === "gender") { $("#gender-err").hide(); }
  });

  // 6. Validate the whole form
  function validate() {
    var ok = true;
    $(".error").hide();
    $(".invalid").removeClass("invalid");

    var nameRe = /^[A-Za-z .]{3,50}$/;
    var v = function (id) { return $("#" + id).val().trim(); };

    if (!nameRe.test(v("name")))   { showError("name", "Enter a valid name (letters only, min 3)."); ok = false; }
    if (!nameRe.test(v("father"))) { showError("father", "Enter a valid father's name."); ok = false; }

    if (v("dob") === "") {
      showError("dob", "Select your date of birth."); ok = false;
    } else {
      var age = new Date().getFullYear() - new Date(v("dob")).getFullYear();
      if (age < 15 || age > 60) { showError("dob", "Age must be between 15 and 60."); ok = false; }
    }

    if (!$("input[name=gender]:checked").length) {
      $("#gender-err").text("Please select a gender.").show(); ok = false;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v("email"))) { showError("email", "Enter a valid email address."); ok = false; }
    if (!/^[6-9]\d{9}$/.test(v("phone")))                { showError("phone", "Enter a 10-digit number starting with 6-9."); ok = false; }
    if (v("address").length < 10)                         { showError("address", "Address must be at least 10 characters."); ok = false; }
    if (!/^[A-Za-z .]{2,40}$/.test(v("city")))           { showError("city", "Enter a valid city."); ok = false; }
    if (v("state") === "")                                { showError("state", "Select a state."); ok = false; }
    if (!/^\d{6}$/.test(v("pin")))                        { showError("pin", "PIN must be 6 digits."); ok = false; }
    if (v("qualification") === "")                        { showError("qualification", "Select a qualification."); ok = false; }

    var marks = parseFloat(v("marks"));
    if (isNaN(marks) || marks < 0 || marks > 100)         { showError("marks", "Enter a percentage from 0 to 100."); ok = false; }
    if (v("course") === "")                               { showError("course", "Select a course."); ok = false; }

    if (!$("#declare").is(":checked")) {
      $("#declare-err").text("Please accept the declaration.").show(); ok = false;
    }
    return ok;
  }

  // 7. On submit: stop if invalid, otherwise let PHP receive the data
  $("#reg-form").submit(function (e) {
    if (!validate()) {
      e.preventDefault();
      var first = $(".error:visible").first();
      $("html, body").animate({ scrollTop: first.offset().top - 140 }, 400);
    } else {
      $(this).find("button[type=submit]").text("Submitting...");
    }
  });

  // 8. Reset button clears errors, age and counter
  $("#reg-form").on("reset", function () {
    $(".error").hide();
    $(".invalid").removeClass("invalid");
    $("#age").text("");
    $("#count").text("0");
  });

});