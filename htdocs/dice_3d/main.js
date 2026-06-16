/*
bring in the Threejs library
and all the necessary functions
*/
import * as Threejs from "three";

import { Renderer } from "./components/Renderer.js";
import { Camera } from "./components/Camera.js";
import { Player } from "./components/Player.js";
import { D4 } from "./components/d4.js";
import { D8 } from "./components/d8.js";
import { D10 } from "./components/d10.js";
import { D12 } from "./components/d12.js";
import { D20 } from "./components/d20.js";
import { map, setupMap } from "./components/Map.js";
import { DirectionalLight } from "./components/DirectionalLight.js";
import { spinDiceWithValue } from "./utils/spinDice.js";
import { spinD4WithValue } from "./utils/spinD4.js";

//our scene

const scene = new Threejs.Scene();

//keeing dices ad templates and hidden
const d4Template = D4();
const d6Template = Player();
const d8Template = D8();
const d10Template = D10();
const d12Template = D12();
const d20Template = D20();

//basicaally store them but they will be hidden
[
  d4Template,
  d6Template,
  d8Template,
  d10Template,
  d12Template,
  d20Template,
].forEach((die) => {
  die.visible = false;
  scene.add(die);
});

//set our floor
setupMap();
scene.add(map);

//using a ambient light for our scene
const ambientLight = new Threejs.AmbientLight(0x00f7ff, 0.9);
scene.add(ambientLight);

//using a poing light for our scene like a sun
const floorGlow = new Threejs.PointLight(0x00ff50, 3.0, 500);
//this makes sure the floor does glow
floorGlow.position.set(0, 0, 150);
scene.add(floorGlow);
//adding a directional light to the sscene too
scene.add(DirectionalLight());
//adding our camera
const camera = Camera();
scene.add(camera);
//adding our renderer
const renderer = Renderer();

// Move canvas into the wrapper div from play.php
const wrapper = document.getElementById("threejs-dice-wrapper");
if (wrapper) {
  wrapper.appendChild(renderer.domElement);
  renderer.domElement.classList.add("game");
}

//makes the rendering smooth
renderer.setAnimationLoop(() => renderer.render(scene, camera));
//ensures teh camera resizes to teh renderer
window.addEventListener("resize", () => camera.Resize(renderer));

/*
dynamically creating our dices
active dice function takes a number of faces (like 4, 6, 8, 10, 12, or 20), 
creates a new 3D dice mesh of that category, 
and returns it so that we can roll multiple dice of the same kind without reusing the same one.
*/
let activeDice = [];

//create
function createDiceInstance(faces) {
  let newDie;
  switch (faces) {
    case 4:
      newDie = D4();
      break;
    case 6:
      newDie = Player();
      break;
    case 8:
      newDie = D8();
      break;
    case 10:
      newDie = D10();
      break;
    case 12:
      newDie = D12();
      break;
    case 20:
      newDie = D20();
      break;
    default:
      newDie = D20();
  }
  return newDie;
}

/*
this makes sure how the dices should be spread in the floor,
to avoid overpaing we make sure to change their x axis values
*/
function positionDiceInRow(diceArray) {
  //count number of dice to spread
  const count = diceArray.length;
  //space between each dice
  const spacing = 55;
  //total width needed for all dices inclusing spaces
  const totalWidth = (count - 1) * spacing;
  //this ensures teh row is perfectly center
  const startX = -totalWidth / 2;
  //loop through each dice and place it in the row
  diceArray.forEach((die, index) => {
    //placing each dice with the proper space in the row
    die.position.x = startX + index * spacing;
  });
}

/*
his function throws away all the dice from the last roll to make space for new dice, 
otherwise you'd see old dice on top of each other overlapping.
*/
function cleanupActiveDice() {
  //go through every dice in the scene
  activeDice.forEach((die) => {
    //remove the dice
    scene.remove(die);
    //if the dice has 3d shape remove it from the memory
    if (die.geometry) die.geometry.dispose();
    //same if it has color and textures too
    if (die.material) {
      if (Array.isArray(die.material)) {
        //this delets each material one by one
        die.material.forEach((m) => m.dispose());
      } else {
        die.material.dispose();
      }
    }
  });
  //clear everything, to have a fresh start
  activeDice = [];
}

/*
window.playDiceRoll is the main dice mechanic,
it grabs the roll data from the backend, 
creates the exact number and type of dice needed for the roll, 
places them on the table, spins them to land on their predetermined values, 
then displays the final result with the needed color and label.
*/
window.playDiceRoll = async function (data) {
  //get all the values from the new format data object
  const { types, rolls, outcome, result, flag, shift_value } = data;

  //this ensures the 3d dice is visible on the page
  if (wrapper) wrapper.style.display = "block";
  //pick the hint text element
  const hint = document.getElementById("hint");
  //change the hint element to this when rolling
  if (hint) hint.textContent = "[ ROLLING… ]";

  //clean old dices from the previous rolls
  cleanupActiveDice();

  //the new format includes types and rolls for the dice
  //types = [4, 4, 4, 6, 6] which is 5 dice total
  //rolls = [3, 4, 2, 5, 1] which is one value per dice

  //this has all the new dices we are about to make
  const diceList = [];

  //loop through all the dice types
  for (let i = 0; i < types.length; i++) {
    //get the numeber of faces for the current dice type
    const faces = types[i];
    //how many of this dice type we need
    const value = rolls[i];
    //this creates a brand new mesh of the correct dice type
    const newDie = createDiceInstance(faces);
    //add this dice to the scene
    scene.add(newDie);
    //make sure to remember this dice, so it can be deleted later
    activeDice.push(newDie);
    //store everything together dice type , faces and its values
    diceList.push({ mesh: newDie, faces, value });
  }

  //spread teh newly created dice properly in the row so that they wont ovverlap
  positionDiceInRow(activeDice);

  /*
This spins all dice simultaneously with their predetermined values, 
waits for every animation to finish, 
then displays the final outcome number in the result box.
*/
  const spinPromises = diceList.map(({ mesh, faces, value }) => {
    //d4 needs a different logic because its a pyramid shape
    if (faces === 4) return spinD4WithValue(mesh, scene, value);
    //all the pther dices used the similiar dice like spinning
    else return spinDiceWithValue(mesh, scene, `D${faces}`, value);
  });

  //wait till all the dices are complete with their animations
  await Promise.all(spinPromises);

  //get the result box that shows the final number
  const resultBox = document.getElementById("result-box");
  //get the number dispalayed inside the result box
  const resultVal = document.getElementById("result-val");

  //put the final number result, so that it display in the screen
  if (resultVal) resultVal.textContent = outcome;

  /*
  this defines the colors for roll results: green for success, 
  yellow/orange for partial success, and red for failure.
  */
  const outcomeColours = {
    //success
    success: "#00ff66",
    //partial success
    partial: "#ffcc00",
    //failure
    failure: "#ff4444",
  };

  /*
  wrap properties(flag 6)
  this is the Wrap calculation, so it takes your dice sum, adds half the possible range of outcomes, 
  and if the result exceeds the maximum roll, it "wraps around" back to the low end.
  */
  if (flag === "Wrap") {
    //if the result number is there, style it with these settings
    if (resultVal) {
      //adds purple color
      resultVal.style.color = "#bf40ff";
      //adds glow effect
      resultVal.style.textShadow = "0 0 25px #bf40ff";
    }

    // Calculate wrap display values
    const sumRolls = rolls.reduce((a, b) => a + b, 0);
    //to track the higest and lowest possible rolls
    //highest possible rolls
    let maxRoll = 0;
    //lowest possible rolls
    let minRoll = 0;
    //loop through each dice type to calculate the total possible range
    for (let i = 0; i < types.length; i++) {
      //adding faces * count to get the maximum possible rolls
      maxRoll += types[i];
      //adding 1 * count to get the minimum possible rolls
      minRoll += 1;
    }

    //all the posible outcomes together
    const extent = maxRoll - minRoll;
    //half the range is the amount we shift by
    const shiftAmount = shift_value || 0;
    //finally add the shift value to the raw value to get the wrapped value
    const shifted = sumRolls + shiftAmount;

    /*
    this creates and shows a special calculation label for wrap rolls (like "Wrapped! 21 → 5+1 = 6"), 
    or hides it and uses normal outcome colors for regular rolls.
    */
    let wrapCalcLabel = document.getElementById("wrap-calc-label");
    //create  a new label not exist in the result box
    if (!wrapCalcLabel && resultBox) {
      //create a div for showing the wrap calculation
      wrapCalcLabel = document.createElement("div");
      //assigning it an id
      wrapCalcLabel.id = "wrap-calc-label";
      //including style for the wrap calculation
      wrapCalcLabel.style.cssText = `
        font-size: 0.55rem;
        letter-spacing: 0.1em;
        margin-top: 4px;
        opacity: 0.9;
        font-family: 'Share Tech Mono', monospace;
      `;

      //find the outcome label, so that it can be place above
      const outcomeLabel = document.getElementById("roll-outcome-label");
      if (outcomeLabel) {
        //put or wrap calculation label right before it
        resultBox.insertBefore(wrapCalcLabel, outcomeLabel);
      } else {
        //if not outcome label just add it to the end of the result box
        resultBox.appendChild(wrapCalcLabel);
      }
    }

    //if the wrap calculation label is there
    if (wrapCalcLabel) {
      //make sure it is visible
      wrapCalcLabel.style.display = "block";
      //checking if tje shift valuue iss overflowed past the maximum roll possible
      if (shifted > maxRoll) {
        //find out howmuch it overlflowed by
        const overflow = shifted - maxRoll;
        //show the wrapped message with the overflow
        wrapCalcLabel.textContent = `Wrapped! ${shifted} → ${minRoll}+${overflow} = ${outcome}`;
      } else {
        //if not overflow just show simple calculation
        wrapCalcLabel.textContent = `${sumRolls} + ${shiftAmount} = ${shifted} → ${outcome}`;
      }
      //adds the wrap calculation text is purple color
      wrapCalcLabel.style.color = "#bf40ff";
    }
  } else {
    //for the normal rools result use the normal outcome color options
    if (resultVal) {
      //apply the colors based on teh outcome: success. partial, failure
      resultVal.style.color = outcomeColours[result] ?? "#00ff66";
      //also add the glowing effect
      resultVal.style.textShadow = `0 0 20px ${outcomeColours[result] ?? "#00ff66"}`;
    }
    //hides wrap calc label for normal rols
    const wrapCalcLabel = document.getElementById("wrap-calc-label");
    if (wrapCalcLabel) {
      wrapCalcLabel.style.display = "none";
      wrapCalcLabel.innerHTML = "";
    }
  }

  /*
  create or update result label
  this creates the outcome label showing success or failure"(with wrapped added for wrap rolls), 
  updates the hint text with the roll type, and after 3 seconds hides everything and cleans up the dice.
  */

  //get the roll outcome label: success. partial, failure
  let resultLabel = document.getElementById("roll-outcome-label");
  //in case it doesnot exist, create it
  if (!resultLabel && resultBox) {
    //this is the new div for outcome text
    resultLabel = document.createElement("div");
    //giving it an id
    resultLabel.id = "roll-outcome-label";
    //adding different style properties to it
    resultLabel.style.cssText = `
      font-size: 14px !important;
      letter-spacing: 0.3em !important;
      text-transform: uppercase !important;
      margin-top: 6px !important;
      font-family: 'Share Tech Mono', monospace !important;
    `;

    //make sure it appears at the bottom of teh result box
    resultBox.appendChild(resultLabel);
  }

  //check if the outcome label exist
  if (resultLabel) {
    //check if this is wrap roll
    if (flag === "Wrap") {
      //if wrap roll then show the result with wrapped suffiix
      resultLabel.textContent = `${result.toUpperCase()} • WRAPPED`;
      //add the same color
      resultLabel.style.color = "#bf40ff";
    } else {
      //normal rolls just show the result
      resultLabel.textContent = result.toUpperCase();
      //again color it based on the outcome types
      resultLabel.style.color = outcomeColours[result] ?? "#00ff66";
    }
  }

  //this ensures the result box is visible with the fade animation
  if (resultBox) resultBox.classList.add("show");

  //different texts to appear in the hint bar for each roll type
  const flagLabels = {
    Sum: "[ SUM ]",
    Advantage: "[ ADVANTAGE — HIGHEST TAKEN ]",
    Disadvantage: "[ DISADVANTAGE — LOWEST TAKEN ]",
    AoD: "[ ADVANTAGE OF DISADVANTAGE ]",
    DoA: "[ DISADVANTAGE OF ADVANTAGE ]",
    Wrap: "[ WRAP — OVERFLOW WRAPPED AROUND ]",
  };

  //it updates the hints text to show what type of roll the current roll is
  if (hint) hint.textContent = flagLabels[flag] ?? "[ ROLL COMPLETE ]";

  //cleans everything up in 3 sec
  setTimeout(() => {
    //hide the result box with the fade animation
    if (resultBox) resultBox.classList.remove("show");
    //reset the hint back to teh default text
    if (hint) hint.textContent = "[ PICK AN OPTION TO ROLL ]";
    cleanupActiveDice();
  }, 3000);
};

/*
note: debugging testing section (only for dev testing)

This is a hidden debug feature that lets you click the dice canvas to roll one of every dice type at once with random values, 
useful for testing the 3D animations without going through the actual game flow.
*/
/*
if (wrapper) {
  //checks for any clicks in the canvas
  wrapper.addEventListener("click", (e) => {
    //only works on the canvas
    if (e.target === renderer.domElement) {
      //clear any dices in the current scene
      cleanupActiveDice();
      //creating one of each dice type
      const demoDice = [
        createDiceInstance(6),
        createDiceInstance(20),
        createDiceInstance(12),
        createDiceInstance(10),
        createDiceInstance(8),
        createDiceInstance(4),
      ];

      //add these new dices to the scene
      demoDice.forEach((die) => {
        //appera on the screen
        scene.add(die);
        //traking it for cleanup
        activeDice.push(die);
      });

      //spread every dice evenly in the row
      positionDiceInRow(activeDice);
      //spin this dice in a random value
      spinDice(demoDice[0], scene, "D6");
      //spin this dice in a random value
      spinDice(demoDice[1], scene, "D20");
      //spin this dice in a random value
      spinDice(demoDice[2], scene, "D12");
      //spin this dice in a random value
      spinDice(demoDice[3], scene, "D10");
      //spin this dice in a random value
      spinDice(demoDice[4], scene, "D8");
      //spin this dice in a random value
      spinD4(demoDice[5], scene);
    }
  });
}
  */
