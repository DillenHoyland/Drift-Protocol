<nav class="navbar navbar-expand-sm navbar-dark bg-dark border-top border-bottom" data-bs-theme="dark" aria-label="Header Nav menu">
    <div class="container-fluid ms-3">
      <a href="drift_protocol.php" class="navbar-icon rounded-3 fw-bold text-decoration-none">DP</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#headerNav" aria-controls="headerNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="headerNav">
        <ul class="navbar-nav ms-3 me-auto mb-2 mb-sm-0">
          <li class="nav-item">
            <!-- <a class="nav-link active" aria-current="page" href="#">Home</a> -->
             <span class="logo h1 fw-bold pt-1 text-white">Drift <span class="text-primary">Protocol</span></span>
          </li>
        </ul>
        <form>
        <input class="form-control form-control-sm" type="text" placeholder="Search" aria-label="Search" name="search">
        </form>

        <div class="w3-dropdown-hover position-relative">
        <button class="btn btn-link" onclick="showModal('loginModal')" aria-label="Open login modal"><i class="fa fa-user text-primary ms-3 me-1 navIcons" aria-hidden="true"></i> </button>
            <div class="w3-dropdown-content w3-bar-block userNav navDropdown">
                <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']==1) { ?>
                  <button type="button" class="w3-bar-item bg-dark text-primary dropdownItem" onclick="showModal('logoutModal')" aria-label="Logout of site"><i class="fa fa-sign-out pt-1 w3-right" aria-hidden="true"></i> <span class="fs-6">Logout</span></button> 
                <?php }
                else { ?>
                  <button type="button" class="w3-bar-item bg-dark text-primary dropdownItem" onclick="showModal('loginModal')" aria-label="Open login modal"><i class="fa fa-sign-in pt-1 pe-1 w3-right" aria-hidden="true"></i> <span class="fs-6">Login</span></button>
                  <button type="button" class="w3-bar-item bg-dark text-primary dropdownItem" onclick="showModal('registerModal')" aria-label="Open register modal"><i class="fa fa-user-plus pt-1 w3-right" aria-hidden="true"></i> <span class="fs-6">Register</span></button>                                
                <?php } ?>
            </div>
        </div>

        <div class="w3-dropdown-hover position-relative">
          <button class="btn btn-link"><i class="fa fa-cog text-primary mx-2 navIcons" aria-hidden="true"></i> </button>
            <div class="w3-dropdown-content w3-bar-block navDropdown">
                <div class="form-check form-switch bg-dark dropdownItem">                    
                    <input class="form-check-input" type="checkbox" id="lightSwitch" name="darkmode" onchange="setMode('')">
                    <label class="form-check-label text-primary fs-6" for="lightSwitch">Dark Mode</label>
                </div>
                <div class="form-check form-switch bg-dark dropdownItem">                    
                    <input class="form-check-input" type="checkbox" id="readMode" name="readmode" onchange="setRead('')">
                    <label class="form-check-label text-primary fs-6" for="readMode">Read Mode</label>
                </div>
            </div>
        </div>

      </div>
    </div>
  </nav>