<form method="post" action="/pages/<?php echo $page['id']; ?>/edit">
  <fieldset>
    <label>
      <span class="text_label">Title</span>
      <input type="text" name="page[title]" value="<?php echo $page['title']; ?>" />
    </label>

    <label>
      <span class="text_label">Permalink</span>
      <input type="text" name="page[permalink]" value="<?php echo $page['permalink']; ?>" />
    </label>

    <label>
      <span class="text_label">Content</span>
      <textarea name="page[copy]" cols="80" rows="20"><?php echo $page['copy']; ?></textarea>
    </label>
  </fieldset>

  <fieldset class="buttons">
    <button type="submit">Update</button>
    or <a href="/<?php echo $page['permalink']; ?>">Cancel</a>
  </fieldset>
</form>
