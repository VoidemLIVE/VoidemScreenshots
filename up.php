<?php
// Original script by: https://nextgenupdate.com/forums/computers/886853-how-make-your-own-custom-sharex-image-uploader-custom-domain-etc.html
// Script was modified so that it works with my website
// SECRET_KEY needs to be in an env file
// For any help with this script please refer to the original post

$env = parse_ini_file('.env');
$secret_key = $env["SECRET_KEY"]; //Set this as your secret key, to prevent others uploading to your server.
$sharexdir = "img/"; //This is your file dir, also the link..
$domain_url = 'https://callum.christmas/'; //Add an S at the end of HTTP if you have a SSL certificate.
$lengthofstring = 5; //Length of the file name
$viewer = "viewer.php?id=";

function RandomString($length) {
    $keys = array_merge(range(0,9), range('a', 'z'));

    $key = '';
    for($i=0; $i < $length; $i++) {
        $key .= $keys[mt_rand(0, count($keys) - 1)];
    }
    return $key;
}

if(isset($_POST['secret']))
{
    if($_POST['secret'] == $secret_key)
    {
        $filename = RandomString($lengthofstring);
        $target_file = $_FILES["sharex"]["name"];
        $fileType = pathinfo($target_file, PATHINFO_EXTENSION);

        if (move_uploaded_file($_FILES["sharex"]["tmp_name"], $sharexdir.$filename.'.'.$fileType))
        {
            echo $domain_url.$viewer.$filename;
        }
            else
        {
           echo 'File upload failed - CHMOD/Folder doesn\'t exist?';
        }
    }
    else
    {
        echo 'Invalid Secret Key';
    }
}
else
{
    echo 'No post data recieved';
}
?>