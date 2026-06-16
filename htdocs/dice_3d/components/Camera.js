/*
bring in the Threejs library 
*/
import * as Threejs from "three";

//this is the camera function for the scene
export const Camera = () => {
  //a random base to start of viewing pixel
  const baseSize = 220;

  // FIX: Target the wrapper instead of the whole window
  const wrapper = document.getElementById("threejs-dice-wrapper");

  //this is how wide is your screen
  const howWideIsTheScreen = wrapper ? wrapper.clientWidth : window.innerWidth;

  //this is how tall is your screen
  const howTallIsTheScreen = wrapper ? wrapper.clientHeight : 600;

  //this is the screen shape ratio, is it a phone(0.5) or laptop(1.7) ?
  const screenShape = howWideIsTheScreen / howTallIsTheScreen;

  //this set the width based of the screen shape
  let width;
  if (screenShape < 1) {
    //incase the screen shape is taller than wide, like a phone, keep it as it is
    width = baseSize;
  } else {
    //if the screen shape is wider than tall, like a laptop, stretch the wide to fit
    width = baseSize * screenShape;
  }

  //this set the height based of the screen shape
  let height;
  if (screenShape < 1) {
    //incase the screen shape is taller than wide, like a phone, stretch the height to fit
    height = baseSize / screenShape;
  } else {
    //rather the screen shape is wider than tall, like a laptop, keep it as it is
    height = baseSize;
  }

  //before putting a 🎥 camera it needs to know its range of what is can see 👀

  //maximum to the left the camera can see, negative is the left side in the grid
  const leftEnd = width / -2;

  //maximum to the right the camera can see, positive is the right side in the grid
  const rightEnd = width / 2;

  //maximum to the top the camera can see, positive is the top side in the grid
  const topEnd = height / 2;

  //maximum to the bottom the camera can see, negative is the bottom side in the grid
  const bottomEnd = height / -2;

  //maximum camera can see in close before everything vanishes
  const closeTo = 100;

  //maximum camera can see in far distant before everything vanishes
  const endTo = 1500;

  //choosing what type of camera, in this case its orthographic
  //and using all the ranges than we set previously for this camera
  const camera = new Threejs.OrthographicCamera(
    leftEnd,
    rightEnd,
    topEnd,
    bottomEnd,
    closeTo,
    endTo,
  );

  //this tells the camera too use z as up direction instead of x and y (x, y, z)
  const xUp = 0;
  const yUp = 0;
  const zUp = 1;
  camera.up.set(xUp, yUp, zUp);

  //this is where the camera is sitting using the same axis (x, y, z), like a birds POV
  //300 right on the x axis
  const cameraXPos = 400;

  // -300 backward on the y axis
  const cameraYPos = -400;

  // and 300 top on the z axis
  const cameraZPos = 800;

  //calling those position values
  camera.position.set(cameraXPos, cameraYPos, cameraZPos);

  //this tells the camera where to look at
  //no matter wher the camera is position, look at the absolute center of x, y, z axis
  const lookXAt = 0;
  const lookYAt = 0;
  const lookZAt = 12;
  camera.lookAt(lookXAt, lookYAt, lookZAt);

  // it resizes screen to fit any size
  camera.Resize = (renderer) => {
    //a random base to start of viewing pixel
    const baseSize = 220;

    // FIX: Update resize to also use the wrapper
    const wrapper = document.getElementById("threejs-dice-wrapper");

    //this is how wide is your screen
    const howWideIsTheScreen = wrapper
      ? wrapper.clientWidth
      : window.innerWidth;

    //this is how tall is your screen
    const howTallIsTheScreen = wrapper ? wrapper.clientHeight : 600;

    //this is the screen shape ratio, is it a phone(0.5) or laptop(1.7) ?
    const screenShape = howWideIsTheScreen / howTallIsTheScreen;

    //this set the width based of the screen shape
    let width;
    if (screenShape < 1) {
      //incase the screen shape is taller than wide, like a phone, keep it as it is
      width = baseSize;
    } else {
      //if the screen shape is wider than tall, like a laptop, stretch the wide to fit
      width = baseSize * screenShape;
    }

    //this set the height based of the screen shape
    let height;
    if (screenShape < 1) {
      //incase the screen shape is taller than wide, like a phone, stretch the height to fit
      height = baseSize / screenShape;
    } else {
      //rather the screen shape is wider than tall, like a laptop, keep it as it is
      height = baseSize;
    }

    //maximum to the left the camera can see, negative is the left side in the grid
    const leftEnd = width / -2;

    //maximum to the right the camera can see, positive is the right side in the grid
    const rightEnd = width / 2;

    //maximum to the top the camera can see, positive is the top side in the grid
    const topEnd = height / 2;

    //maximum to the bottom the camera can see, negative is the bottom side in the grid
    const bottomEnd = height / -2;

    //this update the new camera ranges
    camera.left = leftEnd;
    camera.right = rightEnd;
    camera.top = topEnd;
    camera.bottom = bottomEnd;

    //this applys the new changes to the camera
    camera.updateProjectionMatrix();

    // this updates the renderer to the new screen size
    renderer.setSize(howWideIsTheScreen, howTallIsTheScreen);
  };

  return camera;
};
