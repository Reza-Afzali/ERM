document.addEventListener("DOMContentLoaded", function () {
  
        // --- Global Date Calculation ---
        /**
         * Helper function to format date as YYYY-MM-DD
         * @param {Date} date
         */
        function formatDate(date) {
            const yyyy = date.getFullYear();
            // Add 1 because getMonth() is 0-indexed
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        // Calculate Tomorrow's date (MIN date)
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const minBookingDateString = formatDate(tomorrow);
        
        // Calculate date one year from tomorrow (MAX date)
        const oneYearAhead = new Date(tomorrow);
        oneYearAhead.setFullYear(oneYearAhead.getFullYear() + 1);
        const maxBookingDateString = formatDate(oneYearAhead);

        // --- Time Slot Generation (New Function) ---
        /**
         * Generates time slots in 30-minute intervals from 9:00 AM to 5:00 PM
         * and populates the given select element.
         * @param {HTMLSelectElement} selectElement 
         */
        function generateTimeSlots(selectElement) {
            if (!selectElement) return;

            // Clear previous options
            selectElement.innerHTML = '';

            // Add default disabled option
            const defaultOption = document.createElement('option');
            defaultOption.value = "";
            defaultOption.textContent = "Select a Time";
            defaultOption.disabled = true;
            defaultOption.selected = true;
            selectElement.appendChild(defaultOption);

            // Start time: 9:00 AM (9 * 60 minutes)
            let time = 9 * 60; 
            // End time: 5:00 PM (17 * 60 minutes) - inclusive of the 5:00 PM slot if needed, 
            // but we stop before 5:30 PM slot. Let's make it 5:00 PM slot is the last.
            const endTime = 17 * 60; 
            const interval = 30; // 30 minutes

            while (time <= endTime) {
                const hours = Math.floor(time / 60);
                const minutes = time % 60;

                // Format time (e.g., 09:00, 14:30)
                const formattedHours = String(hours).padStart(2, '0');
                const formattedMinutes = String(minutes).padStart(2, '0');
                const time24h = `${formattedHours}:${formattedMinutes}`;

                // Convert to 12-hour format with AM/PM for display
                const displayHours = hours > 12 ? hours - 12 : hours;
                const ampm = hours >= 12 ? 'PM' : 'AM';
                
                const displayTime = `${displayHours === 0 ? 12 : displayHours}:${formattedMinutes} ${ampm}`;

                const option = document.createElement('option');
                option.value = time24h; // Value uses 24h format for submission
                option.textContent = displayTime;
                
                selectElement.appendChild(option);
                
                time += interval;
            }
        }


        // -------------------------------
        // Mobile Menu Toggle
        // -------------------------------
        const mobileBtn = document.getElementById("mobileMenuBtn");
        const mobileNav = document.getElementById("mobileNav");
        let mobileOpen = false;

        if (mobileBtn && mobileNav) {
            mobileBtn.addEventListener("click", () => {
                mobileOpen = !mobileOpen;
                mobileNav.style.display = mobileOpen ? "flex" : "none";
                mobileNav.setAttribute("aria-hidden", !mobileOpen);
            });

            function handleResize() {
                // Hide mobile menu on screens >= 768px
                if (window.innerWidth >= 768) {
                    mobileNav.style.display = "none";
                    mobileOpen = false;
                }
            }

            window.addEventListener("resize", handleResize);
            handleResize(); // Call on load to ensure correct initial state
        }

        // -------------------------------
        // Appointment Overlay & Form Setup
        // -------------------------------
        const overlay = document.getElementById("appointmentOverlay");
        const openButtons = document.querySelectorAll(".btn-book-appointment");
        const closeBtn = document.getElementById("closeAppointment");
        
        const toast = document.getElementById("toastNotification");
        const dateInput = document.getElementById('date-modal');
        const timeSelect = document.getElementById('time-modal'); // New
        
        const TRANSITION_DURATION = 350;

        // Set min and max attributes on the date input
        if (dateInput) {
            dateInput.setAttribute('min', minBookingDateString);
            dateInput.setAttribute('max', maxBookingDateString);
        }

        // Populate time slots
        if (timeSelect) {
            generateTimeSlots(timeSelect);
        }

        const showToast = (message) => {
            toast.textContent = message;
            toast.classList.add("visible");
            setTimeout(() => {
                toast.classList.remove("visible");
            }, 3000);
        };

        const openOverlay = () => {
            overlay.style.display = "flex";
            document.body.style.overflow = 'hidden'; // Prevent main body scroll
            // Delay adding 'show' class to allow CSS transition to work
            setTimeout(() => {
                overlay.classList.add("show");
            }, 10);
        };

        const closeOverlay = () => {
            overlay.classList.remove("show");
            document.body.style.overflow = ''; // Restore body scroll
            setTimeout(() => {
                overlay.style.display = "none";
            }, TRANSITION_DURATION);
        };

        // Event listeners for modal control
        if (openButtons.length > 0) {
            openButtons.forEach((btn) => {
                btn.addEventListener("click", (e) => {
                    e.preventDefault();
                    openOverlay();
                });
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener("click", closeOverlay);
        }

        if (overlay) {
            overlay.addEventListener("click", (e) => {
                if (e.target === overlay) {
                    closeOverlay();
                }
            });
        }

        // --- VALIDATION FUNCTIONS ---

        /**
         * Clears any existing error message element and error class for the field.
         * @param {HTMLElement} parentRow The form-row container.
         */
        function clearErrorDisplay(parentRow) {
            parentRow.classList.remove('error');
            const existingError = parentRow.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
        }

        /**
         * Displays the error message in red text below the input field.
         * @param {HTMLElement} parentRow The form-row container.
         * @param {string} message The error text to display.
         */
        function displayError(parentRow, message) {
            clearErrorDisplay(parentRow); // Clear previous errors first
            
            parentRow.classList.add('error');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            // Insert the error message after the input/select/textarea element
            parentRow.appendChild(errorDiv); 
        }

        /**
         * Validates a single form field and manages error display.
         * @param {HTMLElement} input The input, select, or textarea element to validate.
         * @returns {boolean} True if valid, false otherwise.
         */
        function validateField(input) {
            // === CORE FIX: Clear native validity state every time validation runs ===
            // This prevents the default browser popup from ever appearing.
            input.setCustomValidity(''); 
            
            const value = input.value.trim();
            const parentRow = input.closest('.form-row');
            let isValid = true;
            let errorMessage = '';

            // 1. Check for REQUIRED fields (handles input, select, and textarea)
            if (input.getAttribute('data-required') === 'true' && value === '') {
                isValid = false;
                errorMessage = 'This field is required.';
            } 
            // 2. Email format check
            else if (input.type === 'email' && value !== '') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address.';
                }
            }
            // 3. Phone number check
            else if (input.type === 'tel' && value !== '') {
                // Allows common formatting but requires at least 10 digits
                const phoneRegex = /^(?=.*[0-9]{10,})[0-9\s\-()+]*$/;
                if (!phoneRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid 10-digit phone number.';
                }
            }
            // 4. Date validation check (Tomorrow + one year range)
            else if (input.type === 'date' && value !== '') {
                const selectedDate = new Date(value);
                
                const minDate = new Date(minBookingDateString);
                const maxDate = new Date(maxBookingDateString);
                
                // Normalize time component
                selectedDate.setHours(0, 0, 0, 0);
                minDate.setHours(0, 0, 0, 0);
                maxDate.setHours(0, 0, 0, 0);
                
                if (selectedDate < minDate) {
                    isValid = false;
                    errorMessage = 'Booking must be at least one day in advance.';
                } else if (selectedDate > maxDate) {
                    isValid = false;
                    errorMessage = `Booking is only available until ${maxBookingDateString}.`;
                }
            }
            
            // Manage visual feedback
            if (!isValid) {
                displayError(parentRow, errorMessage);
                // Set validity for screen readers, but the message itself won't pop up due to the initial clear.
                input.setCustomValidity(errorMessage); 
            } else {
                clearErrorDisplay(parentRow);
            }
            
            return isValid;
        }

        // Function to validate the entire form
        function validateForm(form) {
            // Only target inputs/selects/textareas that are visible and relevant for validation
            const inputs = form.querySelectorAll('input, select, textarea');
            let allValid = true;

            inputs.forEach(input => {
                // Must call validateField for ALL inputs! This ensures all native validity is cleared.
                // NOTE: We check for visibility just in case a future form has hidden fields, but here all are visible.
                if (input.offsetParent !== null && !validateField(input)) {
                    allValid = false;
                }
            });

            return allValid;
        }
        
        // --- FORM SUBMISSION WITH VALIDATION ---
        
        const formsToValidate = document.querySelectorAll('.book-form'); 

        formsToValidate.forEach(form => {
            // Add live validation feedback on blur (when user leaves a field)
            const formControls = form.querySelectorAll('input, select, textarea');
            
            formControls.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                // Clear validity and visual error when user starts typing/changing
                input.addEventListener('input', function() {
                    this.setCustomValidity(''); // Keep this here for good measure
                    clearErrorDisplay(this.closest('.form-row'));
                });
            });
            
            // Handle submission
            form.addEventListener("submit", function (e) {
                // === CRITICAL STEP: Prevent default browser action immediately ===
                e.preventDefault(); 

                if (validateForm(this)) {
                    // Form is valid!
                    console.log("Form submitted successfully. Simulating submission...");
                    
                    // Retrieve form data for logging (optional, for demonstration)
                    const formData = new FormData(this);
                    const submissionData = Object.fromEntries(formData.entries());
                    console.log("Submission Data:", submissionData);

                    // Reset the form and clear error states
                    this.reset(); 
                    this.querySelectorAll('.form-row').forEach(row => clearErrorDisplay(row));
                    // Reset the select back to the disabled 'Select a Time' option
                    if (timeSelect) {
                        timeSelect.value = "";
                    }
                    
                    // Close the modal
                    closeOverlay();
                    
                    // Show success notification 
                    const successMessage = this.id === 'contactFormOverlay' 
                                        ? "Your message has been successfully sent!" 
                                        : "Your appointment has been successfully requested!";
                    showToast(successMessage);
                    
                } else {
                    // Validation failed. Scroll to the first error field.
                    const firstInvalid = this.querySelector('.form-row.error input, .form-row.error select, .form-row.error textarea');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalid.focus();
                    }
                    console.log("Validation failed. Please correct the errors.");
                }
            });
        });
    });