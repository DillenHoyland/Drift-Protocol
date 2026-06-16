<?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === 1) { 
    $dbc = dbOpen("PDO");
    try {

        $st = $dbc->prepare("select * from sessions where user_id=?");
        $st->execute([$_SESSION['user_id']]);
        $gameSessions = $st->fetchAll();
    }
    catch(PDOException $e) {
        $error = $e->getMessage();
        confirmModal("Error: $error", "");
        exit();
    }
    if(count($gameSessions) > 0) {
    ?>
    <!-- Session Modal  -->
     <form method="get">
        <div class="modal modal-medium" id="sessionModal">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">                    
                    <div class="modalTitle">Select a saved game session</div>
                    <span class="close float-end w3-right" onclick="hideModal('sessionModal')">&times;</span>
                </div>
                <div class="modal-body">
                    <?php
                    $count = 1;
                    foreach($gameSessions as $gameSession) { ?>
                    <div class="d-flex gap-2 mb-2">
                        <button class="btn btn-outline-secondary border border-5 border-secondary btn-lg w3-hover-border-white w3-opacity w3-hover-opacity-off flex-grow-1" name="game_session" value="<?=$gameSession['session_id']?>"><!--<i class="spinner-border spinner-border-sm text-secondary mb-1 me-2"></i>--><i class="spinner-grow spinner-grow-sm text-secondary mb-1 me-2"></i> ID: <?=$gameSession['session_id']?> Game State: <?=$gameSession['current_state_id']?></button>
                        <button class="btn btn-outline-danger border border-5 border-danger btn-lg w3-hover-border-white w3-opacity w3-hover-opacity-off flex-shrink-0 w3-hover-text-white" name="game_delete" value="<?=$gameSession['session_id']?>"><i class="fa fa-trash" aria-hidden="true"></i></button>
                    </div>
                    <?php if($count++ >= 5) break;
                    }
                    $empty = 5 - (count($gameSessions));
                    if($empty > 0) {
                    for($i=1;$i<=$empty;$i++) { ?>
                        <div class="w3-row"><button class="btn btn-outline-light w3-border border-5 w3-border-light-grey btn-lg w3-hover-border-white w3-opacity w3-hover-opacity-off w-100 mb-2" disabled>Empty Save Slot</button></div>
                    <?php }
                    }
                    ?>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <!-- <button type="submit" class="btn btn-primary" id="sessionBtn" name="sessionBtn">Login</button> -->
                    <button type="button" class="btn btn-danger cancel" onclick="hideModal('sessionModal')">Cancel</button>
                </div>
            </div>
        </div>
    </form>
    <!-- End of Session Modal -->
<?php }
}

?>
