<?php

function logDetailedAction($conn, $userID, $username, $roleType, $actionType, $partID, $oldValue, $newValue, $fieldName) {
    $timestamp = date("Y-m-d H:i:s");
    
    $logQuery = $conn->prepare("INSERT INTO logs (UserID, ActionBy, RoleType, ActionType, PartID, OldValue, NewValue, FieldName, Timestamp) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$logQuery) {
        error_log("Error preparing detailed log statement: " . $conn->error);
        return false;
    }
    
    $logQuery->bind_param("isssissss", $userID, $username, $roleType, $actionType, $partID, $oldValue, $newValue, $fieldName, $timestamp);
    $result = $logQuery->execute();
    $logQuery->close();
    
    return $result;
}

function logSimpleAction($conn, $userID, $username, $roleType, $actionType, $partID) {
    $timestamp = date("Y-m-d H:i:s");
    
    $logQuery = $conn->prepare("INSERT INTO logs (UserID, ActionBy, RoleType, ActionType, PartID, Timestamp) 
                                VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$logQuery) {
        error_log("Error preparing simple log statement: " . $conn->error);
        return false;
    }
    
    $logQuery->bind_param("isssis", $userID, $username, $roleType, $actionType, $partID, $timestamp);
    $result = $logQuery->execute();
    $logQuery->close();
    
    return $result;
}

function logUserAction($conn, $userID, $action, $details = '', $affectedID = null, $affectedType = null) {
    $timestamp = date('Y-m-d H:i:s');
    $userIP = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    $query = "INSERT INTO activity_log 
             (UserID, Action, Details, AffectedID, AffectedType, Timestamp, IPAddress, UserAgent) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
             
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssssss", 
        $userID,
        $action,
        $details,
        $affectedID,
        $affectedType,
        $timestamp,
        $userIP,
        $userAgent
    );
    
    return $stmt->execute();
}

function logSystemEvent($conn, $eventType, $description, $severity = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $serverIP = $_SERVER['SERVER_ADDR'];
    
    $query = "INSERT INTO system_log 
             (EventType, Description, Severity, Timestamp, ServerIP) 
             VALUES (?, ?, ?, ?, ?)";
             
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss",
        $eventType,
        $description,
        $severity,
        $timestamp,
        $serverIP
    );
    
    return $stmt->execute();
}

function logError($conn, $errorMessage, $errorCode = null, $stackTrace = null) {
    $timestamp = date('Y-m-d H:i:s');
    $userIP = $_SERVER['REMOTE_ADDR'];
    $requestURL = $_SERVER['REQUEST_URI'];
    
    $query = "INSERT INTO error_log 
             (ErrorMessage, ErrorCode, StackTrace, Timestamp, IPAddress, RequestURL) 
             VALUES (?, ?, ?, ?, ?, ?)";
             
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss",
        $errorMessage,
        $errorCode,
        $stackTrace,
        $timestamp,
        $userIP,
        $requestURL
    );
    
    return $stmt->execute();
}

function logPartOperation($conn, $userID, $partID, $operation, $oldValues = null, $newValues = null) {
    $timestamp = date('Y-m-d H:i:s');
    $details = json_encode([
        'operation' => $operation,
        'old_values' => $oldValues,
        'new_values' => $newValues
    ]);
    
    return logUserAction($conn, $userID, 'PART_' . strtoupper($operation), $details, $partID, 'part');
}

function logServiceOperation($conn, $userID, $serviceID, $operation, $oldValues = null, $newValues = null) {
    $timestamp = date('Y-m-d H:i:s');
    $details = json_encode([
        'operation' => $operation,
        'old_values' => $oldValues,
        'new_values' => $newValues
    ]);
    
    return logUserAction($conn, $userID, 'SERVICE_' . strtoupper($operation), $details, $serviceID, 'service');
}

function logSupplierOperation($conn, $userID, $supplierID, $operation, $oldValues = null, $newValues = null) {
    $timestamp = date('Y-m-d H:i:s');
    $details = json_encode([
        'operation' => $operation,
        'old_values' => $oldValues,
        'new_values' => $newValues
    ]);
    
    return logUserAction($conn, $userID, 'SUPPLIER_' . strtoupper($operation), $details, $supplierID, 'supplier');
}

function logAuthEvent($conn, $userID, $eventType, $status, $details = '') {
    $timestamp = date('Y-m-d H:i:s');
    $userIP = $_SERVER['REMOTE_ADDR'];
    
    $query = "INSERT INTO auth_log 
             (UserID, EventType, Status, Details, Timestamp, IPAddress) 
             VALUES (?, ?, ?, ?, ?, ?)";
             
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss",
        $userID,
        $eventType,
        $status,
        $details,
        $timestamp,
        $userIP
    );
    
    return $stmt->execute();
}

function getActivityLogs($conn, $filters = [], $limit = 100, $offset = 0) {
    $query = "SELECT al.*, u.Username 
             FROM activity_log al 
             LEFT JOIN user u ON al.UserID = u.UserID 
             WHERE 1=1";
             
    if (!empty($filters['user_id'])) {
        $query .= " AND al.UserID = '" . $conn->real_escape_string($filters['user_id']) . "'";
    }
    if (!empty($filters['action'])) {
        $query .= " AND al.Action = '" . $conn->real_escape_string($filters['action']) . "'";
    }
    if (!empty($filters['date_from'])) {
        $query .= " AND al.Timestamp >= '" . $conn->real_escape_string($filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $query .= " AND al.Timestamp <= '" . $conn->real_escape_string($filters['date_to']) . "'";
    }
    
    $query .= " ORDER BY al.Timestamp DESC LIMIT ?, ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $offset, $limit);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>