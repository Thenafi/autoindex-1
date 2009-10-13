<? 

  function echo_tr() {
    global $grey_toggle;
    
    if ($grey_toggle == true) $grey_toggle = false;
    else $grey_toggle = true;
    
    echo "<tr class=\"" . ($grey_toggle ? "lighterbg" : "darkerbg") . "\">\n";
  }
  
  function join_paths() {
      $args = func_get_args();
      $paths = array();
      foreach ($args as $arg) {
          $paths = array_merge($paths, (array)$arg);
      }
      foreach ($paths as &$path) {
          $path = trim($path, '/');
      }
      return "/".join('/', $paths);
  }

  function in_excludes($s) {
    //$excludes = array("README.txt", "README.html", ".git", ".gitignore", ".svn");
    $excludes = array(".git", ".gitignore", ".svn");
    foreach ($excludes as $entry)
    {
      if($s == $entry) return true;
    }
    
    return false;
  }

  function list_dir($root) {
    global $script_path;
    
    $files = array();
    $dirs = array();
    $dir = opendir($root);
    while ( ($entry = readdir($dir)) !== false ) {
      if (in_excludes($entry)) continue;
      
      $full_path = join_paths($root, $entry); 
      //echo "$full_path<br>";
      
      if (is_dir($full_path) && $full_path != $script_path) {
        $dirs[] = $entry;
      }
      if (is_file($full_path)) {
        // if (strtolower(substr($entry, strlen($entry)-4, 4)) == ".mp3")
        
        $files[] = array(
          "name"=>$entry, 
          "size"=>filesize($full_path), 
          "path"=>$full_path 
        );
      }
    }
      
    closedir($dir);

    sort($files);
    sort($dirs);
    reset($files);
    reset($dirs);

    //print_r(array($files,$dirs));
    return array($files, $dirs);
  }


  $root            = $_SERVER["DOCUMENT_ROOT"]; 
  $location        = $_SERVER["REQUEST_URI"]; 
  $script_location = str_replace("index.php", "", $_SERVER["PHP_SELF"]); 
  $script_path     = join_paths($root, $script_location); 
  $path            = join_paths($root, $location);
  //echo "path = $path<br>";
  //echo "script_path = $script_path<br>";

?>  



<title>Directory listing of <?= $location ?></title>
<link href="/autoindex/default.css" rel="stylesheet" type="text/css">
<body class="bodystyles">

<?= "<h2 class=\"descfont\">Directory listing of: $location</h2>" ?>

<table cellpadding="3">
<tr class="headerbg">
<td>&nbsp;</td>
<td class="headerfont" ><b>Filename</b></td>
<td align="right" class="headerfont"><b>Size</b></td>
</tr>

<?

  if ( file_exists($readme = join_paths($path, "README.html")) ) {
    include($readme);
    echo "<br><br>";
  }
  if ( file_exists($readme = join_paths($path, "README.txt")) ) {
    echo "<pre>";
    include($readme);
    echo "</pre><br>";
  }
  
  $img_file = "/autoindex/file.gif";
  $img_folder = "/autoindex/folder.gif";
  $grey_toggle = true;
  
  list($files, $dirs) = list_dir($path);
  
  
  // Display list of directories:

  if (!empty($dirs))
  {
    for ($i = 0; $i < sizeof($dirs); $i++) {
      
      $dirdesc = $dirs[$i];
      
      if ($dirdesc != ".") {
        echo_tr();
        
        if ($dirdesc == "..")
          $dirdesc = "[ Previous Directory ]";
        
        echo "<td><img src=\"$img_folder\"></td>\n";
        echo "<td class=\"dirfont\"><a href=\"{$dirs[$i]}\">$dirdesc</a></td>\n";
        echo "<td class=\"dirfont\" align=\"right\">&lt;DIR&gt;</td>\n";
      }
    }
  }
	
  // Display list of files:
      
  if (!empty($files))
  {
    foreach ($files as $file) {
    //for ($i = 0; $i < sizeof($files); $i++) {
      echo_tr();
      
      echo "<td><img src=\"$img_file\"></td>\n";
      echo "<td class=\"filefont\"><a href=\"{$file["name"]}\">{$file["name"]}</a></td>\n";
      echo "<td align=\"right\" class=\"filefont\">" . number_format($file["size"]) . "&nbsp;</td>\n";
    }
  }
?>