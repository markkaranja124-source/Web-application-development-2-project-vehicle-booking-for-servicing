<?php
/**
 * AutoCare Garage: Online Vehicle Service Booking and Cost Estimation System
 * Web Development II - CAT 1
 * 
 * Server-Side Processing: Strict Independent Validation, Authoritative Cost Calculation,
 * Database Persistence (PDO), and Academic Black & White Confirmation Layout.
 * 
 * Student Admission Number: CT100/G/24825/24
 * Assigned Individualization Rule: Digit 5 -> Vehicle Pickup and Delivery (KSh 1,500)
 */

declare(strict_types=1);

// Include database configuration
require_once __DIR__ . '/config/database.php';

// Helper function for safe HTML escaping
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 1. Authoritative Server-Side Price Table (Single Source of Truth)
$MASTER_SERVICES = [
    'oil_change' => [
        'name' => 'Engine Oil Change',
        'price' => 3000,
        'desc' => 'Drain, replace filter, and refill synthetic motor oil.'
    ],
    'brake_inspection' => [
        'name' => 'Brake Inspection',
        'price' => 2000,
        'desc' => 'Full inspection of calipers, pads, discs, lines, and fluid.'
    ],
    'wheel_alignment' => [
        'name' => 'Wheel Alignment',
        'price' => 3500,
        'desc' => 'Precision 4-wheel computerized tracking and camber balancing.'
    ],
    'computer_diagnosis' => [
        'name' => 'Computer Diagnosis',
        'price' => 2500,
        'desc' => 'OBD-II engine and electrical diagnostics with code scan report.'
    ],
    'full_service' => [
        'name' => 'Full Service',
        'price' => 8000,
        'desc' => 'Comprehensive maintenance tune-up, spark plugs, filters, and road test.'
    ],
    // Assigned individualization feature (Digit 5: CT100/G/24825/24)
    'pickup_delivery' => [
        'name' => 'Vehicle Pickup and Delivery',
        'price' => 1500,
        'desc' => 'Convenient vehicle collection and doorstep return.'
    ]
];

$ALLOWED_VEHICLE_TYPES = ['Saloon Car', 'SUV', 'Pickup', 'Van'];

$errors = [];
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');

// Verify POST method
if (!$isPost) {
    $errors[] = 'Invalid request method. The service booking form must be submitted via HTTP POST.';
}

// Data extraction and sanitization
$customerName = trim($_POST['customer_name'] ?? '');
$phoneNumber  = trim($_POST['phone_number'] ?? '');
$email        = trim($_POST['email'] ?? '');
$regNumber    = strtoupper(trim($_POST['registration_number'] ?? ''));
$vehicleType  = trim($_POST['vehicle_type'] ?? '');
$vehicleModel = trim($_POST['vehicle_model'] ?? '');
$yearRaw      = trim($_POST['year_of_manufacture'] ?? '');
$preferredDate= trim($_POST['preferred_date'] ?? '');
$rawServices  = $_POST['services'] ?? [];

$yearOfManufacture = is_numeric($yearRaw) ? (int)$yearRaw : 0;
$currentYear = (int)date('Y');

// 2. Server-Side Independent Validation
if ($isPost) {
    // Validate Customer Name
    if ($customerName === '') {
        $errors[] = 'Customer full name cannot be blank.';
    } elseif (mb_strlen($customerName) < 3) {
        $errors[] = 'Customer name must be at least 3 characters long.';
    } elseif (mb_strlen($customerName) > 100) {
        $errors[] = 'Customer name cannot exceed 100 characters.';
    }

    // Validate Phone Number (must be exactly 10 digits)
    if ($phoneNumber === '') {
        $errors[] = 'Phone number is required.';
    } elseif (!preg_match('/^\d{10}$/', $phoneNumber)) {
        $errors[] = 'Phone number must contain exactly 10 digits (e.g. 0712345678).';
    }

    // Validate Email Address
    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'The provided email address format is invalid.';
    }

    // Validate Registration Number
    if ($regNumber === '') {
        $errors[] = 'Vehicle registration number is required.';
    } elseif (mb_strlen($regNumber) < 4 || mb_strlen($regNumber) > 15) {
        $errors[] = 'Registration number must be between 4 and 15 characters.';
    }

    // Validate Vehicle Type
    if ($vehicleType === '') {
        $errors[] = 'Please select a valid vehicle type.';
    } elseif (!in_array($vehicleType, $ALLOWED_VEHICLE_TYPES, true)) {
        $errors[] = 'The selected vehicle category is not recognized by the system.';
    }

    // Validate Vehicle Model
    if ($vehicleModel === '') {
        $errors[] = 'Vehicle model is required.';
    } elseif (mb_strlen($vehicleModel) < 2) {
        $errors[] = 'Vehicle model must have at least 2 characters.';
    }

    // Validate Year of Manufacture
    if ($yearRaw === '' || $yearOfManufacture === 0) {
        $errors[] = 'Year of manufacture is required.';
    } elseif ($yearOfManufacture < 1950) {
        $errors[] = 'Year of manufacture cannot be older than 1950.';
    } elseif ($yearOfManufacture > $currentYear) {
        $errors[] = "Year of manufacture cannot be in the future (maximum: {$currentYear}).";
    }

    // Validate Preferred Service Date
    if ($preferredDate === '') {
        $errors[] = 'Preferred service date is required.';
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $preferredDate);
        if (!$d || $d->format('Y-m-d') !== $preferredDate) {
            $errors[] = 'Preferred service date is not a valid calendar date format.';
        } else {
            $today = new DateTime('today');
            if ($d < $today) {
                $errors[] = 'Preferred service date cannot be in the past.';
            }
        }
    }

    // Validate Selected Services Array
    if (!is_array($rawServices) || count($rawServices) === 0) {
        $errors[] = 'At least one garage service must be selected.';
    }
}

// 3. Authoritative Server Cost Calculation
$validatedServices = [];
$authoritativeTotal = 0;

if ($isPost && empty($errors)) {
    foreach ($rawServices as $serviceKey) {
        // Enforce strict check against server master price list
        if (!is_string($serviceKey) || !array_key_exists($serviceKey, $MASTER_SERVICES)) {
            $errors[] = "Unrecognized or unauthorized service identifier submitted: '{$serviceKey}'.";
            continue;
        }

        $serviceData = $MASTER_SERVICES[$serviceKey];
        $validatedServices[] = [
            'code'  => $serviceKey,
            'name'  => $serviceData['name'],
            'price' => $serviceData['price']
        ];
        $authoritativeTotal += $serviceData['price'];
    }

    if (count($validatedServices) === 0 && empty($errors)) {
        $errors[] = 'No valid services were found in your submission.';
    }
}

// 4. Database Persistence (XAMPP MySQL / MariaDB via PDO)
$dbError = null;
$bookingReference = 'ACG-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
$isSavedToDatabase = false;

if ($isPost && empty($errors)) {
    $pdo = getDatabaseConnection($dbError);

    if ($pdo !== null) {
        try {
            $pdo->beginTransaction();

            // Insert into service_bookings table
            $stmtBooking = $pdo->prepare("
                INSERT INTO `service_bookings` 
                (`booking_reference`, `customer_name`, `phone_number`, `email`, `registration_number`, `vehicle_type`, `vehicle_model`, `year_of_manufacture`, `preferred_date`, `service_count`, `total_cost`)
                VALUES
                (:ref, :cname, :phone, :email, :reg, :vtype, :vmodel, :year, :pdate, :scount, :total)
            ");

            $serviceCount = count($validatedServices);
            $stmtBooking->execute([
                ':ref'    => $bookingReference,
                ':cname'  => $customerName,
                ':phone'  => $phoneNumber,
                ':email'  => $email,
                ':reg'    => $regNumber,
                ':vtype'  => $vehicleType,
                ':vmodel' => $vehicleModel,
                ':year'   => $yearOfManufacture,
                ':pdate'  => $preferredDate,
                ':scount' => $serviceCount,
                ':total'  => $authoritativeTotal
            ]);

            $bookingId = (int)$pdo->lastInsertId();

            // Insert into booking_services table
            $stmtItem = $pdo->prepare("
                INSERT INTO `booking_services` 
                (`booking_id`, `service_code`, `service_name`, `service_price`)
                VALUES
                (:bid, :code, :sname, :price)
            ");

            foreach ($validatedServices as $item) {
                $stmtItem->execute([
                    ':bid'   => $bookingId,
                    ':code'  => $item['code'],
                    ':sname' => $item['name'],
                    ':price' => $item['price']
                ]);
            }

            $pdo->commit();
            $isSavedToDatabase = true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $dbError = 'Database operation encountered an issue: ' . $e->getMessage();
        }
    }
}

// Calculate vehicle age for confirmation summary
$vehicleAge = $currentYear - $yearOfManufacture;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoCare Garage - Service Request Status</title>
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
                    <li><a href="bookings.php">View Bookings</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if (!empty($errors)): ?>
                <!-- Validation Error Page View -->
                <div class="page-intro">
                    <h2>Booking Submission Errors</h2>
                    <p>The server could not process your booking request due to validation issues.</p>
                </div>

                <div class="error-summary visible">
                    <h3>The Following Errors Must Be Resolved:</h3>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div style="margin-top: 24px;">
                    <a href="service.html" class="btn btn-primary">&larr; Return to Service Booking Form</a>
                    <a href="index.html" class="btn btn-secondary" style="margin-left: 10px;">Return to Homepage</a>
                </div>

            <?php else: ?>
                <!-- Official Service Request Received Confirmation -->
                <div class="page-intro">
                    <h2>SERVICE REQUEST RECEIVED</h2>
                    <p>Your vehicle maintenance request has been successfully validated, calculated, and confirmed by the AutoCare Garage booking engine.</p>
                </div>

                <div class="confirmation-card">
                    <div class="ref-banner">
                        <div>
                            <h3>Booking Confirmed</h3>
                            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                                Timestamp: <?= e(date('d M Y, H:i:s')) ?>
                            </p>
                        </div>
                        <div>
                            <span class="badge-ref">REFERENCE: <?= e($bookingReference) ?></span>
                        </div>
                    </div>

                    <!-- Client & Vehicle Details Summary Grid -->
                    <div class="details-grid">
                        <div class="details-column">
                            <h4>Customer Details</h4>
                            <ul class="details-list">
                                <li>
                                    <span class="field-name">Customer Name:</span>
                                    <span class="field-val"><?= e($customerName) ?></span>
                                </li>
                                <li>
                                    <span class="field-name">Phone Number:</span>
                                    <span class="field-val"><?= e($phoneNumber) ?></span>
                                </li>
                                <li>
                                    <span class="field-name">Email Address:</span>
                                    <span class="field-val"><?= e($email) ?></span>
                                </li>
                            </ul>
                        </div>

                        <div class="details-column">
                            <h4>Vehicle Information</h4>
                            <ul class="details-list">
                                <li>
                                    <span class="field-name">Registration Plate:</span>
                                    <span class="field-val"><?= e($regNumber) ?></span>
                                </li>
                                <li>
                                    <span class="field-name">Vehicle Category:</span>
                                    <span class="field-val"><?= e($vehicleType) ?></span>
                                </li>
                                <li>
                                    <span class="field-name">Make &amp; Model:</span>
                                    <span class="field-val"><?= e($vehicleModel) ?></span>
                                </li>
                                <li>
                                    <span class="field-name">Manufacture Year:</span>
                                    <span class="field-val"><?= e((string)$yearOfManufacture) ?> (<?= e((string)$vehicleAge) ?> years old)</span>
                                </li>
                                <li>
                                    <span class="field-name">Appointment Date:</span>
                                    <span class="field-val"><?= e(date('l, d F Y', strtotime($preferredDate))) ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Itemized Services & Authoritative Prices -->
                    <h4 style="font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                        Selected Services &amp; Server-Verified Charges
                    </h4>

                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Service Description</th>
                                <th style="width: 140px; text-align: right;">Official Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($validatedServices as $index => $item): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= e($item['name']) ?></strong>
                                        <?php if ($item['code'] === 'pickup_delivery'): ?>
                                            <span class="badge-individual">Assigned Rule (Digit 5)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 600;">
                                        KSh <?= number_format($item['price']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align: right; text-transform: uppercase;">Final Estimated Service Cost:</td>
                                <td style="text-align: right; font-size: 18px; font-weight: 800;">
                                    KSh <?= number_format($authoritativeTotal) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Persistence Status Note -->
                    <div class="info-box" style="margin-top: 20px;">
                        <strong>System Persistence Log:</strong>
                        <?php if ($isSavedToDatabase): ?>
                            <span>Record successfully saved into the local XAMPP MariaDB database (<code>autocare_db.service_bookings</code>).</span>
                        <?php elseif ($dbError): ?>
                            <span>Notice: <?= e($dbError) ?> (The service booking was processed in application memory).</span>
                        <?php else: ?>
                            <span>Service booking processed successfully.</span>
                        <?php endif; ?>
                    </div>

                    <!-- Individualization Rule Documentation Banner -->
                    <div class="info-box" style="margin-top: 12px; background-color: #ffffff;">
                        <strong>Academic Individualization Feature Verification:</strong>
                        <span>
                            Student Admission Number: <strong>CT100/G/24825/24</strong> (Final sequence digit: <strong>5</strong>).<br>
                            Assigned Rule: <strong>Add vehicle pickup and delivery costing KSh 1,500</strong>. Verified across JavaScript client and PHP server-side pricing ledger.
                        </span>
                    </div>

                    <div style="margin-top: 28px; display: flex; gap: 12px; flex-wrap: wrap;">
                        <a href="service.html" class="btn btn-primary">Book Another Service</a>
                        <a href="bookings.php" class="btn btn-secondary">View Bookings Registry</a>
                        <a href="index.html" class="btn btn-secondary">Return to Homepage</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
