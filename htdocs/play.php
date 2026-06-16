<?php
include './siteincludes/session_init.php';
if(!isset($pageInfo) || empty($pageInfo)) $pageInfo = array("Drift Protocol");
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== 1 || !isset($_SESSION['user_id']) || !isset($_SESSION['session_id'])) {
    header('Location: drift_protocol.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme=<?=(isset($_COOKIE['darkmode']) && $_COOKIE['darkmode'] == 'false') ? '"light"': '"dark"'?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="./styles/styles.css">
	<link rel="stylesheet" href="./dice_3d/styles/dice_3d.css">
	<link rel="stylesheet" href="./dice_3d/style.css">

	<!-- default font size for the wrap result -->
    <style>
     #wrap-calc-label { font-size: 20px !important; }
    </style>

	<script src="./scripts/functions.js"></script>
	<script src="https://cdn.tailwindcss.com"></script>
	<script>
	  tailwind.config = {
	    corePlugins: {
	      preflight: false,  /* disable reset so existing styles.css is not affected */
	    },
	    theme: {
	      extend: {
	        fontFamily: {
	          display: ['Orbitron', 'sans-serif'],
	          mono: ['Space Mono', 'monospace'],
	        },
	        colors: {
	          primary: '#00ff88',
	          'primary-dark': '#00cc6a',
	          secondary: '#ff006e',
	          'bg-dark': '#0a0e27',
	          'bg-darker': '#050812',
	          'text-primary': '#e0e0e0',
	          'text-secondary': '#a0a0a0',
	          border: '#1a1f3a',
	          'accent-blue': '#00d9ff',
	        }
	      }
	    }
	  }
	</script>
	<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
	
	<script type="importmap">
	{
    "imports": {
      "three": "https://unpkg.com/three@0.160.0/build/three.module.js"
    }
   }
    </script>
	<!-- Icons > Favicons -->
	<link rel="apple-touch-icon" sizes="180x180" href="styles/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="styles/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="styles/icons/favicon-16x16.png">
    <link rel="manifest" href="styles/icons/site.webmanifest">
	<!-- Title -->
	<title><?=$pageInfo[0]?></title> <!-- echo page title -->
</head>
<body class=<?= (isset($_COOKIE["readmode"]) && $_COOKIE["readmode"] === "true") ? "acc" : "def"?>>

	<div class="navbar">
        <a href="drift_protocol.php">
			<span class="logo">DP</span><span class="titleText"><span class="white">Drift</span> Protocol</span>
		</a>
    </div>

	<table id="info">
		<tbody>
			<!-- row for displaying errors -->
			<tr id="errorBar">
				<td>
					<p id="errorBarText"></p>
				</td>
			</tr>
			<!-- narrative text -->
			<tr>
				<td id="narr" colspan="3">
					<p id="narrTitle" class="title">narrTitle_PLACEHOLDER</p>
					<p id="narrText">narrText_PLACEHOLDER</p>
				</td>
			</tr>

			<tr id="firstrow">
				
			<!-- stats blocks -->
				<td class="sidebar" rowspan="3" colspan="1">
					<table id="statblkSidebar">
					<tbody>
							<tr><td class="title" colspan="2">STATS</td></tr>
							<tr>
								<td class="statblk" id="Stability">
									<div class="statblkTitle">STABILITY</div>
									<!-- <div class="statblkStat">stab_PLACEHOLDER</div> -->
									<div class="statblkStat"><span class="blink">&#9632;</span> Loading</div>
								</td>
								<td class="statblk" id="ModelConfidence">
									<div class="statblkTitle">MODEL CONF.</div>
									<!-- <div class="statblkStat">mconf_PLACEHOLDER</div> -->
									<div class="statblkStat"><span class="blink">&#9632;</span> Loading</div>
								</td>
							</tr>
							<tr>
								<td class="statblk" id="Trust">
									<div class="statblkTitle">TRUST</div>
									<!-- <div class="statblkStat">trust_PLACEHOLDER</div> -->
									 <div class="statblkStat"><span class="blink">&#9632;</span> Loading</div>
								</td>
								<td class="statblk" id="Risk">
									<div class="statblkTitle">RISK</div>
									<!-- <div class="statblkStat">risk_PLACEHOLDER</div> -->
									 <div class="statblkStat"><span class="blink">&#9632;</span> Loading</div>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<!-- options added here by JS if needed -->
		</tbody>
	</table>
	</div>

	<div class="wrapper">
		<!-- TOOLTIPS FOR DICE ROLL OPTIONS -->
		<div id="tooltipDiv">
		</div>
		<!-- 3d dice  -->
		<div id="threejs-dice-wrapper" style="display: block;">
			  <canvas class="game"></canvas>
		
			  <div id="result-box">
			   <div class="result-label">RESULT</div>
			   <div class="result-val" id="result-val">-</div>
			  </div>
		
			 <div id="hint">[ PICK AN OPTION TO ROLL ]</div>
			</div>
		<div class="footer">
			<div class="titleText white">&copy; Drift <span class="titleText">Protocol</span> 2026<?php if(date('Y') > 2026) { echo '-'.date('Y'); } ?></div>
		</div>
	</div>

	<!-- dice animation script FIRST, then game logic -->
	<script type="module" src="./dice_3d/main.js"></script>
	<script type="module" src="./scripts/render_state.js"></script>
</body>
