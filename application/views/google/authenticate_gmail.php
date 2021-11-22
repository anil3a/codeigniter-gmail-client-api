
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
                <?php if ($hasGoogleTokenInvalid) : ?>
                  <a href="/google/remove_access_token" class="btn btn-warning">Click here to REMOVE Invalid access token</a>
                <?php elseif(!$hasGoogleToken): ?>
                  <a href="<?php print $urlToAuth;?>" class="btn btn-success">Click here to authorize application</a>
                <?php else: ?>
                  <a href="/google/remove_access_token" class="btn btn-warning">Click here to REMOVE access token</a>
                <?php endif; ?>
              </div>

            </div>
            <br />
            <br />
            <div>
              <label for="urls_to_add_to_google_project_for_authentication">Get API from Google Credentials</label>
              <div id="urls_to_add_to_google_project_for_authentication">
                <span>IMPORTANT NOTE: SSL is required in adding URLs to Google project</span>
                <ul>
                  <li>Authorized Origin url :  <?php echo site_url()?></li> 
                  <li>Authorized redirect URI : <?php echo site_url('google/oauthcallback')?></li>
                </ul>
            </div>
            <br />
            <br />
            <br />
            <br />
            <br />
            <br />
            <div>
              <label for="link_to_google_project">Get API from Google Credentials</label>
              <div id="link_to_google_project">
                <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="btn btn-default">Click here to get API from Google Credentials</a>
            </div>
            <br />
            <div>
              <label for="link_to_see_google_app_in_your_account">Manage Apps in your account that has access</label>
              <div id="link_to_see_google_app_in_your_account">
                <a href="https://myaccount.google.com/permissions" target="_blank" class="btn btn-default">Google Account Authorised Apps</a>
            </div>
            <br />
        </div>

    </div>
</div>