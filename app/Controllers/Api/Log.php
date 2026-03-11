<?php

namespace App\Controllers\Api;

class Log extends BaseController
{

    /**
     * Handles the logging of data via an API endpoint.
     *
     * This method captures JSON data from the request body, validates the required fields,
     * and inserts the log data into the database. It returns appropriate HTTP responses
     * based on the success or failure of the operation.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     * 
     * - HTTP 400: If the request data is invalid or required fields are missing.
     * - HTTP 500: If there is a failure in inserting the log data into the database.
     * - HTTP 200: If the log data is successfully inserted.
     *
     * Request JSON Structure:
     * {
     *     "message": string, // Required. The log message. Truncated to 255 characters if longer.
     *     "level": int,      // Required. The log level. Must be numeric.
     *     "domain": string   // Required. The domain associated with the log.
     * }
     *
     * Response JSON Structure:
     * - On success:
     *   {
     *       "success": "Log data inserted successfully"
     *   }
     * - On error:
     *   {
     *       "error": "Error message describing the issue"
     *   }
     */
    public function index()
    {
        // Capture JSON data from the request body
        $data = $this->request->getJSON(true);
        if (!is_array($data)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No data provided']);
        }
        // Validate the message field
        if (!isset($data['message'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing message field']);
        }
        // Test message is over 255 characters, if so, truncate it
        if (strlen($data['message']) > 255) {
            $data['message'] = substr($data['message'], 0, 255);
        }
        // Validate the level field
        if (!isset($data['level'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing level field']);
        }
        // Test level is numeric
        if (!is_numeric($data['level'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Level must be numeric']);
        }
        // Validate the domain field
        if (!isset($data['domain'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing domain field']);
        }
        // Load the model
        $model = model('LogsModel');
        // Insert the log data into the database
        $result = $model->insert([
            'message' => $data['message'],
            'level' => $data['level'],
            'domain' => $data['domain'],
        ]);
        // Check if the insert was successful
        if ($result === false) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to insert log data']);
        }
        // Return a success response
        return $this->response->setJSON(['success' => 'Log data inserted successfully']);
    }
}