<?php
class UserOption {
	// properties:
	
	// display-relevant info:
	private string $optText; // never null. Text to display to user.
	private ?Stats $optCheckType; // null if option not a check. Uses Stats enum.
	private ?array $optStatRequirements; // Null if no extra requirements for option to be shown. Array of associative arrays. Structure: [ ["stat"=>"risk", "value"=>2, "type"=>"below"], ["stat"=>"risk", "value"=>0, "type"=>"above"], ... ]
	private ?array $optDCs; // null if option not a check. Associative array of "Meets it beats it" number for a "success" outcome and "partial success" outcome. Structure: ["success"=>15, "partial"=>"10"].
	private ?int $optBonus; // null if option not a check. Non-stat-based bonuses to add to the user's rolls (e.g. reward for an earlier decision). Can be negative, but must be whole number.
	
	// updating state, stats, and option bonuses for different results:
	private ?array $optNextIDs; // associative array, [ "selection"=>selectionID, "success"=>successID, "partial"=>partialSuccessID, "failure"=>failureID ]. IDs are the database IDs for the "next" state for selection/success/partial/failure. Set to null for keys which are not applicable, e.g. "selection"=>null if option is a check.
	private ?array $optStatChanges; // Associative array of associative arrays. Stores stat changes for each possible outcome (S/S/PS/F). Structure example for a non-check: [ "selection"=>["risk"=>1, "trust"=>-1], "success"=>null, "partial"=>null, "failure"=>null ] 
	private ?array $optBonusChanges; // Associative array of associative arrays. Similar to above, but for bonuses to future options. Structure example for a non-check: [ "selection"=>[12=>1, 13=>-1], "success"=>null, "partial"=>null, "failure"=>null ] 
	
	
	// constructor - just parse SQL query result here. Note SQL query result is a JOIN of options and bonuses tables.
	function __construct($SQLResult) {
		$this->parseQuery($SQLResult);
	}
	
	// construct option object from SQL query result.
	private function parseQuery($SQLResult) {		
		// text to display to user. String in DB.
		$this->setOptText($SQLResult["opt_text"]);
		// check type; if opt is not a check, null. String in DB, but ensure formatting matches Stats enum (e.g. "ModelConfidence", not "modelconfidence" or "model_confidence" etc.
		if ($SQLResult["opt_check_type"] != null) {
			$this->setOptCheckType(Stats::tryFrom($SQLResult["opt_check_type"]));
		} else $this->setOptCheckType(null);
		// stat requirements for option to be displayed to user; if user doesn't meet these requirements, they won't be offered this option. 
		// JSON in DB in format: {"stats": [{"stat": "Risk", "num": 1, "type": "above"}, {"stat": "Risk", "num": 4, "type": "below"}, ...]}
		$this->setOptStatRequirements(json_decode($SQLResult["opt_requirement"], true)["stats"]);
		// DCs for opt check (number the roll has to meet/beat to be a success/partial); if opt is not a check, null. Stored as two int fields in DB.
		$this->setOptDCs(array("success"=>$SQLResult["opt_success_dc"], "partial"=>$SQLResult["opt_partial_dc"]));
		
		// flat bonus to check from prior user choices, stored in DB bonuses table as an int. If null, set to 0 (as we do arithmetic (add) with this later).
		if ($SQLResult["bonus"] != null) {
			$this->setOptBonus($SQLResult["bonus"]);
		} else $this->setOptBonus(0);
		
		// next state IDs for each possible outcome - stored as 4x int fields in DB.
		$this->setOptNextIDs(array(	"selection"=>$SQLResult["opt_selection_next_id"],
									"success"=>$SQLResult["opt_success_next_id"],
									"partial"=>$SQLResult["opt_partial_next_id"],
									"failure"=>$SQLResult["opt_failure_next_id"]
									));
		
		// stat changes on selection of option - if option is a check, null in DB, otherwise JSON of form:
		// {"stats": [{"stat": "Risk", "change": -1}, ...], "option_bonuses": [{"option_id": 4, "change": -2}, ...]}
		if ($SQLResult["opt_selection_change"] != null) {
			$selectionChanges = json_decode($SQLResult["opt_selection_change"], true);
		} else $selectionChanges = array("stats"=>null, "option_bonuses"=>null);
		
		// as above, but for checks (i.e. these are all null in DB if opt is not a check) - with three fields in DB, one each for S/PS/F
		if ($SQLResult["opt_success_change"] != null) {
			$successChanges = json_decode($SQLResult["opt_success_change"], true);
		} else $successChanges = array("stats"=>null, "option_bonuses"=>null);
		
		if ($SQLResult["opt_partial_change"] != null) {
			$partialChanges = json_decode($SQLResult["opt_partial_change"], true);
		} else $partialChanges = array("stats"=>null, "option_bonuses"=>null);
		
		if ($SQLResult["opt_failure_change"] != null) {
			$failureChanges = json_decode($SQLResult["opt_failure_change"], true);
		} else $failureChanges = array("stats"=>null, "option_bonuses"=>null);
		
		// we restructure these a set of stat changes with the outcome as the key (S/S/PS/F => [statchangelist])
		// i.e. ["selection"=>[["stat"=>"risk", "change"=>-1], ...], "success=>....]
		$this->setOptStatChanges(array("selection"=>$selectionChanges["stats"],
									"success"=>$successChanges["stats"],
									"partial"=>$partialChanges["stats"],
									"failure"=>$failureChanges["stats"],
									));
		// as above, but for bonus changes
		$this->setOptBonusChanges(array("selection"=>$selectionChanges["option_bonuses"],
									"success"=>$successChanges["option_bonuses"],
									"partial"=>$partialChanges["option_bonuses"],
									"failure"=>$failureChanges["option_bonuses"],
									));
	}
	
	
	// convert DCs into what's required on the roll (i.e. initialDC - bonuses_from_prior_choices - bonuses_from_user_stats)
	public function getOptRequirements($statbonus) {
		$DCs = $this->getOptDCs();
		$reqs = array();
		foreach($DCs as $DC) {
			array_push($reqs, $DC - $this->getOptBonus() - $statbonus);
		}
		return $reqs;
	}
	

	// getters
	public function getOptText() {
		return $this->optText;
	}
	public function getOptCheckType() {
		return $this->optCheckType;
	}
	public function getOptStatRequirements() {
		return $this->optStatRequirements;
	}
	public function getOptDCs() {
		return $this->optDCs;
	}
	public function getOptBonus() {
		return $this->optBonus;
	}
	public function getOptNextIDs() {
		return $this->optNextIDs;
	}
	public function getOptStatChanges() {
		return $this->optStatChanges;
	}
	public function getOptBonusChanges() {
		return $this->optBonusChanges;
	}
	
	// setters
	public function setOptText($optText) {
		$this->optText = $optText;
	}
	public function setOptCheckType($optCheckType) {
		$this->optCheckType = $optCheckType;
	}
	public function setOptStatRequirements($optStatRequirements) {
		$this->optStatRequirements = $optStatRequirements;
	}
	public function setOptDCs($optDCs) {
		$this->optDCs["success"] = $optDCs["success"];
		$this->optDCs["partial"] = $optDCs["partial"];
	}
	public function setOptBonus($optBonus) {
		$this->optBonus = $optBonus;
	}
	public function setOptNextIDs($optNextIDs) {
		$this->optNextIDs = $optNextIDs;
	}
	public function setOptStatChanges($optStatChanges) {
		$this->optStatChanges = $optStatChanges;
	}
	public function setOptBonusChanges($optBonusChanges) {
		$this->optBonusChanges = $optBonusChanges;
	}
	

}
?>