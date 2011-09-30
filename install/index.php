<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 5.0//EN" "http://www.w3.org/TR/html5/strict.dtd">
<html>  
<head>  
	<title>Install Sappho DBMS</title>  
	<link href="style.css" rel="stylesheet" type="text/css">
	<link href="../css/jquery-ui-1.8.15.custom.css" rel="stylesheet" text="text/css">
	<link href="../css/jquery.treeview.css" rel="stylesheet" text="text/css">
	<script language="javascript" src="../js/jquery-1.6.2.js"></script>
	<script language="javascript" src="../js/jquery-ui-1.8.15.custom.min.js"></script>
	<script language="javascript" src="../js/jquery.treeview.js"></script>
</head>  
<body>  
	<!-- JQUERY -->
	<script>
		$(function(){
			function checkDBConnection(){
				$.ajax({
					url: "checkdb.php",
					type: "POST",
					data: {host: $("#db-host").val(),
					       name: $("#db-name").val(),
						   user: $("#db-user").val(),
						   password: $("#db-password").val(),
						   type: $("#db-type").val()},
					dataType: "json",
					success: function(req){
								if(req.state == "OK")
								{
									$("#db-ok").val("true");
									$("#progress").progressbar("value", 16);
									$("#db-info").html("<p class='notification' style='background: #00ff00'>Database check OK!</p>");
								}
								else
								{
									$("#db-ok").val(false);
									$("#db-info").html("<p class='notification' style='background: #ff0000'>Could not connect database - "+req.error_message+"</p>");
								}
							},
					error: function(x, text, thr){
							$("#db-ok").val(false);
							$("#db-info").html("<p class='notification' style='background: #ff0000'>Error: "+text+" - "+thr+"</p>");
						   }						   
				});
			}
			
			function dbNextStep(){
				if($("#db-ok").val() != "true")
				{
					$("#db-info").html("<p class='notification' style='background: #ffff00'>Please check if your connection works fist!</p>");
					return;
				}
				$("#installsteps").accordion("activate", 1);
				$("#progress").progressbar("value", 33);
			}
			
			function generateDatabase(){
				if($("#db-ok").val() != "true")
				{
					$("#gendb-info").html("<p class='notification' style='background: #ff0000'>Complete step 1 before generating the database!</p>");
					return;
				}
				
				$.ajax({
					url: "gendb.php",
					type: "POST",
					data: {host: $("#db-host").val(),
					       name: $("#db-name").val(),
						   user: $("#db-user").val(),
						   password: $("#db-password").val(),
						   type: $("#db-type").val()},
					dataType: "json",
					success: function(req){
								if(req.state == "OK")
								{
									$("#gendb-ok").val("true");
									$("#progress").progressbar("value", 50);
									$("#gendb-info").html("<p class='notification' style='background: #00ff00'>Database generated.</p>");
								}
								else
								{
									$("#gendb-ok").val("false");
									$("#gendb-info").html("<p class='notification' style='background: #ff0000'>Could not generate database - "+req.error_message+"</p>");
								}
							},
					error: function(x, text, thr){
							$("#gendb-ok").val("false");
							$("#gendb-info").html("<p class='notification' style='background: #ff0000'>Error: "+text+" - "+thr+"</p>");
						   }						   
				});
			}
			
			function gendbNextStep(){
				if($("#gendb-ok").val() != "true")
				{
					$("#gendb-info").html("<p class='notification' style='background: #ffff00'>Please genereate the database fist!</p>");
					return;
				}
				$("#installsteps").accordion("activate", 2);
				$("#progress").progressbar("value", 66);
			}
			
			function createUser(){
				if($("#gendb-ok").val() != "true")
				{
					$("#user-info").html("<p class='notification' style='background: #ffff00'>Please complete all former steps!</p>");
					return;
				}
				
				$.ajax({
					url: "cruser.php",
					type: "POST",
					data: {host: $("#db-host").val(),
					       name: $("#db-name").val(),
						   user: $("#db-user").val(),
						   password: $("#db-password").val(),
						   type: $("#db-type").val(),
						   auser: $("#user-name").val(),
						   apwd: $("#user-password").val()},
					dataType: "json",
					success: function(req){
								//alert(req);
								if(req.state == "OK")
								{
									$("#user-ok").val("true");
									$("#progress").progressbar("value", 90);
									$("#user-info").html("<p class='notification' style='background: #00ff00'>User created.</p>");
								}
								else
								{
									$("#user-ok").val("false");
									$("#user-info").html("<p class='notification' style='background: #ff0000'>Could not create user - "+req.error_message+"</p>");
								}
							},
					error: function(x, text, thr){
							//alert(x);
							$("#user-ok").val("false");
							$("#user-info").html("<p class='notification' style='background: #ff0000'>Error: "+text+" - "+thr+"</p>");
						   }
				});
			}

			function userNextStep(){
				if($("#user-ok").val() != "true")
				{
					$("#user-info").html("<p class='notification' style='background: #ffff00'>You have to create an admin user!</p>");
					return;
				}
				
				// write config here
			}
			
			$("#installsteps").accordion({autoHeight: false});
			$("input:button").button();
			$("#progress").progressbar({value: 0});
			
			$("#db-chk").click(checkDBConnection);
			$("#db-next-step").click(dbNextStep);
			$("#gendb-button").click(generateDatabase);
			$("#gendb-next-step").click(gendbNextStep);
			$("#user-button").click(createUser);
			
			$("#db-ok").val("false");
			$("#gendb-ok").val("false");
			$("#user-ok").val("false");
		});
	</script>
	
	<!-- PAGE CONTENT -->
	<header>
		<h1>Install Sappho DMS</h1>
	</header>
	<section class="installframe">
		<h3>Information</h3>
		<p>Complete each of the steps below and press the install-button afterwards. The installation routine
		   tries to configure Sappho to run on your server with the given settings.</p>
		   
		<h3>Progress</h3>
		<div id="progress"></div>
		
		<h3>Installation Steps</h3>
		 <div id="installsteps">
			<h3><a href="#">1 - Database Settings</a></h3>
			<div>
				<p>Please enter your database settings.</p>
				<form>
					<table>
						<tr>
							<th>
								Host:
							</th>
							<td>
								<input type="text" id="db-host" />
							</td>
							<td rowspan="3" align="center" style="width:100%;">
								<input type="button" id="db-chk" value="check database connection" />
							</td>
						</tr>
						<tr>
							<th>
								Database Name:
							</th>
							<td>
								<input type="text" id="db-name" />
							</td>
						</tr>
						<tr>
							<th>
								User:
							</th>
							<td>
								<input type="text" id="db-user" />
							</td>
						</tr>
						<tr>
							<th>
								Password:
							</th>
							<td>
								<input type="password" id="db-password" />
							</td>
							<td align="center">
								<input type="button" id="db-next-step" value="next step" />
							</td>
						</tr>
						<tr>
						    <th>Type: </th>
						    <td>
								<select id="db-type">
									<option value="mysql">MySQL</option>
									<option value="postgre">PostgreSQL</option>
								</select>
							</td>
							<td align="center">
								<div id="db-info"><p>&nbsp;</p></div>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<h3><a href="#">2 - Create Database</a></h3>
			<div>
				<p>By clicking the button below the database will be created from scratch.
				   Please mind that <b><font color="#ff0000">all existing data</font></b> in the selected database <b><font color="#ff0000">will be lost!</font></b></p>
				<p align="center">
					<table align="center">
						<tr>
							<td>
								<input type="button" value="generate database" id="gendb-button"/>
							</td>
							<td>
								<input type="button" value="next step" id="gendb-next-step"/>
							</td>
						</tr>
						<tr>
							<td align="center" colspan="2"><div id="gendb-info"></div></td>
						</tr>
					</table>
				</p>
			</div>
			<h3><a href="#">3 - Administrator User</a></h3>
			<div>
				<p>This step will create an adminstrative user for the DMS.</p>
				<p>
					<table align="center">
						<tr>
							<th>
								Username:
							</th>
							<td>
								<input type="text" id="user-name" value="admin" />
							</td>
						</tr>
						<tr>
							<th>
								Password:
							</th>
							<td>
								<input type="password" id="user-password" />
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" id="user-button" value="create user"/>
							</td>
							<td>
								<input type="button" id="user-next-step" value="next step"/>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div id="user-info"></div>
							</td>
						</tr>
					</table>
				</p>
			</div>
		 </div>
		 <input type="hidden" id="db-ok" value="false"/>
		 <input type="hidden" id="gendb-ok" value="false"/>
		 <input type="hidden" id="user-ok" value="false"/>
	</section>
</body>