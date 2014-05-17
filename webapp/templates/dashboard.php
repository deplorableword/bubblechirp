<div class="row">
  <div class="large-7 small-centered columns">
	  
	<h2>Settings for your BubbleChirp</h2>
	
	<p>Device id: <?=$device_id?></p>
	
	<hr />
	
	<h3>Quickfire</h3>

	<div class="large-7">
		<form action="/api/test/" method="post">
			<input type="submit" class="button secondary small" value="Fire" />
		</form>
	</div>
	
	<hr />
	 		  
	<h3>Twitter</h3>
	
	<?php if ($tracking == 1){ ?>	
	<p>You are currently connected to Twitter.
		<?php if ($hashtag){?>
		 Tracking <a href="https://twitter.com/search?src=typd&q=<?=$hashtag?>"><?=$hashtag?>.</a>
		<?php } else { ?>
		Enter a hashtag to start tracking.
		<?php }?>
				
		<div class="large-12">		
			<form action="/api/hashtag/update/" method="post" class="row collapse">
				<div class="small-1 columns">
					<span class="prefix">#</span>
				</div>
				<div class="small-9 columns">
					<input type="text" name="hashtag" id="right-label" placeholder="<?=$hashtag?>">
				</div>
				<div class="small-2 columns">
					<input type="submit" class="button  postfix"  value="Update" />
				</div>
			</form>
		</div>
		
	<p><a href="/api/twitter/disconnect" class="button small">Stop</a></p>	  

	 <?php } else { ?>
	 	<div>
			<p>BubbleChirp has <strong>stopped</strong> tracking.
			
			<p><a href="/api/twitter/auth" class="button small">Start</a></p>
	 	</div>
	<?php } ?>
		 
	  
	</div>
</div>
