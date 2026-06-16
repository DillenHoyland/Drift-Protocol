/*
bring in Threejs library
*/
// import * as Threejs from "three";

/*
bring in the player texture function for the player
this allows each face of the player can have its own number texture
*/
// import { PlayerTexture } from "./PlayerTexture.js";

//player function
// export const Player = () => {
//   const materials = [
//     //applies the player texture material to all the sides of the player
//     //we can choose any number for any side
//     new Threejs.MeshStandardMaterial({ map: PlayerTexture(1) }),
//     new Threejs.MeshStandardMaterial({ map: PlayerTexture(6) }),
//     new Threejs.MeshStandardMaterial({ map: PlayerTexture(2) }),
//     new Threejs.MeshStandardMaterial({ map: PlayerTexture(5) }),
//     new Threejs.MeshStandardMaterial({ map: PlayerTexture(3) }),
//     new Threejs.MeshStandardMaterial({ map: PlayerTexture(4) }),
//   ];

//   //how big the player is ?
//   const playerWidth = 40;
//   const playerheight = 40;
//   const playerdDepth = 40;

//   //playerbody mesh which has both the size and material for the player
//   const playerBody = new Threejs.Mesh(
//     new Threejs.BoxGeometry(playerWidth, playerheight, playerdDepth),
//     materials,
//   );

//   //this is the rest position for the player
//   playerBody.position.z = 23;

//   //this allows the player to cast shadow in the scene
//   playerBody.castShadow = true;

//   //this allow to receive shadow from other object in the scene
//   playerBody.receiveShadow = true;

//   return playerBody;
// };

/*
bring in Threejs library
*/
import * as Threejs from "three";

// player function
export const Player = () => {
  //how big is the dice ?
  const width = 40;
  const height = 40;
  const depth = 40;

  /*
  set up dice sizes using our earlier properties, 
  box geometry goes perfectly with D6 with 6 faces
  */
  const diceSizes = new Threejs.BoxGeometry(width, height, depth);

  /*
  face material builder function that applies the same texture and properties for all the faces 
  also avoid repeating the same dice properties
  */
  //faceMaterial function
  const material = () =>
    //adding standard material becaue it comes with several properties of its own
    new Threejs.MeshStandardMaterial({
      //applying our custom texture here, so that it could be used in all the faces
      // map: PlayerTexture(num, "D6"),
      //similiarly, adding base color that could be used in all the faces
      color: 0x0a1850,
      //adding glow effect that could be used in all the faces
      emissive: 0x001a2a,
      //setting how much the glow should spread that could be used in all the faces
      emissiveIntensity: 0.4,
      //this gives a metalic look that could be used in all the faces
      metalness: 0.2,
      //this gives a reflective touch that could be used in all the faces
      roughness: 0.15,
      //this makes the face transparant like glass
      transparent: true,
      //this too add glass like look to the faces
      opacity: 1.0,
    });

  /*
  applies the player texture material to all the sides of the player
  we can choose any number for any side
  6 face materials — right, left, top, bottom, front, back
  */
  // const materials = [
  //   // right
  //   faceMaterial(1),
  //   // left
  //   faceMaterial(6),
  //   // top
  //   faceMaterial(2),
  //   // bottom
  //   faceMaterial(5),
  //   // front
  //   faceMaterial(3),
  //   // back
  //   faceMaterial(4),
  // ];

  //we create a mesh whihc contains both our dice sizes and texture materials
  const playerBody = new Threejs.Mesh(diceSizes, material());

  //default resting position of our dice
  playerBody.position.z = 23;

  //this allow our dice to cast shadow
  playerBody.castShadow = true;

  //this allow our object to recieve shdos of surrounding objects
  playerBody.receiveShadow = true;

  //glowing edges of the dice
  //detechs our edgeSizes by assigning to it our diceSizes
  const edgeSizes = new Threejs.EdgesGeometry(diceSizes);
  //creating basic texture of our edges
  const edgeTexture = new Threejs.LineBasicMaterial({
    //similiarly, adding base color our edges
    color: 0x00e5ff,
    //this makes the edges transparant like glass
    transparent: true,
    //this too add glass like look to the edges
    opacity: 0.9,
  });

  /*
  now we turn the edges and its properties to visible glowing line,
  this include adding both the edge sizes and  edge texture to line segments
  */
  const edgeLines = new Threejs.LineSegments(edgeSizes, edgeTexture);

  //finally we assign this edgeLines to our player body mesh
  playerBody.add(edgeLines);

  return playerBody;
};
//end Player
