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
    }, 300 );
}