tinyMCE.init({
  mode : "textareas",
  theme : "advanced",
  plugins: "spellchecker,paste,inlinepopups",
  theme_advanced_path : true,
  theme_advanced_toolbar_location : 'top',
  theme_advanced_buttons1: "spellchecker,separator,cut,copy,paste,separator,undo,redo,separator,pasteword,selectall,separator,charmap,code",
  theme_advanced_buttons2: "formatselect,separator,bold,italic,underline,separator,justifyleft,justifycenter,justifyright,separator,numlist,bullist,outdent,indent,separator,image,separator,link,unlink",
  theme_advanced_buttons3: "",
  theme_advanced_blockformats : "p,h1,h2,h3,h4,h5,h6",
  theme_advanced_toolbar_align : "left",
  theme_advanced_more_colors : 1,
  theme_advanced_row_height : 23,
  theme_advanced_resizing: true,
  theme_advanced_resize_horizontal : 0,
  theme_advanced_resizing_use_cookie : 1,
  theme_advanced_path_location: "bottom",
  relative_urls : false,
  remove_script_host : false,
  cleanup: true,
  spellchecker_languages: "+English=en",
  spellchecker_rpc_url: "/pages/spellcheck"
});

$(document).ready(function($) {
  $(function() {
      $('form').uploadProgress({
        /* scripts locations for safari */
        jqueryPath: "/javascripts/jquery.js",
        uploadProgressPath: "/javascripts/jquery.uploadProgress.js",
        /* function called each time bar is updated */
        uploading: function(upload) {$('#percents').html(upload.percents+'%');},
        /* selector or element that will be updated */
        progressBar: "#progressbar",
        /* progress reports url */
        progressUrl: "/upload/progress",
        /* how often will bar be updated */
        interval: 1000
      });
  });
});

