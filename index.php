<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 5.0//EN" "http://www.w3.org/TR/html5/strict.dtd">
<?php
	session_start();
	
	include("open_script.php");
	
	if(!isset($_GET["view"])) $_GET["view"] = "dashboard";
?>
<html>  
<head>  
	<title>Sappho - Interactive Document Management</title>  
	<link href="css/style.css" rel="stylesheet" type="text/css">
	<link href="css/jquery-ui-1.8.15.custom.css" rel="stylesheet" text="text/css">
	<link href="css/jquery.treeview.css" rel="stylesheet" text="text/css">
	<script language="javascript" src="js/jquery-1.6.2.js"></script>
	<script language="javascript" src="js/jquery-ui-1.8.15.custom.min.js"></script>
	<script language="javascript" src="js/jquery.treeview.js"></script>
	
	<!-- JQUERY -->
	<script>
		$(function(){
			function doLogout(){
				$.post("actions/logout.php",{}, function(data){
												location.reload();
												return true;
											});
			}
			
			$("#logout_link").click(doLogout);
		});
	</script>
</head>  
<body>  
	<header> 
		<h1>Sappho: Document Repository</h1>  
	</header>  
	<nav>  
		<ul>
			<li><a href="index.php?view=dashboard">Dashboard</a></li>
			<li><a href="index.php?view=repository">Repository</a></li>
			<li><a href="index.php?view=settings">Settings</a></li>
			<li><a href="index.php?view=about">Search</a><li>
			<?php 
				if(isset($_SESSION["logged_in"]))
					echo '<li><a href="#" id="logout_link">Logout</a></li>';
			?>
		</ul>
	</nav>   
	<section id="contentBox">
		<div id="content">
			<?php
				if($_GET["view"] == "admin"){include("views/admin.html");}
				else if(!isset($_SESSION["logged_in"])){include("views/login.html");}
				else if($_SESSION["logged_in"] != 1){include("views/login.html");}
				else if($_GET["view"] == "repository"){ include("views/document_view.html"); }
                else if($_GET["view"] == "dashboard"){ include("views/dashboard_view.html"); }
				else if($_GET["view"] == "settings"){ include("views/settings_view.html"); }
				else if($_GET["view"] == "search"){ include("search_view.html"); }
			?>
				 
		</div>
	</section>	
	<footer>  
		<p>Sappho Document Management System (c) 2011 by Daniel Eder
		   <?php if(isset($_SESSION["logged_in"]))
					echo '| <a href="index.php?view=admin">Admin</a>';?></p>
	</footer>  
</body>  
</html>
<?php
	mysql_close_db();
?>
