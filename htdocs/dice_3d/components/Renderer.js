/*
bring in the Threejs library
*/
import * as Threejs from "three";

//this is the renderer function
export const Renderer = () => {
  //this is selecting teh canvas element for the html and if not exit returning an error
  const canvas = document.querySelector("canvas.game");
  if (!canvas) throw new Error("no canvas found !");

  //this sets up the renderer by using the webgl renderer
  const renderer = new Threejs.WebGLRenderer({
    //this make sure that the background of the renderer is transparent
    alpha: true,

    //this make sure that the endged of the objects in the scene and smooth out
    antialias: true,

    //this tells th renderer to draw in the canvas that we set earlier
    canvas: canvas,
  });

  //this make sure the game pixel quality set to appropriate screen types
  renderer.setPixelRatio(window.devicePixelRatio);

  //this make sure the game takeup the entire sceen of the display
  // FIX: Get the wrapper element to measure the proper container size
  const wrapper = document.getElementById("threejs-dice-wrapper");

  //whats the width of the current screen ?
  const howWideIsTheScreen = wrapper ? wrapper.clientWidth : window.innerWidth;

  //whats the height of the current screen ?
  const howTallIsTheScreen = wrapper ? wrapper.clientHeight : 600;

  //make sure renderer uses the current screen sizes
  renderer.setSize(howWideIsTheScreen, howTallIsTheScreen);

  //this enables to be render in the scene
  renderer.shadowMap.enabled = true;

  //setting soft shadows, which are soft instead of sharp and edgy
  const softShadows = Threejs.PCFShadowMap;

  //applys the shadow style to the renderer
  renderer.shadowMap.type = softShadows;

  return renderer;
};
