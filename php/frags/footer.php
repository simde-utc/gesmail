      </div>
    </div>
    <div class="container-fluid">
      <hr>
      <footer class="text-right">
        <p>&copy; SiMDE 2019</p>
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
  <script>
    document.getElementById("toggleMenuButton").addEventListener("click", function(evt) {
      document.getElementById("leftMenu").classList.toggle("d-none");
      document.getElementById("content").classList.toggle("d-none");
    });
  </script>
</html>