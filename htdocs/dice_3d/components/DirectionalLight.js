/*
bring in the Threejs library
*/
import * as Threejs from "three";

//Directional light function
export const DirectionalLight = () => {
  //choosin what tyoe of light source, in this cas its directional light like ☀️ sun
  const dirLight = new Threejs.DirectionalLight(0x00ff50, 2.5);

  //this is where the directional light source is sitting like the sun along teh x, y ans z axis
  //-150 left on the y axis
  const dirLightXPos = -150;

  //-150 backwards on the y axis
  const dirLightYPos = -150;

  //200 top on the z axis
  const dirLightZPos = 200;

  dirLight.position.set(dirLightXPos, dirLightYPos, dirLightZPos);

  //this sets z axis as up direction instead of x and y (x, y, z)
  const xUp = 0;
  const yUp = 0;
  const zUp = 1;
  dirLight.up.set(xUp, yUp, zUp);

  //this makes sure any object in the scene can cast shadow when under the directional light source
  dirLight.castShadow = true;

  //this ensures the pixel size for the cast shadows, higher means sharper shadows
  const shadowQualityWidth = 2000;
  const shadowQualityHeight = 2000;
  dirLight.shadow.mapSize.width = shadowQualityWidth;
  dirLight.shadow.mapSize.height = shadowQualityHeight;

  /*
  this is our 2nd camera which is a shadow camera and is invisible,
  but it helps to decide where to cast the shadows based on the directional light and its pov
  */

  //this tells the shadow camera to use z as up direction instead of x and y (x, y, z)
  const xUpShadowCamera = 0;
  const yUpShadowCamera = 0;
  const zUpShadowCamera = 1;

  dirLight.shadow.camera.up.set(
    xUpShadowCamera,
    yUpShadowCamera,
    zUpShadowCamera,
  );

  //before putting a 🎥 shadow camera it needs to know its range of what is can see 👀

  //maximum to the left the camera can see, negative is the left side in the grid
  const shadowLeftEnd = -600;

  //maximum to the right the camera can see, positive is the right side in the grid
  const shadowRightEnd = 600;

  //maximum to the top the camera can see, positive is the top side in the grid
  const shadowTopEnd = 600;

  //maximum to the bottom the camera can see, negative is the bottom side in the grid
  const shadowBottomEnd = -600;

  //maximum camera can see in close before everything vanishes
  const shadowCloseTo = 50;

  //maximum camera can see in far distant before everything vanishes
  const shadowEndTo = 600;

  //now uisng the ranges that we set previously for the shadow camera
  dirLight.shadow.camera.left = shadowLeftEnd;
  dirLight.shadow.camera.right = shadowRightEnd;
  dirLight.shadow.camera.top = shadowTopEnd;
  dirLight.shadow.camera.bottom = shadowBottomEnd;
  dirLight.shadow.camera.near = shadowCloseTo;
  dirLight.shadow.camera.far = shadowEndTo;

  return dirLight;
};
