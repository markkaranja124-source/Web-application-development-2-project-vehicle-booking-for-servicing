<?php
/**
 * AutoCare Garage: View Bookings Registry
 * Web Development II - CAT 1
 * 
 * Displays all persisted service bookings retrieved from the MariaDB database.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$dbError = null;
$pdo = getDatabaseConnection($dbError);
$bookings = [];

if ($pdo !== null) {
    try {
        $sql = "
            SELECT 
                b.id,
                b.booking_reference,
                b.customer_name,
                b.phone_number,
                b.email,
                b.registration_number,
                b.vehicle_type,
                b.vehicle_model,
                b.year_of_manufacture,
                b.preferred_date,
                b.service_count,
                b.total_cost,
                b.created_at,
                GROUP_CONCAT(s.service_name SEPARATOR ', ') AS services_list
            FROM `service_bookings` b
            LEFT JOIN `booking_services` s ON b.id = s.booking_id
            GROUP BY b.id
            ORDER BY b.id DESC
        ";
        $stmt = $pdo->query($sql);
        $bookings = $stmt->fetchAll();
    } catch (Exception $ex) {
        $dbError = 'Error querying booking records: ' . $ex->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoCare Garage - Service Bookings Registry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <div class="brand-section">
                <h1><a href="index.html">AutoCare Garage</a></h1>
                <p class="tagline">Online Vehicle Service Booking &amp; Cost Estimation System</p>
            </div>
            <nav class="main-nav" aria-label="Main Navigation">
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="service.html">Book a Service</a></li>
                    <li><a href="bookings.php" class="active">View Bookings</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="page-intro">
                <h2>Service Bookings Registry</h2>
                <p>All recorded vehicle maintenance appointments retrieved live from the AutoCare Garage database.</p>
            </div>

            <?php if ($dbError): ?>
                <div class="error-summary visible">
                    <h3>Database Notice</h3>
                    <p><?= e($dbError) ?></p>
                </div>
            <?php endif; ?>

            <?php if (empty($bookings)): ?>
                <div class="card" style="text-align: center; padding: 40px 20px;">
                    <h3>No Bookings Recorded Yet</h3>
                    <p style="margin: 12px 0 20px;">There are currently no service appointments saved in the database.</p>
                    <a href="service.html" class="btn btn-primary">Book Your First Service</a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto; margin-bottom: 24px;">
                    <table class="services-selection-table" style="min-width: 800px;">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Reference</th>
                                <th>Customer</th>
                                <th>Vehicle Details</th>
                                <th>Preferred Date</th>
                                <th>Services Booked</th>
                                <th style="text-align: right; width: 120px;">Total Cost</th>
                                <th style="width: 150px;">Date Booked</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td>
                                        <strong style="font-family: monospace; font-size: 13px;"><?= e($b['booking_reference']) ?></strong>
                                    </td>
                                    <td>
                                        <strong><?= e($b['customer_name']) ?></strong><br>
                                        <span style="font-size: 12px; color: var(--text-secondary);"><?= e($b['phone_number']) ?></span>
                                    </td>
                                    <td>
                                        <strong><?= e($b['registration_number']) ?></strong> &mdash; <?= e($b['vehicle_type']) ?><br>
                                        <span style="font-size: 12px; color: var(--text-secondary);"><?= e($b['vehicle_model']) ?> (<?= e((string)$b['year_of_manufacture']) ?>)</span>
                                    </td>
                                    <td>
                                        <strong><?= e(date('d M Y', strtotime($b['preferred_date']))) ?></strong>
                                    </td>
                                    <td style="font-size: 13px;">
                                        <?= e($b['services_list'] ?? 'None specified') ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 700;">
                                        KSh <?= number_format((int)$b['total_cost']) ?>
                                    </td>
                                    <td style="font-size: 12px; color: var(--text-secondary);">
                                        <?= e(date('d M Y, H:i', strtotime($b['created_at']))) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: gap; gap: 12px;">
                    <p style="font-size: 14px; color: var(--text-secondary);">
                        Total Recorded Bookings: <strong><?= count($bookings) ?></strong>
                    </p>
                    <div>
                        <a href="service.html" class="btn btn-primary">Add New Booking</a>
                        <a href="index.html" class="btn btn-secondary" style="margin-left: 8px;">Back to Home</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
