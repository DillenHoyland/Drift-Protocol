/*
bring the Threejs library 
*/
import * as Threejs from "three";
/*
other necessary files for the floor function
*/
import { totalTilePerRow, tileSize } from "../constants.js";

//we create a custum curcuit texture using canvas rather then using a image
//circuit texture function
const circuitTexture = () => {
  //size of the texture
  const tSize = 1024;

  //applying the size to the html canvas to create the texture
  const tCanvas = document.createElement("canvas");
  tCanvas.width = tSize;
  tCanvas.height = tSize;

  //adding bruch to paint the texture in the canvas
  const tBrush = tCanvas.getContext("2d");

  //choosing color and size for painting the background for the texture
  //first layer of teh texture
  tBrush.fillStyle = "#000d00";
  //fill the whole texture size canvas
  tBrush.fillRect(0, 0, tSize, tSize);

  //now adding a gSize layer on top of it with the chosen color
  //second layer
  tBrush.strokeStyle = "rgba(0,255,60,0.07)";
  //adjusting the width of the stroke
  tBrush.lineWidth = 1;

  //value for the per pixel for the grid
  const gSize = 32;

  /*using the grid per pixel to outline the grid layer
  first, loops through the texture size*/
  for (let i = 0; i < tSize; i += gSize) {
    //start a new line
    tBrush.beginPath();
    //saying move to the top most pixel of the texture size
    tBrush.moveTo(i, 0);
    //start drawing line from the top to the bottom pixel of the texture size
    tBrush.lineTo(i, tSize);
    //render it on canvas creates a vertical line
    tBrush.stroke();

    //similiarly, start a new line
    tBrush.beginPath();
    //saying move to the top most pixel of the texture size
    tBrush.moveTo(0, i);
    //start drawing line from the top to the bottom pixel of the texture size
    tBrush.lineTo(tSize, i);
    //render it on canvas creates a horizontal line
    tBrush.stroke();
  }

  //this is the another layer which has the circuit traces and glow effects to make the look complete
  //generates random number
  const randomGen = (num) => Math.floor(Math.random() * num);

  /*so now we can use the random number to generate traces randomly accross the texture
  here t stand for trace, so we loop through the grid 130 times so that its filled with traces over it*/
  for (let t = 0; t < 130; t++) {
    /*so the tSize / gSize give us the specific number of that grid cell 
    and multiplying it to the gSize gives us 32 different cells on both axis horizontally and vertically*/
    let x = randomGen(tSize / gSize) * gSize;
    let y = randomGen(tSize / gSize) * gSize;

    //adding to it texture center glow look
    /*knowing how far the point is from the center horizontally and vertically,
    so in order to know the distance from center we substract the the random generated number 
    from the center which is tSize / 2 
    */

    //center of canvas
    const xCenter = tSize / 2;
    const yCenter = tSize / 2;

    //know the dixtance of x and y from teh center
    const farFromC = Math.hypot(x - xCenter, y - yCenter);

    //how far the center glow should scale
    const glowFade = tSize * 0.6;

    //the normal glow distance from the center
    const normalGlowDis = farFromC / glowFade;

    //glow settings
    //max glow opacity
    const glowMax = 0.5;

    //glow opacity reduction over distance
    const glowReduce = normalGlowDis;

    //subtract the glow distance change with the max opacity
    const rawGlow = glowMax - glowReduce;

    //set the minimum value for the glow
    const glow = Math.max(0.05, rawGlow);

    //making sure the strokes uses the random and glow by distant effecct
    tBrush.strokeStyle = `rgba(0,255,70,${glow})`;

    //this new random traces have thicker lines and glowing variation by distance
    tBrush.lineWidth = 2.5;

    //ready to start a new line
    tBrush.beginPath();
    //move the random starting point to x and y axis of the texture size
    tBrush.moveTo(x, y);

    //creating sevreal versions of traces some longer other shorter;
    //min versions
    const minVersion = 2;

    //extra versions
    const extraVersion = 5;

    //generate random extra variations
    const extraRandom = randomGen(extraVersion);

    //total number of variations
    const variation = minVersion + extraRandom;

    //goes throught the loop to gie us any random 4 numbers//
    for (let v = 0; v < variation; v++) {
      //any random 4 numbers
      const randomDir = randomGen(4);
      //any random length
      const randomlen = (1 + randomGen(4)) * gSize;

      //applying random drections//
      //if randomDir == 0 then add, which is move right in horizontal axis
      if (randomDir === 0) x += randomlen;
      //if randomDir == 1 then subtract. which is move left in horizontal axis
      else if (randomDir === 1) x -= randomlen;
      //if randomDir == 2 then add, which is move down in vertical axis
      else if (randomDir === 2) y += randomlen;
      //if randomDir == 3 then subtract, which is move up in vertical axis
      else y -= randomlen;

      //this makes ure that the random updated direction dont go out of the canvas horizontally
      x = Math.max(0, Math.min(tSize, x));
      //this makes ure that the random updated direction dont go out of the canvas vertically
      y = Math.max(0, Math.min(tSize, y));

      //start drawing line from the current ot the update positions
      tBrush.lineTo(x, y);
    }
    //render it on canvas creates the traces with glow for the texture
    tBrush.stroke();

    //glow dot
    //circle radius
    const cirRadius = 4;
    //start a new shape
    tBrush.beginPath();
    //drawing a new circle from the current traces x and y positions as the starting point
    tBrush.arc(x, y, cirRadius, 0, Math.PI * 2);
    //color the circle using the earlier glow
    tBrush.fillStyle = `rgba(0,255,70,${glow * 0.9})`;
    //paint the circle
    tBrush.fill();

    //outer ring
    //make the rings a bit bigger than the circle
    const ringRadius = 10;
    //similiarly, start a new shape
    tBrush.beginPath();
    //drawing a rings from the current traces x and y positions as the starting point
    tBrush.arc(x, y, ringRadius, 0, Math.PI * 2);
    //color the ring using the earlier glow
    tBrush.strokeStyle = `rgba(0,255,70,${glow * 0.8})`;
    //adjust the width of the ring
    tBrush.lineWidth = 2.2;
    //render teh ring in the canvas
    tBrush.stroke();
  }

  //floor circle gradiant, which is the final layer
  //circle gradiant radius
  const cirGradiantRadius = 0;
  //circle gradiant radius
  const outerCirGradiantRadius = 0.38;
  //circle gradiant radius
  const cirGradiant = tBrush.createRadialGradient(
    //inner circle properties:
    //horizontal center of the inner circle
    tSize / 2,
    //vertically center of the inner circle
    tSize / 2,
    //inner circle radius, make it start from absolute point
    cirGradiantRadius,

    //outer circle properties:
    //horizontal center of the outer circle
    tSize / 2,
    //vertical center of the outer circle
    tSize / 2,
    //outer circle - gradiant fades out at 38% of the canvas
    tSize * outerCirGradiantRadius,
  );

  //at 0 position set the color to
  cirGradiant.addColorStop(0, "rgba(0,255,80,0.18)");
  //at 1 position set thh color to which is transparent
  cirGradiant.addColorStop(1, "rgba(0,0,0,0)");

  //now use the above gradiant as fill color instead of a solid color
  tBrush.fillStyle = cirGradiant;

  //paint the entire canvas with that earlier fill color
  tBrush.fillRect(0, 0, tSize, tSize);

  //return everything all the layers background, grid, traces, dots, rings, glow as a custom texture
  return new Threejs.CanvasTexture(tCanvas);
};
//custom circuit texture end

//setting up our base texture using the circuit texture
const baseTexture = circuitTexture();
//floor function which creates a one row for the floor
export const Floor = (rowNumber) => {
  //this creates a container for the floor to hold the objects
  const rowGroup = new Threejs.Group();
  //move this row based on the number to avoid overlaping
  let moveY = rowNumber * tileSize;
  rowGroup.position.y = moveY;
  //total width of the rowGroup
  let rowWidth = totalTilePerRow * tileSize;

  //each row gets its own texture reference with a unique vertical slice
  //this makes the 9 rows together show ONE unified circuit board

  //firstly we clone our base texture, this ensure customizing this row wont effect the other rows
  const circuit01 = baseTexture.clone();
  // always refresh the texture
  circuit01.needsUpdate = true;
  //properly wrap around the x axis horizontally
  circuit01.wrapS = Threejs.RepeatWrapping;
  //properly wrap around the y axis vertically
  circuit01.wrapT = Threejs.RepeatWrapping;

  // rows go from -4 to 4 = 9 total
  //total rows we have -4 to +4
  const allRows = 9;

  /*
  change the row scale to row index 0–8 (rowNumber -4 to 4, rowNumber 0 to 8)
  this was done because we can use texture calculation without negative numbers
  */
  const rowIndex = rowNumber + 4;

  /*
  since we are working with just a single row group 
  we need to make sure to show up only the texture for current specific row from the row group
  */
  //1 is full width and 1 / allRows is only one row's portion of the height
  circuit01.repeat.set(1, 1 / allRows);
  //so we need each row to show it own slice of the texture to avoid identical textures
  circuit01.offset.set(0, rowIndex / allRows);

  //this is the mesh, which contains the texture and the sahpe for the floor
  const floorBase = new Threejs.Mesh(
    //this creates the shape for the floor
    new Threejs.BoxGeometry(rowWidth, tileSize, 3),
    //this creates the material and color for the floor by mapping our circuit texture
    new Threejs.MeshBasicMaterial({ map: circuit01 }),
  );

  //correctly placing mesh height, half the height of the floor depth which is 3
  let halfheight = 3 / 2;
  floorBase.position.z = halfheight;

  //to allow shadows on the floor
  floorBase.receiveShadow = true;

  //add the mesh to the group, so that it can be reuse
  rowGroup.add(floorBase);

  return rowGroup;
};

//the floor function
// export const Floor = (rowNumber) => {
//   //this creates a container for the floor
//   const rowGroup = new Threejs.Group();

//   //move this row based on the number to avoid overlaping
//   let moveY = rowNumber * tileSize;
//   rowGroup.position.y = moveY;

//   //total width of the rowGroup
//   let rowWidth = totalTilePerRow * tileSize;

//   //floor material color
//   let floorColor = 0xbaf455;

//   //this is the mesh, which contains the texture and the sahpe for the floor
//   const floorBase = new Threejs.Mesh(
//     //this creates the shape for the floor
//     new Threejs.BoxGeometry(rowWidth, tileSize, 3),
//     //this creates the material and color for the floor
//     new Threejs.MeshLambertMaterial({
//       color: floorColor ? floorColor : 0xffffff,
//     }),
//   );

//   //correctly placing mesh height, half the height of the floor depth which is 3
//   let halfheight = 3 / 2;
//   floorBase.position.z = halfheight;

//   //to allow shadows on the floor
//   floorBase.receiveShadow = true;

//   //add the mesh to the group, so that it can be reuse
//   rowGroup.add(floorBase);

//   return rowGroup;
// };
