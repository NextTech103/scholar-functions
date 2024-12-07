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

// Include your custom files
include 'scrap.php';
include 'citescrap.php';

// Netlify function entry point
return function ($event) {

    // Get the request URI (URL path) from the server
    $request_uri = $event['path'];  // Changed to work with event object

    // Handle the root route
    if ($request_uri === '/scholar/' || $request_uri === '/scholar/index.php') {
        return [
            'statusCode' => 200,
            'body' => "Home Page"
        ];
    }
    // Handle the about page
    elseif (preg_match('/^\/scholar\/scrap(\?.*)?$/', $request_uri)) {
        header('Content-Type: application/json');
        if (isset($event['queryStringParameters']['q'])) {
            $query = $event['queryStringParameters']['q'];  // Access query parameters from event
            return [
                'statusCode' => 200,
                'body' => scrapScholar($query)
            ];
        } else {
            return [
                'statusCode' => 400,
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Missing query parameter: q',
                    'payload' => null
                ], JSON_PRETTY_PRINT)
            ];
        }
    }
    // Handle the product page with dynamic ID parameter
    elseif ($request_uri === '/scholar/cite/') {
        // Extract the product URL from the request body
        $scrapUrl = $event['body']; // Use event['body'] for POST data
        return [
            'statusCode' => 200,
            'body' => scrapScholarCite($scrapUrl)
        ];
    }
    // Custom 404 page for undefined routes
    else {
        return [
            'statusCode' => 404,
            'body' => "Not Found"
        ];
    }
};
