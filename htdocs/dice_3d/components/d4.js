/*
bring the Threejs library 
*/
import * as Threejs from "three";

//this is our d4 func
export const D4 = () => {
  //this is the size of our tetrahedron
  const size = 40;

  //this create the pyramid like geometry and convert to non-indexed
  const geometry = new Threejs.TetrahedronGeometry(size).toNonIndexed();

  //this calculate the normal vector of one face to use for the earlier rotation
  const faceNormal = new Threejs.Vector3(-1, -1, -1).normalize();
  //this define which direction is down (flat on the floor)
  const down = new Threejs.Vector3(0, 0, -1);

  //this create a rotation that makes one face flat downward on the floor
  //creates an empty rotation intruction
  const eQuat = new Threejs.Quaternion();

  //then we fill it with instructiion like rotate from this face direction to pointing down
  const fQuat = eQuat.setFromUnitVectors(faceNormal, down);

  //lastly, store it in a variable
  const quat = fQuat;

  //this adds rotation to the geometry so it sits flat
  //creates an empty matrix
  const eMatrix = new Threejs.Matrix4();

  //now fill the matrix with our rotaionmakeRotationFromQuaternion(quat)
  const rMatrix = eMatrix.makeRotationFromQuaternion(quat);

  //finally apply teh rotatiion to the whole geometry
  geometry.applyMatrix4(rMatrix);

  //this creates UV coordinates for each face, needed for textures
  //so each traingle face needs 3 points

  //an empty array to hold all the uvs coordinators
  const uvs = [];

  //first corne, which is the bottom left of the image
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

  //this loops 4 times and add UV coordinates
  for (let tFace = 0; tFace < 4; tFace++) {
    //apply the uv coordinates for one facce of the traingle
    uvs.push(c1_U, c1_V, c2_U, c2_V, c3_U, c3_V);
  }
  //this attach the UV coordinates to the geometry
  geometry.setAttribute(
    "uv",
    new Threejs.BufferAttribute(new Float32Array(uvs), 2),
  );

  //ensures clear any existing material groups
  geometry.clearGroups();

  //assigns each face to its own material group
  for (let tFace = 0; tFace < 4; tFace++) {
    //which vertex this face start at ?
    const sVertex = tFace * 3;
    //how many vertices in teh current face ?
    const tVertices = 3;
    //which material the current face should use ?
    const mNumber = tFace;

    //this apply the particular material for that particular face
    geometry.addGroup(sVertex, tVertices, mNumber);
  }

  //this is the helper function that creates the standard sci-fi material for each face
  const faceMaterial = () =>
    new Threejs.MeshStandardMaterial({
      //deep navy blue base color
      color: 0x0a1850,
      //slightly dark blue glow
      emissive: 0x001a2a,
      //low glow intensity
      emissiveIntensity: 0.4,
      //it is metallic
      metalness: 0.2,
      //it is shiny
      roughness: 0.15,
      //this is  transparency
      transparent: true,
      //fully opaque
      opacity: 1.0,
    });

  //array containing 4 materials  for each faces
  const materials = [
    faceMaterial(),
    faceMaterial(),
    faceMaterial(),
    faceMaterial(),
  ];

  //this is the main dice mesh with geometry and materials
  const dice = new Threejs.Mesh(geometry, materials);

  //this calculate the bounding box to position the die correctly
  dice.updateMatrixWorld(true);

  const box = new Threejs.Box3().setFromObject(dice);

  //sets the dice position, so it rests exactly on the floor surface
  //where is the bottom of the dice ?
  const dBottom = box.min.z;

  //we want the bottom to touch the flooor which is at z axis 0
  const dlift = -dBottom;

  //position the dice at the folling x, y and z coordinates
  const xPos = 80;
  const yPos = 0;
  const zPos = dlift;

  //apply the positions
  dice.position.set(xPos, yPos, zPos);

  //this enable the dice to cast shadows onto the floor
  dice.castShadow = true;
  //this enable the dice to receive shadows from other objects
  dice.receiveShadow = true;

  //this create edge geometry to highlight the dice edges
  const edges = new Threejs.EdgesGeometry(geometry);
  //gives a glowing cyan material for the edges
  const edgeMaterial = new Threejs.LineBasicMaterial({
    //cyan/neon blue color
    color: 0x00e5ff,
    //this is transparency
    transparent: true,
    //Ssightly transparent for glow effect
    opacity: 0.9,
  });

  //this combine edges and material into visible edge lines
  const edgeLines = new Threejs.LineSegments(edges, edgeMaterial);
  //this attach the edge lines to the dice so they move together
  dice.add(edgeLines);

  //finally, return the complete d4 mesh ready to be added to the scene
  return dice;
};
