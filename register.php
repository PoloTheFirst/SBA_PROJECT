<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

// Get countries and regions for dropdowns
$countries = $pdo->query("SELECT * FROM countries ORDER BY name")->fetchAll();

// Get phone codes for dropdown
$phoneCodes = $pdo->query("SELECT code, phone_code, name FROM countries WHERE phone_code IS NOT NULL ORDER BY phone_code")->fetchAll();

// Get all states for the initial dropdown
$all_states = $pdo->query("SELECT s.*, c.name as country_name FROM states s JOIN countries c ON s.country_id = c.id ORDER BY c.name, s.name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | TravelGO Orbit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #111827;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .searchable-dropdown {
            position: relative;
        }

        .dropdown-options {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .dropdown-option {
            padding: 8px 12px;
            cursor: pointer;
            color: #333;
        }

        .dropdown-option:hover {
            background: #f0f0f0;
        }

        .address-textarea {
            min-height: 80px;
            resize: vertical;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center py-8">
    <!-- Register Card -->
    <div class="register-card rounded-xl p-8 w-full max-w-2xl shadow-2xl">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-white mb-2">Create Account</h2>
            <p class="text-blue-200">Join TravelGO Orbit today</p>
        </div>

        <!-- Error Message -->
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-sm">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Register Form -->
        <form action="auth.php" method="POST" class="space-y-4" id="registration-form">
            <input type="hidden" name="action" value="register">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Personal Information -->
                <div>
                    <label for="first_name" class="block text-white text-sm font-medium mb-1">First Name *</label>
                    <div class="relative">
                        <i data-feather="user" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <input type="text" id="first_name" name="first_name" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800"
                            placeholder="Your first name">
                    </div>
                </div>

                <div>
                    <label for="last_name" class="block text-white text-sm font-medium mb-1">Last Name *</label>
                    <div class="relative">
                        <i data-feather="user" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <input type="text" id="last_name" name="last_name" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800"
                            placeholder="Your last name">
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-white text-sm font-medium mb-1">Username *</label>
                    <div class="relative">
                        <i data-feather="at-sign" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <input type="text" id="username" name="username" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800"
                            placeholder="Choose a username"
                            pattern="[a-zA-Z0-9_]{3,20}"
                            title="3-20 characters, letters, numbers, and underscores only">
                    </div>
                    <div id="username-availability" class="text-xs mt-1 hidden"></div>
                </div>

                <div>
                    <label for="email" class="block text-white text-sm font-medium mb-1">Email *</label>
                    <div class="relative">
                        <i data-feather="mail" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <input type="email" id="email" name="email" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800"
                            placeholder="your@email.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-white text-sm font-medium mb-1">Password *</label>
                    <div class="relative">
                        <i data-feather="lock" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <input type="password" id="password" name="password" required minlength="8"
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800"
                            placeholder="Minimum 8 characters">
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-white text-sm font-medium mb-1">Confirm Password *</label>
                    <div class="relative">
                        <i data-feather="lock" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800"
                            placeholder="Confirm your password">
                    </div>
                </div>

                <!-- Phone Number with Searchable Country Code -->
                <div class="md:col-span-2">
                    <label class="block text-white text-sm font-medium mb-1">Phone Number *</label>
                    <div class="flex space-x-2">
                        <div class="relative flex-1 searchable-dropdown">
                            <i data-feather="phone" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300 z-10"></i>
                            <input type="text" id="phone_code_input" name="phone_code_input" required
                                class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800"
                                placeholder="Search country or code"
                                autocomplete="off">
                            <input type="hidden" id="phone_code" name="phone_code">
                            <div id="phone_code_options" class="dropdown-options"></div>
                        </div>
                        <div class="flex-1">
                            <input type="tel" id="phone_number" name="phone_number" required
                                class="w-full px-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800"
                                placeholder="Phone number">
                        </div>
                    </div>
                    <div id="phone-validation" class="text-xs text-gray-400 mt-1 hidden">
                        Phone number validation will appear here
                    </div>
                </div>

                <div>
                    <label for="date_of_birth" class="block text-white text-sm font-medium mb-1">Date of Birth *</label>
                    <div class="relative">
                        <i data-feather="calendar" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <input type="date" id="date_of_birth" name="date_of_birth" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800"
                            max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
                    </div>
                    <div class="text-xs text-yellow-400 mt-1">
                        <i data-feather="alert-circle" class="w-3 h-3 inline mr-1"></i>
                        You must be 18 years or older to use our service
                    </div>
                </div>

                <div>
                    <label for="gender" class="block text-white text-sm font-medium mb-1">Gender</label>
                    <div class="relative">
                        <i data-feather="user" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <select id="gender" name="gender" class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800 appearance-none">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                            <option value="prefer_not_to_say">Prefer not to say</option>
                        </select>
                        <i data-feather="chevron-down" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Country/Region, Province and City -->
                <div>
                    <label for="country" class="block text-white text-sm font-medium mb-1">Country/Region *</label>
                    <div class="relative">
                        <i data-feather="globe" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <select id="country" name="country_id" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800 appearance-none">
                            <option value="">Select Country/Region</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= $country['id'] ?>" data-code="<?= $country['code'] ?>">
                                    <?= htmlspecialchars($country['name']) ?>
                                    <?= $country['name'] === 'Hong Kong' ? ' SAR' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i data-feather="chevron-down" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                    </div>
                </div>

                <div>
                    <label for="state_id" class="block text-white text-sm font-medium mb-1">State/Province</label>
                    <div class="relative">
                        <i data-feather="map" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <select id="state_id" name="state_id"
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800 appearance-none">
                            <option value="">Select State/Province</option>
                            <!-- States will be loaded dynamically -->
                        </select>
                        <i data-feather="chevron-down" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                    </div>
                </div>

                <div>
                    <label for="city" class="block text-white text-sm font-medium mb-1">City *</label>
                    <div class="relative">
                        <i data-feather="map-pin" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <select id="city" name="city_id" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800 appearance-none" disabled>
                            <option value="">Select Country/Region First</option>
                        </select>
                        <i data-feather="chevron-down" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Address and Postal Code -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-white text-sm font-medium mb-1">Street Address *</label>
                    <div class="relative">
                        <i data-feather="home" class="absolute left-3 top-3 text-blue-300"></i>
                        <textarea id="address" name="address" required
                            class="address-textarea w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800"
                            placeholder="Enter your complete street address including building number, street name, district, etc."
                            minlength="10"></textarea>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        Please provide your complete street address including building number, street name, district, etc.
                    </div>
                </div>

                <!-- Searchable Postal Code -->
                <div class="md:col-span-2">
                    <label for="postal_code_input" class="block text-white text-sm font-medium mb-1">Postal Code *</label>
                    <div class="relative searchable-dropdown">
                        <i data-feather="hash" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300 z-10"></i>
                        <input type="text" id="postal_code_input" name="postal_code_input" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800"
                            placeholder="Type to search or enter postal code"
                            autocomplete="off">
                        <input type="hidden" id="postal_code" name="postal_code">
                        <div id="postal_code_options" class="dropdown-options"></div>
                    </div>
                    <div id="postal-code-hint" class="text-xs text-blue-400 mt-1">
                        Start typing to see suggestions or enter your postal code
                    </div>
                </div>
            </div>

            <div class="flex items-center mt-6">
                <input type="checkbox" id="terms" name="terms" required class="mr-2 w-4 h-4">
                <label for="terms" class="text-white text-sm">
                    I agree to the <a href="content_page/terms.php" class="text-blue-300 hover:underline" target="_blank">Terms of Service</a> and
                    <a href="content_page/privacy.php" class="text-blue-300 hover:underline" target="_blank">Privacy Policy</a>
                </label>
            </div>

            <div class="flex items-center mt-2">
                <input type="checkbox" id="newsletter" name="newsletter" class="mr-2 w-4 h-4">
                <label for="newsletter" class="text-white text-sm">
                    Send me travel deals and promotional emails
                </label>
            </div>

            <button type="submit"
                class="w-full bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-4 rounded-lg transition-colors mt-4">
                Create Account
            </button>
        </form>

        <div class="text-center mt-6">
            <p class="text-blue-200">Already have an account?
                <a href="login.php" class="text-white font-medium hover:text-yellow-400 transition-colors">Sign in</a>
            </p>
        </div>
    </div>

    <script>
        feather.replace();

        // Phone Code Searchable Dropdown - FIXED VERSION
        const phoneCodes = <?= json_encode($phoneCodes) ?>;
        const phoneCodeInput = document.getElementById('phone_code_input');
        const phoneCodeHidden = document.getElementById('phone_code');
        const phoneCodeOptions = document.getElementById('phone_code_options');

        // Postal Code Searchable Data
        const postalCodeSamples = [{
                code: "10001",
                location: "New York, US"
            },
            {
                code: "SW1A 1AA",
                location: "London, UK"
            },
            {
                code: "M5V 2T6",
                location: "Toronto, CA"
            },
            {
                code: "2000",
                location: "Sydney, AU"
            },
            {
                code: "100-0001",
                location: "Tokyo, JP"
            },
            {
                code: "999077",
                location: "Hong Kong SAR"
            },
            {
                code: "10110",
                location: "Bangkok, TH"
            },
            {
                code: "100",
                location: "Taipei, TW"
            },
            {
                code: "75001",
                location: "Paris, FR"
            },
            {
                code: "10115",
                location: "Berlin, DE"
            },
            {
                code: "00118",
                location: "Rome, IT"
            },
            {
                code: "1011",
                location: "Amsterdam, NL"
            },
            {
                code: "08001",
                location: "Barcelona, ES"
            },
            {
                code: "018989",
                location: "Singapore, SG"
            }
        ];

        // Phone Code Search Functionality - FIXED
        function initializePhoneCodeSearch() {
            phoneCodeInput.addEventListener('focus', function() {
                showPhoneCodeOptions('');
            });

            phoneCodeInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                showPhoneCodeOptions(searchTerm);
            });

            // Show all options when clicking the input
            phoneCodeInput.addEventListener('click', function() {
                if (this.value === '') {
                    showPhoneCodeOptions('');
                }
            });
        }

        function showPhoneCodeOptions(searchTerm) {
            phoneCodeOptions.innerHTML = '';

            let filteredCodes = phoneCodes;

            if (searchTerm.length > 0) {
                filteredCodes = phoneCodes.filter(country =>
                    country.phone_code.toLowerCase().includes(searchTerm) ||
                    country.name.toLowerCase().includes(searchTerm) ||
                    country.code.toLowerCase().includes(searchTerm)
                );
            }

            if (filteredCodes.length > 0) {
                phoneCodeOptions.style.display = 'block';
                filteredCodes.forEach(country => {
                    const option = document.createElement('div');
                    option.className = 'dropdown-option';
                    option.textContent = `${country.phone_code} - ${country.name} (${country.code})`;
                    option.addEventListener('click', function() {
                        phoneCodeInput.value = `${country.phone_code} - ${country.name}`;
                        phoneCodeHidden.value = country.phone_code;
                        phoneCodeOptions.style.display = 'none';
                        validatePhoneNumber();
                    });
                    phoneCodeOptions.appendChild(option);
                });
            } else {
                phoneCodeOptions.style.display = 'none';
            }
        }

        // Postal Code Search Functionality
        const postalCodeInput = document.getElementById('postal_code_input');
        const postalCodeHidden = document.getElementById('postal_code');
        const postalCodeOptions = document.getElementById('postal_code_options');
        const postalCodeHint = document.getElementById('postal-code-hint');

        function initializePostalCodeSearch() {
            postalCodeInput.addEventListener('focus', function() {
                showPostalCodeOptions('');
            });

            postalCodeInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                showPostalCodeOptions(searchTerm);
            });
        }

        function showPostalCodeOptions(searchTerm) {
            postalCodeOptions.innerHTML = '';

            let filteredCodes = postalCodeSamples;

            if (searchTerm.length > 0) {
                filteredCodes = postalCodeSamples.filter(item =>
                    item.code.toLowerCase().includes(searchTerm) ||
                    item.location.toLowerCase().includes(searchTerm)
                );
            }

            if (filteredCodes.length > 0) {
                postalCodeOptions.style.display = 'block';
                filteredCodes.forEach(item => {
                    const option = document.createElement('div');
                    option.className = 'dropdown-option';
                    option.textContent = `${item.code} - ${item.location}`;
                    option.addEventListener('click', function() {
                        postalCodeInput.value = item.code;
                        postalCodeHidden.value = item.code;
                        postalCodeOptions.style.display = 'none';
                        updatePostalCodeHint(item.code);
                    });
                    postalCodeOptions.appendChild(option);
                });
            } else {
                postalCodeOptions.style.display = 'none';
                postalCodeHidden.value = searchTerm;
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.searchable-dropdown')) {
                phoneCodeOptions.style.display = 'none';
                postalCodeOptions.style.display = 'none';
            }
        });

        // Username availability check
        document.getElementById('username').addEventListener('blur', function() {
            const username = this.value;
            const availabilityDiv = document.getElementById('username-availability');

            if (username.length >= 3) {
                fetch(`checkusername.php?username=${encodeURIComponent(username)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.available) {
                            availabilityDiv.textContent = '✓ Username available';
                            availabilityDiv.className = 'text-xs text-green-400 mt-1';
                        } else {
                            availabilityDiv.textContent = '✗ Username already taken';
                            availabilityDiv.className = 'text-xs text-red-400 mt-1';
                        }
                        availabilityDiv.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error checking username:', error);
                    });
            }
        });

        // Country-State-City Dropdown Logic - FIXED
        document.getElementById('country').addEventListener('change', function() {
            const countryId = this.value;
            const stateSelect = document.getElementById('state_id');
            const citySelect = document.getElementById('city');
            const selectedOption = this.options[this.selectedIndex];
            const countryCode = selectedOption.getAttribute('data-code');

            if (countryId) {
                // Load states for this country
                fetch(`get_states.php?country_id=${countryId}`)
                    .then(response => response.json())
                    .then(states => {
                        stateSelect.innerHTML = '<option value="">Select State/Province</option>';
                        states.forEach(state => {
                            stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                        });
                        stateSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error loading states:', error);
                        stateSelect.innerHTML = '<option value="">Error loading states</option>';
                    });

                // Load cities for this country
                fetch(`get_cities.php?country_id=${countryId}`)
                    .then(response => response.json())
                    .then(cities => {
                        citySelect.innerHTML = '<option value="">Select City</option>';
                        cities.forEach(city => {
                            citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                        });
                        citySelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error loading cities:', error);
                        citySelect.innerHTML = '<option value="">Error loading cities</option>';
                    });
            } else {
                stateSelect.innerHTML = '<option value="">Select Country First</option>';
                stateSelect.disabled = true;
                citySelect.innerHTML = '<option value="">Select Country First</option>';
                citySelect.disabled = true;
            }
        });

        // Phone number validation
        function validatePhoneNumber() {
            const phoneCode = phoneCodeHidden.value;
            const phoneNumber = document.getElementById('phone_number').value.replace(/\D/g, '');
            const validationDiv = document.getElementById('phone-validation');

            if (phoneCode && phoneNumber) {
                if (phoneNumber.length < 7) {
                    validationDiv.textContent = 'Phone number seems too short';
                    validationDiv.className = 'text-xs text-red-400 mt-1';
                } else if (phoneNumber.length > 15) {
                    validationDiv.textContent = 'Phone number seems too long';
                    validationDiv.className = 'text-xs text-red-400 mt-1';
                } else {
                    validationDiv.textContent = '✓ Valid phone number format';
                    validationDiv.className = 'text-xs text-green-400 mt-1';
                }
                validationDiv.classList.remove('hidden');
            } else {
                validationDiv.classList.add('hidden');
            }
        }

        document.getElementById('phone_number').addEventListener('input', validatePhoneNumber);

        // Postal code hint update
        function updatePostalCodeHint(postalCode) {
            postalCodeHint.textContent = `Using postal code: ${postalCode}`;
            postalCodeHint.className = 'text-xs text-green-400 mt-1';
        }

        // Password confirmation validation
        document.getElementById('registration-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const phoneCode = phoneCodeHidden.value;
            const phoneNumber = document.getElementById('phone_number').value;
            const username = document.getElementById('username').value;
            const countryId = document.getElementById('country').value;
            const cityId = document.getElementById('city').value;

            let errors = [];

            if (password !== confirmPassword) {
                errors.push('Passwords do not match!');
            }

            if (password.length < 8) {
                errors.push('Password must be at least 8 characters long!');
            }

            if (!phoneCode) {
                errors.push('Please select a phone country code!');
            }

            if (!phoneNumber || phoneNumber.replace(/\D/g, '').length < 7) {
                errors.push('Please enter a valid phone number!');
            }

            if (username.length < 3) {
                errors.push('Username must be at least 3 characters long!');
            }

            if (!countryId) {
                errors.push('Please select a country!');
            }

            if (!cityId) {
                errors.push('Please select a city!');
            }

            // Age validation (18+)
            const dob = new Date(document.getElementById('date_of_birth').value);
            const today = new Date();
            const age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }

            if (age < 18) {
                errors.push('You must be at least 18 years old to register!');
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errors.join('\n'));
                return false;
            }
        });

        // Address textarea auto-resize
        document.getElementById('address').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Set minimum height for address field
        document.addEventListener('DOMContentLoaded', function() {
            const addressField = document.getElementById('address');
            addressField.style.minHeight = '80px';

            // Initialize search functionality
            initializePhoneCodeSearch();
            initializePostalCodeSearch();
        });
    </script>
</body>

</html>