export const moduleIsActive = ( module ) => {
	let isActive = false;

	if ( isDefined( ultpModules.active ) ){
		if ( ultpModules.active.hasOwnProperty( module ) ){
			isActive = true;
		}
	}

	return isActive;
}

export const isDefined = ( variable ) => {
    return variable !== undefined && variable !== null;
}