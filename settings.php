<?php

require_once '/home/fpp/media/plugins/fpp-website-jukebox/common.php';

function validateApiKey(string $apiKey)
{
    WriteSettingToFile(API_KEY, $apiKey, JUKEBOX_PLUGIN_NAME);
    return true;
}

function validateWebPollTime(int $seconds)
{
    if ($seconds > 0) {
        WriteSettingToFile(WEB_POLL_TIME, $seconds, JUKEBOX_PLUGIN_NAME);
        return true;
    }

    return "Poll time must be greater than zero";
}

function validateWebsiteEndpoint(string $websiteUri)
{
    if (filter_var($websiteUri, FILTER_VALIDATE_URL) !== false) {
        WriteSettingToFile(WEBSITE_ENDPOINT, $websiteUri, JUKEBOX_PLUGIN_NAME);
        return true;
    }

    return "Please enter a valid wesite endpoint URL";
}

$errors = array();
if (!empty($_POST)) {
    array_push($errors, validateApiKey($_POST[API_KEY]));
    array_push($errors, validateWebPollTime($_POST[WEB_POLL_TIME]));
}
?>

<html>

<body>
  <?php
  $succeeded = 0;
  foreach ($errors as $error) {
      if ($error !== true && !empty($error)) {
          echo "<div class='p-1 alert bg-danger text-white font-weight-bold'>" . $error . "</div>";
          continue;
      }
      $succeeded++;
  }

  if (!empty($_POST) && sizeof($errors) == $succeeded) {
      echo "<div class='p-1 alert bg-success text-white font-weight-bold'>Configuration saved successfully.</div>";
  }
  ?>

  <div class="row my-3">
    <div class="col-md-2 text-center">Donate</div>
    <div class="col-md">
      Enjoy using this plugin? Please consider making a donation to support the future development of this plugin.
      <div>
        <a href="https://www.paypal.com/donate/?hosted_button_id=GXFQ3GT6DRZFN" target="_blank">
          <button class="buttons">Make Donation</button></a>
      </div>
    </div>
  </div>

  <form method="post">
    <div class="row my-3">
      <div class="col-md-2 text-center">
        Website Endpoint
      </div>
      <div class="col-md">
          <input type="text" name="<?php echo WEBSITE_ENDPOINT; ?>"
            value="<?php echo ReadSettingFromFile(WEBSITE_ENDPOINT, JUKEBOX_PLUGIN_NAME); ?>" required="required" />
        <div class="text-muted">
          Enter the URL on your website.
        </div>
      </div>
    </div>

    <div class="row my-3">
      <div class="col-md-2 text-center">
        Website API Key
      </div>
      <div class="col-md">
          <input type="password" name="<?php echo API_KEY; ?>"
            value="<?php echo ReadSettingFromFile(API_KEY, JUKEBOX_PLUGIN_NAME); ?>" required="required" />
        <div class="text-muted">
          Enter the API key that you have defined with the website configuration. The longer the key, the better.
        </div>
      </div>
    </div>

    <div class="row my-3">
      <div class="col-md-2 text-center">
        Poll Time
      </div>
      <div class="col-md">
          <input type="number" name="<?php echo WEB_POLL_TIME; ?>"
            value="<?php echo ReadSettingFromFile(WEB_POLL_TIME, JUKEBOX_PLUGIN_NAME); ?>" required="required" />
        <div class="text-muted">
          Enter the number of seconds before the end of the song to check for queued song requests.
        </div>
      </div>
    </div>

    <button class="buttons my-3" type="submit">Save Settings</button>
  </form>
</body>

</html>