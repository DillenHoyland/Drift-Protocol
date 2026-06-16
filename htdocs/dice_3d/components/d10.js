/*
bring the Threejs library 
*/
import * as Threejs from "three";

//this is our d10 func
export const D10 = () => {
  //this set the base size of the dice
  const size = 30;

  //this calculate the z height of the middle ring like potion, where the two pyramids meet
  const ringZ = size * 0.1;
  //this calculate the radius of the middle ring
  const ringR = size * 0.9;
  //this calculates the top point z position to make proper pyramid shape

  //this is the angle of pentagon corner, 36 deg
  const pCAngle = Math.PI / 5;
  //the cos tells us how slanted the sides are
  const slanted = Math.cos(pCAngle);
  //top part of the fraction
  const oSlanted = 1 + slanted;
  //bottom part of the fraction
  const omSlanted = 1 - slanted;
  //multiply the ring height by the top
  const tptHeight = ringZ * oSlanted;
  //finally, divide to get the perfect top height
  const ptHeight = tptHeight / omSlanted;

  //this store all vertex positions x, y, z
  const vertices = [];
  //this store all traingle indices positions, which verticles from which face
  const indices = [];

  //this add the top pole vertex
  vertices.push(0, 0, ptHeight);

  //it create the 5 vertices or points that go around the middle
  for (let pNum = 0; pNum < 5; pNum++) {
    //which slice are we on
    const sSize = (Math.PI * 2) / 5;
    //angle on the circle
    const aoCircle = pNum * sSize;
    //gets the x and y position on the circle
    const xPos = ringR * Math.cos(aoCircle);
    const yPos = ringR * Math.sin(aoCircle);

    //lower ring is below the middle, so negative z
    const zPos = ringZ;

    vertices.push(xPos, yPos, zPos);
  }

  //it create the 5 vertices or point that go around the middle
  for (let pNum = 0; pNum < 5; pNum++) {
    //// Shift the bottom ring by half a step like zig zag
    const hSlice = Math.PI / 10;
    //this figures the angle, starting offset by half a slice
    const sSize = (Math.PI * 2) / 5;
    //angle on the circle
    const aoCircle = pNum * sSize + hSlice;
    //gets the x and y position on teh circle
    const xPos = ringR * Math.cos(aoCircle);
    const yPos = ringR * Math.sin(aoCircle);

    //lower ring is below the middle, so negative z
    const zPos = -ringZ;

    vertices.push(xPos, yPos, zPos);
  }

  //it add the bottom pole vertex hence negative
  vertices.push(0, 0, -ptHeight); //

  //so we need to connect the top pole to the rings to make 5 triangle faces
  //here each face is actually made of 2 triangles, like a kite.

  //get 3 points for the first traingle
  for (let tFace = 0; tFace < 5; tFace++) {
    //we find the 3 poinst for the first traingle
    //point a is the top pole, always 0
    const tPole = 0;

    //point b is a point on the upper ring
    const urPoint = 1 + tFace;

    //point c is a point on the lower ring
    const lrPoint = 6 + tFace;

    //now make the traingle
    indices.push(tPole, urPoint, lrPoint);

    //get the 3 points for the second traingle
    //point a is still the top pole, always 0
    const stPole = 0;

    //point b is the same lowering point user earlier
    const slrPoint = 6 + tFace;

    //point c  is in the next uppe ring point
    const nurPoint = 1 + ((tFace + 1) % 5);

    //create traingle 2
    indices.push(stPole, slrPoint, nurPoint);
  }

  //now we connect the bottom pole to the rings to make 5 more faces
  //same idea, but upside down
  for (let tFace = 0; tFace < 5; tFace++) {
    //we find the 3 poinst for the first traingle
    //point a is the top pole, always 11
    const bPole = 11;

    //point b is a  on the next lower ring
    const nlrPoint = 6 + ((tFace + 1) % 5);

    //point c is in the next upper ring
    const nurPoint = 1 + ((tFace + 1) % 5);

    //create first traingle
    indices.push(bPole, nlrPoint, nurPoint);

    //get the 3 points for the second traingle
    //point a is still the top pole, always 0
    const sbPole = 11;

    //point b is the same lowering point user earlier
    const snurPoint = 1 + ((tFace + 1) % 5);

    //point c  is in the next upper ring point
    const clrPoint = 6 + tFace;

    //create traingle 2
    indices.push(sbPole, snurPoint, clrPoint);
  }

  //this create the geometry from vertices and indices earlier
  //create an empty geometry container
  const geometry = new Threejs.BufferGeometry();

  //we need to put our list of points or vertices into the geometry
  //convert the vertices array to a format understand by Threejs
  const vArray = new Float32Array(vertices);

  //adds position attribute from our vertices or points
  //list of numbers
  const loNum = vArray;

  //every 3 numbers make 1 point
  const npPoint = 3;

  //a container that understand this rule
  const pAttribute = new Threejs.BufferAttribute(loNum, npPoint);

  //attch the position data to our geometry
  geometry.setAttribute("position", pAttribute);

  geometry.setIndex(indices);

  //this ensures to calculate normals for proper lighting
  geometry.computeVertexNormals();

  //this convert to non-indexed so each face can have its own material
  const nonIndexed = geometry.toNonIndexed();

  //this create uv coordinates for each faces
  //an empty array to hold all the uvs coordinators
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

  //in d10 each face is made of 2 triangles, 6 vertices total
  //each triangle needs 3 uv coordinates that makes it total 6 uvs for a single face

  for (let tFace = 0; tFace < 10; tFace++) {
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
  }

  //this attach the uv coordinates to the geometry
  nonIndexed.setAttribute(
    "uv",
    new Threejs.BufferAttribute(new Float32Array(uvs), 2),
  );

  //this ensures it clear and assign material groups, one group per face
  nonIndexed.clearGroups();

  //assign each face its own material
  for (let tFace = 0; tFace < 10; tFace++) {
    //which vertex this face start at ?
    const sVertex = tFace * 6;

    //howmany vertices in the current face ?
    const tVertices = 6;

    //which material the current face should use ?
    const mNumber = tFace;

    nonIndexed.addGroup(sVertex, tVertices, mNumber);
  }

  //this find which face is the bottom so we can make it sit flat
  const pos = nonIndexed.attributes.position;

  //this starts with the highest possible value
  let lowestZ = Infinity;

  //the lowest face
  let bottomFaceIdx = 5;

  //check faces 5 through 9
  for (let tFace = 5; tFace < 10; tFace++) {
    //1 face has 6 vertices, because  there is 2 triangles stuck together
    //we Find all 6 corners of this face

    //corner 1
    const c1Index = tFace * 6;
    //corner 2
    const c2Index = tFace * 6 + 1;
    //corner 3
    const c3Index = tFace * 6 + 2;
    //corner 4
    const c4Index = tFace * 6 + 3;
    //corner 5
    const c5Index = tFace * 6 + 4;
    //corner 6
    const c6Index = tFace * 6 + 5;

    //now get the z height of each corner
    const c1Height = pos.getZ(c1Index);
    const c2Height = pos.getZ(c2Index);
    const c3Height = pos.getZ(c3Index);
    const c4Height = pos.getZ(c4Index);
    const c5Height = pos.getZ(c5Index);
    const c6Height = pos.getZ(c6Index);

    //add all the heights together
    const soHeight =
      c1Height + c2Height + c3Height + c4Height + c5Height + c6Height;

    //getting average height by dividing it with 6
    const aHeight = soHeight / 6;

    //if this is the lowest face ? if yes
    if (aHeight < lowestZ) {
      //then this is the lowest face
      lowestZ = aHeight;
      bottomFaceIdx = tFace;
    }
  }

  //it get three vertices of the bottom face we found earlier
  //tells where does the point line in the position array

  const cA_Index = bottomFaceIdx * 6;
  //this is an empty vector
  const cA_Empty = new Threejs.Vector3();
  //fill the position data from our geometry
  const cA = cA_Empty.fromBufferAttribute(pos, cA_Index);

  //tells where does the point line in the position array
  const cB_Index = bottomFaceIdx * 6 + 1;
  //this is an empty vector
  const cB_Empty = new Threejs.Vector3();
  //fill the position data from our geometry
  const cB = cB_Empty.fromBufferAttribute(pos, cB_Index);

  //tells where does the point line in the position array
  const cC_Index = bottomFaceIdx * 6 + 2;
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

  //puts the rotate instruction from quaternion to the empty matrix
  const rMatrix = eMatrix.makeRotationFromQuaternion(quat);

  //finally apply that to the entire geometry
  nonIndexed.applyMatrix4(rMatrix);

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

  //this creates 10 materials , one per face
  const materials = Array.from({ length: 10 }, () => faceMaterial());
  //it create the final dice mesh
  const dice = new Threejs.Mesh(nonIndexed, materials);

  //this position the dice so it rests exactly on the table
  dice.updateMatrixWorld(true);
  const box = new Threejs.Box3().setFromObject(dice);
  dice.position.set(0, 80, 3.1 - box.min.z);

  //this store initial rotation for animation reference
  dice.userData.baseQuaternion = new Threejs.Quaternion();
  //this enable the die to cast shadows onto the table
  dice.castShadow = true;
  //this enable the die to receive shadows from other objects
  dice.receiveShadow = true;

  //this adds glowing cyan edge lines
  dice.add(
    new Threejs.LineSegments(
      new Threejs.EdgesGeometry(nonIndexed),
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

  //finally, return the complete d10 mesh
  return dice;
};
