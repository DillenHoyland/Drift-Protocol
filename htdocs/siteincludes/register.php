<!-- Register Modal  -->
<form method="post" enctype="multipart/form-data">
    <div class="modal modal-medium" id="registerModal">
        <div class="modal-content">
    
          <!-- Modal Header -->
          <div class="modal-header">
            <div class="modalTitle">Register</div>
            <span class="close float-end" onclick="hideModal('registerModal')">&times;</span>
          </div>
          <!-- Modal body -->
          <div class="modal-body">
            <div class="form-input">
                <label for="user_name" class="input-label">Username</label>
                <input type="text" class="input-text" name="user_name" id="user_name">
            </div>
            <div class="form-input">
                <label for="email" class="input-label">Email</label>
                <input type="email" class="input-text" name="email" id="email">
            </div>
            <div class="form-input">
                <label for="password" class="input-label">Password</label>
                <input type="password" class="input-text" name="password" id="password">
            </div>
            <div class="form-input">
                <label for="cpassword" class="input-label">Confirm Password</label>
                <input type="password" class="input-text" name="cpassword" id="cpassword">
            </div>
            <div class="input group input-group-sm d-flex flex-column" data-bs-theme="light">
            <label for="user_img" id="user_img_label" class="input-label">User Image (optional)</label>
            <input type="file" class="form-control" name="user_img" id="user_img" aria-describedby="user_img_label" aria-label="Upload">
            </div>
          </div>
          <!-- Modal footer -->
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="registerBtn" name="registerBtn" value="register">Register</button>
            <button type="button" class="btn btn-danger cancel" onclick="hideModal('registerModal')">Cancel</button>
        </div>
        <div class="modal-footer">
            <span>Already registered? <a class="loginButton text-info" id="loginAnchor" onclick="loginModal()">Login</a></span>
        </div>
    
        </div>
    </div>
</form>
<!-- End of Register Modal -->

<?php if(array_key_exists('registerBtn', $_POST)) {
    $errors = "";
    # Connect to Database
    $dbc = dbOpen();
    # Assign variables to form input
    if (!isset($_POST['user_name']) || empty($_POST['user_name'])) $errors .= "Username required\n<br>";
    else {
        $user_name = $dbc->real_escape_string(trim($_POST['user_name']));
        $q = "select username from users where username = '$user_name'";
        $r = $dbc->query($q);
        if($r->num_rows >= 1) $errors .= "Username is taken, please choose another\n<br>";
    }
    if (!isset($_POST['email']) || empty($_POST['email'])) $errors .= "Email Address required\n<br>";
    else {
        $email = $dbc->real_escape_string(trim($_POST['email']));
        $q = "select email from users where email = '$email'";
        $r = $dbc->query($q);
        if($r->num_rows >= 1) $errors .= "Email address is already registered. <a class='loginButton' onclick='loginModal()'>Login?</a>\n<br>";
    }
    if (!isset($_POST['password']) || empty($_POST['password'])) $errors .= "Password required\n<br>";
    else {
        $password = $dbc->real_escape_string(trim($_POST['password']));
        if(strlen($password) < 6) $errors .= "Password must be at least 6 characters\n<br>";
        if(!isset($_POST['cpassword']) || $_POST['cpassword'] !== $password) $errors .= "Passwords must match\n<br>";
    }

    $img = 'img/users/no-image.png';
    if($_FILES['user_img']['size'] > 0) {
        $check = getimagesize($_FILES["user_img"]["tmp_name"]); 
        if($check== false) $errors .= "Image is invalid\n<br>";
    }

    if(empty($errors)) {
    $query = "INSERT INTO users (username, email, pass, reg_date) VALUES ('$user_name', '$email', SHA2('$password',256), NOW() )";
    $result = $dbc->query($query) ;
    if ($result) {
        $user_id = $dbc->insert_id;

        if(isset($check) && $check !== false) {
            mkdir("img/users/$user_id");
            $filename = basename($_FILES['user_img']['name']);                   
            $folder = "img/users/$user_id/".$filename;
            if (move_uploaded_file($_FILES['user_img']['tmp_name'], $folder)) {
                $img = "img/users/$user_id/$filename";
                $q = "update users set img_url = '$img' where user_id = '$user_id'";
                $r = $dbc->query($q);
            }
        }

        

        $q2 = "SELECT email_validation, admin_email from settings where id=1";
        $r2 = $dbc->query($q2);
        $row = $r2->fetch_assoc();
        if ($row['email_validation'] == 1) {
            messageModal('You have registered successfully!<br>Please check your spam folder for your confirmation email');
            require_once "Mail.php";
            $admin_email = $row['admin_email'];
            $hash = uniqid('', true);
            $safeHash = $dbc->real_escape_string($hash);
            $insertQuery = "insert into emailvalidate (email, pass_key, date_created, status) "." values ('$email','$safeHash',NOW(), 'A')";
            if (!$dbc->query($insertQuery))
            {
                messageModal('An error has occured, please try again');
            }

            $urlHash = urlencode($hash);

            $from = $admin_email;
            $to = $email;
          
            $host = "ssl://smtp.gmail.com";
            $port = "465";
            $username = 'contactremonetized@gmail.com';
            $password = 'zwrfbmmuxtrxvqww';
          
            $subject = "Welcome to Drift Protocol";
            $body = "Welcome!\r\n
            Thank you for joining Drift Protocol!\r\n
            Please confirm your email address by clicking the link below\r\n
            https://abcd2.xyz/index.php?validate=true&ref=$urlHash\r\n
            If the link does not work, try to copy and paste the url directly into your browser.\r\n
            If you did not sign up to Drift protocol, please ignore this email\r\n
            \r\n
            Many thanks\r\n
            DP Team";
          
            $headers = array ('From' => $from, 'To' => $to,'Subject' => $subject);
            $smtp = Mail::factory('smtp',
              array ('host' => $host,
                'port' => $port,
                'auth' => true,
                'username' => $username,
                'password' => $password));
          
            $mail = $smtp->send($to, $headers, $body);
          
            if (PEAR::isError($mail)) {
              messageModal($mail->getMessage());
            } else {
                $_SESSION['loggedin'] = 1;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['admin'] = 0;
                $_SESSION['active'] = 0;
                $_SESSION['username'] = $user_name;
                $_SESSION['email'] = $email;
                $_SESSION['img_url'] = $img;
                confirmModal("You have registered successfully! Please check your email inbox for your confirmation email", "");
            }

        }
        else {
            $_SESSION['loggedin'] = 1;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['admin'] = 0;
            $_SESSION['active'] = 1;
            $_SESSION['username'] = $user_name;
            $_SESSION['email'] = $email;
            $_SESSION['img_url'] = $img;
            confirmModal("You have registered successfully!", "");
        }
        }
        else {
            messageModal("An unknown error occured, please try again");
        }        
    }
    else {
        confirmModal($errors, '');
    }
    dbClose();
}

?>
