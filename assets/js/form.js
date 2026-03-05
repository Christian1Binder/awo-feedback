// assets/js/form.js
document.addEventListener('DOMContentLoaded', function() {
    var sourceOtherRadio = document.getElementById('q1_source_other');
    var sourceRadios = document.querySelectorAll('input[name="q1_source"]');
    var otherInput = document.getElementById('q1_other_input');

    if (sourceOtherRadio && otherInput && sourceRadios.length > 0) {
        function toggleOtherInput() {
            if (sourceOtherRadio.checked) {
                otherInput.style.display = 'block';
                otherInput.setAttribute('required', 'required');
            } else {
                otherInput.style.display = 'none';
                otherInput.removeAttribute('required');
                otherInput.value = '';
            }
        }

        sourceRadios.forEach(function(radio) {
            radio.addEventListener('change', toggleOtherInput);
        });

        // Initial check on load
        toggleOtherInput();
    }
});