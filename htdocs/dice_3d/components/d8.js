/*
bring the Threejs library 
*/
import * as Threejs from "three";

//thia is out d8 func
export const D8 = () => {
  //this set the base size of the octahedron
  const size = 30;
  //it create the 8 sided geometry and convert to non indexed for per face coloring
  const geometry = new Threejs.OctahedronGeometry(size).toNonIndexed();

  //this create UV coordinates for each face, needed for textures
  //an empty array to hold all the uvs coordinators
  const uvs = [];

  //first corner, which is the bottom left of the image
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
  for (let tFace = 0; tFace < 8; tFace++) {
    //apply the uv coordinates for one face of the traingle
    uvs.push(c1_U, c1_V, c2_U, c2_V, c3_U, c3_V);
  }

  //this attach the uv coordinates to the geometry
  geometry.setAttribute(
    "uv",
    new Threejs.BufferAttribute(new Float32Array(uvs), 2),
  );

  //it clear any existing material groups
  geometry.clearGroups();

  //assigns each face to its own material group
  for (let tFace = 0; tFace < 8; tFace++) {
    //which vertex this face start at ?
    const sVertex = tFace * 3;
    //how many vertices in teh current face ?
    const tVertices = 3;
    //which material the current face should use ?
    const mNumber = tFace;

    //this apply the particular material for that particular face
    geometry.addGroup(sVertex, tVertices, mNumber);
  }

  //this find which face is the bottom so we can make it sit flat on the floor
  const pos = geometry.attributes.position;

  //this starts with the highest possible value
  let lowestZ = Infinity;

  //the lowest face
  let bottomFaceIdx = 0;

  //it Check all 8 faces to find the one with lowest z average, bottom face
  for (let tFace = 0; tFace < 8; tFace++) {
    //find the corner of the faces
    //first corner
    const fCornerIndex = tFace * 3;
    //second corner
    const sCornerIndex = tFace * 3 + 1;
    //third corner
    const tCornerIndex = tFace * 3 + 2;

    //get the z position of each corner
    const fCornerHeight = pos.getZ(fCornerIndex);
    const sCornerHeight = pos.getZ(sCornerIndex);
    const tCornerHeight = pos.getZ(tCornerIndex);

    //now add them
    const sHeight = fCornerHeight + sCornerHeight + tCornerHeight;

    //now divide it by 3 to get the avg height
    const aHeight = sHeight / 3;

    //is this the lowest face we have seen so far ?
    if (aHeight < lowestZ) {
      //yes! this is the current lowest height
      lowestZ = aHeight;
      bottomFaceIdx = tFace;
    }
  }

  //this gets the three vertices of the earlier bottom face
  //tells where does the point live in the position array
  const cA_Index = bottomFaceIdx * 3;
  //this is an empty vector
  const cA_Empty = new Threejs.Vector3();
  //fill the position data from our geometry
  const cA = cA_Empty.fromBufferAttribute(pos, cA_Index);

  //tells where does the point line in the position array
  const cB_Index = bottomFaceIdx * 3 + 1;
  //this is an empty vector
  const cB_Empty = new Threejs.Vector3();
  //fill the position data from our geometry
  const cB = cB_Empty.fromBufferAttribute(pos, cB_Index);

  //tells where does the point line in the position array
  const cC_Index = bottomFaceIdx * 3 + 2;
  //this is an empty vector
  const cC_Empty = new Threejs.Vector3();
  //fill the position data from our geometry
  const cC = cC_Empty.fromBufferAttribute(pos, cC_Index);

  //this calculate the normal vector, which way the face is pointing
  const faceNormal = new Threejs.Vector3()
    .crossVectors(
      new Threejs.Vector3().subVectors(cB, cA),
      new Threejs.Vector3().subVectors(cC, cA),
    )
    .normalize();

  //the face normal tells us whhich direction face is pointing
  //somtimes its pointing up instead of down

  //if the face is pointing upward
  if (faceNormal.z > 0) {
    //the flip it so if face downwards
    faceNormal.negate();
  }

  //this point the face straight down
  const sDown = new Threejs.Vector3(0, 0, -1);

  //create an empty rotate instruction
  const eQuat = new Threejs.Quaternion();

  //fill it with instruction like rotate from the current direction to straight down
  const fQuat = eQuat.setFromUnitVectors(faceNormal, sDown);

  //storing it
  const quat = fQuat;

  //this apply the rotation so the dice sits flat on the floor
  //this creates an empty transformatio matrix;
  const eMatrix = new Threejs.Matrix4();

  //fill the matrix with the rotation from quaternion
  const rMatrix = eMatrix.makeRotationFromQuaternion(quat);

  //finally apply that to the entire geometry
  geometry.applyMatrix4(rMatrix);

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

  //this create an array of 8 materials, one for each face
  const materials = Array.from({ length: 8 }, () => faceMaterial());

  //this create the main dice mesh with geometry and materials
  const dice = new Threejs.Mesh(geometry, materials);

  //calculate the bounding box to position the dice correctly
  dice.updateMatrixWorld(true);
  const box = new Threejs.Box3().setFromObject(dice);

  //this then position the die so it rests exactly on the floor surface
  dice.position.set(-80, 0, -box.min.z);

  //it store initial rotation for animation reference
  dice.userData.baseQuaternion = new Threejs.Quaternion();

  //this enable the die to cast shadows onto the floor
  dice.castShadow = true;
  //this enable the die to receive shadows from other objects
  dice.receiveShadow = true;

  //it create thr edge geometry to highlight the dice edges
  const edges = new Threejs.EdgesGeometry(geometry);
  //this create the glowing cyan material for the edges
  const edgeMaterial = new Threejs.LineBasicMaterial({
    //cyan/neon blue color
    color: 0x00e5ff,
    //this Allow transparency
    transparent: true,
    //this is slightly transparent for glow effect
    opacity: 0.9,
  });

  //this add the glowing edge lines to the die
  dice.add(new Threejs.LineSegments(edges, edgeMaterial));

  //finally, return the complete d8 mesh ready to be added to the scence
  return dice;
};
