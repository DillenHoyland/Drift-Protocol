<?php
set_include_path('./includes');

include 'State.php';
include 'UserOption.php';
include 'PseudoState.php';
include 'StatsEnum.php';
include 'RiskProfileEnum.php';
include 'ModifiersEnum.php';
include 'Dice.php';
include 'DiceCombo.php';
include 'StatsHelpers.php';

// ******* SETUP:
// NOTE: play.php checks for login, user_id, and session_id being set. If not set, redirects user to login.
// states.php thus assumes these are set correctly.

session_name("drift-protocol");
session_start();

// error handling:
// to store any error messages to display to user
$errors = array();
// force exception on non-fatal errors/warnings so try/catch works as it should.
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
// generic error handling to include in catch blocks
function handleStandardErrorsAndExit(string $internalErrMsg, string $userErrMsg, $mysqli) {
	global $errors;
	error_log(date('Y-m-d H:i:s') . " Error: " . $internalErrMsg);
	array_push($errors, "Error occurred. Please contact support with message: " . date('Y-m-d H:i:s') . " - " . $userErrMsg);
	echo json_encode(array("errors"=>$errors));
	$mysqli -> close();
	restore_error_handler();
	exit();
}

// Decode any POSTED data as associative array - ["choice"] *should* be the only thing coming back (other than RESET dev debug tool)
$POSTED = json_decode(file_get_contents('php://input'), true);

// open DB connection
try {
	$mysqli = new mysqli("localhost","root","","drift_protocol");
	if ($mysqli -> connect_errno) {
		handleStandardErrorsAndExit("DB_CONN_ERROR " . $mysqli -> connect_errno,
									"DB_CONN_ERROR " . $mysqli -> connect_errno . " for session_id " . $_SESSION["session_id"],
									$mysqli);
	}
} catch (Throwable $e) {
	// note, doesn't use standard error handler because $mysqli isn't defined if creation fails!
	error_log(date('Y-m-d H:i:s') . "Error: DB_CONN_ERROR. Throwable: " . $e->getMessage());
	array_push($errors, "Connection error. Please contact support with message: " . date('Y-m-d H:i:s') . " - " . "Error: DB_CONN_ERROR for session_id " . $_SESSION["session_id"] . ".");
	echo json_encode(array("errors"=>$errors));
	restore_error_handler();
	exit();
}

// if key info not set / session expired, load in user's current progress from database and clear pseudos
if (!isset($_SESSION["state"]) || !isset($_SESSION["stats"]) || !isset($_SESSION["pseudoFlag"]) || !isset($_SESSION["pseudoState"])) {
	getSessionInfo($_SESSION["session_id"], $mysqli);
	$_SESSION["pseudoFlag"] = false;
	$_SESSION["pseudoState"] = false;
}

// baseroll hardcoded constant in this prototype; in future versions, could have users' baseroll change e.g. with stats,
// such as starting at 2d10, and going to the riskier 1d20 or more consistent 2d8+1d4 depending on e.g. RiskProfile
if (!isset($_SESSION["baseroll"])) {
	$baserollDie = new Dice(20);
	$_SESSION["baseroll"] = new DiceCombo(array($baserollDie));
}

// DEBUG / TEMP - reset button probably shouldn't be included in final version. Resets state to 1 and stats to 0. Does NOT update bonuses table, have to go into DB to do that.
if (isset($POSTED["reset"])) {
	updateDB(	1, 
				array(	["stat"=>Stats::Risk->value, "change"=>-1*$_SESSION["stats"][Stats::Risk->value]],
						["stat"=>Stats::ModelConfidence->value, "change"=>-1*$_SESSION["stats"][Stats::ModelConfidence->value]],
						["stat"=>Stats::Trust->value, "change"=>-1*$_SESSION["stats"][Stats::Trust->value]],
						["stat"=>Stats::Stability->value, "change"=>-1*$_SESSION["stats"][Stats::Stability->value]]
					),
				null, $mysqli);
	getSessionInfo($_SESSION["session_id"], $mysqli);
	$_SESSION["pseudoFlag"] = false;
	$_SESSION["pseudoState"] = false;
}



// ******* CORE SCRIPT - NARRATIVE ENGINE:

// Need to change states based on user choices and/or roll results. 
// Note, if user just started, nothing will have been POSTED yet, so this will be skipped.
// Note, if user just did a dice roll, then we POSTED that "choice = null" to skip this block. As the state was changed *during the dice roll outcome determination*, we just need to return state (see after this block.)
if (isset($POSTED["choice"])) {
	
	// check type of "choice" posted; if not null and not a (whole) number, definitely not right!
	if ($POSTED["choice"] != null && (!is_numeric($POSTED["choice"]) || floor($POSTED["choice"]) != $POSTED["choice"])) {
		handleStandardErrorsAndExit("Narrative Engine with session_id " . $_SESSION["session_id"] . "; " . $POSTED["choice"] . " POSTed as choice",
									"Did not recognise choice for session_id: " . $_SESSION["session_id"],
									$mysqli);
	}

	$choice = $POSTED["choice"];

	// set opt for usage in scenarios 2-3 below.
	// If no pseudo, we use the POSTed value.
	if ($_SESSION["pseudoFlag"] == false) {
		try {
			$opt = $_SESSION["state"]->getOptionList()[$choice];
		} catch (Throwable $e) {
			handleStandardErrorsAndExit("Narrative Engine with session_id " . $_SESSION["session_id"] . "; " . $POSTED["choice"] . "POSTed as choice but no corresponding option found. Throwable: " . $e->getMessage(),
										"Invalid choice submitted for session_id: " . $_SESSION["session_id"],
										$mysqli);
		}
	}
	// else, the POSTed value will be what the user selected for their dice roll option, **not** what they chose from among the State options. 
	// We store the State choice in pseudostate when it's constructed, so access through there instead.
	else {
		$opt = $_SESSION["state"]->getOptionList()[$_SESSION["pseudoState"]->getStateChoice()];
	}
	
	
	// three possible scenarios:
	
	// Scenario 1: user is in a pseudostate, and just selected a diceroll option. If so, we'll have pseudoflag = true.
	if ($_SESSION["pseudoFlag"] == true) {
		// create new baseroll dice (so we can roll it; prior baseroll die will have been rolled already!)
		$baserollDie = new Dice(20);
		$_SESSION["baseroll"] = new DiceCombo(array($baserollDie));

		// execute dice roll and report back UI-necessary info (rolled dice, values, outcome, etc.)
		// TODO: make this bit just work better in pseudostate class - see comments there. Requires frontend changes as well (how dice roll animations expect info).
		try {
			$rollInfo = $_SESSION["pseudoState"]->getPseudoStateRollInfo($choice);
		} catch (Throwable $e) {
			handleStandardErrorsAndExit("Narrative Engine with session_id " . $_SESSION["session_id"] . "; Failed to get roll info. Throwable: " . $e->getMessage(),
										"Issue generating roll information for session_id: " . $_SESSION["session_id"],
										$mysqli);
		}
		
		$result = $rollInfo["result"];
		// based on result, update DB - change state based on nextID, change stats, and update or add any future option bonuses
		try {
			$nextID = $opt->getOptNextIDs()[$result];
			$statUpdates = $opt->getOptStatChanges()[$result];
			$bonusUpdates = $opt->getOptBonusChanges()[$result];
		} catch (Throwable $e) {
			handleStandardErrorsAndExit("Narrative Engine with session_id " . $_SESSION["session_id"] . "; unable to retrieve DB updates for on-" . $result . " with (JSON) opt: " . json_encode($opt). ". Throwable: " . $e->getMessage(),
										"Issue retrieving on-" . $result . " update values for session_id: " . $_SESSION["session_id"],
										$mysqli);
		}
		updateDB($nextID, $statUpdates, $bonusUpdates, $mysqli);
		
		// after updating, load in new information from DB to State, and clear pseudoflag/state
		getSessionInfo($_SESSION["session_id"], $mysqli);
		$_SESSION["pseudoFlag"] = false;
		$_SESSION["pseudoState"] = false;
		
		// add stat change text to state (if any)
		try {
			$_SESSION["state"]->addChangeText($statUpdates);
		} catch (Throwable $e) {
			error_log(date('Y-m-d H:i:s') . " Error: Narrative Engine with session_id " . $_SESSION["session_id"] . "; error generating change text for stat changes (JSON) " . json_encode($statUpdates) . ". Throwable: " . $e->getMessage());
			// non-fatal error, don't exit or display to user.
		}
		
		// send UI Information back to client to render the diceroll animation
		echo json_encode($rollInfo);
		// exit - the frontend will re-call this script after playing the dice roll animation
		$mysqli -> close();
		restore_error_handler();
		exit();
	}
	
	// Scenario 2: the user just selected a state choice, and it's a "check".
	else if ($opt->getOptCheckType() != null) {
		// State remains unchanged, but we additionally create and return a pseudostate (and set pseudoFlag). The pseudostate includes the dice roll options (incl. distributions, DC requirements, etc.) displayed to the user during a check.
		$_SESSION["pseudoFlag"] = true;
		try {
			$_SESSION["pseudoState"] = new PseudoState(	$_SESSION["baseroll"],
														// horrid syntax created by enum - TLDR, grabs relevant stat bonus (e.g. if its a Stability check, grabs user's Stability stat)
														$opt->getOptRequirements($_SESSION["stats"][Stats::tryFrom($opt->getOptCheckType()->value)->value]), 
														$_SESSION["riskProfile"],
														$_SESSION["stats"][Stats::ModelConfidence->value],
														$choice
														);
		} catch (Throwable $e) {
			handleStandardErrorsAndExit("Narrative Engine with session_id " . $_SESSION["session_id"] . "; pseudostate creation failed with SESSION variables:" . json_encode($_SESSION) . ". Throwable: " . $e->getMessage(),
										"Issue creating dice roll state for session_id: " . $_SESSION["session_id"],
										$mysqli);
		}
	}
	
	// Scenario 3: the user just selected a state choice, and it's *not* a check. In this case, we can navigate directly to the subsequent state via the option's "selection" entries.
	else if ($opt->getOptCheckType() == null) {
		// change state based on nextID, change stats, and update or add any future option bonuses
		try {
			$nextID = $opt->getOptNextIDs()["selection"];
			$statUpdates = $opt->getOptStatChanges()["selection"];
			$bonusUpdates = $opt->getOptBonusChanges()["selection"];
		} catch (Throwable $e) {
			handleStandardErrorsAndExit("Narrative Engine with session_id " . $_SESSION["session_id"] . "; unable to retrieve DB updates for on-selection with (JSON) opt: " . json_encode($opt). ". Throwable: " . $e->getMessage(),
										"Issue retrieving selection update values for session_id: " . $_SESSION["session_id"],
										$mysqli);
		}
		updateDB($nextID, $statUpdates, $bonusUpdates, $mysqli);
		
		// after updating, load in new information from DB to State
		getSessionInfo($_SESSION["session_id"], $mysqli);
		
		// add stat change text to state (if any)
		$_SESSION["state"]->addChangeText($statUpdates);
	}
}



// construct and echo UI-relevant information from... 

// ... the current state, which requires setting RiskProfile (with updated stats!), and...
if ($_SESSION["stats"][Stats::Risk->value] > 2) {
	$_SESSION["riskProfile"] = RiskProfile::High;
}
else if ($_SESSION["stats"][Stats::Risk->value] >= 1) {
	$_SESSION["riskProfile"] = RiskProfile::Mid;
}
else {
	$_SESSION["riskProfile"] = RiskProfile::Low;
}

try {
	$currentState = $_SESSION["state"]->getStateUI($_SESSION["riskProfile"], $_SESSION["stats"], $_SESSION["baseroll"]);
} catch (Throwable $e) {
	handleStandardErrorsAndExit("Narrative Engine with session_id " . $_SESSION["session_id"] . "; unable to generate state UI from (JSON) state: " . json_encode($_SESSION["state"]). ". Throwable: " . $e->getMessage(),
								"Issue generating state UI information for session_id: " . $_SESSION["session_id"],
								$mysqli);
}

// ... the pseudostate.
$pseudoState = false;
// if pseudoflag is active, get the dice roll option UI info from pseudostate object.
if ($_SESSION["pseudoFlag"] == true) {
	try {
		$pseudoState = $_SESSION["pseudoState"]->getPseudoStateUI();
	} catch (Throwable $e) {
		handleStandardErrorsAndExit("Narrative Engine with session_id " . $_SESSION["session_id"] . "; unable to generate pseudostate UI from (JSON) pseudostate: " . json_encode($_SESSION["pseudoState"]). ". Throwable: " . $e->getMessage(),
									"Issue generating dice roll UI information for session_id: " . $_SESSION["session_id"],
									$mysqli);
	}
}

// then echo back
echo json_encode(array('currentState'=>$currentState, 'pseudoFlag'=>$_SESSION["pseudoFlag"], 'pseudoState'=>$pseudoState, 'stats'=>$_SESSION["stats"]));

// close single DB connection at end of request
$mysqli -> close();




// ************** DB COMMUNICATION MODULE:


// update all session information from the database, based on the session_id.
// note - nomenclature change here, using snakecase to match DB
function getSessionInfo($session_id, $mysqli) {
	global $errors;	
	// get session data - stats, current state id
	$sessionsQuery = "
				SELECT *
				FROM sessions
				WHERE session_id = {$_SESSION["session_id"]}";

	$sessionsQueryResult = handleDBQueryErrors($sessionsQuery,
											"Mysqli error getting session for session_id: " . $_SESSION["session_id"],
											"Error getting session for session_id: " . $_SESSION["session_id"],
											true,
											$mysqli);
	
	if (!$sessionsQueryResult) {
		echo json_encode(array("errors"=>$errors));
		$mysqli -> close();
		restore_error_handler();
		exit();
	}
	
	
	// should only ever get one result for a session
	if ($sessionsQueryResult->num_rows == 1) {
		$result = $sessionsQueryResult->fetch_assoc();
		// set new state
		$_SESSION["state"] = getStateInfo($result["current_state_id"], $mysqli);
		// update stats
		$_SESSION["stats"] = array(	(Stats::Risk->value)=>$result["risk"], 
									(Stats::ModelConfidence->value)=>$result["model_confidence"], 
									(Stats::Trust->value)=>$result["trust"], 
									(Stats::Stability->value)=>$result["stability"]);
	}
	// else, error
	else {
		if ($sessionsQueryResult->num_rows > 1) {
			handleStandardErrorsAndExit("getSessionInfo with session_id " . $_SESSION["session_id"] . "; " . $sessionsQueryResult->num_rows . "found",
										"Multiple sessions found for session_id: " . $_SESSION["session_id"],
										$mysqli);
		}
		else if ($sessionsQueryResult->num_rows == 0) {
			handleStandardErrorsAndExit("getSessionInfo with session_id " . $_SESSION["session_id"] . "; " . $sessionsQueryResult->num_rows . "found",
										"No session found for session_id: " . $_SESSION["session_id"],
										$mysqli);		
		}
		else {
			handleStandardErrorsAndExit("getSessionInfo with session_id " . $_SESSION["session_id"] . "; JSON sessionsQueryResult:" . json_encode($sessionsQueryResult),
										"Issue retrieving session for session_id: " . $_SESSION["session_id"],
										$mysqli);
		}
	}
}

// pulls state and options information from DB based on state ID
// note - nomenclature change here, using snakecase to match DB
function getStateInfo($state_id, $mysqli) {
	global $errors;	
	// backup - if something goes wrong, we can at least just return same state we had prior
	if (isset($_SESSION["state"])) {
		$newState = $_SESSION["state"];
	}
	// if this is user just loading in the game and thus no state set in session, then we won't have a backup
	// in that instance, if below fails all we can do is throw an error


	// get options from DB
	
	// note, need to alias the columns directly for bonuses, and rename bonuses.option_id to bonuses.opt_id, 
	// because otherwise bonuses.option_id overwrites options.option_id (thus returning the option_id as "NULL" if a bonus doesn't exist).
	$optionsQuery = "
				SELECT *
				FROM options
				LEFT JOIN (	SELECT bonus_id, session_id, option_id AS opt_id, bonus
							FROM bonuses 
							WHERE bonuses.session_id = {$_SESSION["session_id"]}
							) AS bonuses
				ON options.option_id = bonuses.opt_id
				WHERE options.state_id = {$state_id}";

	$optionsQueryResult = handleDBQueryErrors($optionsQuery,
											"Mysqli error getting options for session_id: " . $_SESSION["session_id"],
											"Error getting options for session_id: " . $_SESSION["session_id"],
											true,
											$mysqli);
	
	if (!$optionsQueryResult) {
		echo json_encode(array("errors"=>$errors));
		$mysqli -> close();
		restore_error_handler();
		exit();
	}
	

	// get state info (narrative text) from DB
	$stateQuery = "
			SELECT *
			FROM states
			WHERE state_id = {$state_id}";
	
	$stateQueryResult = handleDBQueryErrors($stateQuery,
											"Mysqli error getting state for session_id: " . $_SESSION["session_id"],
											"Error getting state for session_id: " . $_SESSION["session_id"],
											true,
											$mysqli);
	
	if (!$stateQueryResult) {
		echo json_encode(array("errors"=>$errors));
		$mysqli -> close();
		restore_error_handler();
		exit();
	}
	
	// should only ever get 1 result
	if ($stateQueryResult->num_rows == 1) {
		$state = $stateQueryResult->fetch_assoc();

		$optionList = array();
		// if state is not a terminal state, load in options from options query
		if ($state["terminal_state"] != 1) {
			// should always get >0 results
			if ($optionsQueryResult->num_rows > 0) {
				while ($row = $optionsQueryResult->fetch_assoc()) {
					try {
						$opt = new UserOption($row);
						array_push($optionList, $opt);
					} catch (Throwable $e) {
						handleStandardErrorsAndExit("getStateInfo with session_id " . $_SESSION["session_id"] . "; option creation failed with JSON optionsQueryResult:" . json_encode($optionsQueryResult) . ". Throwable: " . $e->getMessage(),
													"Issue retrieving options for session_id: " . $_SESSION["session_id"],
													$mysqli);
					}
				}
			}
			// else, error
			else {
				if ($optionsQueryResult->num_rows == 0) {
					if ($optionsQueryResult->fetch_assoc()["terminal_state"] != 1) {
						handleStandardErrorsAndExit("getStateInfo with session_id " . $_SESSION["session_id"] . "; " . $optionsQueryResult->num_rows . "found",
													"No options found for session_id: " . $_SESSION["session_id"],
													$mysqli);
					}
				}
				else {
					handleStandardErrorsAndExit("getStateInfo with session_id " . $_SESSION["session_id"] . "; JSON optionsQueryResult:" . json_encode($optionsQueryResult),
												"Issue retrieving options for session_id: " . $_SESSION["session_id"],
												$mysqli);
				}
			}
		}

		// create new state from narrative information + optionsList array from above
		try {
			$newState = new State($state, $optionList);
		} catch (Throwable $e) {
			handleStandardErrorsAndExit("getStateInfo with session_id " . $_SESSION["session_id"] . "; state creation failed with JSON stateQueryResult:" . json_encode($stateQueryResult) . ". Throwable: " . $e->getMessage(),
										"Issue retrieving states for session_id: " . $_SESSION["session_id"],
										$mysqli);
		}
	}
	else {
		if ($stateQueryResult->num_rows > 1) {
			handleStandardErrorsAndExit("getStateInfo with session_id " . $_SESSION["session_id"] . "; " . $stateQueryResult->num_rows . "found",
										"Multiple states found for session_id: " . $_SESSION["session_id"],
										$mysqli);
		}
		else if ($stateQueryResult->num_rows == 0) {
			handleStandardErrorsAndExit("getStateInfo with session_id " . $_SESSION["session_id"] . "; " . $stateQueryResult->num_rows . "found",
										"No state found for session_id: " . $_SESSION["session_id"],
										$mysqli);
		}
		else {
			handleStandardErrorsAndExit("getStateInfo with session_id " . $_SESSION["session_id"] . "; JSON stateQueryResult:" . json_encode($stateQueryResult),
										"Issue retrieving states for session_id: " . $_SESSION["session_id"],
										$mysqli);
		}
	}

	return $newState;
}


// updates DB information on option selection or dice roll outcome
function updateDB($nextID, $statUpdates, $bonusUpdates, $mysqli) {
	global $errors;
	$stateQuery = "
			UPDATE sessions
			SET current_state_id = {$nextID}
			WHERE session_id = {$_SESSION["session_id"]}";
	
	if(!handleDBQueryErrors($stateQuery,
						"Mysqli error updating next state for session_id: " . $_SESSION["session_id"],
						"Error updating state for session_id: " . $_SESSION["session_id"],
						false,
						$mysqli)) {
							echo json_encode(array("errors"=>$errors));
							$mysqli -> close();
							restore_error_handler();
							exit();
						}
	
	// only execute statUpdates if not null!
	if ($statUpdates != null) {
		
		// 0 by default
		$riskUpdateVal = 0;
		$modelConfidenceUpdateVal = 0;
		$trustUpdateVal = 0;
		$stabilityUpdateVal = 0;
		
		// for those that were changed, 
		foreach($statUpdates as $statUpdate) {
			if ($statUpdate["stat"] == Stats::Risk->value) {
				$riskUpdateVal = $statUpdate["change"];
			}
			else if ($statUpdate["stat"] == Stats::ModelConfidence->value) {
				$modelConfidenceUpdateVal = $statUpdate["change"];
			}
			else if ($statUpdate["stat"] == Stats::Trust->value) {
				$trustUpdateVal = $statUpdate["change"];
			}
			else if ($statUpdate["stat"] == Stats::Stability->value) {
				$stabilityUpdateVal = $statUpdate["change"];
			}
		}
		
		$statsQuery = "
				UPDATE sessions
				SET risk = risk + {$riskUpdateVal},
				    model_confidence = model_confidence + {$modelConfidenceUpdateVal},
					trust = trust + {$trustUpdateVal},
					stability = stability + {$stabilityUpdateVal}
				WHERE session_id = {$_SESSION["session_id"]}
				AND user_id = {$_SESSION["user_id"]}";
			
		if(!handleDBQueryErrors($statsQuery,
							"Mysqli error updating stats for session_id: " . $_SESSION["session_id"],
							"Error updating stats for session_id: " . $_SESSION["session_id"],
							false,
							$mysqli)) {
								echo json_encode(array("errors"=>$errors));
								$mysqli -> close();
								restore_error_handler();
								exit();
							}
	}
	

	// only execute bonusUpdates if not null!
	if ($bonusUpdates != null) {
		foreach($bonusUpdates as $bonusUpdate) {
			$existsFlag = false;
			
			$existingBonusDetectionQuery = "
				SELECT *
				FROM bonuses
				WHERE session_id = {$_SESSION["session_id"]}
				AND option_id = {$bonusUpdate["option_id"]}";

			$existingBonusDetectionResult = handleDBQueryErrors($existingBonusDetectionQuery,
																"Mysqli error detecting existing bonus queries for session_id: " . $_SESSION["session_id"],
																"Error updating bonuses for session_id: " . $_SESSION["session_id"],
																true,
																$mysqli);

			if (!$existingBonusDetectionResult) {
				echo json_encode(array("errors"=>$errors));
				$mysqli -> close();
				restore_error_handler();
				exit();
			}
			
			// if a bonus for this session & option already exist, need to UPDATE not INSERT
			if ($existingBonusDetectionResult->num_rows == 1) {
				$existsFlag = true;
			}
			else if ($existingBonusDetectionResult->num_rows > 1) {
				handleStandardErrorsAndExit("updateDB with session_id " . $_SESSION["session_id"] . "; " . $existingBonusDetectionResult->num_rows . "bonuses found",
											"Multiple bonuses found for session_id: " . $_SESSION["session_id"],
											$mysqli);
			}
			// else, 0 rows, existsFlag should stay false
			
			
			$bonusUpdateQuery;
			// if already has an entry, we need to update it by adding the new bonus to the existing one
			if ($existsFlag == true) {
				$bonusUpdateQuery = "
									UPDATE bonuses
									SET bonus = bonus + {$bonusUpdate["change"]}
									WHERE session_id = {$_SESSION["session_id"]}
									AND option_id = {$bonusUpdate["option_id"]}";
			}
			// if it doesn't already exist, instead insert new row with new bonus
			else {
				$bonusUpdateQuery = "
									INSERT INTO bonuses (session_id, option_id, bonus)
									VALUES ({$_SESSION["session_id"]}, {$bonusUpdate["option_id"]}, {$bonusUpdate["change"]})";
			}
			
			if(!handleDBQueryErrors($bonusUpdateQuery,
									"Mysqli error updating bonus queries for session_id: " . $_SESSION["session_id"],
									"Error updating bonuses for session_id: " . $_SESSION["session_id"],
									false,
									$mysqli)) {
										echo json_encode(array("errors"=>$errors));
										$mysqli -> close();
										restore_error_handler();
										exit();
									}
		}
	}
}

function handleDBQueryErrors(string $query, string $internalErrMsg, string $userErrMsg, bool $needResult, $mysqli) {
	global $errors;	
	if ($stmt = $mysqli->prepare($query)) {
		if(!$stmt->execute()) {
			error_log(date('Y-m-d H:i:s') . "Error: " . $internalErrMsg);
			array_push($errors, "Error occurred. Please contact support with message: " . date('Y-m-d H:i:s') . " - " . $userErrMsg);
			return false;
		}
		else if ($needResult == true) {
			return $stmt->get_result();
		}
		else return true;
	}
	else {
		error_log(date('Y-m-d H:i:s') . " - " . $internalErrMsg);
		array_push($errors, $internalErrMsg);
		array_push($errors, "Error occurred. Please contact support with message: " . date('Y-m-d H:i:s') . " - " . $userErrMsg);
		return false;
	}
}

?>
