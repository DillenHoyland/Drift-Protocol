/*
bring the Threejs library 
*/
import * as Threejs from "three";

// Function that creates a floating number above a dice
export const showFloatingNumber = (
  //this is the  value to in the scene
  number,
  //this is 3D dice mesh this number belongs to
  dice,
  //this is 3D scene to add the floating number to
  scene,
  //this is type of dice (D4, D6, etc.)
  diceType,
  //this is the func to call after the floating number disappear
  onComplete,
) => {
  //this create an html canvas element to draw the number on
  const canvas = document.createElement("canvas");
  //canvas width
  const width = 128;
  //canvas height
  const height = 128;

  //we set the canvas size
  canvas.width = width;
  canvas.height = height;

  //then add the drawing context or brush for the canvas
  const ctx = canvas.getContext("2d");

  //this fill color to semi-transparent black for the background circle
  ctx.fillStyle = "rgba(0, 0, 0, 0.85)";

  //start drawing a circle
  ctx.beginPath();

  //draw a circle centered in the canvas with a small margin
  //half a circle in
  const hCircle = Math.PI;
  //double is a full circle
  const fCircle = hCircle * 2;

  //draw the same circle for the border
  //gives the exact center x axis of the canvas
  const xCenter = width / 2;
  //gives the exact center y axis of the canvas
  const yCenter = height / 2;
  //sets the circle radius, half canvas width minus 10px padding
  const radius = width / 2 - 10;
  //start drawing from righmost point
  const sAngle = 0;
  //all the way around to complete a full 360-degree circle
  const eAngle = fCircle;

  ctx.arc(xCenter, yCenter, radius, sAngle, eAngle);
  //fill the circle with the black color
  ctx.fill();

  //set border color to this
  ctx.strokeStyle = "#00e5ff";
  //set border thickness
  ctx.lineWidth = 4;
  //start drawing the border circle
  ctx.beginPath();

  //draw the same circle for the border
  ctx.arc(xCenter, yCenter, radius, sAngle, eAngle);
  //draw the border outline
  ctx.stroke();

  //this set text color to cyan/neon blue
  ctx.fillStyle = "#00e5ff";
  //this set font to bold, size relative to canvas width
  ctx.font = `Bold ${canvas.width * 0.5}px Arial`;
  //this center the text horizontally
  ctx.textAlign = "center";
  //this center the text verticc=ally
  ctx.textBaseline = "middle";
  //it converts the number to text s
  const numberText = number.toString();
  //this centers the number to the canvas
  ctx.fillText(numberText, xCenter, yCenter);

  //this convert the canvas to our custom Three.js texture
  const texture = new Threejs.CanvasTexture(canvas);
  //this create a floating number material using the texture
  const material = new Threejs.SpriteMaterial({ map: texture });
  //this ensures the floating number (always faces the camera)
  const floatSprite = new Threejs.Sprite(material);

  //the position the sprite at the same X and Y as the die
  floatSprite.position.copy(dice.position);
  //this raise it 60 units above the die so it floats above
  floatSprite.position.z += 60;
  //scale the floating numeber to a good visible size
  floatSprite.scale.set(30, 30, 1);

  //adds the floating number to the scene
  scene.add(floatSprite);

  //this auto remove after 5 seconds
  setTimeout(() => {
    //remove the floatSprite from the scene
    scene.remove(floatSprite);
    //free up gpu memory by disposing the texture
    texture.dispose();
    //free up gpu memory by disposing the material
    material.dispose();

    //once floating number disappears, show the result box
    if (onComplete) onComplete(number, diceType);
    // wait till 5 seconds
  }, 5000);
};
