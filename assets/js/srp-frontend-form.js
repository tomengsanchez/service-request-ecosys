document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('serviceRequestForm');
    if (form) {
        const submitButton = form.querySelector('input[type="submit"]');
        const spinner = form.querySelector('.srp-spinner');

        form.addEventListener('submit', function (e) {
            // Basic client-side validation check for required fields
            let isValid = true;
            form.querySelectorAll('[required]').forEach(function(input) {
                if (!input.value.trim()) {
                    isValid = false;
                    // You could add more specific visual feedback here, e.g., border color
                    if (input.nextElementSibling && input.nextElementSibling.classList.contains('srp-error-message')) {
                        // Avoid adding multiple messages if one exists
                    } else {
                        const errorSpan = document.createElement('span');
                        errorSpan.className = 'srp-error-message';
                        errorSpan.style.color = '#dc3232';
                        errorSpan.style.fontSize = '12px';
                        errorSpan.style.display = 'block';
                        errorSpan.textContent = 'This field is required.'; // Basic message
                        // input.parentNode.insertBefore(errorSpan, input.nextSibling);
                    }
                } else {
                     // Remove error message if field is now filled
                    // const existingError = input.parentNode.querySelector('.srp-error-message');
                    // if (existingError) {
                    //     existingError.remove();
                    // }
                }
            });

            if (!isValid) {
                // e.preventDefault(); // Prevent form submission if validation fails
                // alert('Please fill in all required fields.'); // Or better error display
                // return; // Stop here
                // For now, we will let server handle validation, but spinner will still show
            }


            if (submitButton && spinner) {
                submitButton.disabled = true;
                submitButton.value = 'Submitting...';
                spinner.classList.add('srp-visible');
            }
        });
    }
});