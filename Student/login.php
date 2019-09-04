<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true){
    header("location: student_dashboard.php");
    exit;
}
 
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$student_id = $entered_student_password = "";
$student_id_err = $student_password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if student_id is empty
    if(empty($_POST["student_id"])){
        $student_id_err = "Please enter your student id.";
    } else{
        $student_id = $_POST["student_id"];
    }
    
    // Check if entered_student_password is empty
    if(empty($_POST["entered_student_password"])){
        $student_password_err = "Please enter your student password.";
    } else{
        $entered_student_password = $_POST["entered_student_password"];
    }
    
    // Validate credentials
    if(empty($student_id_err) && empty($student_password_err)){
        // Prepare a select statement
        $sql = "SELECT student_id, student_name, student_password, student_school, student_edollar FROM student WHERE student_id = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_student_id);
            
            // Set parameters
            $param_student_id = $student_id; //at this stage already checking the student_id against the DB
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if student_id exists, if yes then verify entered_student_password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $student_id, $student_name, $student_password, $student_school, $student_edollar);
                    if(mysqli_stmt_fetch($stmt)){
                        if($entered_student_password == $student_password){
                            // entered_student_password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["logged_in"] = true;
                            $_SESSION["student_id"] = $student_id;
                            $_SESSION["student_name"] = $student_name;   
                            $_SESSION["student_school"] = $student_school;                     
                            $_SESSION["student_edollar"] = $student_edollar;  

                            // Redirect user to student dashboard page
                            
                            header("location: student_dashboard.php");
                        } else{
                            // Display an error message if entered_student_password is not valid
                            $student_password_err = "The student password you entered was not valid.";
                        }
                    }
                } else{
                    // Display an error message if student_ID doesn't exist
                    $student_id_err = "No account found with that student ID.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Welcome to the BIOS Bidding.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($student_id_err)) ? 'has-error' : ''; ?>">
                <label>Student User ID</label>
                <input type="text" name="student_id" class="form-control" value="<?php echo $student_id; ?>">
                <span class="help-block"><?php echo $student_id_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($student_password_err)) ? 'has-error' : ''; ?>">
                <label>Student Password</label>
                <input type="password" name="entered_student_password" class="form-control">
                <span class="help-block"><?php echo $student_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
        </form>
    </div>    
</body>
</html>