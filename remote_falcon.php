<?php
include_once "/opt/fpp/www/common.php";
include_once "/home/fpp/media/plugins/remote-falcon/baseurl.php";
include("/home/fpp/media/plugins/remote-falcon/plugin_version.php");
$baseUrl = getBaseUrl();
$rfSequencesUrl = getBaseUrlDomain() . "/controlPanel/sequences";
$pluginName = basename(dirname(__FILE__));
$pluginConfigFile = $settings['configDirectory'] ."/plugin." .$pluginName;
    
if (file_exists($pluginConfigFile)) {
  $pluginSettings = parse_ini_file($pluginConfigFile);
}

$pluginVersion = urldecode($pluginSettings['pluginVersion']);

foreach ($pluginSettings as $key => $value) { 
  ${$key} = urldecode($value);
}

$remoteFppEnabled = urldecode($pluginSettings['remote_fpp_enabled']);
$remoteFppEnabled = $remoteFppEnabled == "true" ? true : false;

$url = "http://127.0.0.1/api/plugin/remote-falcon/updates";
$options = array(
  'http' => array(
    'method'  => 'POST',
    'header'=>  "Content-Type: application/json; charset=UTF-8\r\n" .
                "Accept: application/json\r\n"
    )
);
$context = stream_context_create( $options );
$result = file_get_contents( $url, false, $context );
$response = json_decode( $result, true );
if ($response['updatesAvailable'] == 1) {
  $showUpdateDiv = "display:block";
}else{
  $showUpdateDiv = "display:none";
}

$playlistDirectory= $settings['playlistDirectory'];
$playlistOptions = "";
if(is_dir($playlistDirectory)) {
  if ($dirTemp = opendir($playlistDirectory)){
    while (($fileRead = readdir($dirTemp)) !== false) {
      if (($fileRead == ".") || ($fileRead == "..")){
        continue;
      }
      $fileRead = pathinfo($fileRead, PATHINFO_FILENAME);
      $playlistOptions .= "<option value=\"{$fileRead}\">{$fileRead}</option>";
    }
    closedir($dirTemp);
  }
}

$playlists = "";
if (isset($_POST['updateRemotePlaylist'])) {
  $remotePlaylist = trim($_POST['remotePlaylist']);
  if (strlen($remotePlaylist)>=2){
    if(strlen($remoteToken)>1) {
      $playlists = array();
      $remotePlaylistEncoded = rawurlencode($remotePlaylist);
      $url = "http://127.0.0.1/api/playlist/${remotePlaylistEncoded}";
      $options = array(
        'http' => array(
          'method'  => 'GET'
          )
      );
      $context = stream_context_create( $options );
      $result = file_get_contents( $url, false, $context );
      $response = json_decode( $result, true );
      $mainPlaylist = $response['mainPlaylist'];
      $index = 1;
      foreach($mainPlaylist as $item) {
        if($item['type'] === 'both' || $item['type'] === 'sequence') {
          $playlist = null;
          $playlist->playlistName = pathinfo($item['sequenceName'], PATHINFO_FILENAME);
          $playlist->playlistDuration = $item['duration'];
          $playlist->playlistType = 'SEQUENCE';
          $playlist->playlistIndex = $index;
          array_push($playlists, $playlist);
        }else if($item['type'] === 'media') {
          $playlist = null;
          $playlist->playlistName = pathinfo($item['mediaName'], PATHINFO_FILENAME);
          $playlist->playlistDuration = $item['duration'];
          $playlist->playlistType = 'SEQUENCE';
          $playlist->playlistIndex = $index;
          array_push($playlists, $playlist);
        }else if($item['type'] === 'command' && $item['note'] != null && $item['note'] != "") {
          $playlist = null;
          $playlist->playlistName = $item['note'];
          $playlist->playlistDuration = 0;
          $playlist->playlistType = 'COMMAND';
          $playlist->playlistIndex = $index;
          array_push($playlists, $playlist);
        }
        $index++;
      }
      $url = $baseUrl . "/syncPlaylists";
      $data = array(
        'playlists' => $playlists
      );
      $options = array(
        'http' => array(
          'method'  => 'POST',
          'content' => json_encode( $data ),
          'header'=>  "Content-Type: application/json; charset=UTF-8\r\n" .
                      "Accept: application/json\r\n" .
                      "remotetoken: $remoteToken\r\n"
          )
      );
      $context = stream_context_create( $options );
      $result = file_get_contents( $url, false, $context );
      $response = json_decode( $result );
      if($response) {
        WriteSettingToFile("remotePlaylist",$remotePlaylist,$pluginName);
        echo "<script type=\"text/javascript\">$.jGrowl('Remote Playlist Updated!',{themeState:'success'});</script>";
      }else {
        echo "<script type=\"text/javascript\">$.jGrowl('Remote Playlist Update Failed!',{themeState:'danger'});</script>";
      }
    }else {
      echo "<script type=\"text/javascript\">$.jGrowl('Remote Token Not Found!',{themeState:'danger'});</script>";
    }
  }else {
    echo "<script type=\"text/javascript\">$.jGrowl('No Playlist was Selected!',{themeState:'danger'});</script>";
  }
}

$remoteFalconState = "<h4 id=\"remoteFalconRunning\">Remote Falcon is currently running</h4>";
if($remoteFppEnabled == 0) {
  $remoteFalconState = "<h4 id=\"remoteFalconStopped\">Remote Falcon is currently stopped</h4>";
}

if (isset($_POST['updateRemoteToken'])) { 
  $remoteToken = trim($_POST['remoteToken']);
  WriteSettingToFile("remoteToken",$remoteToken,$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Remote Token Updated',{themeState:'success'});</script>";
}

if (isset($_POST['updateRequestFetchTime'])) { 
  $requestFetchTime = trim($_POST['requestFetchTime']);
  if($requestFetchTime >= 1 && $requestFetchTime <= 5) {
    WriteSettingToFile("requestFetchTime",$requestFetchTime,$pluginName);
    echo "<script type=\"text/javascript\">$.jGrowl('Request Fetch Time Updated',{themeState:'success'});</script>";
  }else {
    echo "<script type=\"text/javascript\">$.jGrowl('Must be between 1 and 5',{themeState:'danger'});</script>";
  }
}

if (isset($_POST['updateAdditionalWaitTime'])) { 
  $additionalWaitTime = trim($_POST['additionalWaitTime']);
  if($additionalWaitTime <= 5) {
    WriteSettingToFile("additionalWaitTime",$additionalWaitTime,$pluginName);
    echo "<script type=\"text/javascript\">$.jGrowl('Additional Wait Time Updated',{themeState:'success'});</script>";
  }else {
    echo "<script type=\"text/javascript\">$.jGrowl('Must be less than 5',{themeState:'danger'});</script>";
  }
}

if (isset($_POST['updateFppStatusCheckTime'])) { 
  $requestFetchTime = urldecode($pluginSettings['requestFetchTime']);
  $fppStatusCheckTime = trim($_POST['fppStatusCheckTime']);
  if($fppStatusCheckTime > 0 && $fppStatusCheckTime < $requestFetchTime) {
    WriteSettingToFile("fppStatusCheckTime",$fppStatusCheckTime,$pluginName);
    echo "<script type=\"text/javascript\">$.jGrowl('FPP Status Check Time Updated',{themeState:'success'});</script>";
  }else {
    echo "<script type=\"text/javascript\">$.jGrowl('Must be between 1 and Request Fetch Time',{themeState:'danger'});</script>";
  }
}

$interruptSchedule = urldecode($pluginSettings['interrupt_schedule_enabled']);
$interruptSchedule = $interruptSchedule == "true" ? true : false;

if($interruptSchedule == 1) {
  $interruptYes = "btn-primary";
  $interruptNo = "btn-secondary";
}else {
  $interruptYes = "btn-secondary";
  $interruptNo = "btn-primary";
}
if (isset($_POST['interruptScheduleYes'])) {
  $interruptYes = "btn-primary";
  $interruptNo = "btn-secondary";
  WriteSettingToFile("interrupt_schedule_enabled",urlencode("true"),$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Interrupt Schedule On',{themeState:'success'});</script>";
}
if (isset($_POST['interruptScheduleNo'])) {
  $interruptYes = "btn-secondary";
  $interruptNo = "btn-primary";
  WriteSettingToFile("interrupt_schedule_enabled",urlencode("false"),$pluginName);
  echo "<script type=\"text/javascript\">$.jGrowl('Interrupt Schedule Off',{themeState:'success'});</script>";
}

if (isset($_POST['restartRemoteFalcon'])) {
  $remoteFalconState = "<h4 id=\"remoteFalconRunning\">Remote Falcon is currently running</h4>";
  WriteSettingToFile("remote_fpp_enabled",urlencode("false"),$pluginName);
  WriteSettingToFile("remote_fpp_restarting",urlencode("true"),$pluginName);
}
if (isset($_POST['stopRemoteFalcon'])) {
  $remoteFalconState = "<h4 id=\"remoteFalconStopped\">Remote Falcon is currently stopped</h4>";
  WriteSettingToFile("remote_fpp_enabled",urlencode("false"),$pluginName);
}

$pluginCheckResults = "";
$pluginCheckResultsId = "warning";
if (isset($_POST['checkPlugin'])) {

  //Check internet connection to RF using health endpoint
  $hasInternet = 0;
  $url = $baseUrl . "/actuator/health";
  $options = array(
    'http' => array(
      'method'  => 'GET',
      'header'=>  "Content-Type: application/json; charset=UTF-8\r\n" .
                  "Accept: application/json\r\n"
      )
  );
  $context = stream_context_create( $options );
  $result = file_get_contents( $url, false, $context );
  $response = json_decode( $result );
  if($response) {
    $hasInternet = 1;
  }

  //Check if playlist is synced
  $remotePlaylist = urldecode($pluginSettings['remotePlaylist']);

  //Check if playlist has lead ins or lead outs
  $hasLeadInsOuts = 0;
  if (strlen($remotePlaylist) >= 2) {
    $playlists = array();
    $remotePlaylistEncoded = rawurlencode($remotePlaylist);
    $url = "http://127.0.0.1/api/playlist/${remotePlaylistEncoded}";
    $options = array(
      'http' => array(
        'method'  => 'GET'
        )
    );
    $context = stream_context_create( $options );
    $result = file_get_contents( $url, false, $context );
    $response = json_decode( $result, true );
    $leadIn = $response['leadIn'];
    $leadOut = $response['leadOut'];
    if(count($leadIn) > 0 || count($leadOut) > 0) {
      $hasLeadInsOuts = 1;
    }

    //Check if playlist has special characters in sequence names
    $hasSpecialCharacters = 0;
    $mainPlaylist = $response['mainPlaylist'];
    foreach($mainPlaylist as $item) {
      if($item['type'] == 'both' || $item['type'] == 'sequence' || $item['type'] == 'media') {
        $playlistName = pathinfo($item['sequenceName'], PATHINFO_FILENAME);
        if (!preg_match('/^[a-z0-9 ]+$/i', $playlistName)) {
          $hasSpecialCharacters = 1;
        }
      }
    }

    //Check if synced playlist is scheduled
    $isScheduled = 0;
    $url = "http://127.0.0.1/api/fppd/schedule";
    $options = array(
      'http' => array(
        'method'  => 'GET'
        )
    );
    $context = stream_context_create( $options );
    $result = file_get_contents( $url, false, $context );
    $response = json_decode( $result, true );
    $schedule = $response['schedule'];
    $entries = $schedule['entries'];
    foreach($entries as $item) {
      if($item['playlist'] === $remotePlaylist) {
        $isScheduled = 1;
      }
    }
  }

  if($hasInternet === 0) {
    $pluginCheckResults = $pluginCheckResults . "* Unable to reach Remote Falcon. Check that FPP has access to the internet.</br>";
  }
  if($remotePlaylist === "") {
    $pluginCheckResults = $pluginCheckResults . "* No playlist has been synced with Remote Falcon.</br>";
  }
  if($hasLeadInsOuts === 1) {
    $pluginCheckResults = $pluginCheckResults . "* Remote playlist should not contain Lead Ins or Lead Outs.</br>";
  }
  if($hasSpecialCharacters === 1) {
    $pluginCheckResults = $pluginCheckResults . "* One or more sequences contains special characters. This could cause problems and is best to remove them.</br>";
  }
  if($isScheduled === 1) {
    $pluginCheckResults = $pluginCheckResults . "* Remote playlist should not be part of any schedules.</br>";
  }

  if($pluginCheckResults === "") {
    $pluginCheckResultsId = "good";
    $pluginCheckResults = "No issues found!";
  }
}

$scriptWarning = "";
if (strlen($remotePlaylist) >= 2) {
  $playlists = array();
  $remotePlaylistEncoded = rawurlencode($remotePlaylist);
  $url = "http://127.0.0.1/api/playlist/${remotePlaylistEncoded}";
  $options = array(
    'http' => array(
      'method'  => 'GET'
      )
  );
  $context = stream_context_create( $options );
  $result = file_get_contents( $url, false, $context );
  $response = json_decode( $result, true );
  $mainPlaylist = $response['mainPlaylist'];
  foreach($mainPlaylist as $item) {
    if($item['type'] == 'command') {
      $scriptWarning = "This playlist contains commands! Commands should be used with caution!";
    }
  }
}

?>

<!DOCTYPE html>
<html>
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1"
    crossorigin="anonymous">
  <style>
    a {
      color: #D65A31;
    }
    #bodyWrapper {
      background-color: #20222e;
    }
    .pageContent {
      background-color: #171720;
    }
    .plugin-body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: rgb(238, 238, 238);
      background-color: rgb(0, 0, 0);
      font-size: 1rem;
      font-weight: 400;
      line-height: 1.5;
      padding-bottom: 2em;
      background-image: url("https://remotefalcon.com/brick-wall-background-with-juke.jpg");
      background-repeat: no-repeat;
      background-attachment: fixed;
      background-position: top center;
      background-size: auto 100%;
    }
    .card {
      background-color: rgba(59, 69, 84, 0.7);
      border-radius: 0.5em;
      margin: 1em 1em 1em 1em;
      padding: 1em 1em 1em 1em;
    }
    .card-body {
      background-color: rgba(59, 69, 84, 0);
    }
    .card-subtitle {
      font-size: .9rem;
    }
    .setting-item {
      padding-bottom: 2em;
    }
    .input-group {
      padding-top: .5em;
    }
    .btn-primary {
      background-color: #D65A31;
      border-color: #D65A31;
    }
    .btn-primary:hover {
      background-color: #D65A31;
      border-color: #D65A31;
    }
    .btn-primary:focus {
      background-color: #D65A31;
      border-color: #D65A31;
    }
    .btn-danger {
      background-color: #A72525;
      border-color: #A72525;
    }
    .btn-danger:hover {
      background-color: #A72525;
      border-color: #A72525;
    }
    .btn-danger:focus {
      background-color: #A72525;
      border-color: #A72525;
    }
    .hvr-underline-from-center {
      display: inline-block;
      vertical-align: middle;
      -webkit-transform: perspective(1px) translateZ(0);
      transform: perspective(1px) translateZ(0);
      box-shadow: 0 0 1px rgba(0, 0, 0, 0);
      position: relative;
      overflow: hidden;
    }
    .hvr-underline-from-center:before {
      content: "";
      position: absolute;
      z-index: -1;
      left: 51%;
      right: 51%;
      bottom: 0;
      background: #FFF;
      height: 4px;
      -webkit-transition-property: left, right;
      transition-property: left, right;
      -webkit-transition-duration: 0.3s;
      transition-duration: 0.3s;
      -webkit-transition-timing-function: ease-out;
      transition-timing-function: ease-out;
    }
    .hvr-underline-from-center:hover:before, .hvr-underline-from-center:focus:before, .hvr-underline-from-center:active:before {
      left: 0;
      right: 0;
    }
		#remoteFalconRunning {
			color: #60F779;
		}
		#remoteFalconStopped {
			color: #A72525;
		}
		#update {
      padding-bottom: 1em;
      font-weight: bold;
			color: #A72525;
		}
    #env {
      color: #A72525;
    }
    #warning {
      font-weight: bold;
      color: #A72525;
    }
    #good {
      font-weight: bold;
      color: #60F779;
    }
		#restartNotice {
			font-weight: bold;
      color: #D65A31;
      <? echo $restartNotice; ?>
		}
  </style>
</head>
<body>
  <div class="container-fluid plugin-body">
    <div class="container-fluid" style="padding-top: 2em;">
      <div class="card">
        <div class="card-body"><div class="justify-content-md-center row" style="padding-bottom: 1em;">
          <div class="col-md-auto">
            <h1>Remote Falcon Plugin v<? echo $pluginVersion; ?></h1>
          </div>
        </div>
        <div class="justify-content-md-center row" style="padding-bottom: 1em;">
          <div class="col-md-auto">
            <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=FFKWA2CFP6JC6&currency_code=USD&source=url" target="_blank" rel="noopener noreferrer">
              <img style="margin-left: 1em;" alt="RF_Donate" src="https://remotefalcon.com/support-button-v2.png">
            </a>
          </div>
        </div>
        <div class="justify-content-md-center row" style="padding-bottom: 1em;">
          <div class="col-md-auto">
            <? echo $remoteFalconState; ?>
          </div>
        </div>
        <div style=<? echo "$showUpdateDiv"; ?>>
          <div id="update" class="justify-content-md-center row">
            <div class="col-md-auto">
              <h4 style="font-weight: bold;">An update is available!</h4>
            </div>
          </div>
        </div>
        <div class="justify-content-md-center row">
          <div class="col-md-auto">
            <h4 id="env"><? echo $baseUrl == "http://host.docker.internal:8080/remotefalcon/api" ? "TEST" : "" ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="card">
        <div class="card-body">
          <!-- Remote Token -->
          <div class="justify-content-md-center row setting-item">
            <div class="col-md-6">
							<div class="card-title h5">
								Remote Token <span id="restartNotice"> *</span>
							</div>
							<div class="mb-2 text-muted card-subtitle h6">
								Your Remote Token can be found on the Remote Falcon Control Panel
							</div>
						</div>
            <div class="col-md-6">
              <form method="post">
                <div class="input-group">
                  <input type="text" class="form-control" name="remoteToken" id="remoteToken" placeholder="Remote Token" value=<? echo "$remoteToken "; ?>>
                  <span class="input-group-btn">
                    <button id="updateRemoteToken" name="updateRemoteToken" class="btn mr-md-3 hvr-underline-from-center btn-primary" type="submit">Update</button>
                  </span>
                </div>
              </form>
            </div>
          </div>
          <!-- Remote Playlist -->
          <div class="justify-content-md-center row setting-item">
            <div class="col-md-6">
              <div class="card-title h5">
                Remote Playlist <span id="restartNotice"> *</span>
              </div>
              <div class="mb-2 text-muted card-subtitle h6">
                This is the playlist that contains all the sequences to be controlled by your viewers
              </div>
            </div>
            <div class="col-md-6">
              <form method="post">
                <div class="input-group">
                  <select class="form-select" id="remotePlaylist" name="remotePlaylist">
                    <option selected value=""></option>
                    <? echo "$playlistOptions "; ?>
                  </select>
                  <span class="input-group-btn">
                    <button id="updateRemotePlaylist" name="updateRemotePlaylist" class="btn mr-md-3 hvr-underline-from-center btn-primary" type="submit">Update</button>
                  </span>
                </div>
              </form>
            </div>
          </div>
          <!-- Current Remote Playlist -->
          <div class="justify-content-md-center row setting-item" style="padding-top: .5em;">
            <div class="col-md-6">
              <div class="card-title h5">
                Current Remote Playlist
              </div>
              <div class="mb-2 text-muted card-subtitle h6">
                This is the current playlist synced with Remote Falcon (click to go to Sequences in your Control Panel)
              </div>
            </div>
            <div class="col-md-6">
              <h5><a href=<? echo "$rfSequencesUrl"; ?> target="_blank" rel="noopener noreferrer"><? echo "$remotePlaylist"; ?></a></h5>
              <p id="warning"><? echo "$scriptWarning"; ?></p>
            </div>
          </div>
          <!-- Interrupt Schedule -->
          <div class="justify-content-md-center row setting-item">
            <div class="col-md-6">
              <div class="card-title h5">
                Interrupt Schedule <span id="restartNotice"> *</span>
              </div>
              <div class="mb-2 text-muted card-subtitle h6">
                Determines if a request or vote will interrupt the normal schedule
              </div>
            </div>
            <div class="col-md-6">
              <form method="post">
                <button class="btn mr-md-3 hvr-underline-from-center <? echo $interruptYes; ?>" id="interruptScheduleYes" name="interruptScheduleYes" type="submit">
                  Yes
                </button>
                <button class="btn mr-md-3 hvr-underline-from-center <? echo $interruptNo; ?>" id="interruptScheduleNo" name="interruptScheduleNo" type="submit">
                  No
                </button>
              </form>
            </div>
          </div>
          <!-- Debug Remote Falcon -->
          <div class="justify-content-md-center row setting-item">
            <div class="col-md-6">
              <div class="card-title h5">
                Check Plugin
              </div>
              <div class="mb-2 text-muted card-subtitle h6">
                This will run a check on the plugin configuration and report any issues. Results will display below.
              </div>
            </div>
            <div class="col-md-6">
              <form method="post">
                <button class="btn mr-md-3 hvr-underline-from-center btn-primary" id="checkPlugin" name="checkPlugin" type="submit">
                  Check Plugin
                </button>
              </form>
            </div>
          </div>
          <!-- Debug Remote Falcon Results -->
          <div class="justify-content-md-center row setting-item">
            <div class="col-md-6">
            </div>
            <div class="col-md-6">
              <p id=<? echo $pluginCheckResultsId; ?>>
                <? echo $pluginCheckResults; ?>
              </p>
            </div>
          </div>
          <!-- Restart Remote Falcon -->
          <div class="justify-content-md-center row setting-item">
            <div class="col-md-6">
              <div class="card-title h5">
                Restart Remote Falcon
              </div>
              <div class="mb-2 text-muted card-subtitle h6">
                This will restart the Remote Falcon plugin
              </div>
            </div>
            <div class="col-md-6">
              <form method="post">
                <button class="btn mr-md-3 hvr-underline-from-center btn-primary" id="restartRemoteFalcon" name="restartRemoteFalcon" type="submit">
                  Restart Remote Falcon
                </button>
              </form>
            </div>
          </div>
          <div class="justify-content-md-center row" style="padding-bottom: 1em;">
            <div class="col-md-auto">
              <h4 id="restartNotice">Advanced Settings</h4>
            </div>
          </div>
          <hr />
          <!-- Request Fetch Time -->
          <div class="justify-content-md-center row setting-item">
            <div class="col-md-6">
							<div class="card-title h5">
								Request/Vote Fetch Time <span id="restartNotice"> *</span>
							</div>
							<div class="mb-2 text-muted card-subtitle h6">
								This sets when the plugin checks for the next request/vote. </br>
                Recommended is 3 seconds and must be between 1 and 5 seconds.
							</div>
						</div>
            <div class="col-md-3">
              <form method="post">
                <div class="input-group">
                  <input type="number" class="form-control" name="requestFetchTime" id="requestFetchTime" value=<? echo "$requestFetchTime "; ?>>
                  <span class="input-group-btn">
                    <button id="updateRequestFetchTime" name="updateRequestFetchTime" class="btn mr-md-3 hvr-underline-from-center btn-primary" type="submit">Update</button>
                  </span>
                </div>
              </form>
            </div>
            <div class="col-md-3">
            </div>
          </div>
          <!-- Additional Wait Time -->
          <div class="justify-content-md-center row setting-item">
            <div class="col-md-6">
							<div class="card-title h5">
								Additional Wait Time <span id="restartNotice"> *</span>
							</div>
							<div class="mb-2 text-muted card-subtitle h6">
								This adds extra time after fetching the next request or vote. </br>
                It's recommended to leave this at 0, but if you experience requests </br>
                getting skipped or falling off, you can set this to 5 seconds or less.
							</div>
						</div>
            <div class="col-md-3">
              <form method="post">
                <div class="input-group">
                  <input type="number" class="form-control" name="additionalWaitTime" id="additionalWaitTime" value=<? echo "$additionalWaitTime "; ?>>
                  <span class="input-group-btn">
                    <button id="updateAdditionalWaitTime" name="updateAdditionalWaitTime" class="btn mr-md-3 hvr-underline-from-center btn-primary" type="submit">Update</button>
                  </span>
                </div>
              </form>
            </div>
            <div class="col-md-3">
            </div>
          </div>
          <!-- FPP Status Check Time -->
          <div class="justify-content-md-center row setting-item">
            <div class="col-md-6">
							<div class="card-title h5">
								FPP Status Check Time <span id="restartNotice"> *</span>
							</div>
							<div class="mb-2 text-muted card-subtitle h6">
								This determines how often the plugin calls to the the status of FPP. </br>
                It's recommended to leave this at 1, but if you experience issues with high CPU </br>
                or FPP freezing, you can set this to a higher value. The value must be between 1 and </br>
                the value of your request fetch time.
							</div>
						</div>
            <div class="col-md-3">
              <form method="post">
                <div class="input-group">
                  <input type="number" class="form-control" name="fppStatusCheckTime" id="fppStatusCheckTime" value=<? echo "$fppStatusCheckTime "; ?>>
                  <span class="input-group-btn">
                    <button id="updateFppStatusCheckTime" name="updateFppStatusCheckTime" class="btn mr-md-3 hvr-underline-from-center btn-primary" type="submit">Update</button>
                  </span>
                </div>
              </form>
            </div>
            <div class="col-md-3">
            </div>
          </div>
          <!-- Stop Remote Falcon -->
          <div class="justify-content-md-center row setting-item">
            <div class="col-md-6">
              <div class="card-title h5">
                Stop Remote Falcon
              </div>
              <div class="mb-2 text-muted card-subtitle h6">
                <span id="warning">WARNING! </span>This will immediately stop the Remote Falcon
                plugin and no requests/votes will be fetched!
              </div>
            </div>
            <div class="col-md-6">
            <form method="post">
                <button class="btn mr-md-3 hvr-underline-from-center btn-danger" id="stopRemoteFalcon" name="stopRemoteFalcon" type="submit">
                  Stop Remote Falcon
                </button>
              </form>
            </div>
          </div>
          <span id="restartNotice">* Requires Remote Falcon Restart</span>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>

</body>
</html>