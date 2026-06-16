<?php
include './siteincludes/session_init.php';
include './siteincludes/functions.php';

if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === 1 && isset($_SESSION["user_id"])) {
    $dbc = dbOpen();
    $user_id = $_SESSION['user_id'];
    $q = "INSERT INTO sessions (user_id, current_state_id, risk, model_confidence, trust, stability) VALUES ($user_id, 1, 0, 0, 0, 0)";
    if($dbc->query($q)) {
        $_SESSION['session_id'] = $dbc->insert_id;
        header('Location: play.php');
    }
    else {
        header('Location: drift_protocol.php');
    }
    $dbc->close();
    unset($_SESSION["state"]);
    unset($_SESSION["pseudoFlag"]);
    unset($_SESSION["pseudoState"]);
    session_write_close();
}
else {
    header('Location: drift_protocol.php');
}
exit();
?>