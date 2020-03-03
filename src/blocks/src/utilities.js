export const moduleIsActive = ( toolkitModule ) => {
	let isActive = false;

	if ( isDefined( window.ultGutenbergModules ) ){
		isActive = ultGutenbergModules.includes( toolkitModule );
	}
		
	return isActive;
}

export const isDefined = ( variable ) => {
    return variable !== undefined && variable !== null;
}