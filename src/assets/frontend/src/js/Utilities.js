/**
 * Determine if a variable is set and is not NULL
 *
 * @since 3.3
 *
 * @param  {mixed}      variable - The variable being evaluated
 * @return {boolean}    TRUE if the variable is defined
 */
export const isDefined = ( variable ) => {
	// Returns true if the variable is undefined
    return typeof variable !== 'undefined' && variable !== null;
}

/**
 * Determine whether a variable is empty
 *
 * @since 3.3
 *
 * @param   {mixed}     variable - The variable being evaluated
 * @return  {boolean}   TRUE if the variable is empty
 */
export const isEmpty = ( variable ) => {
    let response = true;

    // Check if the variable is defined, otherwise is empty
    if ( isDefined( variable ) ){
        // Check if it's array
        if ( Array.isArray( variable ) ){
            response = variable.length == 0;
        }
        else if ( isObject( variable ) ){
            response = Object.keys( variable ).length == 0;
        }
        else {
            response = variable == '';
        }
    }

    return response;
}

/**
 * Determine whether a variable is an object.
 * The Object constructor creates an object wrapper for the given value. If the value is null or undefined, it will create and return an empty object, otherwise, it will return an object of a type that corresponds to the given value. If the value is an object already, it will return the value.
 *
 * @since 3.3
 *
 * @param   {mixed}     variable - The variable being evaluated
 * @return  {boolean}   TRUE if the variable is an object
 */
export const isObject = ( variable ) => {
    return variable === Object( variable );
}

/**
 * Simulate a fade in/fade out transition
 *
 * @since 3.3
 * 
 * @param  {String} fade        Whether we have to fade in our fade out the element
 * @param  {Element}   element  Element
 */
export const fade = ( in_out = 'in', element, callback ) => {
    // Check what's the class we have to add
    const fadeClass = `ult--fade-${ in_out }`;

    // Define the default callback
    callback = isDefined( callback ) ? callback : () => {};

    // Add the fade class
    element.classList.add( fadeClass );

    // Remove it after 300ms (the fade duration)
    setTimeout(() => {
        // Remove the fade class
        element.classList.remove( fadeClass );

        // Invoke the callback
        callback();
    }, 280 );
}

/**
 * Performs an AJAX request
 *
 * @since 3.3
 *
 * @param {object}    data - Data to be sent in the request
 * @param {callback}  [onSuccess] - Function to be invoked if the request is successful
 * @param {callback}  [onFail] - Function to be invoked if the request fails
 */

export function AJAXRequest( action = null, data = null, onSuccess = null, onFail = null ){
    // Add {action} to the data object
    data = { ...data, ...{
        action: action,
        nonce:  UncannyToolkit.ajax.nonce
    }};

    // Do the call
    fetch( UncannyToolkit.ajax.url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cache-Control': 'no-cache',
        },
        body: new URLSearchParams( data ),
        
    })
    .then(( response ) => {
        // Check if the call was not successful
        if ( ! response.ok ){
            console.error( '✋Uncanny Toolkit: The fetch call threw an error' );

            if ( isDefined( onFail ) ){
                onFail({ ...response, ...{ success: false }});
            }

            // Stop chain
            Promise.reject( err );
        }
    })
    .then(( response ) => response.json() )
    .then(( response ) => {
        // Check if the call was successful
        if ( response.success ){
            if ( isDefined( onSuccess ) ){
                onSuccess( response );
            }
        }
        else {
            if ( isDefined( onFail ) ){
                onFail({ ...response, ...{ success: false }});
            }
        }
    })
    .catch(( response ) => {
        console.error( '✋Uncanny Toolkit: The fetch call threw an error' );

        if ( isDefined( onFail ) ){
            onFail({ ...response, ...{ success: false }});
        }
    });;
}