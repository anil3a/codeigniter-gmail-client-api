
<div class="container">


    <div class="row">

        <div class="col-md-11 col-center-align">
            <br><br>

            <h1>Google Emails</h1>

            <br>
            <br>

            <div class="emails">

            	<label for="gmailaccesstoken">Grant Google Access Token</label>
				<div id="gmailaccesstoken">
					<?php if (!$hasGoogleToken) : ?>
	                  <a href="<?php print $urlToAuth;?>" class="btn btn-success">Click here to authorize application</a>
	                <?php else: ?>
	                  <a href="/google/remove_access_token" class="btn btn-warning">Click here to REMOVE access token</a>
	                <?php endif; ?>
				</div>

            </div>
        </div>

    </div>
</div>