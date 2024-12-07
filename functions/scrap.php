<?php
    function scrapScholar($query){

        try{
          // The URL to scrape
            $url = "https://scholar.google.com/scholar?q=%22" . urlencode($query) . "%22";

            if (isset($_GET['start'])) {
                // Concatenate the start parameter properly to the URL
                $url .= "&start=" . urlencode($_GET['start']);  // Use the correct parameter name 'start' and concatenate properly
            }

            // date since  
            if(isset($_GET['as_ylo'])) {
                $url .= "&as_ylo=" . urlencode($_GET['as_ylo']);
            }
            
            // date to
            if(isset($_GET['as_yhi'])){
                $url .= "&as_yhi=" . urlencode($_GET['as_yhi']);
            }
            
            // value 1 or 0 by default 0 1 = sort dy date
            if(isset($_GET['scisbd'])){
                $url .= "&scisbd=" . urlencode($_GET['scisbd']);
            }
            
            // value 1 or 0 review or any 1 = review
            if(isset($_GET['as_rr'])){
                $url .= "&as_rr=" . urlencode($_GET['as_rr']);
            }
            
            // value 1 or 0, by default 0 include citations
            if(isset($_GET['as_vis'])){
                $url .= "&as_vis=" . urlencode($_GET['as_vis']);
            }

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


            $headers = [
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
                "Accept-Encoding: gzip, deflate, br",
                "Accept-Language: en-US,en;q=0.5"
            ];
            
            // Set the cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $random_user_agent);  // Mimic a browser
            // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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

            // Create a new DOMDocument object
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // Suppress HTML parsing errors

            // Load HTML content into the DOMDocument object
            $dom->loadHTML($html);

            // Use XPath to query specific elements
            $xpath = new DOMXPath($dom);

            // Get all <a> tags
            $links = $xpath->query("//div[@class='gs_ri']");

            // Initialize an array to hold scraped data
            $items = [];

            // Find all <div class="gs_ri"> elements
            $entries = $xpath->query('//div[@class="gs_ri"]');

            foreach ($entries as $entry) {
                // Extract the title (from <a> inside <h3> with class "gs_rt")
                $titleNode = $xpath->query('.//h3[@class="gs_rt"]/a', $entry);
                $title = $titleNode->length > 0 ? $titleNode->item(0)->nodeValue : 'N/A';

                // Get the 'id' attribute of the <a> tag
                $citeQuery = $titleNode->length > 0 ? "info:" . $titleNode->item(0)->getAttribute('id') . ":scholar.google.com/" : 'N/A';
                $citeUrl = "https://scholar.google.com/scholar?q=" . urlencode($citeQuery) . "&output=cite&scirp=0&hl=en";


                // Extract the author (from <div class="gs_a">)
                $authorNode = $xpath->query('.//div[@class="gs_a"]/a', $entry);
                $author = $authorNode->length > 0 ? $authorNode->item(0)->nodeValue : 'N/A';

                // Extract the year and publisher (you can split this based on your needs)
                $yearPublisherNode = $xpath->query('.//div[@class="gs_a"]', $entry);
                $yearPublisher = $yearPublisherNode->length > 0 ? $yearPublisherNode->item(0)->nodeValue : 'N/A';

                // Extract year and publisher info from the text, if present
                preg_match('/(\d{4})/', $yearPublisher, $yearMatches);
                if($yearMatches[1]){
                    $year = $yearMatches[1];
                } else {
                    $year = 'N/A';
                }
                
                
                $publisher = trim(preg_replace('/\d{4}/', '', $yearPublisher)); // Get publisher by removing the year

                // Store the extracted data in an array
                $items[] = [
                    'title' => $title,
                    'author' => $author,
                    'year' => $year,
                    'publisher' => $publisher,
                    'citeurl' => $citeUrl
                ];
            }


            if(count($items) === 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No Data found',
                    'payload' => null // Use the decoded data here
                ], JSON_PRETTY_PRINT);
            } else {
                // Return the data as JSON
                echo json_encode([
                    'success' => true,
                    'message' => 'Data got successfully',
                    'payload' => $items // Use the decoded data here
                ], JSON_PRETTY_PRINT);
            }  
        } catch(Exception $e){
            echo json_encode([
                'success' => false,
                'message' => 'Something went wrong',
                'payload' => null // Use the decoded data here
            ], JSON_PRETTY_PRINT);
        }
        
    }

?>
