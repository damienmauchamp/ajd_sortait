<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

// loading .env data
if (is_file(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::create(__DIR__ . '/..');
    $dotenv->load();
}

/////// CONFIG ///////
$username = $_ENV['INSTAGRAM_USERNAME'];
$password = $_ENV['INSTAGRAM_PASSWD'];
$debug = false;
$truncatedDebug = false;
//////////////////////

/////// MEDIA ////////
$photoFilename = __DIR__ . '/img.png';
$captionText = 'test';

// usertag
$usertags = [];
$pos_X = 0.5;
$pos_Y = 0.8;
$usertags[] = ['position'=>[$pos_X, $pos_Y], 'user_id' => '1720416472'];
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    // The most basic upload command, if you're sure that your photo file is
    // valid on Instagram (that it fits all requirements), is the following:
    // $ig->timeline->uploadPhoto($photoFilename, ['caption' => $captionText]);

    // However, if you want to guarantee that the file is valid (correct format,
    // width, height and aspect ratio), then you can run it through our
    // automatic photo processing class. It is pretty fast, and only does any
    // work when the input file is invalid, so you may want to always use it.
    // You have nothing to worry about, since the class uses temporary files if
    // the input needs processing, and it never overwrites your original file.
    //
    // Also note that it has lots of options, so read its class documentation!
    $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($photoFilename);

    echo "Uploading...\n";

    $uploaded = $ig->timeline->uploadPhoto($photo->getFile(), ['caption' => $captionText]);
    $uploaded_id = $uploaded->getMedia()->getId();

    echo "Uploaded!, id: '$uploaded_id'\n";

    echo "Deleting...\n";

    $deleted = $ig->media->delete($uploaded_id, 'PHOTO');

    echo "Deleted!\n";

} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}