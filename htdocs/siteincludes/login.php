<?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] == 0) { ?>
    <!-- Login Modal  -->
    <form method="post">
        <div class="modal modal-medium" id="loginModal">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">                    
                    <div class="modalTitle">Login</div>
                    <span class="close float-end w3-right" onclick="hideModal('loginModal')" aria-label="Hide login modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="form-input">
                        <label class="input-label" for="lemail">Email or username</label>
                        <input type="text" class="input-text" id="lemail" name="email" autocomplete="email address">
                    </div>
                    <div class="form-input">
                        <label class="input-label" for="lpassword">Password</label>
                        <input type="password" class="input-text" id="lpassword" name="password">
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="loginBtn" name="loginBtn">Login</button>
                    <button type="button" class="btn btn-danger cancel" onclick="hideModal('loginModal')" aria-label="hide login modal">Cancel</button>
                </div>
                <div class="modal-footer">
                <span>Or <a class="loginButton text-info" id="registerAnchor" onclick="registerModal()" aria-label="open registration modal">register</a> a new account</span>
                </div>
            </div>
        </div>
    </form>

    <!-- End of Login Modal -->
<?php }
else { ?>
    <!-- Logout Modal -->
    <form method="post">
        <div class="modal modal-small" id="logoutModal">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <div class="modalTitle">Logout</div>
                        <span class="close float-end" onclick="hideModal('logoutModal')" aria-label="hide logout modal">&times;</span>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        Are you sure you want to log out of your current session?
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" name="logoutBtn" href="">OK</button>
                        <a class="btn btn-danger" onclick="hideModal('logoutModal')" aria-label="hide logout modal">Cancel</a>
                    </div>
            </div>
        </div>
    </form>
    <!-- Logout Modal -->
<?php } ?>

<?php if(isset($_POST['loginBtn'])) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] == 0) {
        # Connect to Database
        $dbc = dbOpen();
        $email_safe = mysqli_real_escape_string($dbc, trim($_POST['email']));
        $pass_safe = mysqli_real_escape_string($dbc, trim($_POST['password']));
        $query = "SELECT * FROM users WHERE (email = '$email_safe' or username = '$email_safe') AND pass = SHA2('$pass_safe',256)";
        if($result = mysqli_query($dbc, $query)) {
            if(mysqli_num_rows($result) >= 1) {                
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $_SESSION['loggedin'] = 1;
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['admin'] = $row['admin'];
                $_SESSION['active'] = $row['active'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['img_url'] = $row['img_url'];
                confirmModal('You have logged in successfully','');
            }
            else {
                messageModal('User details incorrect, please try again');
            }  
        }
        else {
            messageModal('Something went wrong. Please try again, or contact us to report an error');
        }
        dbClose();
    }
    else {
        messageModal('User already logged in.');
    }   
} ?>

<?php if(isset($_POST['logoutBtn']) && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1) {
    session_unset();
    session_destroy();
    echo '<script>location.href="";</script>';
}
?>