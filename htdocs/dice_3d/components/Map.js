/*
bring the Threejs library 
*/
import * as Threejs from "three";
/*
bring up the floor function as well 
*/
import { Floor } from "./Floor.js";
/*
bring up the constant values as well 
*/
import { totalTilePerRow, tileSize, startTile, endTile } from "../constants.js";

//this create a group to hold all map elements, floor tiles and shadow plane
export const map = new Threejs.Group();
//this function builds the entire floor map with tiles and shadow receiver
export const setupMap = () => {
  //it loop through each row of tiles from startTile to endTile
  for (let rowNumber = startTile; rowNumber <= endTile; rowNumber++) {
    //it create a floor tile row at this position
    const floor = Floor(rowNumber);
    //this adds the row of tiles to the map group
    map.add(floor);
  }

  //separate shadow receiving plane needed as MeshBasicMaterial can't receive shadows
  //a transparent shadowMaterial plane sits just above the floor surface
  //this calculate total number of rows,9 rows: -4 to +4
  const totalRows = endTile - startTile + 1;
  //this calculate total width of the floor
  const floorWidth = totalTilePerRow * tileSize;
  //this calculate total depth of the floor
  const floorDepth = totalRows * tileSize;
  //this calculate the center Y position of the floor
  const floorCenterY = ((startTile + endTile) / 2) * tileSize;

  //it create an invisible plane that will receive shadows
  const shadowPlane = new Threejs.Mesh(
    //it cover entire floor area
    new Threejs.PlaneGeometry(floorWidth, floorDepth),
    //it is a semi-transparent shadow material
    new Threejs.ShadowMaterial({ opacity: 0.5 }),
  );

  //we position the shadow plane at the center of the floor, slightly above the tiles
  //the tiles have top surface at z=3, so place shadow plane at z=3.1 to avoid flickering or overlapping
  shadowPlane.position.set(0, floorCenterY, 3.1);
  //it enable this plane to receive shadows from dice
  shadowPlane.receiveShadow = true;
  //this adds the shadow receiving plane to the map group
  map.add(shadowPlane);
};
