# Welcome to the repository for Drift Protocol, an exciting new game to demonstrate statistical concepts for higher education
The structure of the repo explained:<br>
<pre>

├───.github                     // GITHUB FILES
│   ├───ISSUE_TEMPLATES         // Boilerplate issue types
│   └───workflows               // Automated workflow yaml files  

├───assets                      // Project assets that don't necessarily need to be in the server files, eg fonts
│   ├───Inter-4.1
│   └───Orbitron

├───docs                        // Documentation, technical and academic

├───htdocs                      // Everything 'uploadable' to the server
│   ├───includes                // Modular PHP includes files, eg header, navbar
│   ├───prototype               // Backup of prototype 
│   ├───scripts                 // Javascript files, functions, resources
│   └───styles                  // Stylesheets, fonts or other style assets such as images
│       └───fonts

├───node_modules                // Bootstrap Files, not needed unless compiling SASS stylesheets. Ignored by Git.

..

└───sass                        // SASS/SCSS stylesheets (compiled to htdocs/styles/style.css)
    ├───components              // Styles for components such as navbars or modals
    ├───utilities               // Utilities such as theme colours, breakpoints
    └───vendors                 // Third party assets, eg Bootstrap, w3.css
</pre>
<br>
To run SASS compiler, use:<br>
> sass sass:htdocs/styles<br><br>
Or to run it automatically when any SASS file is saved:<br>
> sass --watch sass/style.scss htdocs/styes/style.css<br><br>
If compiling for the server, use the compressed (minified) variation:<br>
> sass --watch sass/style.scss htdocs/styes/style.css --style=compressed<br>



