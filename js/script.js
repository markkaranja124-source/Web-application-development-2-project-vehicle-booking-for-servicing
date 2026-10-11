/**
 * AutoCare Garage: Online Vehicle Service Booking and Cost Estimation System
 * Web Development II - CAT 1
 * 
 * Client-Side JavaScript: Validation, Dynamic Interactivity & Cost Calculator
 * Student Admission Number: CT100/G/24825/24
 * Assigned Individualization Rule: Digit 5 -> Vehicle Pickup and Delivery (KSh 1,500)
 */

document.addEventListener('DOMContentLoaded', function () {
    // =========================================================================
    // 1. CONFIGURATION & STATE
    // =========================================================================

    // Canonical service pricing dictionary matching server-side rules
    const SERVICE_PRICES = {
        'oil_change': { name: 'Engine Oil Change', price: 3000 },
        'brake_inspection': { name: 'Brake Inspection', price: 2000 },
        'wheel_alignment': { name: 'Wheel Alignment', price: 3500 },
        'computer_diagnosis': { name: 'Computer Diagnosis', price: 2500 },
        'full_service': { name: 'Full Service', price: 8000 },
        // Assigned individualization feature (Digit 5: CT100/G/24825/24)
        'pickup_delivery': { name: 'Vehicle Pickup and Delivery', price: 1500 }
    };

    // Category advisories for dynamic feedback
    const VEHICLE_TYPE_INFO = {
        'Saloon Car': 'Standard passenger car protocol. Recommended for city commuting, saloon suspensions, and standard braking systems.',
        'SUV': 'High ground-clearance protocol. Comprehensive checks for all-wheel drive transfer case, heavy suspension bushings, and all-terrain tyres.',
        'Pickup': 'Commercial utility protocol. Heavy-duty rear leaf-spring inspection, differential oil condition, and cargo brake load balancing.',
        'Van': 'High-occupancy and cargo protocol. Multipoint checks for sliding door mechanisms, high-mileage transmission wear, and heavy-duty suspension.'
    };

    // DOM Element References
    const form = document.getElementById('serviceForm');
    const customerNameInput = document.getElementById('customer_name');
    const phoneNumberInput = document.getElementById('phone_number');
    const emailInput = document.getElementById('email');
    const regNumberInput = document.getElementById('registration_number');
    const vehicleTypeSelect = document.getElementById('vehicle_type');
    const vehicleModelInput = document.getElementById('vehicle_model');
    const manufactureYearInput = document.getElementById('year_of_manufacture');
    const preferredDateInput = document.getElementById('preferred_date');
    const serviceCheckboxes = document.querySelectorAll('input[name="services[]"]');
    
    // UI Output Elements
    const validationSummary = document.getElementById('validationSummary');
    const validationSummaryList = document.getElementById('validationSummaryList');
    const vehicleTypeInfoBox = document.getElementById('vehicleTypeInfo');
    const vehicleTypeDesc = document.getElementById('vehicleTypeDescription');
    const vehicleAgeWarning = document.getElementById('vehicleAgeWarning');
    const vehicleAgeMessage = document.getElementById('vehicleAgeMessage');
    const serviceCountSpan = document.getElementById('serviceCount');
    const totalCostDisplay = document.getElementById('totalCostDisplay');
    const selectedServicesList = document.getElementById('selectedServicesList');
    const summaryVehicleType = document.getElementById('summaryVehicleType');
    const summaryVehicleAge = document.getElementById('summaryVehicleAge');

    // Restrict date picker to today or future dates
    const todayIsoString = new Date().toISOString().split('T')[0];
    if (preferredDateInput) {
        preferredDateInput.setAttribute('min', todayIsoString);
    }

    // =========================================================================
    // 2. HELPER UTILITIES: Currency & Error Rendering
    // =========================================================================

    /**
     * Formats integer amounts into Kenyan Shillings (e.g. KSh 7,500)
     * @param {number} amount 
     * @returns {string}
     */
    function formatCurrency(amount) {
        return 'KSh ' + Number(amount).toLocaleString('en-KE');
    }

    /**
     * Displays an inline error message for a specific input field
     * @param {string} fieldId 
     * @param {string} message 
     */
    function showFieldError(fieldId, message) {
        const inputElem = document.getElementById(fieldId);
        const errorElem = document.getElementById('error-' + fieldId);
        if (inputElem) {
            inputElem.classList.add('input-invalid');
        }
        if (errorElem) {
            errorElem.textContent = message;
            errorElem.classList.add('visible');
        }
    }

    /**
     * Clears an inline error message for a specific input field
     * @param {string} fieldId 
     */
    function clearFieldError(fieldId) {
        const inputElem = document.getElementById(fieldId);
        const errorElem = document.getElementById('error-' + fieldId);
        if (inputElem) {
            inputElem.classList.remove('input-invalid');
        }
        if (errorElem) {
            errorElem.textContent = '';
            errorElem.classList.remove('visible');
        }
    }

    /**
     * Clears all validation errors and hides the top summary banner
     */
    function clearAllErrors() {
        const errorBoxes = document.querySelectorAll('.field-error');
        errorBoxes.forEach(function (box) {
            box.textContent = '';
            box.classList.remove('visible');
        });

        const invalidInputs = document.querySelectorAll('.input-invalid');
        invalidInputs.forEach(function (inp) {
            inp.classList.remove('input-invalid');
        });

        if (validationSummary) {
            validationSummary.classList.remove('visible');
            validationSummaryList.innerHTML = '';
        }
    }

    // =========================================================================
    // 3. TASK 2: FORM VALIDATION (validateServiceForm)
    // =========================================================================

    /**
     * Comprehensive form validation function.
     * Validates all required fields, patterns, boundaries, and selections.
     * @returns {boolean} true if valid, false if errors found
     */
    function validateServiceForm() {
        clearAllErrors();
        const errors = [];
        const currentYear = new Date().getFullYear();

        // 1. Customer Name (not empty after trim)
        const nameVal = customerNameInput.value.trim();
        if (!nameVal) {
            const msg = 'Customer full name is required.';
            showFieldError('customer_name', msg);
            errors.push({ field: 'customer_name', message: msg });
        } else if (nameVal.length < 3) {
            const msg = 'Customer full name must be at least 3 characters.';
            showFieldError('customer_name', msg);
            errors.push({ field: 'customer_name', message: msg });
        }

        // 2. Phone Number (exactly 10 digits)
        const phoneVal = phoneNumberInput.value.trim();
        const phoneRegex = /^\d{10}$/;
        if (!phoneVal) {
            const msg = 'Phone number is required.';
            showFieldError('phone_number', msg);
            errors.push({ field: 'phone_number', message: msg });
        } else if (!phoneRegex.test(phoneVal)) {
            const msg = 'Phone number must contain exactly 10 digits (e.g. 0712345678).';
            showFieldError('phone_number', msg);
            errors.push({ field: 'phone_number', message: msg });
        }

        // 3. Email Address (valid format)
        const emailVal = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        if (!emailVal) {
            const msg = 'Email address is required.';
            showFieldError('email', msg);
            errors.push({ field: 'email', message: msg });
        } else if (!emailRegex.test(emailVal)) {
            const msg = 'Please enter a valid email address (e.g. name@domain.com).';
            showFieldError('email', msg);
            errors.push({ field: 'email', message: msg });
        }

        // 4. Registration Number (not empty)
        const regVal = regNumberInput.value.trim();
        if (!regVal) {
            const msg = 'Vehicle registration number is required.';
            showFieldError('registration_number', msg);
            errors.push({ field: 'registration_number', message: msg });
        }

        // 5. Vehicle Type (must be selected from dropdown)
        const typeVal = vehicleTypeSelect.value;
        if (!typeVal) {
            const msg = 'Please select a vehicle category.';
            showFieldError('vehicle_type', msg);
            errors.push({ field: 'vehicle_type', message: msg });
        }

        // 6. Vehicle Model (not empty)
        const modelVal = vehicleModelInput.value.trim();
        if (!modelVal) {
            const msg = 'Vehicle model is required.';
            showFieldError('vehicle_model', msg);
            errors.push({ field: 'vehicle_model', message: msg });
        }

        // 7. Year of Manufacture (between 1950 and currentYear)
        const yearVal = parseInt(manufactureYearInput.value, 10);
        if (isNaN(yearVal) || !manufactureYearInput.value.trim()) {
            const msg = 'Year of manufacture is required.';
            showFieldError('year_of_manufacture', msg);
            errors.push({ field: 'year_of_manufacture', message: msg });
        } else if (yearVal < 1950) {
            const msg = 'Year of manufacture cannot be older than 1950.';
            showFieldError('year_of_manufacture', msg);
            errors.push({ field: 'year_of_manufacture', message: msg });
        } else if (yearVal > currentYear) {
            const msg = `Year of manufacture cannot be in the future (max: ${currentYear}).`;
            showFieldError('year_of_manufacture', msg);
            errors.push({ field: 'year_of_manufacture', message: msg });
        }

        // 8. Preferred Service Date (provided and not in the past)
        const dateVal = preferredDateInput.value;
        if (!dateVal) {
            const msg = 'Preferred service date is required.';
            showFieldError('preferred_date', msg);
            errors.push({ field: 'preferred_date', message: msg });
        } else {
            const selectedDate = new Date(dateVal + 'T00:00:00');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                const msg = 'Preferred service date cannot be in the past.';
                showFieldError('preferred_date', msg);
                errors.push({ field: 'preferred_date', message: msg });
            }
        }

        // 9. At least one service selected
        let checkedCount = 0;
        serviceCheckboxes.forEach(function (cb) {
            if (cb.checked) {
                checkedCount++;
            }
        });

        if (checkedCount === 0) {
            const msg = 'Please select at least one service from the list below.';
            showFieldError('services', msg);
            errors.push({ field: 'services', message: msg });
        }

        // Handle error summary display
        if (errors.length > 0) {
            validationSummaryList.innerHTML = '';
            errors.forEach(function (err) {
                const li = document.createElement('li');
                li.textContent = err.message;
                validationSummaryList.appendChild(li);
            });
            validationSummary.classList.add('visible');
            validationSummary.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return false;
        }

        return true;
    }

    // =========================================================================
    // 4. TASK 3: DYNAMIC FUNCTIONALITY (Vehicle Type Advisory & Vehicle Age Check)
    // =========================================================================

    /**
     * Displays category-specific maintenance information upon selection
     */
    function handleVehicleTypeChange() {
        const selectedType = vehicleTypeSelect.value;
        if (selectedType && VEHICLE_TYPE_INFO[selectedType]) {
            vehicleTypeDesc.textContent = VEHICLE_TYPE_INFO[selectedType];
            vehicleTypeInfoBox.style.display = 'block';
            summaryVehicleType.textContent = selectedType;
        } else {
            vehicleTypeInfoBox.style.display = 'none';
            summaryVehicleType.textContent = 'Not specified';
        }
    }

    /**
     * Checks vehicle age whenever manufacture year is modified.
     * Displays a dynamic warning for older vehicles (> 10 years and > 15 years).
     */
    function checkVehicleAge() {
        const rawYear = manufactureYearInput.value.trim();
        const year = parseInt(rawYear, 10);
        const currentYear = new Date().getFullYear();

        if (isNaN(year) || year < 1950 || year > currentYear) {
            vehicleAgeWarning.classList.remove('visible');
            summaryVehicleAge.textContent = 'Not calculated';
            return;
        }

        const age = currentYear - year;
        summaryVehicleAge.textContent = `${age} year${age === 1 ? '' : 's'} (${year})`;

        // Age Threshold 1: Over 10 years old
        if (age > 10) {
            let note = `This vehicle is ${age} years old. Note: Vehicles over 10 years require extra mechanical vigilance regarding rubber brake hoses, suspension bushings, cooling radiators, and undercarriage rust.`;
            
            // Age Threshold 2: Over 15 years old
            if (age > 15) {
                note += ' Older vehicles (>15 years) are strongly recommended to include a comprehensive full multipoint inspection before road travel.';
            }

            vehicleAgeMessage.textContent = note;
            vehicleAgeWarning.classList.add('visible');
        } else {
            vehicleAgeWarning.classList.remove('visible');
        }
    }

    // =========================================================================
    // 5. TASK 4: COST CALCULATOR (calculateServiceCost & Summary Updates)
    // =========================================================================

    /**
     * Calculates the estimated service total and updates UI indicators
     */
    function calculateServiceCost() {
        let total = 0;
        let selectedCount = 0;
        const selectedItems = [];

        serviceCheckboxes.forEach(function (checkbox) {
            if (checkbox.checked) {
                const serviceKey = checkbox.value;
                const serviceInfo = SERVICE_PRICES[serviceKey];
                if (serviceInfo) {
                    total += serviceInfo.price;
                    selectedCount++;
                    selectedItems.push({
                        name: serviceInfo.name,
                        price: serviceInfo.price
                    });
                }
            }
        });

        // Update live total and service count badge
        totalCostDisplay.textContent = formatCurrency(total);
        serviceCountSpan.textContent = selectedCount;

        // Update selected items list in sidebar
        updateSelectedServicesSummary(selectedItems);

        // Clear error message if user checked at least one service
        if (selectedCount > 0) {
            clearFieldError('services');
        }
    }

    /**
     * Renders the itemized list of selected services in the sidebar
     * @param {Array<{name: string, price: number}>} items 
     */
    function updateSelectedServicesSummary(items) {
        selectedServicesList.innerHTML = '';

        if (items.length === 0) {
            const emptyLi = document.createElement('li');
            emptyLi.className = 'empty-notice';
            emptyLi.textContent = 'No services selected yet. Please check one or more services.';
            selectedServicesList.appendChild(emptyLi);
            return;
        }

        items.forEach(function (item) {
            const li = document.createElement('li');
            
            const nameSpan = document.createElement('span');
            nameSpan.textContent = item.name;
            
            const priceSpan = document.createElement('span');
            priceSpan.style.fontWeight = '700';
            priceSpan.textContent = formatCurrency(item.price);

            li.appendChild(nameSpan);
            li.appendChild(priceSpan);
            selectedServicesList.appendChild(li);
        });
    }

    /**
     * Resets all dynamic calculations, notices, and error messages
     */
    function resetFormCalculations() {
        clearAllErrors();
        vehicleTypeInfoBox.style.display = 'none';
        vehicleAgeWarning.classList.remove('visible');
        summaryVehicleType.textContent = 'Not specified';
        summaryVehicleAge.textContent = 'Not calculated';
        serviceCountSpan.textContent = '0';
        totalCostDisplay.textContent = formatCurrency(0);
        updateSelectedServicesSummary([]);
    }

    // =========================================================================
    // 6. TASK 5: EVENT LISTENERS (submit, change, input, reset)
    // =========================================================================

    // Submit Event
    form.addEventListener('submit', function (event) {
        const isValid = validateServiceForm();
        if (!isValid) {
            event.preventDefault(); // Stop submission on invalid data
        }
    });

    // Reset Event
    form.addEventListener('reset', function () {
        // Allow native form reset to execute before resetting custom states
        setTimeout(resetFormCalculations, 10);
    });

    // Change & Input Events for Vehicle Type & Age
    vehicleTypeSelect.addEventListener('change', function () {
        handleVehicleTypeChange();
        clearFieldError('vehicle_type');
    });

    manufactureYearInput.addEventListener('input', function () {
        checkVehicleAge();
        clearFieldError('year_of_manufacture');
    });

    // Real-time input clearing for text fields
    customerNameInput.addEventListener('input', function () {
        clearFieldError('customer_name');
    });

    phoneNumberInput.addEventListener('input', function () {
        clearFieldError('phone_number');
    });

    emailInput.addEventListener('input', function () {
        clearFieldError('email');
    });

    regNumberInput.addEventListener('input', function () {
        clearFieldError('registration_number');
    });

    vehicleModelInput.addEventListener('input', function () {
        clearFieldError('vehicle_model');
    });

    preferredDateInput.addEventListener('change', function () {
        clearFieldError('preferred_date');
    });

    // Change Event for Service Checkboxes
    serviceCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', calculateServiceCost);
    });

    // =========================================================================
    // 7. DEMONSTRATION & TEST STATE CONTROLLER
    // Allows lecturers and automated verification to inspect specific states.
    // =========================================================================
    const urlParams = new URLSearchParams(window.location.search);
    const demoMode = urlParams.get('demo');

    if (demoMode === 'sample') {
        customerNameInput.value = 'Faith Muthoni';
        phoneNumberInput.value = '0711987654';
        emailInput.value = 'faith.muthoni@example.com';
        regNumberInput.value = 'KDE 321Y';
        vehicleTypeSelect.value = 'Saloon Car';
        handleVehicleTypeChange();
        vehicleModelInput.value = 'Toyota Premio';
        manufactureYearInput.value = '2019';
        checkVehicleAge();
        preferredDateInput.value = '2026-10-18';
        const cbOil = document.getElementById('svc_oil_change');
        const cbBrake = document.getElementById('svc_brake_inspection');
        if (cbOil) cbOil.checked = true;
        if (cbBrake) cbBrake.checked = true;
        calculateServiceCost();
    } else if (demoMode === 'invalid') {
        // Leave required fields blank or with invalid formats and trigger validation
        customerNameInput.value = 'Jo';
        phoneNumberInput.value = '07123';
        emailInput.value = 'invalid-email-format';
        regNumberInput.value = '';
        vehicleTypeSelect.value = '';
        vehicleModelInput.value = '';
        manufactureYearInput.value = '1940';
        preferredDateInput.value = '2020-01-01';
        validateServiceForm();
    } else if (demoMode === 'dynamic') {
        // Demonstrate vehicle type advisory, vehicle age threshold warning, and live calculator
        customerNameInput.value = 'David Kiprono';
        phoneNumberInput.value = '0720456789';
        emailInput.value = 'david.kiprono@example.com';
        regNumberInput.value = 'KCF 555Z';
        vehicleTypeSelect.value = 'Pickup';
        handleVehicleTypeChange();
        vehicleModelInput.value = 'Isuzu D-Max';
        manufactureYearInput.value = '2008'; // 18 years old -> triggers age notice
        checkVehicleAge();
        preferredDateInput.value = '2026-10-22';
        
        // Select multiple services
        const cbOil = document.getElementById('svc_oil_change');
        const cbBrake = document.getElementById('svc_brake_inspection');
        const cbWheel = document.getElementById('svc_wheel_alignment');
        const cbPickup = document.getElementById('svc_pickup_delivery');
        if (cbOil) cbOil.checked = true;
        if (cbBrake) cbBrake.checked = true;
        if (cbWheel) cbWheel.checked = true;
        if (cbPickup) cbPickup.checked = true;
        calculateServiceCost();
    } else if (demoMode === 'individual') {
        // Focus specifically on Admission No. CT100/G/24825/24 (Digit 5 -> Pickup and Delivery KSh 1,500)
        customerNameInput.value = 'Mercy Chebet';
        phoneNumberInput.value = '0733123456';
        emailInput.value = 'mercy.chebet@example.com';
        regNumberInput.value = 'KBH 999A';
        vehicleTypeSelect.value = 'SUV';
        handleVehicleTypeChange();
        vehicleModelInput.value = 'Subaru Forester';
        manufactureYearInput.value = '2016';
        checkVehicleAge();
        preferredDateInput.value = '2026-10-25';

        const cbFull = document.getElementById('svc_full_service');
        const cbPickup = document.getElementById('svc_pickup_delivery');
        if (cbFull) cbFull.checked = true;
        if (cbPickup) cbPickup.checked = true;
        calculateServiceCost();
    } else {
        // Initial state trigger
        calculateServiceCost();
    }

    const scrollY = urlParams.get('scroll');
    if (scrollY) {
        setTimeout(function() {
            window.scrollTo(0, parseInt(scrollY, 10));
        }, 50);
    }
});
