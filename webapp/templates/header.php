<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BubbleChirp | Welcome</title>
    <link rel="stylesheet" href="css/foundation.min.css" />
    <link rel="stylesheet" href="css/normalize.css" />	
    <link rel="stylesheet" href="css/style.css" />
    <script src="js/vendor/modernizr.js"></script>
  </head>
  <body>
	  <header>
		  <div class="row">		  
		  	<h1><a href="/">BubbleChirp</a></h1>
		</div>		 
	  </header>
	  <?php if($user) { ?>	  
	  <div class="user">		 
		  <div class="row">
		  <div class="large-12 textalign-right">
			  <p>Logged in as <?php echo $user?> <a href="/logout">Logout?</a></p> 
		  </div>
	  	</div>
	  </div>
	  <?php }?>	  
	  <div class="body">