// imports for graph visualisations
import * as d3 from "https://cdn.jsdelivr.net/npm/d3@7/+esm";
import LineChart from "./linechart.js";

// Set tooltip array
 let ttArray = [];

// *** ONLOAD:

// onload of DOM, get initial data from backend and populate HTML elements
document.addEventListener("DOMContentLoaded", async function () {
  // renderPage with null means no "choice" made by user; on backend, this loads their session status from DB and sends it back
  renderPage(null);
});

// global variable for blocking tooltips when dicerolls are playing; initialised to false
var tooltipBlocked = false;

// ***** HANDLING ERRORS FROM BACKEND:
function errorHandler($errorData) {
  // display to user
  let errorBar = document.querySelector("#errorBar");
  let errorBarCell = document.querySelector("#errorBar > td");
  let errorBarText = document.querySelector("#errorBarText");
  errorBarText.innerHTML = $errorData;
  errorBar.style.display = "table-row";
  errorBarCell.style.display = "table-cell";
  errorBarText.style.display = "initial";

  // destroy options etc. (and event listeners alongside them) by calling cleanup()
  // this should prevent users from sending additional requests
  cleanup();
}

// **** CORE FLOW: get data from backend, then populate/render page with that data

// "choice" corresponds to user-selected option (e.g. "1", "2", or "3"). This is all the info we ever need to send to backend.
async function renderPage(choice) {
  // PART1: send choice to backend. Response is the updated data to render on the page.
  let stateData = await getNarrativeData(choice);
  // if response contains errors, handle and stop.
  if (stateData["errors"]) {
    await errorHandler(stateData["errors"]);
    return;
  }
  // PART2: use data in said response to render to page
  construct(stateData);
}

// **** PART1: FETCHING DATA FROM BACKEND:

// when user makes a choice, send to backend (response = new data to render)
// note: sending choice = null (e.g. via renderPage(null) calling getNarrativeData(null)) forces backend to reload user's data from sessions table
// called every time we need to update the page, i.e. onload, or when user makes a choice of any kind
async function getNarrativeData(choice) {
  let choiceObj = { choice: choice };
  // forward user's choice to backend, and await updated information
  const response = await fetch("./includes/states.php", {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(choiceObj),
  });

  //debug to see what backend actually returned
  const rawText = await response.text();

  // response data includes all the information required for rendering page - text, distributions, etc.
  let stateData = await JSON.parse(rawText);
  return stateData;
}

// Called specifically if in dice roll pseudostate, when user makes a choice of which dice they want to roll.
// Specifically: sends user's choice of dice roll to backend, then receives data for and plays the dice roll animation,
// then fetches updated user state from backend with renderPage(null) and populates the page
async function getResult(choice) {
  let choiceObj = { choice: choice };
  // forward the user's choice to the backend, to roll the chosen dice and determine outcome
  const response = await fetch("./includes/states.php", {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(choiceObj),
  });
  // response is the information needed to visualise dice animations
  let resultData = await response.json();
  // if response contains errors, handle and stop.
  if (resultData["errors"]) {
    errorHandler(resultData["errors"]);
    return;
  }
  // trigger 3D dice animation and wait for it to complete, using response data
  const successState = resultData.result;

  //make sure the 3d dice sec is visible
  const threejsWrapper = document.getElementById("threejs-dice-wrapper");
  //get the hint element
  const hint = document.getElementById("hint");
  //ensures teh threejs wrapper is visible
  if (threejsWrapper) {
    threejsWrapper.style.display = "block";
  }
  //this shows the hint and update it to sayy rolling
  if (hint) {
    hint.style.display = "block";
    hint.textContent = "[ ROLLING… ]";
  }

  tooltipBlocked = true;

  //check if the core dice roll function exist before calling it
  if (typeof window.playDiceRoll === "function") {
    //call the dice roll animation with all the data from teh backend

    await window.playDiceRoll({
      //new format for the data object
      types: resultData.types,
      rolls: resultData.rolls,
      outcome: resultData.outcome,
      result: successState,
      flag: resultData.flag,
      shift_value: resultData.shift_value || 0,
    });
  } else {
    //check if somethings wrong and the main function is not calling
    console.warn("playDiceRoll function not found");
  }
  // once dice animation complete:
  // unblock tooltips
  tooltipBlocked = false;
  // hide the chart from that dice roll option and show statblock again,
  // (as we never mouse-outed from the option we clicked on!)
  hideBaserollChart(document.querySelector("#opt" + choice));
  // request post-check user state from backend, and populate HTML elements

  //this allow player to see teh results before the page dissapears
  await new Promise((resolve) => setTimeout(resolve, 3500));

  renderPage(null);
}

// **** PART 2: PAGE RENDER/POPULATE

// FLOW:
// construct page based on updated state information retrieved from backend
function construct(stateData) {
  // remove any obsolete elements from prior state
  cleanup();
  // add narrative text
  constructNarr(stateData);
  // add stats in sidebar
  constructStatblock(stateData);
  // add option boxes and text
  constructOptions(stateData);
  // add charts if applicable
  constructCharts(stateData);

  //this makes sure dice wrapper is visible
  const threejsWrapper = document.getElementById("threejs-dice-wrapper");
  const hint = document.getElementById("hint");
  if (threejsWrapper) {
    threejsWrapper.style.display = "block";
  }
  if (hint) {
    hint.style.display = "block";
  }

  //it only update hint if in pseudo state
  if (stateData["pseudoFlag"] === true) {
    if (hint) {
      hint.textContent = "[ PICK AN OPTION TO ROLL ]";
    }
  } else {
    if (hint) {
      hint.textContent = "[ MAKE A CHOICE ]";
    }
  }
}

// cleanup - removing old elements to make way for new ones constructed
function cleanup() {
  // delete old option boxes
  let opts = document.querySelectorAll(".option");
  for (let opt of opts) {
    opt.remove();
  }
  // for all options other than the first, delete their HTML table rows as well (new state may not need them all!)
  let rows = document.querySelectorAll(".laterrow");
  for (let row of rows) {
    row.remove();
  }
  // delete old charts by deleting the linechart and container div
  let oldCharts = document.querySelectorAll(".baseroll-chart-svg");
  for (let chart of oldCharts) {
    chart.remove();
  }
  let oldDivs = document.querySelectorAll(".baseroll-chart");
  for (let div of oldDivs) {
    div.remove();
  }
  hideTooltip();
}

// add narrative text in
function constructNarr(stateData) {
  // just need to update inner text for narrative title and narrative text
  document.getElementById("narrTitle").innerHTML =
    stateData["currentState"]["narr"]["narrTitle"];
  document.getElementById("narrText").innerHTML =
    stateData["currentState"]["narr"]["narrText"];
}

// add options boxes in
function constructOptions(stateData) {
  // if pseudoFlag active, need to take our options from pseudoState not currentState
  let obj;
  if (stateData["pseudoFlag"] == false) {
    obj = stateData["currentState"];
  } else obj = stateData["pseudoState"];

  // in either case, we now want to loop through the data within....
  for (let [key, value] of Object.entries(obj)) {
    // .... and for all options (i.e. non-narrative information) with a value...
    if (key != "narr" && value != null) {
      // construct option as a table data element
      let td = document.createElement("td");
      td.className = "option";
      td.id = key;
      td.colspan = 2;


      // add option text as a paragraph inside the table data element
      let p = document.createElement("p");
      p.className = "option-text";
      p.innerHTML = value["optText"];

      // if option is a check, additionally add check text "[TYPE CHECK]" (inside a span to apply a CSS class to it)
      if (value["optCheckType"] != null) {
        p.appendChild(document.createElement("br"));
        let span = document.createElement("span");
        span.className = "option-check-text";
        span.innerHTML = "[" + value["optCheckType"].toUpperCase() + " CHECK]";
        p.appendChild(span);
      }
      td.appendChild(p);
      // add event listener to option. On-click from user, we want to pass option value (i.e. "opt1" would pass "1") to backend.
      // For regular states, we call renderPage with the user's choice as the parameter.
      if (stateData["pseudoFlag"] == false) {
        td.addEventListener("click", () => renderPage(key.replace("opt", "")), {
          once: true,
        });
      }
      // For pseudoStates, we instead need user selection to call getResult with the choice as the parameter.
      // getResult just gets and plays the dice roll animation information first, *then* gets updated state information as above afterwards
      else {
        td.addEventListener("click", () => getResult(key.replace("opt", "")), {once : true});
        td.addEventListener("mouseover", () => showTooltip(value['optTooltipText']));
        td.addEventListener("mouseout", () => hideTooltip());
      }

      // first option needs to go in a specific place, the "#firstrow" table row.
      // all subsequent options get added as their own table row
      // so: if an element with class .option is already a child of #firstrow
      if (document.querySelector("#firstrow > .option")) {
        // create a new table row and append our option to it
        let tr = document.createElement("tr");
        tr.className = "laterrow";
        tr.appendChild(td);
        document.querySelector("#info").tBodies[0].appendChild(tr);
      }
      // else, no option in firstrow, so this is first option: prepend it to firstrow
      else {
        document.querySelector("#firstrow").prepend(td);
      }
    }
  }
}

// add stats into stats sidebar
function constructStatblock(stateData) {
  // for each stat, show the associated value
  for (let [key, value] of Object.entries(stateData["stats"])) {
    document.querySelector("#" + key + " .statblkStat").innerHTML =
      getSign(value);
  }
}
// helper for above - adds a "+" for positive numbers
function getSign(num) {
  if (typeof num == "number") {
    if (num >= 0) {
      return "+" + num;
    } else return "" + num;
  } else return "" + num;
}

// construct any necessary chart visualisations (for narrative check options, or in pseudoState for dice roll options)
function constructCharts(stateData) {
  // if pseudoFlag active, need to take our options from pseudoState not currentState
  let obj;
  if (stateData["pseudoFlag"] == false) {
    obj = stateData["currentState"];
  } else obj = stateData["pseudoState"];

  // we want graphs to be visually comparable, i.e. to have consistent y/x-axes.
  // so we need to determine the highest x and highest y across all options being presented; these will be passed as parameters to the linechart visualisation.

  // start by getting max y/x for *each* distribution
  let ys = [];
  let xs = [];
  for (let [key, value] of Object.entries(obj)) {
    // for all options which have a distribution
    if (key != "narr" && value != null && value["optCheckDist"] != null) {
      let data = value["optCheckDist"];
      // get maximum y/x for that distribution
      ys.push(Math.max(...data));
      // note that distribution data uses the array index as the x-axis value, so the length *is* the max x (+1)
      xs.push(data.length);
    }
  }
  // then take highest value of all those
  let yMax = Math.max(...ys);
  let xMax = Math.max(...xs);
  let yMaxPadding = 0.1; // we'll also use this to add some padding to top of chart

  // now can add new charts - loop through either currentState or pseudoState (as set above)
  for (let [key, value] of Object.entries(obj)) {
    // for all options with an associated distribution
    if (key != "narr" && value != null && value["optCheckDist"] != null) {
      // construct div for linechart and add to sidebar
      let chartDiv = document.createElement("div");
      chartDiv.classList.add("baseroll-chart");
      chartDiv.id = "chart" + key;
      let targetDiv = "#" + chartDiv.id; // string for linechart constructor
      document.querySelector(".sidebar").prepend(chartDiv);

      // construct linechart - it gets added to the above div within its constructor by passing in targetDiv string
      // we also pass in the size/marging of the chart
      let data = value["optCheckDist"];
      let requirements = value["optCheckRequirements"];
      let linechart = new LineChart(targetDiv, 340, 200, [20, 20, 30, 40]);

      // use linechart internal method to render our data - the distributuon data and the DC requirements.
      // yMax and xMax will be used to ensure each graph is scaled the same, so they're visually comparable!
      // optCheckMean and optCheckStdDev will be displayed if backend made them available
      // axes will be displayed if model confidence stat high enough
      let axes = value["optCheckAxes"];
      let mean = value["optCheckMean"];
      let stdDev = value["optCheckStdDev"];

      linechart.render(
        key,
        data,
        requirements,
        axes,
        mean,
        stdDev,
        yMax,
        yMaxPadding,
        xMax,
      );
      // add class to the svg generated by linechart constructor
      let svg = document.querySelector(targetDiv + " > svg");
      svg.setAttribute("class", "baseroll-chart-svg");

      // add event listener to option, connected to the linechart
      // when option hovered, hides stats sidebar and shows corresponding chart
      // when un-hovered, vice versa. See below helpers, showBaserollChart and hideBaserollChart.
      document.getElementById(key).addEventListener("mouseover", () =>
        showBaserollChart(document.getElementById("chart" + key), {
          once: true,
        }),
      );
      document.getElementById(key).addEventListener("mouseout", () =>
        hideBaserollChart(document.getElementById("chart" + key), {
          once: true,
        }),
      );
    }
  }
}

// helpers - toggling visibility of baseroll charts on event.
// note - "el" should be *the chart we want to show/hide*, not the event target or anything else
function showBaserollChart(el) {
  // only do anything if the chart has children (i.e. isn't empty/blank); we don't want to toggle visibility of empty divs
  if (el.hasChildNodes()) {
    // if it isn't blank:
    // hide *all* charts
    let charts = document.getElementsByClassName("baseroll-chart");
    for (let i = 0; i < charts.length; i++) {
      charts[i].style.display = "none";
    }
    // hide *stat sidebar*
    document.getElementById("statblkSidebar").style.display = "none";
    // show *just the relevant chart*
    el.style.display = "initial";
  }
}
function hideBaserollChart(el) {
  // only do anything if the chart has children (i.e. isn't empty/blank); we don't want to toggle visibility of empty divs
  if (el.hasChildNodes()) {
    // if it isn't blank:
    // hide *all* charts
    let charts = document.getElementsByClassName("baseroll-chart");
    for (let i = 0; i < charts.length; i++) {
      charts[i].style.display = "none";
    }
    // *show* stat block
    // note - yes, the below really is all necessary, display = 'initial' doesnt work for tables as they have unique default display values in HTML/CSS
    // set display = table for table
    document.getElementById("statblkSidebar").style.display = "table";
    // table body needs to have display = table-row-group
    document.querySelector("#statblkSidebar > tbody").style.display =
      "table-row-group";
    // rows get display = table-row
    let rows = document.querySelectorAll("#statblkSidebar tr");
    for (let el of rows) {
      el.style.display = "table-row";
    }
    // td elements get display = table-cell
    let cells = document.querySelectorAll("#statblkSidebar td");
    for (let el of cells) {
      el.style.display = "table-cell";
    }
  }
}
// helpers - toggling visibility of tooltip div on event
function showTooltip(tooltip) {
  let tooltipDiv = document.querySelector("#tooltipDiv");
  if (tooltipDiv && !tooltipBlocked) {
    ttArray.forEach(timer => clearTimeout(timer));
    ttArray = [];
    
    tooltipDiv.innerHTML = "";
    
    let ttSplitWords = tooltip.split(" ");

    const ttSplit = [];
    let tempStr = "";
    
    let winWidth = window.innerWidth; //visible window space
    let lineLength = winWidth /10.5; // adjust for larger fonts

    let endofline; // bool to end line when textlength is reached
    let endofarray; // bool to end line when last word is reached
    let wordCount = 0; // Tally of words cycled
    let i = 0; // incrementor
         
    // Build tooltip lines
    do {
      endofline = false; //reset line
      tempStr = "";
      do {
        if (wordCount >= ttSplitWords.length) {
          endofarray = true;
          break; // Probably redundant 
        }
        if (((tempStr.length > 0) && tempStr.length + ttSplitWords[wordCount].length) >= lineLength) {
          endofline = true;
          break;
        }
        tempStr += ttSplitWords[wordCount] + " ";
        
        if(((ttSplitWords.length - wordCount) > 1) && ttSplitWords[wordCount + 1].includes("▪")) {
          wordCount++;
          i++;
          endofline = true;
          break;
        }

        wordCount++;
        i++;
         
      }
      while(!endofline);
      ttSplit.push(tempStr);
    }
    while(!endofarray);

    let delay = 0;
    for (let i=0; i<ttSplit.length; i++) {
      ttArray[i] = setTimeout(() =>updateTooltip(ttSplit[i]), (delay));

      delay += ttSplit[i].length * 25;
      delay += 10;
    }
    tooltipDiv.style.display = "block";
  }
}

function updateTooltip(tooltip) {
  let tooltipDiv = document.querySelector("#tooltipDiv");
  let p = document.createElement('p');
  p.className = "tooltip typing";
  p.innerHTML = tooltip;
  tooltipDiv.appendChild(p);
}
function hideTooltip() {
  let tooltipDiv = document.querySelector("#tooltipDiv");
  
  ttArray.forEach(timer => clearTimeout(timer));
  ttArray = [];

  if(tooltipDiv) {
    tooltipDiv.innerHTML = "";
    tooltipDiv.style.display = "none";
  }
}
