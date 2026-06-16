<?php

// a collection of Dice (and their collective probability distribution if summed), plus:
// helper functions for generating other distribution types (e.g. wrapped/U-shape, advantage-types)
// a helper function for generating and returning a separate, "similar" (same maxroll) dice combo to this one
class DiceCombo {
	// properties
	private array $dice;
	private array $dist;
	private float $mean;
	private float $stdDev;
	
	// constructor - provide an ARRAY OF DICE OBJECTS
	function __construct($dice) {
		$this->setDice($dice);
		$this->setDistribution();
	}
	
	
	//**************************** methods
	
	// roll the dice and return them afterwards (note: returns dice objects, not the roll results!)
	public function rollDice() {
		foreach ($this->getDice() as $die) {
			// note - this will NOT change the rolled value on a die that has previously been rolled! 
			// It will only roll a "new" value if the die currently has a roll of "null".
			$die->rollDie();
		}
		// return the now-rolled dice objects
		return $this->getDice();
	}
	
	
	// sets default distribution for the dice in this DiceCombo
	// note - convolutions are associative and distributive, allowing the final distribution to be found by chaining convolutions of each dice with the "next dice" one-at-a-time.
	private function setDistribution() {
		$dice = $this->getDice();
		// edge case - if only one dice in DiceCombo, set dist to that die's dist
		if (count($dice) == 1) {
			$this->dist = $dice[0]->getDist();
			$this->mean = calcMean($this->dist);
			$this->stdDev = calcStdDev($this->dist, $this->mean);
			return;
		}
		
		// otherwise, get each individual die's distribution array into an array-of-arrays
		$dists = array();
		foreach ($dice as $die) {
			array_push($dists, $die->getDist());
		}
		// iterate through these and convolve them together, one at a time; overwrite "next" entry with "current convolved with next", then repeat with the new "next" and "next+1", etc. until end
		for ($i = 0; $i < count($dists)-1; $i++) {
			$dists[$i+1] = $this->convolve($dists[$i], $dists[$i+1]);
		}
		// then the final entry is the final distribution of the full combo
		$this->dist = end($dists);

		// also set mean and standard deviation using helpers
		$this->mean = calcMean($this->dist);
		$this->stdDev = calcStdDev($this->dist, $this->mean);
		
	}
	/* HELPER for convolution:
	get resulting distribution from convolving two distributions
	Convolutions effectively "slide" one function over another, multiplying their values at each "place" together and summing them over the process. It tells us how much two functions "overlap" at a particular place. This can be used to find the probability function resulting from two separate probability functions combined (summed).
	*/
	private function convolve($volume, $kernel) {
		$convolved = array();
		
		// initialise with 0s across full extent of the two distributions being convolved
		for ($j = 0; $j < count($kernel)+count($volume); $j++) {
			array_push($convolved, 0);
		}
		// ...  then do a first pass over volume[0]
		for ($j = 0; $j < count($kernel); $j++) {
			$convolved[$j] = $volume[0] * $kernel[$j];
		}
		
		// then do rest of convolution, repeating above for each place in volume ("sliding" kernel over volume and cumulatively summing overlap at each point)
		for ($i = 1; $i < count($volume); $i++) {
			for ($j = 0; $j < count($kernel); $j++) {
				$convolved[$i+$j] += $volume[$i] * $kernel[$j];
			}
		}
		
		return $convolved;
	}
	
	
	/*
	get distribution when applying advantage, disadvantage, AoD, or DoA of a dice combo with only a single die in it
	should throw error if called for DiceCombo with >1 die in it
	source/derivations for polynomials - generalise or expand solutions at:
	https://www.r-bloggers.com/2020/05/538-dungeons-dragons-riddler/
	https://colab.research.google.com/drive/1L1F4GxijepdhNCrlimeIAxbmZp69_2Y8?usp=sharing
	*/
	public function getAdvDist(Modifiers $flag) {
		$dice = $this->getDice();
		if (count($dice) != 1 || !in_array($flag, array(Modifiers::Advantage, Modifiers::Disadvantage, Modifiers::AoD, Modifiers::DoA))) {
			// TODO: throw error - this is only applicable for 1 dice, and a modifier of one of the advantage types
		}
		else {
			// confirmed only one dice, so access with [0] index
			$face = $dice[0]->getFaces();
			$dist = array();
			// set prob of getting 0 as 0%, as index corresponds to dice value and can't roll 0 on a dice
			array_push($dist, 0);
			// advantage distributions are calculated via polynomial; so pick polynomial based on flag, and use it to calculate prob of each value from 1-maxroll (and push to dist array)
			switch ($flag) {
				// advantage
				case Modifiers::Advantage:
					for ($i = 1; $i <= $face; $i++) {
						array_push($dist, 
									(  (2.0*$i - 1.0) / $face**2.0  )
									);
					}
					break;
				// disadvantage
				case Modifiers::Disadvantage:
					for ($i = 1; $i <= $face; $i++) {
						array_push($dist, 
									(  (2.0*($face - $i) + 1.0) / $face**2.0  )
									);
					}
					break;
				// AoD
				case Modifiers::AoD:
					for ($i = 1; $i <= $face; $i++) {
						array_push($dist, 
									(  (1.0/$face**4.0)  *  (
															($i**3.0) * 4.0
															- ($i**2.0) * (6.0 + 12.0*$face)  
															+ ($i) * (8.0*($face**2.0) + 12.0*$face + 4.0)
															- (4.0*($face**2.0) + 4.0*$face + 1.0)
														  )
									)
									);
					}
					break;
				// DoA
				case Modifiers::DoA:
					for ($i = 1; $i <= $face; $i++) {
						array_push($dist, 
									(  (1.0/$face**4.0)  *  (
															- ($i**3.0) * 4.0
															+ ($i**2.0) * 6.0
															+ ($i) * (4.0*($face**2.0) - 4.0)
															- (2.0*($face**2.0) - 1.0)
														  )
									)
									);
					}
					break;
				default:
					// TODO: throw error, shouldn't be possible given we check for this condition above
					break;
			}
			
			return $dist;
		}
	}
	
	
	/* get a "wrapped around" version of the DiceCombo's distribution, 
	i.e. shifted by (max roll)/2 to the right, but "wrapping around" back to 1 when the previous max is exceeded.
	So on a 2d10, the previous probability of a 20 (1%) becomes the new possibility of a 10. The previous probability of a 5 (4%) becomes the new probability of a 15 (6%). Etc.
	Useful for turning a very "consistent" roll with a narrow std. dev (e.g. 5d4) into a "risky" U-shaped one, with the highest chance of getting a 1 or a 20 and the lowest chance of getting a 10 or 11.
	In terms of dice roll results, this is *effectively equivalent* to the outcome equalling:
	- sum of the rolls 
	- a bonus of (floor)half the "extent" of possible values for the roll (e.g. for 2d10 extent is 2-20, so 19 possible values, so bonus is (floor)19/2 = 9.
	- modulus (max_roll+1)
	- plus a conditional bonus of +min_roll if below the halfway mark of the extent, so wrap to minroll not 0
	*/
	public function getWrapDist() {
		$result = array();
		
		// temporarily remove padding 0s from dist (e.g. the 0% of getting a 0). This is easier to calculate if we just have the relevant values.
		$filtered = array_filter($this->getDist(), function($var) { return $var != 0; } );
		// find what the min possible roll is from minimum index after filtering. (e.g. for 5d4, min roll is 5).
		$min = min(array_keys($filtered));
		// fix indices and find (floor)halfway point of extent of distribution (e.g. for 1d20 this is (floor)10.5 = 10, for 5d4 this is (floor)12.5 = 12).
		$filtered = $tmpFiltered = array_values($filtered);
		$halfway = floor(count($filtered)/2.0);
	
		
		// loop through and swap values
		// if even number of possible values, can just swap simply
		if (count($filtered) % 2 == 0) {
			for ($i = 0; $i < $halfway; $i++) {
				$filtered[$i] = $tmpFiltered[$i+$halfway];
				$filtered[$i+$halfway] = $tmpFiltered[$i];
			}
		}
		// else if odd, have to offset. We bias towards highest peak being on the "successful" end rather than "unsuccessful" end, because winning is fun for players :)
		else {
			for ($i = 0; $i < $halfway; $i++) {
				$filtered[$i] = $tmpFiltered[$i+$halfway+1];
				$filtered[$i+$halfway] = $tmpFiltered[$i];
			}
			// we will have missed updating the final value with the above
			$filtered[count($filtered)-1] = $tmpFiltered[$halfway];
		}
		
		// re-add padding zeroes at start (which indicate that e.g. chance of rolling 0 is 0%). The number of 0s needed is just the number of dice we have (as e.g. 5 dice means 0-4 can't be rolled; 3 dice means 0-2 can't be rolled, etc.)
		for ($i = 0; $i < $min; $i++) {
			array_push($result, 0);
		}
		// add wrapped distribution
		$result = array_merge($result, $filtered);
		// re-add padding zeroes at end (for parity with start - useful for graph visualisations on frontend.) By convention with how our convolutions pan out, this will be (#dice-1) 0s.
		for ($i = 0; $i < $min-1; $i++) {
			array_push($result, 0);
		}
		
		return $result;
		
	}
	
	
	// find and return a similar dice combo to this one. Complex flag = false prefers solutions with fewest types of dice; true prefers more types of dice. 
	// So complex = true would prefer e.g. "2d6+2d8+2d10" while complex=false would prefer e.g. "4d12".
	// solution inspired by the "naive" recursive solution to the famous coin-change problem, tweaked to return all permutations rather than just the minimum number of coins used in any permutation. The full problem typically uses dynamic programming, hence the recursive solution being referred to as the "naive" solution, but it works for us.
	public function getSimilarDiceCombo ($complex=false) {
		// set up parameters:
		// determine max roll of our dice combo. This serves as the "target", the parameter we want to "match" with our new combo.
		$sum = 0;
		foreach ($this->getDice() as $die) {
			$sum += $die->getFaces();
		}
		
		// which dice are eligible for consideration - in our game, this is a hardcoded set list: d4, d6, d8, d10, d12, d20
		$eligibleDiceTypes = [4,6,8,10,12,20];
		
		
		// generate similar dice combo:
		// placeholder empty array to store results in
		$result = array();
		
		// recursive solution finder for parameters set up above. We start with parameter $index = 0.
		$solutions = $this->findCombos($sum, $result, 0, $eligibleDiceTypes);
		
		// cull solutions and pick one
		$culledSolution = $this->cullCombos($solutions, $complex);
		
		// culledSolution is just an array of numbers, e.g. [4,4,6] meaning 2d4+1d6. We turn this into Dice, then a DiceCombo.
		$diceForCombo = array();
		foreach($culledSolution as $face) {
			$die = new Dice($face);
			array_push($diceForCombo, $die);
		}
		$newCombo = new DiceCombo($diceForCombo);
		
		return $newCombo;
	}
	// HELPER: recursively find equivalent dice combos
	// this algorithm adapted from Zabir Al Nazi Nabil @ StackExchange
	// https://stackoverflow.com/a/62010094
	// change: eliminating repeats - by not allowing future recursions to look at smaller die than the "current" one
	// i.e. won't get (4,8) and (8,4), as the latter requires going from a larger d8 at an earlier recursion to a smaller d4 in a later recursion.
	// We'll thus only get (4,8).
	private function findCombos($target, $result, $index, $eligibleDiceTypes) {
		
		// place to store solutions found (we "merge" this back up the recursive calls to return our full list of solutions).
		$solution = array();
		
		// (recursions):
		// base case: we've got a list of dice stored in $result whose sum equals our desired target; that's a solution
		if ($target == 0) {
			array_push($solution, $result);
		}
		// otherwise (inner to outer logic):
		// inner: store a die and recurse with target = (target-die)
		// outer: do the above *for each dice available* (i.e. same size or larger than current dice).
		// this finds all solutions with a given dice as the "next" dice in the chain, then returns that list, then tries the next largest dice as the "next" one in the chain and finds all solutions from there, and so on until all solutions have been found
		else {
			// we pass in $index to say "which is the previous dice? we aren't allowed to use smaller dice than that next."
			for ($i = $index; $i < count($eligibleDiceTypes); $i++) {
				// if not overshooting target
				if ( ($target - $eligibleDiceTypes[$i]) >= 0 ) {
					// add this dice to result
					$result = array_merge($result, array($eligibleDiceTypes[$i]));
					// recur with target reduced by that dice's value, and index set to this dice's index (i.e. from here on, only look at dice equal/larger than this one)
					$subSolutions = $this->findCombos(($target - $eligibleDiceTypes[$i]), $result, $i, $eligibleDiceTypes, $solution);
					//merge any recursively found solutions from the above in to our big list of all solutions
					$solution = array_merge($solution, $subSolutions);
					// remove this dice before we loop back to try a different dice in the same spot 
					// i.e. (if we've just been through the d4, and now want to try a d6 *in the same place*, need to remove d4 first).
					array_pop($result);
				}
			}
		}
		
		// ...  now simply return the completed list of solutions after our recursions are done
		return $solution;
	}
	// HELPER: cull solutions in some way to provide a good/viable subset, and pick from those based on complexity
	private function cullCombos($combos, $complex) {
		$valid = array();
		
		// get our initial dice in equivalent format to $combos, for comparison, so we can ensure not to return the initial dice as an "alternative"!
		$initial = array();
		foreach ($this->getDice() as $die) {
			array_push($initial, $die->getFaces());
		}
		sort($initial);
		
		foreach ($combos as $combo) {
			// general filter to get rid of awkward combos that are hard to visualise/intuitively think about
			if (($numFaces = count(array_unique($combo))) >= 3) {
				// if solution uses more than 3 *types* of dice, skip
				if ($numFaces > 3) {
					continue;
				}
				// if solution uses exactly 3 types of dice and there are < 6 dice total, skip
				if ($numFaces == 3 && count($combo) < 6) {
					continue;
				}
			}
			// filter out same solution as our initial dice, i.e. only push if combo not same as initial
			sort($combo);
			if ($initial !== $combo) {
				array_push($valid, $combo);
			}
		}
		
		// sort valid solutions by the number of types of dice in them (i.e. "complexity")
		usort($valid, function($a, $b) { return count(array_unique($a)) <=> count(array_unique($b)); } );
		
		// set desired complexity - minimum or maximum out of our valid solutions, based on $complex flag parameter
		$complexityBench = null;
		if ($complex == false) {
			// least complex valid combo found, i.e. 0th index now it's sorted by complexity
			$complexityBench = count(array_unique($valid[0]));
		}
		else {
			// most complex valid combo, i.e. final index
			$complexityBench = count(array_unique(end($valid)));
		}
		
		// filter our solutions based on desired complexity, i.e. if they have same number of die types as our "benchmark" (least/most complex available)
		$valid = array_filter($valid, function($var) use ($complexityBench) { return count(array_unique($var)) == $complexityBench; } );
		
		// filtering messes with indexing - the below effectively fixes it by creating a new, properly-indexed array out of whatever is left post-filtering
		$valid = array_values($valid);
		
		// ...so that we can pick one at random by providing a random index
		return $valid[rand(0, count($valid)-1)];
	}


	
	
	
	
	//**************************** getters/setters
	
	// dice:
	public function getDice() {
		return $this->dice;
	}
	public function setDice(array $dice) {
		// check all elements are objects of Dice class
		$formatCheck = true;
		foreach ($dice as $die) {
			if (!is_object($die)) {
				$formatCheck = false;
			}
			else if (get_class($die) != "Dice") {
				$formatCheck = false;
			}
		}
		// if so, set - and also update distribution
		if ($formatCheck == true) {
			$this->dice = $dice;
			$this->setDistribution();
		}
		// if not, throw error
		else {
			// TODO: throw error
		}
	}
	
	// dist, mean, stddev - get only, it should only be set when we update dice combo.
	public function getDist() {
		return $this->dist;
	}
	public function getMean() {
		return $this->mean;
	}
	public function getStdDev() {
		return $this->stdDev;
	}
}
?>