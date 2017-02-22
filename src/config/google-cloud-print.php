<?php
return [
	//file path of client credentials from google developers console download
	'file' => 'client_secrets.json',

	//Redirect uri for oauth2 callback to authorize application
    'redirect_uri' => '',

	//Uri to return to after authorization
	'callback_uri' => '',

	//storage engine to return refresh and access tokens
	'storageEngine' => new \Andrewlamers\PhpGoogleCloudPrint\Storage\Storage(),

	//Type of access to request. Offline access will use a refresh token so user input is no longer required for authorizing requests.
    'access_type' => 'offline',

    //Force approval prompt on every authorization or not
    'approval_prompt' => 'force',

    //Scopes to use for authenticating with google. Cloud print scope is required for cloud print functionality
	'scopes' => [
		"https://www.googleapis.com/auth/cloudprint"
	]
];