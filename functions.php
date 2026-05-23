<?php
// File-based storage for complaints
$dataFile = 'complaints.json';

// Initialize data file if not exists
function initDataFile() {
    global $dataFile;
    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, json_encode(['complaints' => []]));
    }
}

// Generate unique complaint ID
function generateUniqueId() {
    return 'CMP-' . strtoupper(uniqid()) . '-' . rand(1000, 9999);
}

// Save complaint to file
function saveComplaint($complaint) {
    global $dataFile;
    $data = json_decode(file_get_contents($dataFile), true);
    $data['complaints'][] = $complaint;
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    return true;
}

// Get all complaints
function getAllComplaints() {
    global $dataFile;
    if (!file_exists($dataFile)) {
        return [];
    }
    $data = json_decode(file_get_contents($dataFile), true);
    return $data['complaints'] ?? [];
}

// Get complaint by ID
function getComplaintById($id) {
    $complaints = getAllComplaints();
    foreach ($complaints as $complaint) {
        if ($complaint['id'] === $id) {
            return $complaint;
        }
    }
    return null;
}

// Update complaint reply
function updateComplaintReply($id, $reply) {
    global $dataFile;
    $data = json_decode(file_get_contents($dataFile), true);
    foreach ($data['complaints'] as &$complaint) {
        if ($complaint['id'] === $id) {
            $complaint['admin_reply'] = $reply;
            $complaint['replied_at'] = date('Y-m-d H:i:s');
            break;
        }
    }
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    return true;
}

// Get user complaints by email
function getUserComplaints($email) {
    $complaints = getAllComplaints();
    $userComplaints = [];
    foreach ($complaints as $complaint) {
        if ($complaint['email'] === $email) {
            $userComplaints[] = $complaint;
        }
    }
    return $userComplaints;
}
?>
