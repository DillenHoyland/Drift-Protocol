import * as Threejs from "three";
import { showFloatingNumber } from "./showFloatingNumber.js";

//new spinD4WithValue is used by playDiceRoll with predetermined value
export const spinD4WithValue = (dice, scene, targetValue) => {
  //returns a promise, because we wait for the animation to get over
  return new Promise((resolve) => {
    //this is animation progress counter from 0 to 1
    let diceCounter = 0;
    //this rememember the dice starting height
    const baseZ = dice.position.z;

    //this creates random axiss for the dice to rotate
    const axis = new Threejs.Vector3(
      Math.random(),
      Math.random(),
      Math.random(),
    ).normalize();

    //this tells which direction is up in the scene
    //up means ponting in the sky at z axis
    const up = new Threejs.Vector3(0, 0, 1);

    //face 1 is teh corner point down, needs to rotate upwards
    const face1Corner = new Threejs.Vector3(0.9109, -0.2441, -0.3333);
    //emty spin instruction
    const fac1Empty = new Threejs.Quaternion();
    //this implies sping from this corner to staright up
    const face1Rotation = fac1Empty.setFromUnitVectors(face1Corner, up);

    //face 2 is the corner point down towards the floor
    const face2Corner = new Threejs.Vector3(-0.6667, -0.6667, -0.3333);
    //emty spin instruction
    const face2Empty = new Threejs.Quaternion();
    //this implies sping from this corner to staright up
    const face2Rotation = face2Empty.setFromUnitVectors(face2Corner, up);

    //face 3 is the corner point down towards the floor as well
    const face3Corner = new Threejs.Vector3(-0.2441, 0.9107, -0.3333);
    //emty spin instruction
    const face3Empty = new Threejs.Quaternion();
    //this implies sping from this corner to staright up
    const face3Rotation = face3Empty.setFromUnitVectors(face3Corner, up);

    //face 4 is already pointing upwards
    const face4Rotation = new Threejs.Quaternion();

    //Array of all 4 face rotations, one for each side of the pyramid
    const faceQuaternions = [
      face1Rotation,
      face2Rotation,
      face3Rotation,
      face4Rotation,
    ];

    //convert the target value to an array index (0-3)
    const faceIndex = targetValue - 1;
    //get the exact rotation for this specific dice face
    const targetQuaternion = faceQuaternions[faceIndex];

    //this is the spin animation function
    const spin = () => {
      //increments the animation timer
      diceCounter += 0.02;
      //rotate around the random axis
      dice.rotateOnAxis(axis, 0.2);

      //this is the bouncing up
      if (diceCounter < 0.5) dice.position.z += 2;
      //this is the bouncing up
      else dice.position.z -= 2;

      //this makes sure the animation is not complete keep going
      if (diceCounter < 1) {
        requestAnimationFrame(spin);
      } else {
        //if its complete return to the starting height
        dice.position.z = baseZ;
        //snap to the exact face to the target value
        dice.quaternion.copy(targetQuaternion);

        //this makes sure to show floating number and resolve the promise when done
        showFloatingNumber(targetValue, dice, scene, "D4", () => {
          resolve();
        });
      }
    };

    //begin our animation
    spin();
  });
};
