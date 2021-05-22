<?php
session_start();

require_once "Auth.php";
require_once "Util.php";

$auth = new Auth();
$db_handle = new DBController();
$util = new Util();

require_once "authCookieSessionValidate.php";

if ($isLoggedIn) {
    $util->redirect("dashboard.php");
}

if (! empty($_POST["login"])) {
    $isAuthenticated = false;
    
    $username = $_POST["member_name"];
    $password = $_POST["member_password"];
    
    $user = $auth->getMemberByUsername($username);
    if (password_verify($password, $user[0]["member_password"])) {
        $isAuthenticated = true;
    }
    
    if ($isAuthenticated) {
        $_SESSION["member_id"] = $user[0]["member_id"];
        
        // Set Auth Cookies if 'Remember Me' checked
        if (! empty($_POST["remember"])) {
            setcookie("member_login", $username, $cookie_expiration_time);
            
            $random_password = $util->getToken(16);
            setcookie("random_password", $random_password, $cookie_expiration_time);
            
            $random_selector = $util->getToken(32);
            setcookie("random_selector", $random_selector, $cookie_expiration_time);
            
            $random_password_hash = password_hash($random_password, PASSWORD_DEFAULT);
            $random_selector_hash = password_hash($random_selector, PASSWORD_DEFAULT);
            
            $expiry_date = date("Y-m-d H:i:s", $cookie_expiration_time);
            
            // mark existing token as expired
            $userToken = $auth->getTokenByUsername($username, 0);
            if (! empty($userToken[0]["id"])) {
                $auth->markAsExpired($userToken[0]["id"]);
            }
            // Insert new token
            $auth->insertToken($username, $random_password_hash, $random_selector_hash, $expiry_date);
        } else {
            $util->clearAuthCookie();
        }
        $util->redirect("dashboard.php");
    } else {
        $message = "Invalid Login";
    }
}

$display = "none";
if(isset($message))
    $display = "block";
?>


<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet">

    <title>Login</title>

</head>

<body>
    <div class="container full-height">
        <div class="row full-height">
            <div class="d-flex align-items-center justify-content-center full-height">

                <form action="" method="post" id="frmLogin">
                    <div class="alert alert-danger" style="text-align:center;padding:10px;display:<?=$display?>;"><?php if(isset($message)) { echo $message; } ?></div>
                    <div class="field-group">
                        <div>
                            <label for="login">Username</label>
                        </div>
                        <div>
                            <input name="member_name" type="text"
                                value="<?php if(isset($_COOKIE["member_login"])) { echo $_COOKIE["member_login"]; } ?>"
                                class="form-control">
                        </div>
                    </div>
                    <div class="field-group">
                        <div>
                            <label for="password">Password</label>
                        </div>
                        <div>
                            <input name="member_password" type="password"
                                value="<?php if(isset($_COOKIE["member_password"])) { echo $_COOKIE["member_password"]; } ?>"
                                class="form-control">
                        </div>
                    </div>
                    <div class="field-group">
                        <div>
                            <input type="checkbox" name="remember" id="remember"
                                <?php if(isset($_COOKIE["member_login"])) { ?> checked <?php } ?> /> <label
                                for="remember-me">Remember me</label>
                        </div>
                    </div>
                    <div class="field-group">
                        <div>
                            <input type="submit" name="login" value="Login" class="btn btn-primary form-control"></span>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js">
    </script>
</body>

</html>