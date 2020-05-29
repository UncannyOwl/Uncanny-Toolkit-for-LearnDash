const events = require( 'eventslibjs' );

import {
	fade,
	delegateEvent,
	isEmpty,
	isDefined
} from './Utilities';

class Modals {
	constructor(){
		// Create an array to save the IDs of all the modals
		// on this page
		this.allModals = [];

		// Get elements
		this.getElements();

		// Initialize modals
		this.createModals();
	}

	getElements(){
		this.$elements = {
			modals: document.querySelectorAll( '.ult-modal' )
		}
	}

	createModals(){
		// Itearate the modals (if any)
		[ ...this.$elements.modals ].forEach(( $modal ) => {
			// Get the modal ID
			const modalID = isDefined( $modal.getAttribute( 'data-id' ) ) ? $modal.getAttribute( 'data-id' ) : '';

			// Check if there isn't already a modal with that ID
			if ( ! this.allModals.includes( modalID ) ){
				// Then create the modal
				new Modal( $modal );

				// And save the ID
				this.allModals.push( modalID );
			}
		});
	}
}

class Modal {
	constructor( $modalContent ){
		// Get the modal data
		this.getModalData( $modalContent );

		// Render the modal
		this.renderModal( $modalContent );

		// Listen clicks to containers that are trying to open the modal
		this.handleModalOpen();

		// Listen clicks outside the modal box
		this.handleClickOutsideTheModal();
	}

	getModalData( $modalContent ){
		// Get the main modal data like ID, title and the parameter
		// that defines whether it should have the "Discard" button
		this.modalData = {
			id:         isDefined( $modalContent.getAttribute( 'data-id' ) ) ? $modalContent.getAttribute( 'data-id' ) : '',
			title:      isDefined( $modalContent.getAttribute( 'data-title' ) ) ? $modalContent.getAttribute( 'data-title' ) : '',
			btnDismiss: isDefined( $modalContent.getAttribute( 'data-btn-dismiss' ) ) ? !! parseInt( $modalContent.getAttribute( 'data-btn-dismiss' ) ) : true,
		}
	}

	renderModal( $modalContent ){
		// Create the modal
		const $modal = document.createElement( 'div' );

		// Add class to the main container
		$modal.classList.add( 'ult-modal-container' );

		// Set the data-id attribute
		$modal.setAttribute( 'data-id', this.modalData.id );

		// Set the children
		$modal.innerHTML = `
			<div class="ult-modal__inside">
				<div class="ult-modal-box">
					${ ! isEmpty( this.modalData.title ) ? `
						<div class="ult-modal-box-header">
							<h2>${ this.modalData.title }</h2>
						</div>
					` : '' }

					<div class="ult-modal-box-content"></div>

					${ this.modalData.btnDismiss ? `
						<div class="ult-modal-box-footer">
							<button class="ult-modal-box-footer__dismiss">
								${ UncannyToolkit.i18n.dismiss }
							</button>
						</div>
					` : '' }
				</div>
			</div>
		`;

		// Add the modal content
		$modal.querySelector( '.ult-modal-box-content' ).appendChild( $modalContent );

		// Add the modal to the end of the document
		document.body.appendChild( $modal );

		// Save the main elements
		this.$elements = {
			container: $modal,
			box: $modal.querySelector( '.ult-modal-box' )
		}

		// Check if it has the dismiss button
		if ( this.modalData.btnDismiss ){
			// Get the dismiss button
			this.$elements.dismissBtn = $modal.querySelector( '.ult-modal-box-footer__dismiss' );

			// Add the event listener
			this.handleDismissButton();
		}
	}

	handleModalOpen(){
		// Listen clicks to the document, but invoke the callback only if the
		// clicked element matches one of the following selectors:
		// 1. .ult-modal-open[data-id="${ this.modalData.id }"]
		// 2. [class*="ult-modal-open----${ this.modalData.id }"]
		// 3. [href*="ult-modal-open----${ this.modalData.id }"]
		events.on( 'click', `.ult-modal-open[data-id="${ this.modalData.id }"], [class*="ult-modal-open----${ this.modalData.id }"], [href*="ult-modal-open----${ this.modalData.id }"]`, ( event ) => {
			// Prevent default
			event.preventDefault();

			// Show the modal
			this.showModal(); 
		});
	}

	handleDismissButton(){
		// Listen clicks to the dismiss button
		this.$elements.dismissBtn.addEventListener( 'click', () => {
			// Hide the modal
			this.hideModal();
		});
	}

	handleClickOutsideTheModal(){
		// Listen clicks to the while modal container
		this.$elements.container.addEventListener( 'mouseup', ( event ) => {
            // Set the targetted container
            const $container = this.$elements.box;

            if ( ( $container !== event.target ) && ! $container.contains( event.target ) ){
                // Hide modal
                this.hideModal();
            }
        });
	}

	showModal(){
		// Show the modal
		fade( 'in', this.$elements.container );
		this.$elements.container.classList.add( 'ult-modal-container--open' );

		// Add class to the body element, so the user can use it
		// to change other stuff
		document.body.classList.add( 'ult-modal--open' );
	}

	hideModal(){
		// Hide the modal
		fade( 'out', this.$elements.container, () => {
			// Hide the modal
			this.$elements.container.classList.remove( 'ult-modal-container--open' );

			// Remove class from the body element
			document.body.classList.remove( 'ult-modal--open' );
		});
	}
}

export default Modals;