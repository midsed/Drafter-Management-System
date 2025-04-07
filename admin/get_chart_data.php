<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$chart = $_GET['chart'] ?? '';
$period = $_GET['period'] ?? 'daily';
$response = ['labels' => [], 'values' => []];

function getDateFormat($period) {
    switch($period) {
        case 'weekly':
            return '%Y-%u'; // Year-Week
        case 'monthly':
            return '%Y-%m'; // Year-Month
        case 'yearly':
            return '%Y'; // Year
        default:
            return '%Y-%m-%d'; // Year-Month-Day
    }
}

function getDateInterval($period) {
    switch($period) {
        case 'weekly':
            return 'INTERVAL 12 WEEK';
        case 'monthly':
            return 'INTERVAL 12 MONTH';
        case 'yearly':
            return 'INTERVAL 5 YEAR';
        default:
            return 'INTERVAL 30 DAY';
    }
}

$dateFormat = getDateFormat($period);
$interval = getDateInterval($period);

switch($chart) {
    case 'stock':
        $query = "SELECT 
            DATE_FORMAT(DateAdded, '$dateFormat') as date_group,
            AVG(Quantity) as avg_quantity
            FROM part
            WHERE DateAdded >= DATE_SUB(CURDATE(), $interval)
            GROUP BY date_group
            ORDER BY date_group ASC";
        break;

    case 'checkout':
        $query = "SELECT 
            DATE_FORMAT(RetrievedDate, '$dateFormat') as date_group,
            SUM(Quantity) as total_quantity
            FROM receipt
            WHERE RetrievedDate >= DATE_SUB(CURDATE(), $interval)
            GROUP BY date_group
            ORDER BY date_group ASC";
        break;

    case 'value':
        $query = "SELECT 
            Category,
            SUM(Price * Quantity) as total_value
            FROM part
            WHERE DateAdded >= DATE_SUB(CURDATE(), $interval)
            GROUP BY Category
            ORDER BY total_value DESC";
        break;

    case 'value_total':
        $query = "SELECT 
            SUM(Price * Quantity) as grand_total
            FROM part
            WHERE DateAdded >= DATE_SUB(CURDATE(), $interval)";
        break;
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid chart type']);
        exit();
}

$result = $conn->query($query);

if ($result) {
    if ($chart === 'value_total') {
        $row = $result->fetch_assoc();
        $response['grand_total'] = floatval($row['grand_total']);
    } else if ($chart === 'value') {
        $colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#7BC8A4', '#E7E9ED'
        ];
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $response['labels'][] = $row['Category'] ?: 'Uncategorized';
            $response['values'][] = floatval($row['total_value']);
            $response['colors'][] = $colors[$i % count($colors)];
            $i++;
        }
    } else {
        while ($row = $result->fetch_assoc()) {
            $response['labels'][] = $row['date_group'];
            $response['values'][] = floatval($row[($chart === 'stock' ? 'avg_quantity' : 'total_quantity')]);
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);