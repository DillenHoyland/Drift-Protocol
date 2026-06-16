<?php
// a single Die (and its probability distribution). Note - Die is keyword in PHP, so instead we use Dice.
class Dice {
	// properties
	private ?int $rolled = null; // rolled value - null if not rolled yet
	private int $faces; // number of sides / max roll
	private array $dist; // uniform single-dice distribution
	
	// constructor - require faces to be set. Set as integer, e.g. 6 to make the die a d6.
	function __construct($faces) {
		$this->setFaces($faces);
		$this->setRoll(null);
		$this->setDistribution();
	}
	
	// methods
	// roll the dice - pick a random number from 1-maxroll, and return it
	public function rollDie() {
		// note - DOES NOT reroll on second call! Once rolled, the dice's rolled value is locked! The only way to unlock it is to reset the dice using setFaces()
		if ($this->getRoll() == null) {
			$this->setRoll(rand(1, $this->getFaces()));
		}
		return $this->getRoll();
	}
	
	// set distribution (for a single dice, uniform distribution w/ probability = 1/maxroll)
	private function setDistribution() {
		$faces = $this->faces;
		// start with 0, as there's always 0% chance of rolling a 0 and *index is used to indicate value on dice*
		$dist = array(0);
		// then uniform distribution
		for ($i = 0; $i < $faces; $i++) {
			array_push($dist, 1.0/$faces);
		}
		$this->dist = $dist;
	}
	
	// getters/setters
	// faces:
	public function getFaces() {
		return $this->faces;
	}
	public function setFaces($faces) {
		$this->faces = $faces;
		// also reset/update other properties if we're changing the dice type
		$this->setRoll(null);
		$this->setDistribution();
	}
	// roll:
	public function getRoll() {
		return $this->rolled;
	}
	public function setRoll($roll) {
		// check roll is possible on the dice
		if (0 < $roll && $roll <= $this->getFaces()) {
			$this->rolled = $roll;
		}
		else {
			// TODO: error behaviour
		}
	}
	// distribution - only get, should always be set internally when the faces are set
	public function getDist() {
		return $this->dist;
	}
}
?>