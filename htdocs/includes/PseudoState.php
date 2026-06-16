<?php

// PseudoState class corresponds to our "mechanical component" from design phase, along with supporting functionality.
// on selection of a check option, it is invoked to identify dice roll alternatives based on the baseroll and riskprofle, and construct the distributions, text, etc. for them
// on selection of one of the dice roll alternatives, it is invoked again to roll the dice and determine the final outcome of the roll.
class PseudoState {
	//properties
	private array $option1;
	private array $option2;
	private array $option3;
	private array $option1StateData; // storing "metadata" on options to be used for e.g. rolling dice
	private array $option2StateData; // storing "metadata" on options to be used for e.g. rolling dice
	private array $option3StateData; // storing "metadata" on options to be used for e.g. rolling dice
	private int $stateChoice; // storing the option the user selected which caused the pseudostate creation, so we can still refer to it after roll concludes
	private int $modelConfidenceThresholdForDetails = 2; // mean/stdDev data will be included with distributions if user has model confidence over this value
	private int $modelConfidenceThresholdForAxes = 0; // axes data will be included with distributions if user has model confidence over this value


	//TODO: do setters/getters and make below functions use them...
	
	
	// constructor
	function __construct(DiceCombo $baseroll, array $requirements, RiskProfile $riskProfile, int $modelconfidence, int $stateChoice) {
		// save the user-selected choice from the State, so we can grab it again later (as it will be overridden by the user choice for the dice roll options!)
		$this->stateChoice = $stateChoice;
		
		// option 1 is always baseroll sum
		$this->option1 = array(	"optCheckDist"=>$baseroll->getDist(),
								"optCheckRequirements"=>$requirements,
								"optText"=>"Base Roll: ".$this->constructOptText($baseroll),
								"optTooltipText"=>"▪ <b>A single dice roll.</b> ▪ Each outcome is equally likely; a uniform distribution.",
								"optCheckMean"=>null,
								"optCheckStdDev"=>null,
								"optCheckAxes"=>false);
		if ($modelconfidence >= $this->modelConfidenceThresholdForDetails) {
			$this->option1["optCheckMean"] = $baseroll->getMean();
			$this->option1["optCheckStdDev"] = $baseroll->getStdDev();
			$this->option1["optCheckAxes"] = true;
		}
		else if ($modelconfidence >= $this->modelConfidenceThresholdForAxes) {
			$this->option1["optCheckMean"] = null;
			$this->option1["optCheckStdDev"] = null;
			$this->option1["optCheckAxes"] = true;
		}
		$this->option1StateData = array("internalOptionFlag"=>"default",
										"rollInterpretationFlag"=>array("type"=>"sum", "value"=>0),
										"diceToRoll"=>$baseroll);
		
		// options 2-3 are generated based on baseroll and riskprofile.
		$option2Data = $this->pickAltDist($baseroll, $requirements, $riskProfile, $modelconfidence, array());
		$this->option2 = $option2Data["optionData"];
		$this->option2StateData = $option2Data["pseudoStateData"];
		
		// note we pass in the flag selected in option 2 so we don't pick it again.
		$option3Data = $this->pickAltDist($baseroll, $requirements, $riskProfile, $modelconfidence, array($this->option2StateData["internalOptionFlag"]));
		$this->option3 = $option3Data["optionData"];
		$this->option3StateData = $option3Data["pseudoStateData"];
	}
	
	// methods
	// get UI-relevant information (dice roll options)
	public function getPseudoStateUI() {
		return array("opt1"=>$this->option1, "opt2"=>$this->option2, "opt3"=>$this->option3);
	}
	
	// construct user display text for base / sum dice combos (e.g. "1d20", "2d6", "3d4 + 1d8")
	private function constructOptText(DiceCombo $diceCombo) {
		$dice = $diceCombo->getDice();
		$allFaces = array();
		foreach($dice as $die) {
			array_push($allFaces, $die->getFaces());
		}
		
		$freq = array_count_values($allFaces);
		$lastKey = array_key_last($freq);
		
		$text = "";
		foreach($freq as $key => $val) {
			$text = $text."{$val}d{$key}";
			if ($key != $lastKey) {
				$text = $text." + ";
			}
		}
		return $text;
	}
	
	// internal function for choosing & constructing an alternative roll option - based on baseroll, riskProfile of user/session. Excludes any options provided in pickedOptions!
	private function pickAltDist(DiceCombo $baseroll, array $requirements, RiskProfile $riskProfile, int $modelconfidence, array $pickedOptions) {
		
		// generate option flag that hasn't already been picked and fits the riskProfile
		// note: won't allow advantage types if baseroll has >1 dice
		$options = array(Modifiers::Advantage, 
						Modifiers::Disadvantage, 
						Modifiers::AoD, 
						Modifiers::DoA, 
						Modifiers::AltComplex,
						Modifiers::AltSimple, 
						Modifiers::FlatBonusPositive,
						Modifiers::FlatBonusNegative,
						Modifiers::Wrap);
						
		$weights = null;
		
		// different options for weightings depending on risk profile
		if ($riskProfile == RiskProfile::High) {
			// if advantage types are valid, i.e. baseroll has 1 dice only
			if (count($baseroll->getDice()) == 1) {
				// weighted percent chance of each of the options in options array - 5% chance of Advantage, 15% chance of Disadvantage, etc.
				$weights = array(5, 15, 5, 5, 5, 5, 15, 25, 30);
			}
			// else, exclude advantages as an option and distribute proportions among remaining options
			else {
				$options = array_slice($options, 4, true);
				$weights = array(10, 10, 10, 35, 35);
			}
		}
		else if ($riskProfile == RiskProfile::Mid) {
			if (count($baseroll->getDice()) == 1) {
				$weights = array(5, 5, 15, 15, 15, 15, 10, 10, 10);
			}
			else {
				$options = array_slice($options, 4, true);
				$weights = array(20, 20, 20, 20, 20);
			}
		}
		else if ($riskProfile == RiskProfile::Low) {
			if (count($baseroll->getDice()) == 1) {
				$weights = array(15, 5, 15, 15, 15, 15, 10, 5, 5);
			}
			else {
				$options = array_slice($options, 4, true);
				$weights = array(25, 25, 20, 20, 10);
			}
		}
		
		// remove any options already picked from the possible options pool
		foreach($pickedOptions as $pickedOption) {
			// credit to Bojangles on StackExchange for this trick @https://stackoverflow.com/a/7225113
			// if an already picked option is in our options list, find and unset it from weights and options
			if (($index = array_search($pickedOption, $options)) !== false) {
				unset($options[$index]);
				// re-index via array_values, as unset leaves a "gap" in the index/keys
				$options = array_values($options);
				$toRedistribute = $weights[$index];
				unset($weights[$index]);
				$weights = array_values($weights);
				
				// redistribute the probability removed from weights amongst remaining options equally
				$tmp = array();
				foreach($weights as $weight) {
					array_push($tmp, $weight + floor($toRedistribute/count($weights)));
				}
				$weights = $tmp;
				
				// ensure weights still adds to 100; if not (e.g. if toredistribute doesnt divide evenly amongst num of options), add the small bit missing to a random option
				$missing = 100 - array_sum($weights);
				if($missing != 0) {
					$index = rand(0, count($weights)-1);
					$weights[$index] += $missing;
				}
			}
		}
		
		
		// change weights into cumulative form
		$cumulative = array();
		$accumulator = 0;
		for ($i = 0; $i < count($weights); $i++) {
			$accumulator += $weights[$i];
			$cumulative[$i] = $accumulator;
		}
		// prepend a 0 to array, so we can check >= cumulative[index] and < cumulative[next_index] (i.e. for weights (25,25), 0-25 should give options[0], not 25-50)
		array_unshift($cumulative, 0);
		
		$option = Modifiers::Advantage; // default/backup option initialisation
		// roll from 0-100, and use to pick the option
		$roll = rand(1, 100);
		for ($i = 0; $i < count($cumulative)-1; $i++) {
			if ($roll >= $cumulative[$i] && $roll < $cumulative[$i+1]) {
				$option = $options[$i];
			}
		}
		
		// generate information for selected option - dice, distribution data, text & tooltip text to display to user, DC/requirements
		$dist = null;
		$mean = null;
		$stdDev = null;
		$axes = false;
		$text = null;
		$tooltipText = null;
		$diceToRoll = null;
		$reqs = null;
		// additionally store a flag for the type of roll, so we can refer to it later when needing to determine outcome
		$rollInterpretationFlag = null;
		
		switch ($option) {
			case Modifiers::Advantage:
				// distribution
				$dist = $baseroll->getAdvDist(Modifiers::Advantage);
				// text to display to user
				$text = "Get Advantage";
				$tooltipText = 
				"▪ <b>Roll twice, and take highest value.</b>
				 ▪ Vs. Base Roll: chance of outcome is 2 * (chance of outcome on a die) * (chance of outcome <i>or less</i> on other die). The *2 is because it applies regardless of which die is the 'a die' and which is the 'other die'.
				 ▪ This makes the maximum roll most likely overall outcome, as it is <i>always</i> selected regardless of the other die's value.";
				// flag for actual dice rolls later - how to interpret the "outcome" after rolling the diceToRoll, e.g. "take highest of two", "sum all"
				$rollInterpretationFlag = Modifiers::Advantage;
				// dice to roll for this type of roll, e.g. advantage requires rolling two dice
				// note - getDice() returns array of dice. These modifiers should only be called when we have one dice in baseroll, so access the single dice via getDice()[0]
				// for Adv/Disadv, we need two dice of same type.
				$diceToRoll = new DiceCombo(array(	new Dice($baseroll->getDice()[0]->getFaces()), 
													new Dice($baseroll->getDice()[0]->getFaces())));
				// requirements, if they need adjusting based on the option
				$reqs = $requirements;
				break;
			case Modifiers::Disadvantage:
				$dist = $baseroll->getAdvDist(Modifiers::Disadvantage);
				$text = "Get Disadvantage";
				$tooltipText = 
				"▪ <b>Roll twice, and take lowest value.</b>
				 ▪ Vs. Base Roll: chance of outcome is 2 * (chance of outcome on a die) * (chance of outcome <i>or higher</i> on other die). The *2 is because it applies regardless of which die is the 'a die' and which is the 'other die'.
				 ▪ This makes the minimum roll most likely overall outcome, as it is <i>always</i> selected regardless of the other die's value.";
				$rollInterpretationFlag = Modifiers::Disadvantage;
				// note - getDice() returns array of dice. These modifiers should only be called when we have one dice in baseroll, so access the single dice via getDice()[0]
				// for Adv/Disadv, we need two dice of same type.
				$diceToRoll = new DiceCombo(array(	new Dice($baseroll->getDice()[0]->getFaces()), 
													new Dice($baseroll->getDice()[0]->getFaces())));
				$reqs = $requirements;
				break;
			case Modifiers::AoD:
				$dist = $baseroll->getAdvDist(Modifiers::AoD);
				$text = "Get Advantage of Disadvantage";
				$tooltipText = 
				"▪ <b>Roll four times. Split into pairs, and take lowest value of each pair (disadvantage). Then take lowest value of the survivors (advantage).</b>
				 ▪ Vs. Base Roll: Disadvantage initially being applied makes high outcomes very unlikely. Applying advantage afterwards adds a limiting 'envelope' over this disadvantage-like distribution, sharply curbing the chance of getting a very low value.
				 ▪ The result is disadvantage-like for mid-high values and advantage-like for the very low end, making a slightly below-average, middling result the most likely and extreme low/high results unlikely.";
				$rollInterpretationFlag = Modifiers::AoD;
				// note - getDice() returns array of dice. These modifiers should only be called when we have one dice in baseroll, so access the single dice via getDice()[0]
				// for AoD/DoA, we need four dice of same type.
				$diceToRoll = new DiceCombo(array(	new Dice($baseroll->getDice()[0]->getFaces()), 
													new Dice($baseroll->getDice()[0]->getFaces()),
													new Dice($baseroll->getDice()[0]->getFaces()),
													new Dice($baseroll->getDice()[0]->getFaces())));
				$reqs = $requirements;
				break;
			case Modifiers::DoA:
				$dist = $baseroll->getAdvDist(Modifiers::DoA);
				$text = "Get Disadvantage of Advantage";
				$tooltipText = 
				"▪ <b>Roll four times. Split into pairs, and take highest value of each pair (advantage). Then take lowest value of the survivors (disadvantage).</b>
				 ▪ Vs. Base Roll: Advantage initially being applied makes low outcomes very unlikely. Applying disadvantage afterwards adds a limiting 'envelope' over this advantage-like distribution, sharply curbing the chance of getting a very high value.
				 ▪ The result is advantage-like for low-mid values and disadvantage-like for the very top end, making a slightly above-average, middling result the most likely and extreme low/high results unlikely.";
				$rollInterpretationFlag = Modifiers::DoA;
				// note - getDice() returns array of dice. These modifiers should only be called when we have one dice in baseroll, so access the single dice via getDice()[0]
				// for AoD/DoA, we need four dice of same type.
				$diceToRoll = new DiceCombo(array(	new Dice($baseroll->getDice()[0]->getFaces()), 
													new Dice($baseroll->getDice()[0]->getFaces()),
													new Dice($baseroll->getDice()[0]->getFaces()),
													new Dice($baseroll->getDice()[0]->getFaces())));
				$reqs = $requirements;
				break;
			case Modifiers::AltComplex:
				$diceToRoll = $baseroll->getSimilarDiceCombo(true);
				// sum dice values (i.e. add 0 to them)
				$rollInterpretationFlag = array("type"=>"sum", "value"=>0);
				$dist = $diceToRoll->getDist();
				$text = "Alternative Roll: ".$this->constructOptText($diceToRoll);
				$tooltipText = 
				"▪ <b>Swap Base Roll for a different set of dice with the same <i>maximum<i> roll across their sum.</b>
				 ▪ Vs. Base Roll: Adding more dice makes outcomes more 'consistent'; middling outcomes are more likely, but low/high ones are less likely.
				 ▪ This is because a low/high outcome now requires getting 'lucky'/'unlucky' several times over multiple rolls, rather than just once with a single roll. Across multiple rolls, you'll usually get as many low results as high ones, 'averaging out' to a middling outcome.";
				$reqs = $requirements;
				break;
			case Modifiers::AltSimple:
				$diceToRoll = $baseroll->getSimilarDiceCombo(false);
				$rollInterpretationFlag = array("type"=>"sum", "value"=>0);
				$dist = $diceToRoll->getDist();
				$text = "Alternative Roll: ".$this->constructOptText($diceToRoll);
				$tooltipText = 
				"▪ <b>Swap Base Roll for a different set of dice with the same <i>maximum<i> roll across their sum.</b>
				 ▪ Vs. Base Roll: Adding more dice makes outcomes more 'consistent'; middling outcomes are more likely, but low/high ones are less likely.
				 ▪ This is because a low/high outcome now requires getting 'lucky'/'unlucky' several times over multiple rolls, rather than just once with a single roll. Across multiple rolls, you'll usually get as many low results as high ones, 'averaging out' to a middling outcome.";
				$reqs = $requirements;
				break;
			case Modifiers::FlatBonusPositive:
				$diceToRoll = $baseroll;
				// shift to right by rand 2-4
				$buff = rand(2,4);
				// sum dice values
				// note: we could add buff here and keep requirements as they are, but maybe more interesting to say the DC was reduced by X instead
				$rollInterpretationFlag = array("type"=>"sum", "value"=>0);
				$dist = $diceToRoll->getDist();
				$text = "Difficulty Reduced By {$buff}";
				$tooltipText = 
				"▪ <b>Add a flat bonus to the dice roll.</b>
				 ▪ Vs. Base Roll: The probability distribution has the same shape, but is shifted to the right relative to the value required for success - making success more likely.
				 ▪ In terms of 'chance of success', this is identical to reducing the value needed for success by the same amount - i.e. shifting the 'pass mark' to the left - which is visualised above.";
				// effectively shift distribution to right by shifting requirements to left
				$reqs = array();
				foreach($requirements as $req) {
					array_push($reqs, $req - $buff);
				}
				break;
			case Modifiers::FlatBonusNegative:
				$diceToRoll = $baseroll;
				// shift to left by rand 2-4
				$nerf = rand(2,4);
				// sum dice values
				// note: we could subtract nerf here and keep requirements as they are, but maybe more interesting to say the DC was increased by X instead
				$rollInterpretationFlag = array("type"=>"sum", "value"=>0);
				$dist = $diceToRoll->getDist();
				$text = "Difficulty Increased By {$nerf}";
				$tooltipText = 
				"▪ <b>Subtract a flat bonus from the dice roll.</b>
				 ▪ Vs. Base Roll: The probability distribution has the same shape, but is shifted to the left relative to the value required for success - making success less likely.
				 ▪ In terms of 'chance of success', this is identical to increasing the value needed for success by the same amount - i.e. shifting the 'pass mark' to the right - which is visualised above.";
				// effectively shift distribution to left by shifting requirements to right
				$reqs = array();
				foreach($requirements as $req) {
					array_push($reqs, $req - $nerf*-1);
				}
				break;
			case Modifiers::Wrap:
				$diceToRoll = $baseroll->getSimilarDiceCombo(false);
				$rollInterpretationFlag = Modifiers::Wrap;
				$dist = $diceToRoll->getWrapDist();
				// getWrapDist shifts the distribution by 1/2 of its extent (e.g. a 3d10 gets shifted by (floor)(30-3)/2 = 13 ); need to calc this to display to user.
				// maxroll:
				$sumFaces = 0;
				foreach($diceToRoll->getDice() as $die) {
					$sumFaces += $die->getFaces();
				}
				$extent = $sumFaces - count($diceToRoll->getDice());
				$shiftAmount = floor($extent/2.0);
				$text = $this->constructOptText($diceToRoll) . " (+{$shiftAmount}, but wrapped!)";
				$tooltipText = 
				"▪ <b>Add half the range of the roll to the dice outcome - but exceeding the initial maximum roll 'overflows' or 'wraps around' back to the low end!</b>
				 ▪ Vs. A 'Straight' Roll Of Same Dice: Inverts the relationship between 'more dice' and 'consistency'; the more dice added, the more likely extreme values become and the less likely middling values become, rather than the vice-versa. 
				 ▪ This is because the left half of the (usually highly-likely, with many dice) 'middling' outcomes are shifted up to be very high values, but the right half 'overflow'/'wrap' to become very low values.
				 ▪ The same operation applied to a single dice roll would be identical to the original, as the distribution would be uniform.";
				$reqs = $requirements;
				break;
			// return baseroll if something goes amiss and none of the above match - shouldn't happen
			default:
				$diceToRoll = $baseroll;
				$rollInterpretationFlag = array("type"=>"sum", "value"=>0);
				$dist = $diceToRoll->getDist();
				$text = "Base Roll: ".$this->constructOptText($diceToRoll);
				$reqs = $requirements;
				break;
		}
		
		// provide additional graph info if model confidence over threshold value
		if ($modelconfidence >= $this->modelConfidenceThresholdForDetails) {
			$mean = calcMean($dist);
			$stdDev = calcStdDev($dist, $mean);
			$axes = true;
		}
		else if ($modelconfidence >= $this->modelConfidenceThresholdForAxes) {
			$axes = true;
		}
		
		// return info - UI-relevant info in 
		return array("optionData"=>array(	"optCheckDist"=>$dist,
											"optText"=>$text, 
											"optTooltipText"=>$tooltipText,
											"optCheckRequirements"=>$reqs,
											"optCheckMean"=>$mean,
											"optCheckStdDev"=>$stdDev,
											"optCheckAxes"=>$axes),
											
					"pseudoStateData"=>array("internalOptionFlag"=>$option,
											 "rollInterpretationFlag"=>$rollInterpretationFlag,
											 "diceToRoll"=>$diceToRoll));
	}
	
	
	// determine the final result of a dice roll, based on rolling the dice and the roll interpretation flag
	private function determineOutcome(DiceCombo $diceCombo, $rollInterpretationFlag) {
		// roll dice
		$diceCombo->rollDice();
		// get an array of the types of dice, and the actual rolls of the dice
		$types = array();
		$rolls = array();
		foreach($diceCombo->getDice() as $die) {
			array_push($types, $die->getFaces());
			array_push($rolls, $die->getRoll());
		}
		
		// interpret the rolls into a "final outcome" of the roll, e.g. take highest, or sum them
		// note - lower error checking here (e.g. for num of dice == 2 if advantage type), as by time this is reached we've had multiple catches in place
		switch($rollInterpretationFlag) {
			case Modifiers::Advantage:
				// return highest roll
				return max($rolls);
				break;
			case Modifiers::Disadvantage:
				// lowest roll
				return min($rolls);
				break;
				// split the 4 dice into 2 pairs. Lowest of each pair, highest of surivors.
			case Modifiers::AoD:
				$disadv1 = min($rolls[0], $rolls[1]);
				$disadv2 = min($rolls[2], $rolls[3]);
				return max($disadv1, $disadv2);
				break;
				// split the 4 dice into 2 pairs. Highest of each pair, lowest of surivors.
			case Modifiers::DoA:
				$adv1 = max($rolls[0], $rolls[1]);
				$adv2 = max($rolls[2], $rolls[3]);
				return min($adv1, $adv2);
				break;
			case Modifiers::Wrap:
				$sumRolls = array_sum($rolls);
				$sumTypes = array_sum($types);
				$numDice = count($rolls);
				// "base value" of the roll is: 
				// sum of the rolls 
				// + a bonus of (floor)half the "extent" of possible values for the roll (e.g. for 2d10 extent is 2-20, so 19 possible values, so bonus is (floor)19/2 = 9.
				// modulus (max_roll+1)
				$extent = $sumTypes-$numDice;
				$value = (($sumRolls + floor($extent/2.0)) % ($sumTypes+1));
				// however, this wraps the result to 0, not to minroll
				// so: if the result is below half the extent, shift up by min possible roll
				if ($value < floor($extent/2.0)) {
					$value += $numDice;
				}
				return $value;
				break;
			// all other cases than above 
			default:
				if (gettype($rollInterpretationFlag) != "array") {
					throw new Exception("Roll Interpretation Flag not array or other modifier");
					return;
				}
				else if (!isset($rollInterpretationFlag["type"])) {
					throw new Exception("Roll Interpretation Flag array does not have 'type' field set.");
					return;
				} 
				else {
					// room to add more types here, e.g. "avg", in future
					if ($rollInterpretationFlag["type"] == "sum") {
						$sum = array_sum($rolls);
						$bonus = $rollInterpretationFlag["value"];
						return $sum + $bonus;
					}
					else {
						throw new Exception("Roll Interpretation Flag array: did not recognise 'type' field: " . $rollInterpretationFlag["type"] . ".");
						return;
					}
				}
				break;
		}
		
	}
	
	// determine success/partial/failure (roll vs. the requirements)
	private function determineSuccess($opt, $outcome) {
		$requirements = $opt["optCheckRequirements"];
		if ($outcome >= $requirements[0]) {
			return "success";
		}
		else if ($requirements[1] <= $outcome && $outcome < $requirements[0]) {
			return "partial";
		}
		else return "failure";
	}
	
	// roll dice, determine success/partial/failure, and return the relevant information for dice roll animation. 
	// Note, "result" also used to set next state, update stats and bonuses, etc. in narrative engine.
	public function getPseudoStateRollInfo($choice) {
		
		// TODO: fix dice roll animations logic on frontend so we can structure this and determineSuccess/Outcome better,
		// and pass the data in a more sensible fashion -- not the old prototype way
		
		// get info for user-selected option
		if ($choice == 1) {
			$opt = $this->option1;
			$optStateData = $this->option1StateData;
		}
		else if ($choice == 2) {
			$opt = $this->option2;
			$optStateData = $this->option2StateData;
		}
		else if ($choice == 3) {
			$opt = $this->option3;
			$optStateData = $this->option3StateData;
		}
		else {
			// only three above should be possible as we create them with those.
			throw new Exception("Invalid pseudostate choice " . $choice . "; only 1,2,3 allowed.");
			return;
		}
		
		$diceToRoll = $optStateData["diceToRoll"];
		
		// roll dice and determine outcome (the final number result to be compared to DCs/requirements)
		$outcome = $this->determineOutcome($diceToRoll, $optStateData["rollInterpretationFlag"]);
		// compare the outcome to DCs/requirements and determine success, partial success, or failure
		$result = $this->determineSuccess($opt, $outcome);
		
		// get an array of the types of dice, and the actual rolls of the dice
		$types = array();
		$rolls = array();
		foreach($diceToRoll->getDice() as $die) {
			array_push($types, $die->getFaces());
			array_push($rolls, $die->getRoll());
		}

		// convert interpretation flag for frontend
		if (gettype($optStateData["rollInterpretationFlag"]) == "array") {
			$flag = $optStateData["rollInterpretationFlag"]["type"];
		}
		// else it's a Modifier enum
		else {
			$flag = $optStateData["rollInterpretationFlag"]->value;
		}

		return array("result"=>$result, "outcome"=>$outcome, "types"=>$types, "rolls"=>$rolls, "flag"=>$flag);
	}
	
	// getters and setters
	public function getStateChoice() {
		return $this->stateChoice;
	}
	
}
?>