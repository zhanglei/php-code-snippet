<html>
	<head>
		<title>��¼</title>
	</head>
	<body>
		<form action="login.php" method="POST">
			�ʺ�: <input type="text" name="username" value="" /><br/>
			����: <input type="password" name="password" value="" /><br/>
			<input type="hidden" name="redirect" value="<?php if(isset($_GET['redirect'])) echo $_GET['redirect'] ?>" />
			<input type="hidden" name="client" value="<?php if(isset($_GET['client'])) echo $_GET['client'] ?>" />
			<button type="submit">�ύ</button>
		</form>
	</body>
</html>