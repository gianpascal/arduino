<?php
$this->load->view('head');
?>
<body class="skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">


  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
  <?php
$this->load->view('principal_monitoreo.php');
?>

  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Version</b> 2.4.0
    </div>
    <strong>Copyright &copy; 2018 <a href="https://adminlte.io">Giancarlo Arroyo</a>.</strong> Todos los derechos reservados
  </footer>


</div>
<!-- ./wrapper -->
</body>
</html>
<?php
$this->load->view('script');
?>
