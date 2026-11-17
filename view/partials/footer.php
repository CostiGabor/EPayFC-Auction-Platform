<footer class="fixed-bottom border-top-app-color bg-app-bar">
  <div class="container-fluid px-0" id="footer">

    <nav class="row g-0 m-0 w-100 align-items-center text-center">
      <a href="item" class="col d-flex flex-column align-items-center text-decoration-none text-white p-2">
        <i class="bi bi-search fs-5"></i>
        <span>Browse</span>
      </a>

      <?php if(Auth::user_logged()){ ?>
        <a href="item/my_items" class="col d-flex flex-column align-items-center text-decoration-none text-white p-2">
          <i class="bi bi-house fs-5"></i>
          <span>My Items</span>
        </a>

        <a href="item/add_edit_item" class="col d-flex flex-column align-items-center text-decoration-none text-white p-2">
          <i class="bi bi-plus fs-5"></i>
          <span>Add Offer</span>
        </a>
        
         <?php if(Auth::isAdmin()){ ?>
         <a href="category/index" class="col d-flex flex-column align-items-center text-decoration-none text-white p-2">
          <i class="bi bi-tags fs-5"></i>
          <span>Categories</span>
        </a>
        <?php }?>

        <?php if(Auth::isAdmin()){ ?>
         <a href="exam/index" class="col d-flex flex-column align-items-center text-decoration-none text-white p-2">
          <i class="bi bi-tags fs-5"></i>
          <span>Exam</span>
        </a>
        <?php }?>


        <a href="user/show_profile" class="col d-flex flex-column align-items-center text-decoration-none text-white p-2">
          <i class="bi bi-gear fs-5"></i>
          <span>Profile</span>
        </a>
      <?php } else { ?>      
        <a href="user/login" class="col d-flex flex-column align-items-center text-decoration-none text-white p-2">
          <i class="bi bi-person-plus fs-5"></i>
          <span>Join Us</span>
        </a>
      <?php } ?>
    </nav>

  </div>

  <?php require_once "view/view_time.php" ?>
</footer>