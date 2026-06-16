/*
bring the Threejs library 
*/
import * as Threejs from "three";

//this is our d12 func
export const D12 = () => {
  //this set the base size of the dodecahedron
  const size = 30;
  //it create the 12 sided geometry and convert to non ndexed for per face coloring
  //create d12 shape
  const d12Shape = new Threejs.DodecahedronGeometry(size);

  //convert it to non indexed
  const niShape = d12Shape.toNonIndexed();

  //store it
  const geometry = niShape;

  //this dice has 12 sides, and each side is a pentagon 5 sided shape
  const totalFaces = 12;

  // When non indexed, each pentagon becomes 9 vertices, 3 triangles per face
  const vertsPerFace = 9;

  //this create uv coordinates for each sub-triangle, needed for textures
  //an empty array to hold the coordinates
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

  //d10 has 12 pantagon faces, each pentagon is made from 3 triangles,
  //each triangle needs 3 uv coordinates,
  //3 triangles * 6 uv numbers = 18 numbers per face

  for (let tFace = 0; tFace < 12; tFace++) {
    //first traingle
    //bottom left
    uvs.push(c1_U, c1_V);
    //bottom right
    uvs.push(c2_U, c2_V);
    //top middle
    uvs.push(c3_U, c3_V);

    //second traingle
    //bottom left
    uvs.push(c1_U, c1_V);
    //bottom right
    uvs.push(c2_U, c2_V);
    //top middle
    uvs.push(c3_U, c3_V);

    //tthird traingle
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

  //this assign each sub face 9 vertices to its own material group
  for (let tFace = 0; tFace < totalFaces; tFace++) {
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
  //this create the main dice mesh with geometry and materials
  const dice = new Threejs.Mesh(geometry, materials);

  //this calculate the bounding box to position the die correctly
  dice.updateMatrixWorld(true);
  const box = new Threejs.Box3().setFromObject(dice);
  //this Position the d12 in its designated spot on the floor bottom center
  dice.position.set(0, -80, 3.1 - box.min.z);

  //it enable the dice to cast shadows onto the floor
  dice.castShadow = true;
  //it enable the die to receive shadows from other objects
  dice.receiveShadow = true;

  //this adds glowing cyan edge lines
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

  //finally, it return the complete d12 mesh ready to be added to the scene
  return dice;
};
