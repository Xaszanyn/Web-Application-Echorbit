<?php

require_once "../amazon/aws-autoloader.php";
require_once "../utilities/configuration.php";

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

if ($_GET["request"]) {
    $client = new S3Client([
        "version" => "latest",
        "region" => "eu-north-1",
        "credentials" => [
            "key" => AWS_ACCESS,
            "secret" => AWS_SECRET,
        ],
    ]);

    $key = $_GET["request"] == "file" ? "test_file.zip" : "test_image.png";

    try {
        $url = (string) $client->createPresignedRequest($client->getCommand("GetObject", [
            "Bucket" => "echorbit-audio",
            "Key" => $key
        ]), new DateTime("+1 day"))->getUri();

        header("Location: " . $url);
        exit;
    } catch (AwsException $error) {
        echo json_encode(["error" => $error]);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
</head>

<body>
    <h1>Amazon Test Download</h1>
    <hr>
    <p>Click to download file.</p>
    <form method="get" action="https://echorbitaudio.com/services/test/index.php">
        <input type="hidden" name="request" value="file">
        <button type="submit">Download</button>
    </form>
    <hr>
    <p>Click to download image file.</p>
    <form method="get" action="https://echorbitaudio.com/services/test/index.php">
        <input type="hidden" name="request" value="image">
        <button type="submit">Download</button>
    </form>
</body>

</html>