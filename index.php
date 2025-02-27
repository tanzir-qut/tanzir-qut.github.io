<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MongoDB Data Search</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            padding: 20px;
            text-align: center;
        }
        h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 30px;
        }
        input[type="text"] {
            padding: 15px;
            width: 300px;
            border: 2px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        input[type="submit"] {
            padding: 15px 25px;
            font-size: 1rem;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        td {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        hr {
            border: 1px solid #ddd;
            width: 80%;
            margin: 40px auto;
        }
    </style>
</head>
<body>

<h1>Malicious Packages Information Search</h1>

<!-- Center the search form -->
<form method="GET" action="">
    <input type="text" name="query" placeholder="Search by package name...">
    <input type="submit" value="Search">
</form>

<?php
// Function to fetch data from MongoDB API
function fetchMongoData($searchQuery = null) {
    $url = "https://ap-southeast-2.aws.data.mongodb-api.com/app/data-holwzcj/endpoint/data/v1/action/find";
    
    $headers = [
        'Content-Type: application/json',
        'Access-Control-Request-Headers: *',
        'api-key: XvrZVNG1ELhmjMirjtuyJX9ECMlTYhSAhdvS8UWH0DEOORMN3zZQtd2dV4iFpaKf',  // Replace with your actual API key
    ];

    // Construct the query filter
    $query = $searchQuery ? [
        'Malicious Package Name' => ['$regex' => $searchQuery, '$options' => 'i']  // case-insensitive search
    ] : [];
    
    // Payload for MongoDB API request (no projection to fetch all fields)
    $payload = json_encode([
        "collection" => "Step_2",
        "database" => "PyPi_Research",
        "dataSource" => "Cluster0",
        "filter" => $query,
        "limit" => 1000  // Fetch up to 1000 documents
    ]);

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    return $data['documents'] ?? [];
}

// Function to recursively display all document fields, including nested fields
function displayDocument($doc, $parentKey = '') {
    echo "<table>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    
    foreach ($doc as $key => $value) {
        // Form a full key name for nested fields
        $fullKey = $parentKey ? $parentKey . ' -> ' . $key : $key;
        
        if (is_array($value)) {
            // If the value is an array, recursively display its contents
            echo "<tr><td>$fullKey</td><td>";
            displayDocument($value, $fullKey);
            echo "</td></tr>";
        } else {
            // Display non-nested values
            echo "<tr><td>$fullKey</td><td>" . htmlspecialchars($value) . "</td></tr>";
        }
    }
    
    echo "</table>";
}

// Fetch the search query from the form input
$searchQuery = isset($_GET['query']) ? $_GET['query'] : null;
$documents = fetchMongoData($searchQuery);

// Display the data
if (!empty($documents)) {
    foreach ($documents as $doc) {
        echo "<h2>Package: " . htmlspecialchars($doc['Malicious Package Name']) . " (Version: " . htmlspecialchars($doc['Malicious Package Version']) . ")</h2>";
        
        // Display each document in a recursive table format
        displayDocument($doc);
        echo "<hr>";
    }
} else {
    echo "<p>No results found.</p>";
}
?>

</body>
</html>
