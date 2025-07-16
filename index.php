<?php
session_start();
require_once 'config.php';

// Fetch Doctor Details (assuming doctor_id = 1 for Dr. Kapil Shukla)
$doctor_id = 1; // This could be passed via GET parameter in a real app

$doctor_stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
$doctor_stmt->bind_param("i", $doctor_id);
$doctor_stmt->execute();
$doctor_result = $doctor_stmt->get_result();
$doctor = $doctor_result->fetch_assoc();

if (!$doctor) {
    die("Doctor not found.");
}

// Fetch Clinic and Fees Details for this doctor
$clinic_stmt = $conn->prepare("SELECT c.*, f.first_visit_fee, f.follow_up_fee FROM clinics c JOIN fees f ON c.clinic_id = f.clinic_id WHERE c.doctor_id = ?");
$clinic_stmt->bind_param("i", $doctor_id);
$clinic_stmt->execute();
$clinic_result = $clinic_stmt->get_result();
$clinic = $clinic_result->fetch_assoc(); // Assuming one clinic per doctor for simplicity here

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_email = $is_logged_in ? $_SESSION['user_email'] : '';

?>
<!DOCTYPE html>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>Doctor Appointment - Dr. Kapil Shukla</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"/>
  <!-- Flatpickr for date picker -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <style>
   body {
      font-family: "Inter", sans-serif;
    }
  </style>
 </head>
 <body class="bg-gray-100">
  <header class="bg-white border-b border-gray-200 flex items-center px-4 py-2">
   <img alt="Doctors App logo with a blue heart and text Doctors App below" class="h-10 w-10" height="40" src="<?php echo htmlspecialchars($doctor['logo_image']); ?>" width="40"/>
   <span class="ml-3 text-sm text-gray-900">
    <?php echo htmlspecialchars($doctor['name']); ?>
   </span>
  </header>
  <main class="bg-white mt-2 rounded-t-xl shadow-sm max-w-4xl mx-auto">
   <section class="flex items-center gap-6 px-6 py-4 border-b border-gray-200">
    <img alt="Doctor avatar illustration with stethoscope and blue tie" class="w-16 h-16 rounded-full" height="64" src="<?php echo htmlspecialchars($doctor['profile_image']); ?>" width="64"/>
    <div>
     <h2 class="text-sm font-semibold text-gray-900">
      <?php echo htmlspecialchars($doctor['name']); ?>
     </h2>
     <p class="text-xs font-bold text-gray-900 leading-tight">
      <?php echo htmlspecialchars($doctor['qualifications']); ?>
     </p>
     <p class="text-xs text-gray-700 leading-tight">
      Pediatrician
     </p>
     <button class="mt-2 bg-indigo-500 text-white text-xs font-semibold rounded-full px-5 py-1.5 hover:bg-indigo-600" type="button">
      View Profile
     </button>
    </div>
   </section>
   <section class="px-6 py-4">
    <div class="flex justify-between items-center mb-3">
     <div>
      <h3 class="text-sm font-semibold text-gray-900">
       Book Appointment
      </h3>
      <?php if ($clinic): ?>
      <p class="text-xs text-blue-700 font-semibold leading-tight">
       First Visit Fees: ₹ <?php echo htmlspecialchars(number_format($clinic['first_visit_fee'], 0)); ?> Pay at Clinic
      </p>
      <p class="text-xs text-blue-700 font-semibold leading-tight">
       Follow Up Fees: ₹ <?php echo htmlspecialchars(number_format($clinic['follow_up_fee'], 0)); ?> Pay at Clinic
      </p>
      <?php endif; ?>
     </div>
     <?php if ($clinic && $clinic['in_clinic_icon']): ?>
     <div>
      <img alt="Blue icon with white house and plus sign with text In-clinic" class="w-10 h-10" height="40" src="<?php echo htmlspecialchars($clinic['in_clinic_icon']); ?>" width="40"/>
     </div>
     <?php endif; ?>
    </div>
    <form class="space-y-6 text-sm text-gray-900" id="appointmentForm">
     <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4">
      <label class="w-32 font-semibold mb-1 sm:mb-0" for="clinic">
       Clinic Name
      </label>
      <div class="flex flex-col">
       <select class="border border-gray-300 rounded-md px-3 py-1.5 w-full max-w-xs text-xs" id="clinic" name="clinic_id" disabled>
        <?php if ($clinic): ?>
        <option value="<?php echo htmlspecialchars($clinic['clinic_id']); ?>">
         <?php echo htmlspecialchars($clinic['name']); ?>
        </option>
        <?php else: ?>
        <option>No Clinic Available</option>
        <?php endif; ?>
       </select>
       <?php if ($clinic): ?>
       <p class="mt-1 text-xs font-semibold leading-tight max-w-xs">
        <?php echo htmlspecialchars($clinic['address']); ?>
       </p>
       <?php endif; ?>
      </div>
     </div>
     <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4">
      <label class="w-32 font-semibold mb-1 sm:mb-0" for="appointment-date">
       Appointment Date
      </label>
      <input class="border border-gray-300 rounded-md px-3 py-1.5 w-full max-w-xs text-xs" id="appointment-date" name="appointment_date" type="text" value="<?php echo date('m/d/Y'); ?>"/>
     </div>
     <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4">
      <label class="w-32 font-semibold mb-1 sm:mb-0">
       Available Slots
      </label>
      <div class="flex items-center space-x-4 text-xs">
       <div class="flex items-center space-x-1">
        <span class="w-12 h-4 border border-green-400 rounded-full">
        </span>
        <span>
         Available
        </span>
       </div>
       <div class="flex items-center space-x-1">
        <span class="w-12 h-4 bg-gray-600 rounded-full">
        </span>
        <span>
         Booked
        </span>
       </div>
      </div>
     </div>
     <div id="slots-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 text-center text-sm font-semibold text-gray-900 pt-3 border-t border-gray-300">
      <!-- Slots will be loaded here via JavaScript -->
      <div class="col-span-full text-center text-xs font-semibold text-gray-900" id="noSlotsMessage">Loading Slots...</div>
     </div>
     <input type="hidden" id="selected-slot-id" name="selected_slot_id">

     <div class="text-center mt-4">
         <!-- Both buttons are always present, their visibility controlled by JS -->
         <button type="button" id="continueButton" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md opacity-50 cursor-not-allowed hidden" disabled>
             Continue to Book
         </button>
         <button type="button" id="loginToBookButton" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md hidden">
             Login to Book Appointment
         </button>
     </div>

    </form>

    <!-- Login/OTP Modal -->
    <div id="loginModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Login / Register</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">Enter your email to receive an OTP.</p>
                    <input type="email" id="userEmail" class="mt-3 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="your@example.com">
                    <button id="sendOtpButton" class="mt-4 bg-indigo-500 text-white text-xs font-semibold rounded-md px-4 py-2 hover:bg-indigo-600 w-full">Send OTP</button>

                    <div id="otpSection" class="mt-4 hidden">
                        <p class="text-sm text-gray-500">Enter the OTP sent to your email.</p>
                        <input type="text" id="userOtp" class="mt-3 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter OTP">
                        <button id="verifyOtpButton" class="mt-4 bg-green-500 text-white text-xs font-semibold rounded-md px-4 py-2 hover:bg-green-600 w-full">Verify OTP</button>
                    </div>
                    <p id="authMessage" class="mt-3 text-sm text-red-500"></p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="closeModalButton" class="px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

   </section>
  </main>

  <script>
    const clinicId = document.getElementById('clinic').value;
    const doctorId = <?php echo $doctor_id; ?>;
    const appointmentDateInput = document.getElementById('appointment-date');
    const slotsContainer = document.getElementById('slots-container');
    const noSlotsMessage = document.getElementById('noSlotsMessage');
    const selectedSlotIdInput = document.getElementById('selected-slot-id');
    const continueButton = document.getElementById('continueButton');
    const loginToBookButton = document.getElementById('loginToBookButton');
    const loginModal = document.getElementById('loginModal');
    const closeModalButton = document.getElementById('closeModalButton');
    const userEmailInput = document.getElementById('userEmail');
    const sendOtpButton = document.getElementById('sendOtpButton');
    const otpSection = document.getElementById('otpSection');
    const userOtpInput = document.getElementById('userOtp');
    const verifyOtpButton = document.getElementById('verifyOtpButton');
    const authMessage = document.getElementById('authMessage');

    let selectedSlotElement = null; // To keep track of the currently selected slot div

    // Initialize Flatpickr
    flatpickr(appointmentDateInput, {
        dateFormat: "m/d/Y",
        minDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            fetchSlots(dateStr);
        }
    });

    // Function to fetch slots via AJAX
    function fetchSlots(date) {
        noSlotsMessage.textContent = 'Loading Slots...';
        slotsContainer.innerHTML = ''; // Clear previous slots
        selectedSlotIdInput.value = ''; // Clear selected slot
        disableContinueButton(); // Call this here as well

        fetch(`get_slots.php?doctor_id=${doctorId}&clinic_id=${clinicId}&appointment_date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    noSlotsMessage.style.display = 'none';
                    data.forEach(slot => {
                        const slotDiv = document.createElement('div');
                        slotDiv.dataset.slotId = slot.slot_id;
                        slotDiv.dataset.isBooked = slot.is_booked;
                        slotDiv.className = `p-2 border rounded-md cursor-pointer transition-colors duration-200
                                            ${slot.is_booked ? 'bg-gray-300 text-gray-600 line-through' : 'bg-green-100 hover:bg-green-200 border-green-400'}`;
                        if (slot.is_booked) {
                            slotDiv.classList.add('pointer-events-none', 'opacity-60');
                        } else {
                            slotDiv.classList.add('slot-item'); // Add a class for easy selection
                        }
                        slotDiv.textContent = `${slot.start_time.substring(0, 5)} - ${slot.end_time.substring(0, 5)}`;
                        slotsContainer.appendChild(slotDiv);
                    });
                } else {
                    noSlotsMessage.style.display = 'block';
                    noSlotsMessage.textContent = 'No Slots Available for this date.';
                }
            })
            .catch(error => {
                console.error('Error fetching slots:', error);
                noSlotsMessage.style.display = 'block';
                noSlotsMessage.textContent = 'Error loading slots. Please try again.';
            });
    }

    // Event delegation for slot selection
    slotsContainer.addEventListener('click', function(event) {
        const clickedSlot = event.target.closest('.slot-item');
        if (clickedSlot) {
            // Remove 'selected' class from previously selected slot
            if (selectedSlotElement) {
                selectedSlotElement.classList.remove('border-blue-500', 'bg-blue-200');
            }

            // Add 'selected' class to the new slot
            clickedSlot.classList.add('border-blue-500', 'bg-blue-200');
            selectedSlotElement = clickedSlot;

            selectedSlotIdInput.value = clickedSlot.dataset.slotId;
            enableContinueButton();
        }
    });

    function enableContinueButton() {
        // Only enable if the button exists (which it now always will)
        if (continueButton) {
            continueButton.disabled = false;
            continueButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    function disableContinueButton() {
        // Only disable if the button exists (which it now always will)
        if (continueButton) {
            continueButton.disabled = true;
            continueButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Initial button visibility based on login status
    const isUserLoggedIn = <?php echo json_encode($is_logged_in); ?>;
    if (isUserLoggedIn) {
        loginToBookButton.classList.add('hidden');
        continueButton.classList.remove('hidden'); // Show continue button if logged in
    } else {
        loginToBookButton.classList.remove('hidden'); // Show login button if not logged in
        continueButton.classList.add('hidden'); // Hide continue button if not logged in
    }


    // Initial fetch of slots for today's date
    fetchSlots(appointmentDateInput.value);


    // --- Login/OTP Modal Logic ---
    loginToBookButton.addEventListener('click', function() {
        loginModal.classList.remove('hidden');
        authMessage.textContent = ''; // Clear previous messages
        otpSection.classList.add('hidden'); // Hide OTP section initially
    });

    closeModalButton.addEventListener('click', function() {
        loginModal.classList.add('hidden');
    });

    // Close modal if clicked outside
    window.addEventListener('click', function(event) {
        if (event.target == loginModal) {
            loginModal.classList.add('hidden');
        }
    });

    sendOtpButton.addEventListener('click', function() {
        const email = userEmailInput.value;
        if (!email) {
            authMessage.textContent = 'Please enter your email address.';
            return;
        }

        fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                authMessage.textContent = data.message;
                authMessage.classList.remove('text-red-500');
                authMessage.classList.add('text-green-500');
                otpSection.classList.remove('hidden');
            } else {
                authMessage.textContent = data.message;
                authMessage.classList.remove('text-green-500');
                authMessage.classList.add('text-red-500');
                otpSection.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Error sending OTP:', error);
            authMessage.textContent = 'An error occurred. Please try again.';
            authMessage.classList.remove('text-green-500');
            authMessage.classList.add('text-red-500');
        });
    });

    verifyOtpButton.addEventListener('click', function() {
        const email = userEmailInput.value;
        const otp = userOtpInput.value;
        if (!email || !otp) {
            authMessage.textContent = 'Please enter both email and OTP.';
            return;
        }

        fetch('process_otp.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}&otp=${encodeURIComponent(otp)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                authMessage.textContent = data.message;
                authMessage.classList.remove('text-red-500');
                authMessage.classList.add('text-green-500');
                alert('Login successful! You can now book an appointment.');
                loginModal.classList.add('hidden');
                // Reload the page or update UI to reflect login status
                window.location.reload();
            } else {
                authMessage.textContent = data.message;
                authMessage.classList.remove('text-green-500');
                authMessage.classList.add('text-red-500');
            }
        })
        .catch(error => {
            console.error('Error verifying OTP:', error);
            authMessage.textContent = 'An error occurred during OTP verification. Please try again.';
            authMessage.classList.remove('text-green-500');
            authMessage.classList.add('text-red-500');
        });
    });

    // --- Book Appointment Logic ---
    continueButton.addEventListener('click', function() {
        const selectedSlotId = selectedSlotIdInput.value;
        if (!selectedSlotId) {
            alert('Please select an available slot first.');
            return;
        }

        if (confirm('Are you sure you want to book this appointment?')) {
            fetch('book_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `slot_id=${encodeURIComponent(selectedSlotId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Appointment booked successfully! You will receive a confirmation email.');
                    // Re-fetch slots to update their status (show the booked one as grey)
                    fetchSlots(appointmentDateInput.value);
                } else {
                    alert('Booking failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error booking appointment:', error);
                alert('An error occurred during booking. Please try again.');
            });
        }
    });

  </script>
 </body>
</html>
