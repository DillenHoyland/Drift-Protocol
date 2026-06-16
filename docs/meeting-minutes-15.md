# Group Project - Group 1
## Minutes of Meeting 15(?) Tri 2
**Location:** Online - MS Teams
**Date:** 6th April
**In Attendance:** Dillen Hoyland, Gamrik Tapak, Henry Alman, Luke Ker
**Absent:** Ewan Blake
**Time:** 20:00

**Agenda:** No prepared agenda. 
**Topics of discussion:**
Henry expressed concern that we should implement features such as Option Requirement or Option Next which are currently unused. Agreement is made that if time allows at least one example of each should be incorporated into the narrative. *Should-Have*.
Luke presented the frontend (landing page) and the login/ registration process (now working). Also shows some minor changes to the game frontend such as tooltip text placeholders. Ewans suggestion for a typewriter effect implemented.
Gamrik is confident that his dice roll animations will be finished this week. Suggested to prioritise getting the 6 sided dice roll finished as a proof of concept. 

### Task allocations
**Dillen** 
 - Work with Luke on sessions implementation as well as consultation on the database.
 - Research DB connectivity/ efficiency
 - Check out notebook
 - Meet with Ewan and Luke re: login/ registration

**Ewan**
 - Possibly expand on the narrative to allow use of unused features

**Gamrik**
 - continue work on the dice animations 
 - add in visualisations to show how a result was arrived at.

**Henry**
 - Add model confidence feature for graphs (*Frontend*, *Backend*, *should-have*)
 - Axes labels/ standard deviation when Model Confidence is above given threshold
 - Change how diceroll info is sent to frontend
 - Adding tooltip text to JSON object
 - Changing core mechanics to risk not trust 
 - Refactor render_states.js for neatness and legibility
 - Fix bug which gives advantage twice under certain conditions (*should have*, *hotfix*)
 - Refactor/change DB (user/users table, narr_trust to narr_risk)

**Luke**
 - Allow users to select a saved game session
 - Fix login/ session for game
 - Implement tooltip text from JSON object when completed
 - Implement user dashboard
 - Implement Admin Dashboard
 - Tutorial placeholder

**Open**
 - Implement unused features in game narrative, eg Option Requirement
