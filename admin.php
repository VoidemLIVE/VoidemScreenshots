<?php

function printToConsole($data) {
    echo '<script>console.log('.json_encode($data).')</script>';
}
session_start();
$env = parse_ini_file('.env');
$admin_username = $env["USER"];
$admin_password = $env["PASS"];

if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if(isset($_POST['logout'])) {
        session_destroy();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    if(isset($_POST['delete_all'])) {
        $files = glob('img/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    if(isset($_POST['delete']) && isset($_POST['file'])) {
        $file = $_POST['file'];
        if(is_file($file)) {
            unlink($file); 
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        }
    }
    renderAdminContent();
} else {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if ($username === $admin_username && $password === $admin_password) {
            $_SESSION['logged_in'] = true;
            renderAdminContent();
        } else {
            renderLoginForm(true);
        }
    } else {
        renderLoginForm(false);
    }
}

function renderAdminContent() {
    $perPage = 12;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    $dir = "img/";
    $files = glob($dir . "*.{jpg,png,gif,jpeg}", GLOB_BRACE);

    if (isset($_GET['sort'])) {
        $sortOption = $_GET['sort'];
        if ($sortOption === 'date') {
            array_multisort(array_map('filectime', $files), SORT_DESC, $files);
        } elseif ($sortOption === 'size') {
            array_multisort(array_map('filesize', $files), SORT_DESC, $files);
        }
    }

    $totalImages = count($files);
    $totalPages = ceil($totalImages / $perPage);
    $startIndex = ($page - 1) * $perPage;
    $endIndex = min($startIndex + $perPage, $totalImages);
    $filesOnPage = array_slice($files, $startIndex, $perPage);

    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Voidem Screenshots Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <style>
            .active-page {
                background-color: #4a5568;
                color: white;
            }
        </style>
    </head>
    <body class="bg-gray-100">
        <div class="container mx-auto py-8">
            <h1 class="text-3xl font-bold text-center mb-6"><a href="https://callum.christmas/admin.php">Voidem Screenshots Admin</a></h1>
            <div class="flex justify-between mb-4">
                <div class="flex items-center">
                    <label for="sort" class="mr-2">Sort by:</label>
                    <!-- Dropdown for sorting options -->
                    <select id="sort" onchange="sortImages(this.value)" class="bg-white border border-gray-300 rounded px-3 py-1 focus:outline-none focus:ring focus:border-blue-300">
                        <option value="default"'; if (!isset($_GET['sort']) || $_GET['sort'] === 'default') { echo ' selected'; } echo '>Default</option>
                        <option value="size"'; if (isset($_GET['sort']) && $_GET['sort'] === 'size') { echo ' selected'; } echo '>Size</option>
                        <option value="date"'; if (isset($_GET['sort']) && $_GET['sort'] === 'date') { echo ' selected'; } echo '>Date Created</option>
                    </select>
                    <p class="text-gray-700 ml-4">Number of Images: <a class="font-bold">' . $totalImages . '</a></p>
                </div>
                <div class="flex items-center">
                    <form method="POST" action="">
                        <button type="submit" name="logout" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">Logout</button>
                    </form>
                    <form method="POST" action="'. $_SERVER['PHP_SELF'] .'">
                        <input type="hidden" name="delete_all" value="true">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Delete All</button>
                    </form>
                </div>
            </div>
            <!-- Display images for the current page -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">';

    foreach ($filesOnPage as $file) {
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
        $fileSize = filesize($file);
        $upload_date = filectime($file);
        echo '<div class="border border-gray-200 rounded-lg overflow-hidden shadow-md">';
        echo '<img src="' . $file . '" alt="Image" class="w-full h-64 object-cover">';
        echo '<div class="p-4">';
        echo '<p class="text-lg font-semibold">' . $fileName . '.' . $fileExtension . '</p>';
        echo '<p class="text-sm font-semibold">' . formatFileSize($fileSize) . '</p>';
        echo '<p class="text-sm font-semibold">' . 'Uploaded: ' . date('Y-m-d H:i:s', $upload_date) . ' (UTC)' . '</p>';
        echo '<div class="flex justify-between mt-4">';
        echo '<a target="#" href="viewer.php?id=' . $fileName . '" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">View</a>';
        echo '<form method="POST" action="">';
        echo '<input type="hidden" name="file" value="' . $file . '">';
        echo '<button type="submit" name="delete" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    // just closes the previous html ^
    echo '
            </div>
            <div class="flex justify-center mt-4">';
    //page things
    if ($page > 1) {
        echo '<a href="?page=' . ($page - 1) . '&sort=' . (isset($_GET['sort']) ? $_GET['sort'] : 'default') . '" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l">Previous</a>';
    }
    
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = $i === $page ? ' active-page' : '';
        echo '<a href="?page=' . $i . '&sort=' . (isset($_GET['sort']) ? $_GET['sort'] : 'default') . '" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4' . $activeClass . '">' . $i . '</a>';
    }

    if ($page < $totalPages) {
        echo '<a href="?page=' . ($page + 1) . '&sort=' . (isset($_GET['sort']) ? $_GET['sort'] : 'default') . '" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r">Next</a>';
    }
    echo '
            </div>
        </div>
        <script>
            function sortImages(option) {
                if (option === "default") {
                    window.location.href = "admin.php";
                } else {
                    window.location.href = "admin.php?sort=" + option;
                }
            }
        </script>
    </body>
    </html>';
}




function formatFileSize($fileSize) {
    if ($fileSize >= 1024 * 1024) {
        return number_format($fileSize / (1024 * 1024), 2) . ' MB';
    } elseif ($fileSize >= 1024) {
        return number_format($fileSize / 1024, 2) . ' KB';
    } else {
        return $fileSize . ' bytes';
    }
}

function renderLoginForm($error) {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Voidem Screenshots Admin Login</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-100">
        <div class="container mx-auto py-8">
            <h1 class="text-3xl font-bold text-center mb-6">Admin Login</h1>
            <div class="w-1/3 mx-auto">
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="username">Username</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" type="text" name="username" placeholder="Username">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" type="password" name="password" placeholder="Password">
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">Sign In</button>
                    </div>';
    
    if ($error) {
        echo '<p class="text-red-500 mt-2">Invalid username or password.</p>';
    }
    
    echo '
                </form>
            </div>
        </div>
    </body>
    </html>';
}
?>
