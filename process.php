<?php
date_default_timezone_set("Asia/Kolkata");

// Only accept form submissions (not someone opening this file directly)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

// Helper: get a trimmed value from the form
function post($key) {
    return (isset($_POST[$key]) && is_string($_POST[$key])) ? trim($_POST[$key]) : "";
}
// Helper: make text safe to show on the page
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, "UTF-8");
}
// Helper: print one table row
function row($label, $value) {
    echo "<tr><th>" . e($label) . "</th><td>" . $value . "</td></tr>";
}

// ---- 1. Read the form data ----
$name          = post("name");
$father        = post("father");
$dob           = post("dob");
$gender        = post("gender");
$email         = post("email");
$phone         = post("phone");
$address       = post("address");
$city          = post("city");
$state         = post("state");
$pin           = post("pin");
$qualification = post("qualification");
$marks         = post("marks");
$course        = post("course");
$comments      = post("comments");
$declare       = isset($_POST["declare"]);

// Allowed values for dropdowns and checkboxes
$states  = ["Karnataka","Maharashtra","Goa","Tamil Nadu","Kerala","Andhra Pradesh","Telangana","Other"];
$quals   = ["10th / SSLC","12th / PUC","Diploma","Bachelor's Degree"];
$courses = ["Full Stack Web Development","Python Programming","Data Structures with Java","UI / UX Design","Digital Marketing"];
$allowedSkills = ["HTML","CSS","JavaScript","Python","Java"];

$skills = [];
if (isset($_POST["skills"]) && is_array($_POST["skills"])) {
    foreach ($_POST["skills"] as $s) {
        if (in_array($s, $allowedSkills, true)) { $skills[] = $s; }
    }
}

// ---- 2. Validate on the server ----
$errors = [];
$age = null;
$dobDate = DateTime::createFromFormat("Y-m-d", $dob);

if (!preg_match("/^[A-Za-z .]{3,50}$/", $name))   $errors[] = "Enter a valid full name (letters only, 3 to 50 characters).";
if (!preg_match("/^[A-Za-z .]{3,50}$/", $father)) $errors[] = "Enter a valid father's name (letters only).";

if (!$dobDate || $dobDate->format("Y-m-d") !== $dob) {
    $errors[] = "Enter a valid date of birth.";
} else {
    $now = new DateTime();
    if ($dobDate > $now) {
        $errors[] = "Date of birth cannot be in the future.";
    } else {
        $age = $dobDate->diff($now)->y;
        if ($age < 15 || $age > 60) $errors[] = "Age must be between 15 and 60 years.";
    }
}

if (!in_array($gender, ["Male","Female","Other"], true)) $errors[] = "Select your gender.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL))           $errors[] = "Enter a valid email address.";
if (!preg_match("/^[6-9][0-9]{9}$/", $phone))             $errors[] = "Mobile number must be 10 digits and start with 6, 7, 8 or 9.";
if (strlen($address) < 10 || strlen($address) > 200)      $errors[] = "Address must be 10 to 200 characters.";
if (!preg_match("/^[A-Za-z .]{2,40}$/", $city))           $errors[] = "Enter a valid city name.";
if (!in_array($state, $states, true))                     $errors[] = "Select a state.";
if (!preg_match("/^[0-9]{6}$/", $pin))                    $errors[] = "PIN code must be exactly 6 digits.";
if (!in_array($qualification, $quals, true))              $errors[] = "Select your highest qualification.";
if (!is_numeric($marks) || $marks < 0 || $marks > 100)    $errors[] = "Percentage must be a number between 0 and 100.";
if (!in_array($course, $courses, true))                   $errors[] = "Select a course.";
if (strlen($comments) > 200)                              $errors[] = "Comments must be under 200 characters.";
if (!$declare)                                            $errors[] = "You must accept the declaration.";

$appId       = "REG" . date("Ymd") . rand(1000, 9999);
$submittedOn = date("d M Y, h:i A");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo empty($errors) ? "Application Submitted" : "Please Correct the Form"; ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header class="top">
  <h1>Online Course Registration</h1>
</header>

<main class="wrap">

<?php if (!empty($errors)) { ?>

  <!-- ===== Errors found ===== -->
  <div class="result fail">
    <h2>Submission failed</h2>
    <p>Please fix the following and try again:</p>
    <ul>
      <?php foreach ($errors as $err) { echo "<li>" . e($err) . "</li>"; } ?>
    </ul>
    <p class="buttons"><a class="btn" href="javascript:history.back()">Go back to the form</a></p>
  </div>

<?php } else { ?>

  <!-- ===== Success ===== -->
  <div class="result success">
    <div class="tick">&#10003;</div>
    <h2>Application submitted successfully</h2>
    <p>Thank you, <b><?php echo e($name); ?></b>. Your details are shown below.</p>
    <p class="appid">Application ID: <b><?php echo $appId; ?></b> &bull; <?php echo $submittedOn; ?></p>
  </div>

  <section class="summary">
    <h3>Personal Details</h3>
    <table>
      <?php
      row("Full Name", e($name));
      row("Father's Name", e($father));
      row("Date of Birth", e($dobDate->format("d F Y")) . " (" . $age . " years)");
      row("Gender", e($gender));
      ?>
    </table>

    <h3>Contact Details</h3>
    <table>
      <?php
      row("Email", e($email));
      row("Mobile", "+91 " . e($phone));
      row("Address", nl2br(e($address)));
      row("City / State", e($city) . ", " . e($state));
      row("PIN Code", e($pin));
      ?>
    </table>

    <h3>Course Details</h3>
    <table>
      <?php
      row("Qualification", e($qualification));
      row("Percentage", e(number_format((float)$marks, 2)) . " %");
      row("Course Applied", "<b>" . e($course) . "</b>");
      row("Skills Known", empty($skills) ? "None selected" : e(implode(", ", $skills)));
      row("Comments", $comments === "" ? "-" : nl2br(e($comments)));
      ?>
    </table>
  </section>

  <p class="buttons">
    <button class="btn" onclick="window.print()">Print Application</button>
    <a class="btn outline" href="index.html">New Registration</a>
  </p>

<?php } ?>

</main>

<footer>Web Technologies Lab &bull; Programming Assignment 2</footer>
</body>
</html>