<?php if($query == "") { ?>
  <h1>Search</h1>
<?php } else { ?>
  <h1>Search results for &ldquo;<?php echo h($query) ?>&rdquo;</h1>
<?php } ?>

<form method="get" action="/search">
  <fieldset class="search">
    <legend>Enter your search terms below</legend>
    <label>
      <span class="text_label">Search</span>
      <input type="text" name="q" value="<?php echo h($query) ?>" />
    </label>
    <button class="search" type="submit">Search</button>
  </fieldset>
</form>

<?php if(($suggestions = spelling_suggestions($query)) != "") { ?>
  <p><?php echo $suggestions; ?></p>
<?php } ?>

<?php if($query != "") { ?>
  <?php if(count($results) == 0) { ?>
    <p>No search results where found. Please try a different search term</p>  
  <?php } else { ?>
    <ol>
      <?php foreach($results as $result) { ?>
        <li>
          <h2><a href="/<?php echo $result['permalink']; ?>"><?php echo $result['title'] ?></a></h2>
          <p><?php echo $result['abstract']; ?></p>
          
          <div class="toolbox">
            <a href="/<?php echo $result['permalink']; ?>">http://<?php echo $_SERVER['SERVER_NAME']; ?>/<?php echo $result['permalink'] ?>
          </div>
        </li>
      <?php } ?>
    </ol>
  <?php } ?>
<?php } ?>
