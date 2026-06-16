<?php
class Modal {
    // Properties
    public $message;
    public $size;
    public $trueURL;
    public $falseURL;
    public $title;
    public $uniqueid;
    public $form;
    public $display;
    // Constructor
    function __construct($message, $size, $title='', $trueURL = null, $falseURL = null, $form = false) {
        $this->set("message", $message);
        $this->set("size", $size);
        if ($trueURL != null) $this->set("trueURL", $trueURL);
        if ($falseURL != null) $this->set("falseURL", $falseURL);
        if($title != '') $this->set("title", $title);
        else $this->set("title", "Alert");
        $uniqueid = $this->uniqueID();
        $this->set("uniqueid", $uniqueid);
        if($form == true) $this->set("form", true);
        else $this->set("form", false);
        $this->set("display", " d-block");
    }
    // Methods
    protected function getButtons() {
        $trueURL = $this->get("trueURL");
        $falseURL = $this->get("falseURL");
        $uniqueid = $this->get("uniqueid");
        if($trueURL == null) {
            $buttons = '<button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">OK</button>';
        }
        else if ($trueURL != null && $falseURL == null) {
            $buttons = <<<END
                <button type='button' class='btn btn-primary btn-sm' onclick="location.href='$trueURL'">Confirm</button>
                <button type='button' class='btn btn-danger btn-sm' data-bs-dismiss="modal">Cancel</button>
            END;
        }
        else if ($trueURL != null && $falseURL != null) {
            $buttons = <<<END
                <button type="button" class="btn btn-primary btn-sm" onclick="location.href='$trueURL'">Confirm</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="location.href='$falseURL'">Cancel</button>
            END;
        }
        return $buttons;
    }
    public function __toString() {
        $message = $this->get("message");
        if($this->get("size") =="md") $size = "";
        else $size = " modal-".$this->get("size");
        $title = $this->get("title");
        $uniqueid = $this->get("uniqueid");
        $buttons = $this->getButtons();
        if($this->get("form")) {
            $form_s = '<form method="post">';
            $form_e = '</form>';
        }
        $modalToString = <<<END
        <div class="modal fade" id="$uniqueid">
            <div class="modal-dialog$size">
                <div class="modal-content">
                $form_s
                    <div class="modal-header py-2">
                        <h4 class="modal-title h4">$title</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-3">
                        $message
                    </div>
                    <div class="modal-footer py-1">
                        $buttons
                    </div>
                $form_e
                </div>
            </div>
        </div>
        END;

        return $modalToString;
    }
    function uniqueID() {
        $uniqueid = substr(uniqid(), 6);
        return $uniqueid;
    }
    public function getuniqueID() {
        return $this->get("uniqueid");
    }
    // Get and Set
    function set($param, $val = '') {
        if($val != '') $this->$param = $val;
        else $this->$param = $param;
    }
    function get($param) {
        if($this->$param) return $this->$param;
    }
}

class LoginModal extends Modal {
    public function __construct() {
        $loginForm = <<<END
        <div class="form-input">
            <label class="input-label" for="lemail">Email or username</label>
            <input type="text" class="input-text" id="lemail" name="email">
        </div>
        <div class="form-input">
            <label class="input-label" for="lpassword">Password</label>
            <input type="password" class="input-text" id="lpassword" name="password">
        </div>
        END;

        parent::__construct($loginForm, "md", "Login");
    }
    public function getButtons() {
        return <<<END
            <button type="submit" class="button button-blue" id="loginBtn" name="loginBtn">Login</button>
            <button type="button" class="button button-red cancel" data-bs-dismiss="modal">Cancel</button>
        END;  
    }
}
class RegisterModal extends Modal {
    public function __construct() {
        $registerForm = <<<END
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
            <div class="input group input-group-sm d-flex flex-column">
                <label for="user_img" class="fw-bold mt-1"><small>User Image (optional)</small></label>
                <!-- <span class="input-group-text">User Image &nbsp;<small> (optional)</small></span> -->
                <input type="file" class="form-control" name="user_img" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
            </div>
        END;

        parent::__construct($registerForm, "md", "Register");
    }
    public function getButtons() {
        return <<<END
            <button type="submit" class="button button-blue" id="registerBtn" name="registerBtn">Register</button>
            <button type="button" class="button button-red cancel" data-bs-dismiss="modal">Cancel</button>
        END;  
    }
}

?>

<?php class Dropdown {
    public $link;
    public $linktext;
    function __construct($link, $linktext) {
        $this->set("link", $link);
        $this->set("linktext", $linktext);
    }
    public function __toString()
    {
        $link = $this->get("link");
        $linktext = $this->get("linktext");
        
        $html = <<<END
        <div class="w3-dropdown-hover">
            <button class="w3-button">Hover Over Me!</button>
            <div class="w3-dropdown-content w3-bar-block w3-border">
                <a href="$link" class="w3-bar-item w3-button">$linktext</a>
            </div>
        </div>
        END;
        return $html;
    }
    // Getters and Setters
    function set($param, $val) { $this->$param = $val; }
    function get($param) { if($this->$param) return $this->$param; }
} ?> 

<?php function nameDropdown() {
    $nameDropdown = new Dropdown("www.abc.com", "abc website");
    echo $nameDropdown->__toString();
}

// nameDropdown();
?>