<html>
	<head>
		<title>Ê×Ò³</title>
	</head>
	<body>
		<a href="http://passport.bootsite.com/?redirect=<?php echo 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER['PHP_SELF']; ?>&client=bootsite">µÇÂ¼</a>
		<script type="text/javascript">
			var sso;   
			try {   
			  sso = document.createElement('<iframe name="sso">');   
			} catch (ex) {   
			  sso = document.createElement('iframe');   
			}   
			sso.name = "sso";
			sso.id = "sso";
			sso.width = 0;   
			sso.height = 0;   
			sso.marginHeight = 0;   
			sso.marginWidth = 0;   
			sso.setAttribute('style','display:none');
			sso.setAttribute('src','http://passport.bootsite.com/sso.php?domain=.bootsite.com&id=www.bootsite.com');
			document.body.appendChild(sso);
		</script>
	</body>
</html>