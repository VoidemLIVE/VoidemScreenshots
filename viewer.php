<?php
session_start();
$imageID = isset($_GET['id']) ? $_GET['id'] : '';
$dir = "img/";

if (empty($imageID)) {
    die('No image ID provided');
} else if (!file_exists($dir . $imageID . ".png") && !file_exists($dir . $imageID . ".jpg")) {
    die('Image not found');
} else {
    if (file_exists($dir . $imageID . ".png")) {
        $file = $dir . $imageID . ".png";
    } else {
        $file = $dir . $imageID . ".jpg";
    }
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $logged_in = true;
} else {
    $logged_in = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voidem Screenshots</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta content="Voidem Screenshots" property="og:title" />
    <meta content="Screenshot ID: <?php echo $imageID; ?>" property="og:description" />
    <meta content="https://callum.christmas/viewer.php?id=<?php echo $imageID?>" property="og:url" />
    <meta content="https://callum.christmas/<?php echo $file ?>" property="og:image" />
    <meta name="twitter:card" content="summary_large_image">
    <style>
        :root {
            --main-bg: #1f2937;
            --div-bg: #111827;
        }
    </style>
</head>
<body style="background-color: var(--main-bg)" class="text-white flex flex-col justify-center items-center h-screen">
    <div style="background-color: var(--div-bg)" class="max-w-4xl p-8 rounded-lg shadow-lg mb-8">
        <h1 class="text-3xl font-bold text-center mb-6">Voidem's Screenshots</h1>
        <p class="text-center text-gray-400 mb-6">Screenshot ID: <?php echo $imageID; ?></p>
        <div class="border-4 border-gray-700 rounded-lg overflow-hidden">
            <img src="<?php echo $file; ?>" alt="Screenshot Image" class="w-full h-full object-cover">
        </div>
        <p class="text-center text-gray-400 mb-6"><a href="https://voidem.com">Voidem</a></p>
    </div>
    <div class="text-center">
        <a href="<?php echo isset($logged_in) && $logged_in ? 'logout.php?vid=' . $imageID : 'login.php?vid=' . $imageID ?>" class="inline-block px-6 py-3 bg-blue-500 text-white rounded-lg shadow-lg">
            <?php
            if ($logged_in) {
                echo "Logout";
            } else {
                echo "Not logged in";
            }
            ?>
        </a>
        <?php if(isset($logged_in) && ($logged_in)): ?>
            <a href="admin.php" class="inline-block px-6 py-3 bg-blue-500 text-white rounded-lg shadow-lg">Admin</a>
        <?php endif; ?>
    </div>
</body>
</html>
