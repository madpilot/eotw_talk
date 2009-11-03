<?php
  require_once('lib/small_framework.php');
  require_once('lib/pspell.php');
  require_once('lib/helpers.php');
 
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
 
  function h($str)
  {
		return htmlentities($str, ENT_QUOTES, 'UTF-8');
  }

  function get_data($key)
  {
    $raw = NULL;
    if (isset($_POST[$key]))
      $raw = $_POST[$key];

    // Try globals array
    if (!$raw && isset($_GLOBALS) && isset($_GLOBALS["HTTP_RAW_POST_DATA"]))
      $raw = $_GLOBALS["HTTP_RAW_POST_DATA"];

    // Try globals variable
    if (!$raw && isset($HTTP_RAW_POST_DATA))
      $raw = $HTTP_RAW_POST_DATA;

    // Try stream
    if (!$raw) {
      if (!function_exists('file_get_contents')) {
        $fp = fopen("php://input", "r");

        if ($fp) {
          $raw = "";

          while (!feof($fp))
            $raw = fread($fp, 1024);

          fclose($fp);
        }
      } else
        $raw = "" . file_get_contents("php://input");
    }
    return $raw;
  }


  function spelling_suggestions($query) {
    if($query != "")
    {
      $tokens = split(" ", $query);
      $corrected = array();
      $spellchecker = new PSpell();
      $misspelt = $spellchecker->checkWords('en', $tokens);
      foreach($misspelt as $word)
      {
        $corrections = $spellchecker->getSuggestions('en', $word);
        if(count($corrections) > 0)
        {
          $corrected[$word] = $corrections[0];
        }
      }

      if(count($corrected) > 0)
      {
        $formatted_query = $query;
        foreach($corrected as $orig => $correction) {
          $formatted_query = str_replace($orig, "<em>" . $correction . "</em>", $formatted_query);
          $query = str_replace($orig, $correction, $query);
        }
        return "Did you mean <a href=\"/search?q=" . urlencode($query) . "\">" . $formatted_query . "</a>?";
      }
    }
 
    return "";
  }

  function flash()
  {
    if(isset($_SESSION['flash']))
    {
      $flash = $_SESSION['flash'];
      unset($_SESSION['flash']);
      return $flash;
    }
    return "";
  }

  function not_found()
  {
    header('HTTP/1.0 404 Page Not Found');
    include('views/404.php');
  }

  function route($request)
  {
    global $vars, $layout;

    if(preg_match("/^$/", $request))
    {
      $results = mysql_query("SELECT * FROM pages WHERE permalink = 'index'");
      if(mysql_num_rows($results) == 0)
      {
        not_found();
        return;
      }
      $page = mysql_fetch_assoc($results);
      $vars['page'] = $page;
      $vars['page_title'] = $page['title'];
      $vars['keywords'] = $page['keywords'];
      $vars['description'] = $page['abstract'];

      include('views/pages.php');
    }
    elseif(preg_match("/^news\/(.+)$/", $request, $m))
    {
      $permalink = $m[1];
      $results = mysql_query("SELECT * FROM news WHERE permalink = '" . mysql_real_escape_string($permalink) . "'");
      if(mysql_num_rows($results) == 0)
      {
        not_found();
        return;
      }
      $news = mysql_fetch_assoc($results);

      include('views/news.php');

    }
    elseif(preg_match("/^search$/", $request))
    {
      $query = $_REQUEST['q'];
      $r = mysql_query("SELECT * FROM searches WHERE MATCH(title, copy, keywords) AGAINST('" . mysql_real_escape_string($query) . "' IN BOOLEAN MODE)");

      $results = array();
      while($row = mysql_fetch_assoc($r))
      {
        array_push($results, $row);
      }

      include('views/search.php');
    }
    elseif(preg_match("/^pages\/spellcheck$/", $request))
    {
      // This is a modification of the code from Moxie Code
      $input = json_decode(get_data('json_data'));
      $spellchecker = new PSpell();
      $result = call_user_func_array(array($spellchecker, $input->method), $input->params);

      $output = array(
        "id" => $input->id,
        "result" => $result,
        "error" => null
      );
      
      header("Content-type: application/json");
      $layout = false;
      print json_encode($output); 
    }
    elseif(preg_match("/^pages\/(\d+)\/edit$/", $request, $m))
    {
      $results = mysql_query("SELECT * FROM pages WHERE id = '" . $m[1] . "'");
      if(mysql_num_rows($results) == 0)
      {
        not_found();
        return;
      }
      $page = mysql_fetch_assoc($results);
     
      if($_SERVER['REQUEST_METHOD'] == "POST")
      {
        $conditions = array();
        foreach(array('title', 'permalink', 'copy') as $field)
        {
          if($field == "copy")
          {
            $copy = clean($_REQUEST['page'][$field]);
            $conditions['abstract'] = "`abstract` = '" . mysql_real_escape_string(abstractify($copy)) . "'";
            $conditions['keywords'] = "`keywords` = '" . mysql_real_escape_string(join(', ', keywordify($copy))) . "'";
            $conditions['copy'] = "`copy` = '" . mysql_real_escape_string($copy) . "'";
          }
          else
          {
            $conditions[$field] = "`" . $field . "` = '" . mysql_real_escape_string($_REQUEST['page'][$field]) . "'";
          }
        }
        $query = "UPDATE pages SET " . join(', ', $conditions) . " WHERE id = " . $m[1];
        mysql_query($query);
        // Convieniently, the searches table has the same columns as the pages
        $query = "UPDATE searches SET " . join(', ', $conditions) . " WHERE entry_type = 'pages' AND entry_id = " . $m[1];
        mysql_query($query);
        
        $_SESSION['flash'] = "Page updated";
        header("Location: /" . $_REQUEST['page']['permalink']);
        return;
      }
      else
      {
        include('views/edit_page.php');
      }
    }
    elseif(preg_match("/^upload$/", $request))
    {
      if($_SERVER['REQUEST_METHOD'] == "POST")
      {
        // Usually, you'd do something here with the file
      }
      include('views/upload.php');
    }
    elseif(preg_match("/^(.+)$/", $request, $m))
    {
      $results = mysql_query("SELECT * FROM pages WHERE permalink = '" . $m[1] . "'");
      if(mysql_num_rows($results) == 0)
      {
        not_found();
        return;
      }
      $page = mysql_fetch_assoc($results);
      $vars['page'] = $page;
      $vars['page_title'] = $page['title'];
      $vars['keywords'] = $page['keywords'];
      $vars['description'] = $page['abstract'];

      include('views/pages.php');

    }
    else
    {
      not_found();
    }
  }
 
  run();
?>
