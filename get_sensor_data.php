<?php
// Set the content type to application/json
header('Content-Type: application/json');

// --- FILE PATH ---
$data_file = 'sensor_output.json';

// Check if the file exists and is readable
if (file_exists($data_file) && is_readable($data_file)) {
    
    // Read the contents of the file
    $json_output = file_get_contents($data_file);
    
    // Output the JSON content
    http_response_code(200);
    echo $json_output;
    
} else {
    // If the file does not exist or cannot be read
    http_response_code(404); // Not Found
    echo json_encode(['status' => 'error', 'message' => 'Sensor data file not found or inaccessible.']);
}
?>