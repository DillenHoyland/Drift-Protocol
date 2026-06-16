/*
bring the Threejs library 
*/
import * as Threejs from "three";

//our d20 func
export const D20 = () => {
  //this set the base size of the icosahedron
  const size = 25;

  //this create the 20 sided geometry and convert to non ndexed for per face coloring
  //create d20 shape
  const d20Shape = new Threejs.IcosahedronGeometry(size);

  //convert it to non indexed
  const niShape = d20Shape.toNonIndexed();

  // Step 3: Store it
  const geometry = niShape;

  //this dice has 20 sides, and each side is a triangle 3 sided shape
  const totalFaces = 20;

  //so when non indexed, each triangle face becomes 9 vertices, triangles per original face
  const vertsPerFace = 9;

  //this create the UV coordinates for each sub triangle, needed for texture
  //empty array to hold all the uv coordinates
  const uvs = [];

  // //first corner, which is the bottom left of the image
  //complete left
  const c1_U = 0;
  //complete bottom
  const c1_V = 0;
  //second corner, which is the bottom right of the image
  //complete right
  const c2_U = 1;
  //complete bottom
  const c2_V = 0;
  //third corner, which is the top middle of the image
  //in the middle
  const c3_U = 0.5;
  //complete top
  const c3_V = 1;

  //d20 has 20 traingle faces,
  //each triangle needs 3 uv coordinates,
  //each face has 6 numbers in total

  //loop through all 20 faces
  for (let tFace = 0; tFace < 20; tFace++) {
    //single traingle
    //bottom left
    uvs.push(c1_U, c1_V);
    //bottom right
    uvs.push(c2_U, c2_V);
    //top middle
    uvs.push(c3_U, c3_V);
  }

  //this attach the uv coordinates to the geometry
  geometry.setAttribute(
    "uv",
    new Threejs.BufferAttribute(new Float32Array(uvs), 2),
  );

  //this clear any existing material groups
  geometry.clearGroups();

  //it assign each sub face 9 vertices to its own material group
  for (let tFace = 0; tFace < 20; tFace++) {
    //which vertex does the current face start at ?
    // Each face uses 9 vertices, we jump by 9 each time
    const sVertex = tFace * 9;

    //how many vertices in the current face ?
    //each pentagon face has 9 vertices, 3 triangles × 3 vertices each
    const tVertices = 9;

    //which material should this current face use ?
    const mNumber = tFace;

    // Apply the particular material for that particular face
    geometry.addGroup(sVertex, tVertices, mNumber);
  }

  //helper func for standard sci-fi material, same as other dice
  const faceMaterial = () =>
    new Threejs.MeshStandardMaterial({
      //deep navy blue
      color: 0x0a1850,
      //dark blue glow
      emissive: 0x001a2a,
      //this is low glow
      emissiveIntensity: 0.4,
      //slightly metallic
      metalness: 0.2,
      //this is pretty shiny
      roughness: 0.15,
      //this allow transparency
      transparent: true,
      //its fully opaque
      opacity: 1.0,
    });

  //gives each face its own material
  const materials = Array.from({ length: totalFaces }, () => faceMaterial());
  //this then create the main dice mesh with geometry and materials
  const dice = new Threejs.Mesh(geometry, materials);

  //this position the D20 in its designated spot on the table
  dice.position.set(-70, 70, 23);
  //this enable the dice to cast shadows onto the table
  dice.castShadow = true;
  //this enable the die to receive shadows from other objects
  dice.receiveShadow = true;

  //it adds glowing cyan edge lines to highlight the dice shape
  dice.add(
    new Threejs.LineSegments(
      new Threejs.EdgesGeometry(geometry),
      new Threejs.LineBasicMaterial({
        //cyan/neon blue color
        color: 0x00e5ff,
        //this Allow transparency
        transparent: true,
        //this is slightly transparent for glow effect
        opacity: 0.9,
      }),
    ),
  );

  //it return the complete d20 mesh ready to be added to the scene
  return dice;
};
