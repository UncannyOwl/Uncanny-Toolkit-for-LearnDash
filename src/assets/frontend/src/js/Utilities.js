/**
 * Determine if a variable is set and is not NULL
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
 * @since 0.2
 *
 * @param   {mixed}     variable - The variable being evaluated
 * @return  {boolean}   TRUE if the variable is empty
 */

export const isEmpty = ( variable ) => {
    let response = true;

    // Check if the variable is defined, otherwise is empty
    if ( isDefined( variable ) ){
        // Check if it's array
        if ( $.isArray( variable ) ){
            response = variable.length == 0;
        }
        else if ( isObject( variable ) ){
            response = $.isEmptyObject( variable );
        }
        else {
            response = variable == '';
        }
    }

    return response;
}