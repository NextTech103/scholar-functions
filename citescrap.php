<?php
    function scrapScholarCite($url){

        try{
          // The URL to scrape
            // Initialize cURL
            $ch = curl_init($url);


            $user_agents = [
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
                "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0",
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36",
                "Mozilla/5.0 (Windows NT 6.1; rv:72.0) Gecko/20100101 Firefox/72.0",
                "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36",
                "Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0",
                "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:73.0) Gecko/20100101 Firefox/73.0",
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36",
                "Mozilla/5.0 (Windows NT 5.1; rv:32.0) Gecko/20100101 Firefox/32.0",
                "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0"
            ];
            
            // Select a random User-Agent from the array
            $random_user_agent = $user_agents[array_rand($user_agents)];

            // Set the cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $random_user_agent);  // Mimic a browser
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects

            // Disable SSL verification (not recommended for production)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // Execute the cURL request
            $html = curl_exec($ch);

            // Check if there was an error with the cURL request
            if ($html === false) {
                echo "Error fetching the URL: " . curl_error($ch);
                curl_close($ch);
                exit;
            }

            // Close the cURL session
            curl_close($ch);

            echo $html;
        } catch(Exception $e){
            echo json_encode([
                'success' => false,
                'message' => 'Something went wrong',
                'payload' => null // Use the decoded data here
            ], JSON_PRETTY_PRINT);
        }
        
    }

?>
