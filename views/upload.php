<form id="upload" enctype="multipart/form-data" action="/upload" method="post">
  <fieldset>
    <input name="file" type="file"/>
  </fieldset>

  <fieldset class="buttons">
    <button type="submit">Upload</button>
  </fieldset>
</form>

<div id="uploading">
  <div id="progress" class="bar">
    <div id="progressbar">&nbsp;</div>
    <div id="percents"></div>
  </div>
</div>

