<?php 
$pageInfo = array("Drift Protocol | Style Demo");
// array (page title)

include './siteincludes/header.php'; 
?>

<div class="modal modal-small" id="modal-sm" style="display:none">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <div class="modalTitle">Alert</div>
        <span class="close float-end" onclick="hideModal('modal-sm')">&times;</span>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        Small Modal
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <a class="btn btn-primary btn-sm" onclick="hideModal('modal-sm')">OK</a>
      </div>
    </div>
</div>

<div class="modal modal-medium" id="modal-md" style="display:none">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <div class="modalTitle">Alert</div>
        <span class="close float-end" onclick="hideModal('modal-md')">&times;</span>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        Medium Modal (Default)
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <a class="btn btn-primary" onclick="hideModal('modal-md')">OK</a>
      </div>
    </div>
</div>

<div class="modal modal-large" id="modal-lg" style="display:none">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <div class="modalTitle">Alert</div>
        <span class="close float-end" onclick="hideModal('modal-lg')">&times;</span>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        Large Modal
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <a class="btn btn-primary" onclick="hideModal('modal-lg')">OK</a>
      </div>
    </div>
</div>

<div class="modal modal-xlarge" id="modal-xl" style="display:none">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <div class="modalTitle">Alert</div>
        <span class="close float-end" onclick="hideModal('modal-xl')">&times;</span>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        XL Modal
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <a class="btn btn-primary" onclick="hideModal('modal-xl')">OK</a>
      </div>
    </div>
</div>

<!-- Page content -->
<div class="container">
  <img src="./assets/img/default_black.png" alt="" class="img-fluid my-1">
  <img src="./assets/img/alt_black.png" alt="" class="img-fluid my-1">
  <img src="./assets/img/momh.png" alt="" class="img-fluid my-1">
  <img src="./assets/img/plain_grey.png" alt="" class="img-fluid my-1">
  <img src="./assets/img/thick_white.png" alt="" class="img-fluid my-1">
  <img src="./assets/img/min_white.png" alt="" class="img-fluid my-1">
</div>
    <nav class="navbar navbar-expand-lg navbar-light bg-primary">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">Navbar</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Dropdown
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <li><a class="dropdown-item" href="#">Action</a></li>
                  <li><a class="dropdown-item" href="#">Another action</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="#">Something else here</a></li>
                </ul>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">Another Link</a>
              </li>  
              <li class="nav-item">
                <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
              </li>
            </ul>
            <form class="d-flex">
              <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
              <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
          </div>
        </div>
      </nav>

      <!-- Page -->
    <div class="container-xxl">
        <div class="row mt-3">
            <div class="col-lg-4 col-md-6">
              <div class="mt-3 mb-3">
                  <h1 class="h1">Headers</h1>
                  <h1 class="h1">.h1 text</h1>
                  <h2 class="h2">.h2 text</h2>
                  <h3 class="h3">.h3 text</h3>
                  <h4 class="h4">.h4 text</h4>
                  <h5 class="h5">.h5 text</h5>
                  <h6 class="h6">.h6 text</h6>
                </div>
                <div class="mt-3 mb-3">
                  <p class="fs-1">Font-size</p>
                  <p class="fs-1">.fs-1 text</p>
                  <p class="fs-2">.fs-2 text</p>
                  <p class="fs-3">.fs-3 text</p>
                  <p class="fs-4">.fs-4 text</p>
                  <p class="fs-5">.fs-5 text</p>
                  <p class="fs-6">.fs-6 text</p>
                </div>
               <div class="mt-3 mb-3">
                <p>Hello this is body text, Documentation and examples for common text utilities to control 
                    alignment, wrapping, weight, and more.</p>
               </div>
               
               <div class="my-3">
                <p>Progress Bars:</p> 
                <div class="progress" style="height:10px">
                  <div class="progress-bar" style="width:40%;"></div>
                </div>
                <br>
                <div class="progress" style="height:20px">
                  <div class="progress-bar" style="width:50%;">50%</div>
                </div>
                <br>
                <div class="progress" style="height:30px">
                  <div class="progress-bar" style="width:60%;">Other Label</div>
                </div>
              </div>

                <div class="mt-5 mb-5">
                  <p>Buttons</p>
                    <button type="button" class="btn btn-primary">Primary</button>
                    <button type="button" class="btn btn-secondary">Secondary</button>
                    <button type="button" class="btn btn-success">Success</button>
                    <button type="button" class="btn btn-danger">Danger</button>
                    <button type="button" class="btn btn-warning">Warning</button>
                    <button type="button" class="btn btn-info">Info</button>
                    <button type="button" class="btn btn-light">Light</button>
                    <button type="button" class="btn btn-dark">Dark</button>
                    <button type="button" class="btn btn-link">Link</button>
                </div>
                <div class="my-5">
                  <p>Button Sizes</p>
                  <button type="button" class="btn btn-primary btn-lg">Large</button>
                  <button type="button" class="btn btn-primary">Default</button>
                  <button type="button" class="btn btn-primary btn-sm">Small</button>
                </div>
                <div class="d-grid">
                  <p>Full-width button/ block button</p>
                  <button type="button" class="btn btn-primary">Full-Width Button</button>
                </div>
                <div class="mt-5 mb-5">
                  <p>Outline Buttons</p>
                    <button type="button" class="btn btn-outline-primary">Primary</button>
                    <button type="button" class="btn btn-outline-secondary">Secondary</button>
                    <button type="button" class="btn btn-outline-success">Success</button>
                    <button type="button" class="btn btn-outline-danger">Danger</button>
                    <button type="button" class="btn btn-outline-warning">Warning</button>
                    <button type="button" class="btn btn-outline-info">Info</button>
                    <button type="button" class="btn btn-outline-light">Light</button>
                    <button type="button" class="btn btn-outline-dark">Dark</button>
                </div>


                <div class="mt-5 mb-3">
                  <p>Button groups</p>
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <button type="button" class="btn btn-primary">Left</button>
                        <button type="button" class="btn btn-primary">Middle</button>
                        <button type="button" class="btn btn-primary">Right</button>
                      </div>
                </div>
                <div class="mt-3 mb-3">
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                        <button type="button" class="btn btn-danger">Left</button>
                        <button type="button" class="btn btn-warning">Middle</button>
                        <button type="button" class="btn btn-success">Right</button>
                      </div>
                </div>
                <div class="mt-3 mb-3">
                  <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                    <button type="button" class="btn btn-primary">1</button>
                    <button type="button" class="btn btn-primary">2</button>
                  
                    <div class="btn-group" role="group">
                      <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        Dropdown
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                      </ul>
                    </div>
                  </div>

                  <div class="row mt-5 mb-3">
                    <div class="col-12">
                      <p>Borders</p>
                    </div>
                    <div class="col-3 p-4 border border-primary me-2 my-2"></div>
                    <div class="col-3 p-4 border border-secondary me-2 my-2"></div>
                    <div class="col-3 p-4 border border-success me-2 my-2"></div>
                    <div class="col-3 p-4 border border-danger me-2 my-2"></div>
                    <div class="col-3 p-4 border border-warning me-2 my-2"></div>
                    <div class="col-3 p-4 border border-info me-2 my-2"></div>
                    <div class="col-3 p-4 border border-light me-2 my-2"></div>
                    <div class="col-3 p-4 border border-dark me-2 my-2"></div>
                    <div class="col-3 p-4 border border-white me-2 my-2"></div>
                  </div>
                  <div class="row mt-5 mb-3 d-flex">
                    <div class="col-3 p-4 border border-primary border-1 me-2 my-2"></div>
                    <div class="col-3 p-4 border border-primary border-2 me-2 my-2"></div>
                    <div class="col-3 p-4 border border-primary border-3 me-2 my-2"></div>
                    <div class="col-3 p-4 border border-primary border-4 me-2 my-2"></div>
                    <div class="col-3 p-4 border border-primary border-5 me-2 my-2"></div>
                  </div>
                 

                </div>
              </div>
       
        <div class="col-lg-4 col-md-6">
        <div class="my-5">
                  <p>Modals</p>
                  <button type="button" class="btn btn-primary btn-sm" onclick="showModal('modal-sm')">Small</button>
                  <button type="button" class="btn btn-primary"  onclick="showModal('modal-md')">Default</button>
                  <button type="button" class="btn btn-primary btn-lg" onclick="showModal('modal-lg')">Large</button>
                  <button type="button" class="btn btn-primary btn-lg" onclick="showModal('modal-xl')">Extra Large</button>
                </div>

          
            <div class="mt-3 mb-3">
            <p>Alerts</p>
                <div class="alert alert-primary" role="alert">
                    A simple primary alert—check it out!
                  </div>
                  <div class="alert alert-secondary" role="alert">
                    A simple secondary alert—check it out!
                  </div>
                  <div class="alert alert-success" role="alert">
                    A simple success alert—check it out!
                  </div>
                  <div class="alert alert-danger" role="alert">
                    A simple danger alert—check it out!
                  </div>
                  <div class="alert alert-warning" role="alert">
                    A simple warning alert—check it out!
                  </div>
                  <div class="alert alert-info" role="alert">
                    A simple info alert—check it out!
                  </div>
                  <div class="alert alert-light" role="alert">
                    A simple light alert—check it out!
                  </div>
                  <div class="alert alert-dark" role="alert">
                    A simple dark alert—check it out!
                  </div>
            </div>
            <div class="mt-3 mb-3">
                <h1 class="display-1">Display 1</h1>
                <h1 class="display-2">Display 2</h1>
                <h1 class="display-3">Display 3</h1>
                <h1 class="display-4">Display 4</h1>
                <h1 class="display-5">Display 5</h1>
                <h1 class="display-6">Display 6</h1>
            </div>
            <div class="mt-3 mb-3">
              <div class="accordion" id="accordionExample">
                  <div class="accordion-item mb-3">
                    <h2 class="accordion-header" id="headingOne">
                      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Accordion Item #1
                      </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                      <div class="accordion-body">
                        <strong>This is the first item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                      </div>
                    </div>
                  </div>
                  <div class="accordion-item shadow mb-3">
                    <h2 class="accordion-header mt-3" id="headingTwo">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        Accordion Item #2
                      </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                      <div class="accordion-body">
                        <strong>This is the second item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                      </div>
                    </div>
                  </div>
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        Accordion Item #3
                      </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                      <div class="accordion-body">
                        <strong>This is the third item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                      </div>
                    </div>
                  </div>
                </div>
          </div>
          <div class="mt-3 mb-3">
            <p>Breadcrumb navigation</p>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Home</li>
              </ol>
            </nav>
            
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Library</li>
              </ol>
            </nav>
            
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Library</a></li>
                <li class="breadcrumb-item active" aria-current="page">Data</li>
              </ol>
            </nav>
          </div>
          <div class="mt-3 mb-3">
                    <p>Table</p>
                    <table class="table table-bordered border-primary">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">First</th>
                          <th scope="col">Last</th>
                          <th scope="col">Handle</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <th scope="row">1</th>
                          <td>Mark</td>
                          <td>Otto</td>
                          <td>@mdo</td>
                        </tr>
                        <tr>
                          <th scope="row">2</th>
                          <td>Jacob</td>
                          <td>Thornton</td>
                          <td>@fat</td>
                        </tr>
                        <tr>
                          <th scope="row">3</th>
                          <td colspan="2">Larry the Bird</td>
                          <td>@twitter</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
          <p>Badges</p>
            <div class="mt-3 mb-3">
                <span class="badge bg-primary">Primary</span>
                <span class="badge bg-secondary">Secondary</span>
                <span class="badge bg-success">Success</span>
                <span class="badge bg-danger">Danger</span>
                <span class="badge bg-warning text-dark">Warning</span>
                <span class="badge bg-info text-dark">Info</span>
                <span class="badge bg-light text-dark">Light</span>
                <span class="badge bg-dark">Dark</span>
            </div>
            <div class="mt-3 mb-3">
              <span class="badge rounded-pill bg-primary">Primary</span>
              <span class="badge rounded-pill bg-secondary">Secondary</span>
              <span class="badge rounded-pill bg-success">Success</span>
              <span class="badge rounded-pill bg-danger">Danger</span>
              <span class="badge rounded-pill bg-warning text-dark">Warning</span>
              <span class="badge rounded-pill bg-info text-dark">Info</span>
              <span class="badge rounded-pill bg-light text-dark">Light</span>
              <span class="badge rounded-pill bg-dark">Dark</span>
            </div>
            <div class="my-3">
              <p>Spinners</p>
              <div class="spinner-border text-muted"></div>
              <div class="spinner-border text-primary"></div>
              <div class="spinner-border text-success"></div>
              <div class="spinner-border text-info"></div>
              <div class="spinner-border text-warning"></div>
              <div class="spinner-border text-danger"></div>
              <div class="spinner-border text-secondary"></div>
              <div class="spinner-border text-dark"></div>
              <div class="spinner-border text-light"></div>
            </div>
            <div class="my-3">
            <div class="spinner-grow text-muted"></div>
            <div class="spinner-grow text-primary"></div>
            <div class="spinner-grow text-success"></div>
            <div class="spinner-grow text-info"></div>
            <div class="spinner-grow text-warning"></div>
            <div class="spinner-grow text-danger"></div>
            <div class="spinner-grow text-secondary"></div>
            <div class="spinner-grow text-dark"></div>
            <div class="spinner-grow text-light"></div>
            </div>
            <div class="mt-3 mb-3">
              <p>Cards</p>
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                      <h5 class="card-title">Primary card title</h5>
                      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                  </div>
                  <div class="card text-white bg-secondary mb-3">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                      <h5 class="card-title">Secondary card title</h5>
                      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                  </div>
                  <div class="card text-white bg-success mb-3">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                      <h5 class="card-title">Success card title</h5>
                      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                  </div>
                  <div class="card text-white bg-danger mb-3">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                      <h5 class="card-title">Danger card title</h5>
                      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                  </div>
                  <div class="card text-dark bg-warning mb-3">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                      <h5 class="card-title">Warning card title</h5>
                      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                  </div>
                  <div class="card text-dark bg-info mb-3">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                      <h5 class="card-title">Info card title</h5>
                      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                  </div>
                  <div class="card text-dark bg-light mb-3">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                      <h5 class="card-title">Light card title</h5>
                      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                  </div>
                  <div class="card text-white bg-dark mb-3">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                      <h5 class="card-title">Dark card title</h5>
                      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                  </div>
            </div>
        </div>
    </div>
    </div>

    
    <?php include './siteincludes/footer.php'; ?>
    <!-- <script src="assets/js/bootstrap.bundle.min.js"></script> -->
    <!-- <script src="assets/js/popper.min.js"></script> -->

