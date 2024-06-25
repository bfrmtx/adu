if (location.search != '') {
  var pos = location.search.slice(1).split("=")[0];
  var urlin = window.location.href;
  var url = urlin.slice(0).split('?')[0];
  window.location.href =  url + '#' + pos;
}
// when a js form is finally submitted as index.php?site_name=rhoden we want index.php#site_name to jump to the form
