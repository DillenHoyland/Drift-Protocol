<?php
// stores narrative state and options available to the user, along with key UI info for those options at the time they're presented/before they are selected (e.g. base roll distributions).
class State {
	// properties
	private string $narrTitle; // string from DB
	private string $narrTextHighRisk; // string from DB
	private string $narrTextMidRisk; // string from DB
	private string $narrTextLowRisk; // string from DB
	private string $changeText = ""; // constructable string for any stat changes, null by default
	private array $optionList; // array of UserOption objects
	private int $modelConfidenceThresholdForDetails = 2; // mean/stdDev data will be included with distributions if user has model confidence over this value
	private int $modelConfidenceThresholdForAxes = 0; // axes data will be included with distributions if user has model confidence over this value


	// constructor
	// SQLQueryResult is a SELECT * from the DB's states table. Option list is an array of UserOption objects.
	function __construct($SQLQueryResult, $optionList) {
		$this->setNarrTitle($SQLQueryResult["narr_title"]);
		$this->setNarrTextHighRisk($SQLQueryResult["narr_text_high_risk"]);
		$this->setNarrTextMidRisk($SQLQueryResult["narr_text_mid_risk"]);
		$this->setNarrTextLowRisk($SQLQueryResult["narr_text_low_risk"]);
		$this->setOptionList($optionList);
	}
	
	// get UI-relevant information to display to user
	public function getStateUI($riskProfile, $stats, $baseroll) {
		$stateUI = array();
		
		// NARRATIVE:
		
		// narrative title
		$stateUI["narr"] = array();
		$stateUI["narr"]["narrTitle"] = $this->getNarrTitle();

		// narrative text - based on RiskProfile
		if ($riskProfile == RiskProfile::High) {
			$stateUI["narr"]["narrText"] = $this->getNarrTextHighRisk() . $this->getChangeText();
		}
		else if ($riskProfile == RiskProfile::Mid) {
			$stateUI["narr"]["narrText"] = $this->getNarrTextMidRisk() . $this->getChangeText();
		}
		else {
			$stateUI["narr"]["narrText"] = $this->getNarrTextLowRisk() . $this->getChangeText();
		}
		
		// convert options too - each is an array of optText, optCheckType, optCheckDCs, optCheckRequirements, optCheckDist (i.e. baseroll)
		foreach($this->getOptionList() as $index => $opt) {
			// for each option in optionList, check if user meets the stat requirements to be shown that option
			$reqsMet = true;
			if ($opt->getOptStatRequirements() != null) {
				// loop through stat requirements, check all are met
				foreach($opt->getOptStatRequirements() as $statReq) {
					// if needs to be above, but stat is below/equal, requirement not met
					if ($statReq["type"] == "above" && $stats[Stats::tryFrom($statReq["stat"])->value] < $statReq["num"]) {
						$reqsMet = false;
					}
					// if needs to be below, but stat is above/equal to, requirement not met
					if ($statReq["type"] == "below" && $stats[Stats::tryFrom($statReq["stat"])->value] > $statReq["num"]) {
						$reqsMet = false;
					}
				}
			}
			// if reqs are indeed met, then add to stateUI
			if ($reqsMet == true) {
				// if opt is a check/not a check...
				$checkType = $opt->getOptCheckType();
				if ($checkType == null) {
					$optCheckRequirements = null;
					$optCheckDist = null;
					$optCheckMean = null;
					$optCheckStdDev = null;
					$optCheckAxes = false;
				}
				else {
					$optCheckRequirements = $opt->getOptRequirements($stats[Stats::tryFrom($opt->getOptCheckType()->value)->value]);
					$optCheckDist = $baseroll->getDist();
					// provide additional graph info if model confidence over threshold value
					if ($stats[Stats::ModelConfidence->value] >= $this->modelConfidenceThresholdForDetails) {
						$optCheckMean = $baseroll->getMean();
						$optCheckStdDev = $baseroll->getStdDev();
						$optCheckAxes = true;
					}
					else if ($stats[Stats::ModelConfidence->value] >= $this->modelConfidenceThresholdForAxes) { 
						$optCheckMean = null;
						$optCheckStdDev = null;
						$optCheckAxes = true;
					}
					else {
						$optCheckMean = null;
						$optCheckStdDev = null;
						$optCheckAxes = false;
					}
				}
				// construct array for sending to frontend, with only required information in it
				$stateUI["opt{$index}"] = array("optText"=>$opt->getOptText(),
													"optCheckType"=>$checkType,
													"optCheckDCs"=>$opt->getOptDCs(),
													"optCheckRequirements"=>$optCheckRequirements,
													"optCheckDist"=>$optCheckDist,
													"optCheckMean"=>$optCheckMean,
													"optCheckStdDev"=>$optCheckStdDev,
													"optCheckAxes"=>$optCheckAxes);
			}
		}
		
		return $stateUI;
	}
	
	// adding text to reflect any stat changes to narrative state text
	public function addChangeText($statUpdates) {
		// if nothing to add, return
		if ($statUpdates == null) {
			return;
		}
		// if there are some stat updates...
		$changeTextOpen = "<br><br><span class='stat-update-text-success'><i><b>Changes: </b>";
		$changeTextClose = "</i></span>";
		$changeTextMiddle = "";
		$statUpdatesLastKey = array_key_last($statUpdates);
		foreach ($statUpdates as $key => $statUpdate) {
			// if positive change, add "+" symbol
			$change;
			if ($statUpdate["change"] > 0) {
				$change = "+{$statUpdate["change"]}";
			}
			else $change = "{$statUpdate["change"]}";
			// text e.g. "Risk +1"
			$changeTextMiddle .= "{$statUpdate["stat"]} {$change}";
			// if >1 stat update, separate with semicolons, but don't do so for final one
			if ($key != $statUpdatesLastKey) {
				$changeTextMiddle .= "; ";
			}
		}
		
		$this->setNarrTextHighRisk($this->getNarrTextHighRisk() . $changeTextOpen.$changeTextMiddle.$changeTextClose);
		$this->setNarrTextMidRisk($this->getNarrTextMidRisk() . $changeTextOpen.$changeTextMiddle.$changeTextClose);
		$this->setNarrTextLowRisk($this->getNarrTextLowRisk() . $changeTextOpen.$changeTextMiddle.$changeTextClose);
	}
	
	
	
	// getters/setters
	public function getNarrTitle() {
		return $this->narrTitle;
	}
	public function setNarrTitle(string $narrTitle) {
		$this->narrTitle = $narrTitle;
	}
	public function getNarrTextHighRisk() {
		return $this->narrTextHighRisk;
	}
	public function setNarrTextHighRisk(string $narrTextHighRisk) {
		$this->narrTextHighRisk = $narrTextHighRisk;
	}
	public function getNarrTextMidRisk() {
		return $this->narrTextMidRisk;
	}
	public function setNarrTextMidRisk(string $narrTextMidRisk) {
		$this->narrTextMidRisk = $narrTextMidRisk;
	}
	public function getNarrTextLowRisk() {
		return $this->narrTextLowRisk;
	}
	public function setNarrTextLowRisk(string $narrTextLowRisk) {
		$this->narrTextLowRisk = $narrTextLowRisk;
	}
	public function getChangeText() {
		return $this->changeText;
	}
	public function setChangeText(string $changeText) {
		$this->changeText = $changeText;
	}
	public function getOptionList() {
		return $this->optionList;
	}
	public function setOptionList(array $optionList) {
		$this->optionList = $optionList;
	}
}
?>