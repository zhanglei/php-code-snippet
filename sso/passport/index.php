<html>
	<head>
		<title>µÇÂ¼</title>
	</head>
	<body>
		<form action="login.php" method="POST">
			ÕÊºÅ: <input type="text" name="username" value="" /><br/>
			ÃÜÂë: <input type="password" name="password" value="" /><br/>
			<input type="hidden" name="redirect" value="<?php if(isset($_GET['redirect'])) echo $_GET['redirect'] ?>" />
			<input type="hidden" name="client" value="<?php if(isset($_GET['client'])) echo $_GET['client'] ?>" />
			<button type="submit">Ìá½»</button>
		</form>
	</body>
</html>