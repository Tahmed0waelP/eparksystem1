<?php
// Set the content type to application/json
header('Content-Type: application/json');

// --- FILE PATH FOR ACCESSIBLE DATA ---
// This file will hold the latest full JSON structure
$output_file = 'sensor_output.json'; 

// --- CONSTANT SENSOR DATA LOOKUP TABLE ---
$sensor_constants = [
    "T-4001-A" => [
        "sensor_place" => "Server Room 1",
        "sensor_location" => ["plus_code" => "26VQ+Q8"]
    ],
    "H-2055-B" => [
        "sensor_place" => "Warehouse Floor B",
        "sensor_location" => ["plus_code" => "26VQ+Q8"]
    ]
];

// 1. Get the raw POST data (minimal JSON from the ESP32)
$json_data = file_get_contents('php://input');
$received_data = json_decode($json_data, true);

// Check for errors in received data
if ($received_data === null || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data received.']);
    exit;
}

// 2. Process and merge the data
$final_output = [];

foreach ($received_data as $sensor_update) {
    $id = $sensor_update['sensor_id'] ?? null;
    $availability = $sensor_update['availability'] ?? null;

    if ($id && isset($sensor_constants[$id]) && $availability !== null) {
        
        // Reconstruct the full sensor data payload
        $full_sensor_data = array_merge(
            ['sensor_id' => $id],
            $sensor_constants[$id],
            ['availability' => $availability]
        );
        
        $final_output[] = $full_sensor_data;
    }
}

// 3. Save the final JSON to the file
if (!empty($final_output)) {
    $output_json = json_encode($final_output, JSON_PRETTY_PRINT);
    // Use file_put_contents to save the updated data
    if (file_put_contents($output_file, $output_json) !== false) {
        // Success response to ESP32
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Data updated and saved.']);
    } else {
        // File saving failed
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Data processed but failed to save to file.']);
    }
} else {
    // No valid data processed
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No valid sensor data was processed.']);
}
?>