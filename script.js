document.addEventListener('DOMContentLoaded', function() {
    var countryCodeSelect = document.getElementById('country-code');
    var mobileInput = document.getElementById('mobile');

    countryCodeSelect.addEventListener('change', function() {
        var selectedCountryCode = countryCodeSelect.value;
        switch (selectedCountryCode) {
            case '+1':
                mobileInput.placeholder = 'Enter remaining 10 digits';
                break;
            case '+44':
                mobileInput.placeholder = 'Enter remaining 9 digits';
                break;
            case '+91':
                mobileInput.placeholder = 'Enter remaining 10 digits';
                break;
            case '+86':
                mobileInput.placeholder = 'Enter remaining 11 digits';
                break;
            case '+251':
                mobileInput.placeholder = 'Enter remaining 11 digits';
                break;
            default:
                mobileInput.placeholder = 'Enter remaining phone number';
        }
    });
});

function validateForm() {
    var email = document.getElementById('email').value;
    var firstName = document.getElementById('first-name').value;
    var lastName = document.getElementById('last-name').value;
    var country = document.getElementById('country').value;

    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return false;
    }

    // Check if any of the fields are empty
    if (!firstName || !lastName || !country) {
        alert("Please fill out all fields.");
        return false;
    }

    // Form is valid, proceed with submission
    return true;
}
document.getElementById('registration').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submit action
    // Perform your form submission logic here, such as sending data to a server
    // ...
    // After successful registration logic:
    alert('You have successfully registered!');
    // Or, if you prefer to display the message directly on the page:
    document.getElementById('success-message').innerText = 'You have successfully registered!';
});
