<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | TravelGO Orbit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #111827;
            color: #ffffff;
        }
        .content-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900">
    <!-- Navigation -->
    <nav class="bg-gray-800 text-white py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <a href="index.php" class="flex items-center space-x-2">
                    <i data-feather="navigation" class="text-yellow-400"></i>
                    <span class="text-xl font-bold">TravelGO Orbit</span>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    Back to Home
                </a>
                <a href="terms.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    Terms of Service
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="content-card rounded-lg p-8">
            <h1 class="text-3xl font-bold text-white mb-6">Privacy Policy</h1>
            <p class="text-gray-300 mb-6">Last updated: <?= date('F j, Y') ?></p>

            <div class="space-y-6 text-gray-300">
                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">1. Information We Collect</h2>
                    <p class="mb-4">We collect information you provide directly to us including:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li><strong>Personal Information:</strong> Name, email, phone number, date of birth</li>
                        <li><strong>Booking Information:</strong> Flight details, passenger information, payment details</li>
                        <li><strong>Location Data:</strong> Country, city, and address information</li>
                        <li><strong>Account Information:</strong> Username, password, preferences</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">2. How We Use Your Information</h2>
                    <p class="mb-4">We use the information we collect to:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Process and manage your flight bookings</li>
                        <li>Provide customer support and respond to inquiries</li>
                        <li>Send booking confirmations and travel updates</li>
                        <li>Improve our services and user experience</li>
                        <li>Send promotional offers (with your consent)</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">3. Information Sharing</h2>
                    <p class="mb-4">We do not sell your personal information. We may share information with:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li><strong>Airlines and Travel Partners:</strong> To complete your bookings</li>
                        <li><strong>Payment Processors:</strong> To handle transactions securely</li>
                        <li><strong>Legal Authorities:</strong> When required by law or to protect our rights</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">4. Data Security</h2>
                    <p class="mb-4">We implement appropriate security measures to protect your personal information including:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>SSL encryption for data transmission</li>
                        <li>Secure storage with access controls</li>
                        <li>Regular security assessments</li>
                        <li>Two-factor authentication options</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">5. Age Restrictions</h2>
                    <p class="mb-4">Our Service is not intended for individuals under the age of <strong class="text-yellow-400">18 years</strong>. We do not knowingly collect personal information from children under 18. If we become aware that we have collected personal information from a child under 18, we will take steps to delete such information.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">6. Cookies and Tracking</h2>
                    <p class="mb-4">We use cookies and similar tracking technologies to track activity on our Service and hold certain information. Cookies are files with small amount of data which may include an anonymous unique identifier.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">7. Your Rights</h2>
                    <p class="mb-4">You have the right to:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Access and receive a copy of your personal data</li>
                        <li>Rectify or update your personal information</li>
                        <li>Delete your personal information</li>
                        <li>Restrict or object to our processing of your data</li>
                        <li>Data portability</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">8. Data Retention</h2>
                    <p class="mb-4">We retain your personal information only for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">9. International Data Transfers</h2>
                    <p class="mb-4">Your information may be transferred to and maintained on computers located outside of your state, province, country or other governmental jurisdiction where the data protection laws may differ from those of your jurisdiction.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">10. Changes to This Policy</h2>
                    <p class="mb-4">We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">11. Contact Us</h2>
                    <p class="mb-4">If you have any questions about this Privacy Policy, please contact us:</p>
                    <div class="bg-gray-800 p-4 rounded-lg">
                        <p><strong>Email:</strong> privacy@travelgorbit.com</p>
                        <p><strong>Address:</strong> TravelGO Orbit Privacy Office, 123 Travel Street, Hong Kong SAR</p>
                        <p><strong>Data Protection Officer:</strong> dpo@travelgorbit.com</p>
                    </div>
                </section>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-700">
                <p class="text-gray-400 text-sm">
                    By using our Service, you acknowledge that you have read and understand this Privacy Policy.
                </p>
            </div>
        </div>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>