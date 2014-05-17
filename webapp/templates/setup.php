<div class="row">
  <div class="large-7 small-centered columns">
	  
	  <h1>Setup a new bubblechrip</h1>

	  <div class="panel">	
	  <ol>
		  <li>Setup a <a href="http://bergcloud.com/devcenter/">new project</a> in the dev centre.</li>
		  <li>Download and upload the Ardunio code to your device. The device claimcode can be found in the serial monitor.</li>
		  <li><a href="http://bergcloud.com/devcenter/projects/">Claim the device</a> in the dev centre.</li>			  
		  <li>Enter your email and select a password.</li>
		  <li>Paste in your Device ID.</li>
	  </ol>
	  </div>
	  
	  <form action="/setup" method="post" accept-charset="utf-8">
	  <div class="row">
	    <div class="large-6 columns">
	      <label>Email</label>
	      <input type="text" name="email" placeholder="me@you.com" />
	    </div>
	  </div>

	  <div class="row">
	    <div class="large-6 columns">
	      <label>Password</label>
	      <input type="password" name="password"/>
	    </div>
	  </div>
	  
	  <div class="row">
	    <div class="large-6 columns">
	      <label>Device ID</label>
	      <input type="text" name="device-id" placeholder="e.g. 063dbe21f6a6d0a1" />
	    </div>
	  </div>

	  <div class="row">
	    <div class="large-6 columns">
			<input type="submit" class="medium primary button" value="Go" /></p>
	    </div>
	  </div>
	  </form>
	</div>
</div>