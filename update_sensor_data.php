<?php
// Set the content type to application/json
header('Content-Type: application/json');

// Define the path to the sensor data file
$sensor_file = 'sensor_output.json';

// 1. Get the JSON data sent from the Android app (POST request body)
$input_json = file_get_contents('php://input');
$new_sensor_data = json_decode($input_json, true);

// Check if the input is valid and contains a sensor_id
if ($new_sensor_data === null || !isset($new_sensor_data['sensor_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing sensor data.']);
    exit;
}

$new_sensor_id = $new_sensor_data['sensor_id'];
$new_sensor_location = $new_sensor_data['sensor_location'];
$new_availability = $new_sensor_data['availability'];
$new_sensor_place = $new_sensor_data['sensor_place'];

// 2. Read the existing sensor data file
if (!file_exists($sensor_file)) {
    // If the file doesn't exist, start with an empty array
    $sensors = [];
} else {
    $current_data = file_get_contents($sensor_file);
    $sensors = json_decode($current_data, true);
    
    // Check for JSON decoding error
    if ($sensors === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Error decoding existing sensor data JSON.']);
        exit;
    }
}

// Ensure $sensors is an array
if (!is_array($sensors)) {
    $sensors = [];
}

$sensor_found = false;

// 3. Iterate through existing sensors to find and update
foreach ($sensors as $key => $sensor) {
    if ($sensor['sensor_id'] === $new_sensor_id) {
        // Sensor found, update its details
        $sensors[$key]['sensor_place'] = $new_sensor_place;
        $sensors[$key]['sensor_location']['plus_code'] = $new_sensor_location['plus_code'];
        $sensors[$key]['availability'] = $new_availability;
        $sensor_found = true;
        break;
    }
}

// 4. If sensor not found, add it as a new record
if (!$sensor_found) {
    $sensors[] = [
        'sensor_id' => $new_sensor_id,
        'sensor_place' => $new_sensor_place,
        'sensor_location' => $new_sensor_location,
        'availability' => $new_availability
    ];
}

// 5. Encode the updated array back to JSON format
$updated_json = json_encode($sensors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// 6. Write the updated JSON string back to the file
if (file_put_contents($sensor_file, $updated_json) === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to write data to sensor_output.json. Check file permissions.']);
    exit;
}

// 7. Success response
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => $sensor_found ? 'Sensor updated successfully.' : 'New sensor added successfully.']);

?>