  // Country code placeholder updates
document.addEventListener('DOMContentLoaded', function() {
    var countryCodeSelect = document.getElementById('country-code');
    var mobileInput = document.getElementById('mobile');

    if (countryCodeSelect && mobileInput) {
        countryCodeSelect.addEventListener('change', function() {
            var code = countryCodeSelect.value;
            var placeholders = {
                '+1': 'Enter remaining 10 digits',
                '+44': 'Enter remaining 9 digits',
                '+91': 'Enter remaining 10 digits',
                '+86': 'Enter remaining 11 digits',
                '+81': 'Enter remaining 10 digits',
                '+7': 'Enter remaining 10 digits',
                '+33': 'Enter remaining 9 digits',
                '+49': 'Enter remaining 10 digits',
                '+61': 'Enter remaining 9 digits',
                '+251': 'Enter remaining 9 digits'
            };
            mobileInput.placeholder = placeholders[code] || 'Enter your phone number';
        });
        
        // Trigger once on load
        countryCodeSelect.dispatchEvent(new Event('change'));
    }

    // Form validation - only blocks if invalid
    var regForm = document.querySelector('form[action="p.php"]');
    if (regForm) {
        regForm.addEventListener('submit', function(event) {
            // Run validation
            if (!validateForm()) {
                event.preventDefault(); // Only stop if validation fails
                event.stopPropagation();
                return false;
            }
            // If validation passes, form submits normally to p.php
            console.log('Form validation passed - submitting...');
        });
    }
});

// Validation function - returns TRUE to allow submission, FALSE to block
function validateForm() {
    // Get elements
    var username = document.getElementById('username');
    var password = document.getElementById('password');
    var firstName = document.getElementById('first-name');
    var lastName = document.getElementById('last-name');
    var email = document.getElementById('email');
    var mobile = document.getElementById('mobile');
    var gender = document.getElementById('gender');
    var course = document.getElementById('course');
    var country = document.getElementById('country');

    // Check if elements exist
    if (!username || !email || !firstName || !lastName) {
        console.error('Form elements not found');
        return true; // Let server handle it
    }

    // Username validation (min 3 chars)
    if (username.value.trim().length < 3) {
        alert('Username must be at least 3 characters long.');
        username.focus();
        return false;
    }

    // Password validation (min 6 chars)
    if (password && password.value.length < 6) {
        alert('Password must be at least 6 characters long.');
        password.focus();
        return false;
    }

    // Name validation (only letters and spaces)
    var namePattern = /^[a-zA-Z\s]+$/;
    if (!namePattern.test(firstName.value.trim())) {
        alert('First name should contain only letters.');
        firstName.focus();
        return false;
    }
    if (!namePattern.test(lastName.value.trim())) {
        alert('Last name should contain only letters.');
        lastName.focus();
        return false;
    }

    // Email validation
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email.value.trim())) {
        alert('Please enter a valid email address (e.g., user@example.com).');
        email.focus();
        return false;
    }

    // Phone validation (only numbers, min 8 digits)
    if (mobile) {
        var phoneDigits = mobile.value.replace(/\D/g, '');
        if (phoneDigits.length < 8) {
            alert('Please enter a valid phone number (at least 8 digits).');
            mobile.focus();
            return false;
        }
    }

    // Gender validation
    if (gender && gender.value === '') {
        alert('Please select your gender.');
        gender.focus();
        return false;
    }

    // Course validation
    if (course && course.value === '') {
        alert('Please select a course.');
        course.focus();
        return false;
    }

    // Country validation
    if (country && country.value === '') {
        alert('Please select your country.');
        country.focus();
        return false;
    }

    // All validations passed - allow form submission
    return true;
}

// Optional: Real-time validation feedback
document.addEventListener('DOMContentLoaded', function() {
    // Add visual feedback on input
    var inputs = document.querySelectorAll('#registration input[required], #registration select[required]');
    
    inputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.style.borderColor = '#dc3545'; // Red
            } else {
                this.style.borderColor = '#28a745'; // Green
            }
        });
        
        input.addEventListener('focus', function() {
            this.style.borderColor = '#ff7200'; // Orange (brand color)
        });
    });
});
// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        var target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
