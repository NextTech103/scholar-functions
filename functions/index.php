<?php
// Allow requests from any origin
header("Access-Control-Allow-Origin: *");
// Allow all HTTP methods
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// Allow all headers
header("Access-Control-Allow-Headers: *");
// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Respond with HTTP 200 for preflight
    exit;
}
include 'scrap.php';
include 'citescrap.php';
// Get the request URI (URL path) from the server
$request_uri = $_SERVER['REQUEST_URI'];
// Handle the root route
if ($request_uri === '/scholar/' || $request_uri === '/scholar/index.php') {
    echo "Home Page";
}
// Handle the about page
elseif (preg_match('/^\/scholar\/scrap(\?.*)?$/', $request_uri)) {
    header('Content-Type: application/json');
    if (isset($_GET['q'])) {
        $query = $_GET['q'];
        echo scrapScholar($query);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Missing query parameter: q',
            'payload' => null
        ], JSON_PRETTY_PRINT);
    }
    
}
// Handle the product page with dynamic ID parameter
elseif ($request_uri === '/scholar/cite/') {
    // Extract the product ID from the URL
    $scrapUrl = $_POST['url'];
    echo scrapScholarCite($scrapUrl);
}
// Custom 404 page for undefined routes
else {
    echo "Not Found";
}
?>
