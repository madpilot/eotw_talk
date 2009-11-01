<?php
  require_once('lib/small_framework.php');
  if($_ENV['FRAMEWORK_ENV'] == 'production')
  {
    mysql_connect('localhost', 'eotw', 'eotw');
    mysql_select_db('eotw_production');
  }
  else
  {
    mysql_connect('localhost', 'eotw', 'eotw');
    mysql_select_db('eotw_demo');
  }

  function index()
  {
    include('views/index.php');
  }
  
  function not_found()
  {
    header('HTTP/1.0 404 Page Not Found');
    include('views/404.php');
  }

  function news($permalink)
  {
    $results = mysql_query("SELECT * FROM news WHERE permalink = '" . mysql_real_escape_string($permalink) . "'");
    if(mysql_num_rows($results) == 0)
    {
      not_found();
      return;
    }
    $news = mysql_fetch_assoc($results);

    include('views/news.php');
  }

  function search()
  {
    $query = split(' ', $_REQUEST['query']);
    $results = mysql_query("SELECT * FROM search WHERE keywords IN ('" . join(',', $query) . "')");

    $pages = array();
    while($row = mysql_fetch_assoc($results))
    {
      array_push($pages, $row);
    }

    include('views/search.php');
  }
  
  function route($request)
  {
    if(preg_match("/^$/", $request))
    {
      index();
    }
    elseif(preg_match("/^news\/(.+)$/", $request, $m))
    {
      news($m[1]);
    }
    elseif(preg_match("/^search$/", $request))
    {
      search();
    }
    else
    {
      not_found();
    }
  }
 
  run();
?>
