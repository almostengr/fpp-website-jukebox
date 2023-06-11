<?php

require_once '/home/fpp/media/plugins/fpp-website-jukebox/source/SettingService.php';

$settingRepository = new $settingRepository();
$settingService = new $settingService($settingRepository);

$errors = array();
if (!empty($_POST)) {
  array_push($errors, $settingService->createUpdateSetting(API_KEY, $_POST[API_KEY]));
  array_push($errors, $settingService->createUpdateSetting(POLL_ERR, $_POST[POLL_TIME]));
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
        <input type="text" name="<?php echo WEBSITE_ENDPOINT; ?>" value="<?php echo $settingService->getSetting(WEBSITE_ENDPOINT); ?>"
          required="required" />
        <div class="text-muted">
          Enter the URL on your website to the "jukeboxapi.php" file.
        </div>
      </div>
    </div>

    <div class="row my-3">
      <div class="col-md-2 text-center">
        Website API Key
      </div>
      <div class="col-md">
        <input type="password" name="<?php echo API_KEY; ?>" value="<?php echo $settingService->getSetting(API_KEY); ?>" required="required" />
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
        <input type="number" name="<?php echo POLL_TIME; ?>" value="<?php echo $settingService->getSetting(POLL_TIME); ?>" required="required" />
        <div class="text-muted">
          Enter the number of seconds before the end of the song to check for queued song requests.
        </div>
      </div>
    </div>

    <button class="buttons my-3" type="submit">Save Settings</button>
  </form>
</body>

</html>