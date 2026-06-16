<?php
function dbOpen($mode="") {
    include(__DIR__ . "/../db/dbaccessdp.php");    
    
    if ($mode === "PDO") {
        try{
            $dbc = new PDO("mysql:host=$server;dbname=$db", $user, $pass);
            $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            die("Could not connect. " . $e->getMessage());
        }
        return $dbc; 
    }
    else {
        # Create connection
        $dbc = new mysqli($server, $user, $pass, $db);
        # Check connection
        if ($dbc->connect_error) {
            die("Connection failed: " . $dbc->connect_error);
        }
        return $dbc;
    }    
}

function dbClose() {
    # Close database connection.
    if (isset($dbc)) $dbc->close();
}
  
function dbCount($tab) {
    $dbc = dbOpen();
    $q = "SELECT COUNT(*) FROM ".$tab;
    $r = $dbc->query($q);
    $row = mysqli_fetch_array($r, MYSQLI_ASSOC);  
    return $row['COUNT(*)'];
    dbClose();
}

function clearSession() {
    session_destroy();
}

function messageModal($msg) { 
    $uniqueid = substr(uniqid(), 6);  
    ?>
    <div class="modal modal-small" id="<?=$uniqueid?>" style="display:block">
        <div class="modal-content">
          <!-- Modal Header -->
          <div class="modal-header">
            <div class="modalTitle">Alert</div>
            <span class="close float-end" onclick="hideModal('<?=$uniqueid?>')">&times;</span>
          </div>
          <!-- Modal body -->
          <div class="modal-body">
            <?php echo $msg; ?>
          </div>
          <!-- Modal footer -->
          <div class="modal-footer">
            <a class="btn btn-primary btn-sm" onclick="hideModal('<?=$uniqueid?>')">OK</a>
          </div>
        </div>
    </div>
    
<?php }  ?>
    
<?php function confirmModal($msg, $trueURL, $falseURL='') { 
    $uniqueid = substr(uniqid(), 6);    
    ?>
    <div class="modal modal-small" id="<?=$uniqueid?>" style="display:block">
          <div class="modal-content">
          <!-- Modal Header -->
          <div class="modal-header">
              <div class="modalTitle">Confirm</div>
              <span class="close float-end" onclick="hideModal('<?=$uniqueid?>')">&times;</span>
          </div>
          <!-- Modal body -->
          <div class="modal-body">
              <?php echo $msg; ?>
          </div>
          <!-- Modal footer -->
          <div class="modal-footer">
              <button type="submit" class="btn btn-primary btn-sm" name="confirmBtn" onclick="location.href='<?php echo $trueURL;?>'">OK</button>
              <?php if ($falseURL != ''): ?><button class="button button-red" id="modalCloseBottom" onclick="location.href='<?php echo $falseURL;?>'">Cancel</button><?php endif; ?>
          </div>
          </div>
    </div>
<?php } ?>

<?php function formHandler($ref) {
    
    if(isset($_GET['game_session'])) {
        $game_session = (int)$_GET['game_session'];
        $_SESSION['session_id'] = $game_session;
        unset($_SESSION["state"]);
        unset($_SESSION["pseudoFlag"]);
        unset($_SESSION["pseudoState"]);
        session_write_close();
        echo "<script>location.href='play.php';</script>";
    }
    if(isset($_GET['game_delete'])) {
        $msg = "Are you sure you want to delete Game Session " .$_GET['game_delete']."?";
        $url = "drift_protocol.php?delete=".$_GET['game_delete'];
        confirmModal($msg, $url);
    }
    if(isset($_GET['notification']) && $_GET['notification'] == true) {
        messageModal("Session save deleted");
        echo "<script>showModal('sessionModal');</script>";
    }
    if(isset($_GET['delete'])) {
        try{
            $dbc = dbOpen("PDO");
            $st = $dbc->prepare("select * from sessions where session_id=?");
            $st->execute([$_GET['delete']]);
            $gameSession = $st->fetch();
            if($gameSession['user_id'] != $_SESSION['user_id']) {
                throw new Exception($dbc->error);
                dbClose();
                exit();
            }
            else {
                $st1 = $dbc->prepare("delete from bonuses where session_id=?");
                $st1->execute([$_GET['delete']]);
                $st2 = $dbc->prepare("delete from sessions where session_id=?");
                $st2->execute([$_GET['delete']]);
                dbClose();
                echo "<script>location.href='drift_protocol.php?notification=true';</script>";
            }        
        }
        catch(PDOException $e) {
            $error = $e->getMessage();
            messageModal("Error: $error");
            dbClose();
            exit();
        }        
    }
}
?>
