<?php
  $layout = 'views/layout.php';
  $vars = array();

  function run()
  {
    global $layout;
    global $vars;

    $request = $_REQUEST['request'];
    ob_start();
    route($request);
    $content = ob_get_contents();
    ob_end_clean();

    include($layout);
  }
?>
