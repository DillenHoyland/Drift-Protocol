import { showFloatingNumber } from "./showFloatingNumber.js";

//new spinDiceWithValue, which is used by playDiceRoll
export const spinDiceWithValue = (dice, scene, diceType, targetValue) => {
  //returns a promise, because we wait for the animation to get over
  return new Promise((resolve) => {
    //keeping animation progress 0 to 1
    let diceCounter = 0;

    //using pi value for rotation
    const pi = Math.PI;

    //the dice gets snap at 90deg so that it rest on one of its face
    const snapPos = pi / 2;

    //this is the min rotation speed
    let minSpeed = 0.1;
    //this is the max rotation speed
    let addedSpeed = 0.2;

    //this gives a random speed boost to the x axis
    let randomX = Math.random() * addedSpeed;
    //this gives a random speed boost to the y axis
    let randomY = Math.random() * addedSpeed;

    //final x rotaion speed
    let xSpeed = minSpeed + randomX;
    //final y rotaion speed
    let ySpeed = minSpeed + randomY;

    //a variable to hold the max vvalue for the dice tyoes
    let hValue;

    //the highest value number  for d6 is 6
    if (diceType === "D6") {
      hValue = 6;
    }
    //the highest value number  for d8 is 8
    else if (diceType === "D8") {
      hValue = 8;
    }
    //the same... for d10 is 10
    else if (diceType === "D10") {
      hValue = 10;
    }
    //the same... for d12 is 12
    else if (diceType === "D12") {
      hValue = 12;
    }
    //the same... for d20 is 20
    else if (diceType === "D20") {
      hValue = 20;
    }
    //if we dont know the dice type, the highest value number default is 20
    else hValue = 20;

    //this is s helper function that converts the dice value to a specific rotation
    const getRotationForValue = (value, hValue) => {
      //d6 has exact face mapping for visual accuracy
      //is this a normal cube d6 ? if yes
      if (hValue === 6) {
        //player gets 1, dont spin
        if (value === 1) return { x: 0, y: 0 };
        //player gets 2, spin one quater on x axis
        if (value === 2) return { x: 1, y: 0 };
        //player gets 3, spin one quater on y axis
        if (value === 3) return { x: 0, y: 1 };
        //player gets 4, spin once on x axis and once on y axis
        if (value === 4) return { x: 1, y: 1 };
        //player gets 5, spin 2 quater on x axis
        if (value === 5) return { x: 2, y: 0 };
        //player gets 6, spin 2 quater on y axis
        if (value === 6) return { x: 0, y: 2 };

        //in case, return no rotation
        return { x: 0, y: 0 };
      } else {
        //if its not a d6 cube and other dice type
        //for other dice, using a formula we create for different rotations
        //multiply the value by 7
        const tSeven = value * 7;
        //divide by 4 and keep only the leftovers
        const leftOverX = tSeven % 4;
        //this is our x spin value
        const turnX = leftOverX;

        //multiply the value by 7
        const tThirteen = value * 13;
        //divide by 4 and keep only the leftovers
        const leftOverY = tThirteen % 4;
        //this is our x spin value
        const turnY = leftOverY;

        //multiply the value by 7
        const tThree = value * 3;
        //divide by 4 and keep only the leftovers
        const leftOverZ = tThree % 4;
        //this is our x spin value
        const turnZ = leftOverZ;

        return {
          x: turnX,
          y: turnY,
          z: turnZ,
        };
      }
    };

    //this gets the target rotation for our specific values
    const targetRot = getRotationForValue(targetValue, hValue);
    //get the x spin value from the get rotation value
    const targetXRot = targetRot.x;
    //get the y spin value from the get rotation value
    const targetYRot = targetRot.y;
    //get the z spin value from the get rotation value, if no z spin value then 0 by default
    const targetZRot = targetRot.z || 0;

    //this is the spin animation function
    const spin = () => {
      //increments the animation timer
      diceCounter = diceCounter + 0.02;

      //rotate on all the axis
      dice.rotation.x += xSpeed;
      dice.rotation.y += ySpeed;
      dice.rotation.z += xSpeed * 0.5;

      //this is the bouncing up
      if (diceCounter < 0.5) {
        dice.position.z += 2;
      } else {
        //this is the bouncing up
        dice.position.z -= 2;
      }

      //this makes sure the animation is not complete keep going
      if (diceCounter < 1) {
        requestAnimationFrame(spin);
      } else {
        //if its complete return to this height
        dice.position.z = 23;
        //snap to this position  in the x axis after rotation
        dice.rotation.x = targetXRot * snapPos;
        //snap to this position  in the y axis after rotation
        dice.rotation.y = targetYRot * snapPos;
        //snap to this position  in the z axis after rotation
        dice.rotation.z = targetZRot * snapPos;

        //this makes sure to show floating number and resolve the promise when done
        showFloatingNumber(targetValue, dice, scene, diceType, () => {
          resolve();
        });
      }
    };

    //begin our animation
    spin();
  });
};

//another helper function: displays the result box( only uused bby the demo dice spin)
const showResult = (number, type) => {
  //get the result box element
  const resultBox = document.getElementById("result-box");
  //get the number displayed
  const resultVal = document.getElementById("result-val");

  //check if this elements exist
  if (!resultBox || !resultVal) {
    console.error("Result UI elements not found");
    return;
  }

  //this adds the number to into the display
  resultVal.textContent = number;
  //this ensures the result box is visible
  resultBox.classList.add("show");

  //this hides it after 3 sec
  setTimeout(() => {
    resultBox.classList.remove("show");
  }, 3000);
};
