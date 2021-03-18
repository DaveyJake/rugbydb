jQuery( document ).ready( function( $ ) {
    $( '.wpcm-location-picker' ).locationpicker({
        location: {
            latitude: Number( $( '.wpcm-latitude' ).val() ),
            longitude: Number( $( '.wpcm-longitude' ).val() ),
            place_id: String( $( '.place-id' ).val() )
        },
        inputBinding: {
            latitudeInput: $( '.wpcm-latitude' ),
            longitudeInput: $( '.wpcm-longitude' ),
            locationNameInput: $( '.wpcm-address' ),
            locationPlaceIdInput: $( '.place-id' ),
            schemaStreetAddress: $( '.streetAddress' ),
            schemaAddressLocality: $( '.addressLocality' ),
            schemaAddressRegion: $( '.addressRegion' ),
            schemaPostalCode: $( '.postalCode' ),
            schemaAddressCountry: $( '.addressCountry' )
        },
        radius: 0,
        addressFormat: null,
        enableAutocomplete: true
    });
});
