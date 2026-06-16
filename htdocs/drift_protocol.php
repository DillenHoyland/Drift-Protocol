<?php
$pageInfo = array("Drift Protocol | Welcome");
// array (page theme light/dark, page title)
include './siteincludes/header.php';
?>
<!-- Page content -->
<div class="container my-3">
    <h1 class="h1 w3-center border-bottom py-3 my-3">Drift Protocol: an educational RPG</h1>
    <p>This project aims to develop a text-adventure roleplaying game (RPG). The game mechanics will utilize graphical probability distribution functions that reflect the probabilistic underpinnings of each "event", or single instance of gameplay. Each event will present text options to the player alongside these graphical representations.The graphics will illustrate National 5 Mathematics concepts related to statistics which include probability theory, standard deviation, and comparison of datasets and summary statistics. These concepts will appear throughout the game, and through repeated exposure the player's understanding will be supported.</p>
    <p>The utilization of games as a medium for learning has been shown to bolster motivation, increase engagement, improve attitudes, enhance enjoyment, and inspire learning. A large meta-analysis found that 84% of 57 total studies reported positive results from implementing a game through which to provide maths education. This has been replicated in another large meta-analysis - of 43 studies, it was found that games enhance the sense of achievement during the learning process, which is known to reduce challenge amongst learners.</p>
    <!-- <h2 class="h2 w3-center border-bottom py-2 my-2">How to Play</h2>
    <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Incidunt non eveniet sit nemo voluptatem in optio adipisci vitae sapiente illo unde inventore necessitatibus temporibus quis reprehenderit, illum, nihil minus velit quasi? Pariatur molestiae asperiores voluptatibus quos libero modi repudiandae, illum accusantium iure amet quae maiores sequi placeat. Officia, officiis ex?</p> -->
    <h2 class="h2 w3-center border-bottom py-2 my-2">Start</h2>
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === 1) { ?>
    <p class="d-flex justify-content-center my-2">
        <a class="btn btn-outline-primary btn-lg dp-btn" href="new_game.php"><span class="w3-monospace">Start New Game</span></a>
    </p>
    <?php 
    $dbc = dbOpen("PDO");
    try {

        $st = $dbc->prepare("select * from sessions where user_id=?");
        $st->execute([$_SESSION['user_id']]);
        $gameSessions = $st->fetchAll();
        dbClose();
    }
    catch(PDOException $e) {
        $error = $e->getMessage();
        confirmModal("Error: $error", "");
        exit();
    }
    if(count($gameSessions) > 0) {
    ?>
    <p class="d-flex justify-content-center mb-5">
        <button class="btn btn-outline-secondary btn-lg dp-btn" type="button" onclick="showModal('sessionModal')"><span class="w3-monospace">Continue Saved Game</span></button>
    </p>
    <?php }
    }
    else { ?>
        <p class="d-flex justify-content-center mt-2 mb-5">You must be<span class="text-primary cpoint px-1" onclick="showModal('registerModal')" aria-label="registration link">registered</span> and <span class="text-primary cpoint px-1" onclick="showModal('loginModal')" aria-label="login link">signed in</span> to play Drift Protocol. Join for free today!</p>
    
    <?php } ?>
    <h2 class="h2 w3-center border-bottom py-2 my-2">How to Play</h2>
    <div id="guide" class="carousel slide" data-bs-ride="carousel">

  <!-- Indicators/dots -->
  <div class="carousel-indicators">
  <button type="button" data-bs-target="#guide" data-bs-slide-to="0" class="active" aria-label="Move to slide 1"></button>
    <button type="button" data-bs-target="#guide" data-bs-slide-to="1" aria-label="Move to slide 2"></button>
    <button type="button" data-bs-target="#guide" data-bs-slide-to="2" aria-label="Move to slide 3"></button>
    <button type="button" data-bs-target="#guide" data-bs-slide-to="3" aria-label="Move to slide 4"></button>
  </div>
  
  <!-- The slideshow/carousel -->
  <div class="carousel-inner">
    <div class="carousel-item active">
    <img src="./assets/img/1.png" alt="Step 1: begin analysis" class="d-block" style="width:100%">
    </div>
    <div class="carousel-item">
      <img src="./assets/img/2.png" alt="Step 2: Showing the probability graph" class="d-block" style="width:100%">
    </div>
    <div class="carousel-item">
      <img src="./assets/img/3.png" alt="Step 3: rolling the dice" class="d-block" style="width:100%">
    </div>
    <div class="carousel-item">
      <img src="./assets/img/4.png" alt="Step 4: resetting the game" class="d-block" style="width:100%">
    </div>
  </div>
  
  <!-- Left and right controls/icons -->
  <button class="carousel-control-prev" type="button" data-bs-target="#guide" data-bs-slide="prev" aria-label="previous slide">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#guide" data-bs-slide="next" aria-label="next slide">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>
</div>

<?php include './siteincludes/footer.php'; ?>