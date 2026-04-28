jQuery(document).ready(function($) {
    const states = {
        'usa': [{ value: 'ca', text: 'California' }, { value: 'ny', text: 'New York' }, { value: 'tx', text: 'Texas' }],
        'uk': [{ value: 'eng', text: 'England' }, { value: 'sct', text: 'Scotland' }, { value: 'wls', text: 'Wales' }]
    };

    $('#irc-destination-country').on('change', function() {
        const country = $(this).val();
        const stateSelect = $('#irc-drop-state');
        stateSelect.empty().append('<option value="">Select State/County</option>'); 
        
        if (country && states[country]) {
            states[country].forEach(state => {
                stateSelect.append($('<option>', { value: state.value, text: state.text }));
            });
            stateSelect.prop('disabled', false); 
        } else {
            stateSelect.prop('disabled', true); 
        }
        $('#irc-price-result').text('Estimated Rate: --');
    });

    $('#irc-show-rate-card').on('click', function() {
        var mobile = $('#irc-mobile').val();
        var pincode = $('#irc-pickup-pincode').val();
        var country = $('#irc-destination-country').val();
        var weight = parseFloat($('#irc-weight').val());

        if (!mobile || !pincode || !country || isNaN(weight) || weight <= 0) {
            alert("Please fill in all required fields properly.");
            return;
        }
        
        $('#irc-price-result').text('Calculating...');

        $.ajax({
            url: codiRatesObj.ajaxurl, // Uses WordPress localized object
            type: 'POST',
            data: {
                action: 'codi_get_rate',
                country: country,
                weight: weight
            },
            success: function(response) {
                if(response.success) {
                    $('#irc-price-result').text('Estimated Rate: ₹' + response.data.price);
                } else {
                    $('#irc-price-result').text('Estimated Rate: ' + response.data.message);
                }
            },
            error: function() {
                $('#irc-price-result').text('Server error. Please try again.');
            }
        });
    });
});