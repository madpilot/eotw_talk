<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>World&rsquo;s PHP smallest framework<?php echo isset($vars['page_title']) ? ' - ' . $vars['page_title'] : '' ?></title>
    <?php if(isset($vars['keywords'])) { ?>
      <meta name="Keywords" content="<?php echo $vars['keywords']; ?>" />
    <?php } ?>
    <?php if(isset($vars['description'])) { ?>
      <meta name="Description" content="<?php echo $vars['description']; ?>" />
    <?php } ?>
    <script type="text/javascript" src="/javascripts/tiny_mce/tiny_mce.js"></script>
    <script type="text/javascript" src="/javascripts/application.js"></script>
    <link href="/stylesheets/default.css" media="all" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <div id="header">
      <h1>Junior &mdash; The world&rsquo;s smallest PHP framework</h1>
    </div>

    <div id="content">
      <?php if($flash = flash()) { ?>
        <div class="message"><?php echo $flash; ?></div>
      <?php } ?>
      <?php echo $content; ?>
    </div>
  </body>
</html>
