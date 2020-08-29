      </div>
    </div>
    <div class="container-fluid">
      <hr>
      <footer class="d-flex justify-content-between">
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="darkSwitch" />
          <label class="custom-control-label" for="darkSwitch">Thème sombre</label>
        </div>
        <span class="mb-2">
          <i class="fa fa-code" aria-hidden="true"></i> avec le <i class="fa fa-heart" aria-hidden="true"></i> par Noé AMIOT
        </span>
      </footer>
    </div>
    <div id="messageBox" role="alert" class="container-fluid alert alert-warning alert-dismissible fade show d-none">
      <strong id="messageBoxState"></strong><span> : </span><span id="messageBoxContent"></span>
      <button id="closeMessageBox" type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  </body>
  <script src="js/all.js"></script>
  <script src="js/dark.js"></script>
  <script>
    document.getElementById("toggleMenuButton").addEventListener("click", function(evt) {
      document.getElementById("leftMenu").classList.toggle("d-none");
      document.getElementById("content").classList.toggle("d-none");
    });
  </script>
</html>
