# Online Vehicle Service Booking and Cost Estimation System
**AutoCare Garage — Academic Assignment Project**  
**Course:** Web Development II (CAT 1)  
**Student Admission Number:** `CT100/G/24825/24`  
**Assigned Individualization Rule:** **Digit 5** (`24825` -> Last digit `5`) &mdash; **Add Vehicle Pickup and Delivery costing KSh 1,500**

---

## 1. Project Overview & Purpose

The **AutoCare Garage Online Vehicle Service Booking and Cost Estimation System** is a university-level web application designed to streamline vehicle maintenance scheduling. It allows customers to:
1. Provide their contact and vehicle specifications (category, model, year, appointment date).
2. Select customized automotive maintenance packages (Engine Oil Change, Brake Inspection, Wheel Alignment, Computer Diagnosis, Full Service, and Vehicle Pickup & Delivery).
3. View real-time dynamic feedback (category maintenance advisories, vintage vehicle age warnings, itemized selected services, live count, and instant total cost in Kenyan Shillings).
4. Submit the booking request via HTTP POST to an authoritative PHP backend that independently validates all inputs, re-verifies pricing, persists the booking into a MySQL/MariaDB database, and generates an official confirmation receipt with a unique booking reference.

The project strictly adheres to an **academic black-and-white visual design** (pure white background, solid black borders, typography, and buttons, with subtle grey borders) to maximize legibility, contrast, and professional restraint without distracting color accents, emojis, or stock photos.

---

## 2. Technologies Used

* **HTML5:** Semantic document structure (`<header>`, `<nav>`, `<main>`, `<fieldset>`, `<table>`, `<footer>`), accessible forms with labels and ARIA live regions.
* **CSS3:** Vanilla CSS design system with custom properties, responsive grid and flexbox layouts, visible keyboard focus indicators, and strict black-and-white academic styling.
* **JavaScript (ES6):** Vanilla client-side script implementing `validateServiceForm()`, `calculateServiceCost()`, `checkVehicleAge()`, dynamic DOM updates, and custom event handlers.
* **PHP 8.x:** Server-side processor (`process_service.php`) enforcing independent POST validation, sanitization, authoritative cost recalculation, and database operations.
* **MySQL / MariaDB:** Relational database management via PDO prepared statements with transaction handling and foreign key integrity.
* **XAMPP:** Local Apache HTTP Server (Port 80) and MySQL Server (Port 3306).

---

## 3. Project Directory Structure

```text
vehicleservice/
│
├── index.html                   # AutoCare Garage homepage, service overview, and booking steps
├── service.html                 # Vehicle service booking form & interactive cost calculator
├── process_service.php          # Server-side form processor, validation, total verification, & confirmation
├── bookings.php                 # Live database bookings registry showing all confirmed appointments
├── database.sql                 # MySQL schema (service_bookings & booking_services tables)
├── README.md                    # Comprehensive setup, execution, and testing documentation
│
├── config/
│   └── database.php             # Database credentials and robust PDO connection handler
│
├── css/
│   └── style.css                # Shared academic black-and-white stylesheet
│
├── js/
│   └── script.js                # Form validation, live calculator, and dynamic advisories
```

---

## 4. Admission-Number Individualization Feature

* **Student Admission Number:** `CT100/G/24825/24`
* **Sequence Extraction:** The numeric sequence is `24825`. The final digit is **`5`**.
* **Assigned Rule (Digit 5):**
  > **"Add vehicle pickup and delivery costing KSh 1,500."**
* **Implementation Details:**
  * **Frontend (`service.html` & `js/script.js`):** "Vehicle Pickup and Delivery" is included in the service packages table with a charge of **KSh 1,500** and marked with an `[Assigned Rule (Digit 5)]` badge. When checked, the live JavaScript calculator immediately increments the total by KSh 1,500 and lists the service in the booking breakdown.
  * **Backend (`process_service.php`):** The server master price dictionary includes `'pickup_delivery' => ['name' => 'Vehicle Pickup and Delivery', 'price' => 1500]`. If submitted, the authoritative PHP calculation charges exactly KSh 1,500, verifies the item, saves it to MariaDB, and displays it on the confirmation page.

---

## 5. Standard Service Price Schedule

| Service Code | Service Name | Base Rate (KSh) | Description |
| :--- | :--- | :---: | :--- |
| `oil_change` | Engine Oil Change | 3,000 | Oil drain, filter replacement, synthetic refill |
| `brake_inspection` | Brake Inspection | 2,000 | Inspection of pads, discs, calipers, and fluid |
| `wheel_alignment` | Wheel Alignment | 3,500 | Precision computerized tracking and camber |
| `computer_diagnosis` | Computer Diagnosis | 2,500 | OBD-II scanning, error code diagnosis |
| `full_service` | Full Service | 8,000 | Multipoint inspection, plugs, filters, road test |
| `pickup_delivery` | Vehicle Pickup and Delivery | 1,500 | Doorstep collection and vehicle return (**Digit 5**) |

---

## 6. Installation & Setup Instructions (XAMPP on Windows)

Follow these steps to run the application on any standard Windows PC with XAMPP:

### Step 1: Start XAMPP Services
1. Open the **XAMPP Control Panel**.
2. Start the **Apache** module (default Port `80`).
3. Start the **MySQL** module (default Port `3306`).

### Step 2: Copy Project to Document Root
1. Extract the submission ZIP file (`web development two cat 1 project.zip`).
2. Copy the entire `vehicleservice/` folder into your XAMPP web root:
   ```text
   C:\xampp\htdocs\vehicleservice
   ```

### Step 3: Import Database Schema
1. Open your web browser and navigate to phpMyAdmin:
   ```text
   http://localhost/phpmyadmin/
   ```
2. Click the **Import** tab.
3. Choose the file `C:\xampp\htdocs\vehicleservice\database.sql` and click **Import** (or **Go**).
4. Alternatively, from a Windows command prompt:
   ```cmd
   C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\vehicleservice\database.sql
   ```
   *This creates the database `autocare_db` and tables `service_bookings` and `booking_services`.*

### Step 4: Database Configuration (Optional)
The file `config/database.php` uses standard default XAMPP credentials:
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'autocare_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```
If your local MySQL uses a custom password or port, edit these values in `config/database.php`.

### Step 5: Launch the Application
Open your browser and navigate to:
```text
http://localhost/vehicleservice/index.html
```

---

## 7. Testing & Verification Checklist

### A. Client-Side JavaScript Validation (`service.html`)
1. Click **Book a Service** and try clicking **Submit Service Request** with empty fields:
   * **Result:** Form submission is halted. Inline error messages appear beneath each missing field, and a consolidated error banner appears at the top.
2. Enter a phone number with fewer or more than 10 digits (e.g. `12345`):
   * **Result:** Rejected with message `"Phone number must contain exactly 10 digits (e.g. 0712345678)"`.
3. Enter an invalid email address (e.g. `user@test`):
   * **Result:** Rejected with email format error.
4. Enter a manufacturing year before 1950 or in the future:
   * **Result:** Rejected with bounds error.
5. Select a preferred service date in the past:
   * **Result:** Rejected with message `"Preferred service date cannot be in the past"`.
6. Leave all services unchecked:
   * **Result:** Rejected with message `"Please select at least one service from the list below"`.

### B. Dynamic Interactivity & Advisories
1. **Vehicle Category Advice:** Select **SUV**, **Pickup**, or **Van** in the dropdown. Notice the immediate contextual advisory box update.
2. **Vehicle Age Check:** Enter year `2008` (18 years old). Notice the dynamic warning:
   * `"This vehicle is 18 years old. Note: Vehicles over 10 years require extra mechanical vigilance... Older vehicles (>15 years) are strongly recommended to include a comprehensive full multipoint inspection..."`
3. **Live Cost Calculator:**
   * Check **Engine Oil Change** (KSh 3,000) &rarr; Total shows `KSh 3,000`.
   * Check **Brake Inspection** (KSh 2,000) &rarr; Total updates immediately to `KSh 5,000`.
   * Check **Vehicle Pickup and Delivery** (KSh 1,500) &rarr; Total updates immediately to `KSh 6,500`.
   * Uncheck any service &rarr; Total decreases immediately, and the item is removed from the sidebar breakdown.
4. **Form Reset:** Click **Reset Form** &rarr; Inputs, error notices, dynamic warnings, and the calculation sidebar return to initial empty state.

### C. Server-Side PHP Validation & Database Persistence (`process_service.php`)
1. Fill valid sample details and click **Submit Service Request**:
   * Customer: `Peter Kamau`, `0722123456`, `peter.kamau@example.com`
   * Vehicle: `KDA 789B`, `SUV`, `Toyota Land Cruiser Prado`, `2019`, date next week
   * Services: `Engine Oil Change`, `Brake Inspection`, `Vehicle Pickup and Delivery`
2. **Confirmation Screen:**
   * Displays heading **`SERVICE REQUEST RECEIVED`**.
   * Shows unique reference code (e.g. `ACG-2026-XXXXX`) and timestamp.
   * Displays client and vehicle summary cards.
   * Itemizes services and official rates: Engine Oil Change (KSh 3,000), Brake Inspection (KSh 2,000), Vehicle Pickup and Delivery (KSh 1,500).
   * Calculates authoritative total: **`KSh 6,500`**.
   * Displays log confirming data was persisted to `autocare_db.service_bookings`.
3. **Database Verification:**
   * Open phpMyAdmin (`http://localhost/phpmyadmin/`) &rarr; `autocare_db` &rarr; `service_bookings`. The new booking record is visible.

---

## 8. Security & Code Quality Measures

* **Input Sanitization & Output Escaping:** All user-supplied values are escaped with `htmlspecialchars()` before HTML rendering to eliminate Cross-Site Scripting (XSS).
* **Server-Side Authoritative Pricing:** Prices submitted by the client are ignored; PHP cross-references submitted keys against the server price dictionary to eliminate price manipulation.
* **SQL Injection Prevention:** All database operations utilize PDO prepared statements with parameterized inputs.
* **Graceful Database Degradation:** If MySQL is not running or the database has not yet been imported, PHP displays a user-friendly configuration notice while safely completing in-memory estimation without exposing raw server exceptions.
