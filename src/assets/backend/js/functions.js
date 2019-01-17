jQuery( function($){
    $( document ).ready( function(){
        ULT_Modules.init();
    });

    var ULT_Modules = {
        init: function(){
            // Check if we have to init all the modules functions
            if ( this.isTheModulesPage() ){
                this.Modules.init( this );
                this.Search.init( this );
                this.Filters.init( this );
                this.StatusToggle.init( this );
                this.Views.init( this );
                this.SettingsModal.init( this );
            }
        },

        Modules: {
            $elements: {},

            init: function( ULT_Modules ){
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Get elements
                this.$elements.modulesContainer = $( '.ult .ult-directory-modules' );
                this.$elements.modules = $( '.ult .ult-directory-module' );

                // Create Shuffle Instance
                this.createShuffleInstance();
            },

            // Search a module using a data attribute
            searchModule: function( name, value ){
                return this.$elements.modules.filter(() => {
                    return $( this ).data(name) == value;
                });
            },

            // Shuffle
            createShuffleInstance: function(){
                // Get Shuffle
                let Shuffle = window.Shuffle;

                // Create Shuffle instance
                this.ULT_Modules.shuffle = new Shuffle( this.$elements.modulesContainer, {
                    itemSelector: '.ult-directory-module',
                    sizer: '.ult-directory-module',
                    buffer: 1,
                });
            },

            // Apply all filters
            filter: function(){
                this.ULT_Modules.shuffle.filter(( element ) => {
                    return this.ULT_Modules.Search.matchSearch( element ) && this.ULT_Modules.Filters.matchFilters( element );
                });
            },

            // Show or hide loading animation
            changeLoadingStatus: function( $module, enable ){
                if ( enable ){
                    $module.addClass( 'ult-directory-module--loading' );
                }
                else {
                    $module.removeClass( 'ult-directory-module--loading' );
                }
            }
        },

        Search: {
            // Data
            searchQuery: '',
            searchResults: [],

            // We're going to save DOM elements here
            $elements: {},

            // Init
            init: function ( ULT_Modules ){
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Get elements
                this.$elements.searchField = $( '#ult-directory-search-input' );

                // Init Fuse
                this.createFuseInstance();
            },

            // Create Fuse Instance
            // Read http://fusejs.io/ for more instructions
            createFuseInstance: function(){
                // Fuse options
                var options = {
                    shouldSort: true,
                    threshold: 0.6,
                    location: 0,
                    distance: 100,
                    maxPatternLength: 32,
                    minMatchCharLength: 1,
                    keys: [
                        'title',
                        'description',
                        'keywords',
                    ]
                };

                // Instance
                this.fuseInstance = new Fuse( ultModules, options );

                // Bind changes
                this.bindSearch();
            },

            // Bind search
            bindSearch: function (){
                // Bind input event
                this.$elements.searchField.on( 'input', () => {
                    // Query
                    let query = this.$elements.searchField.val();

                    // Get results
                    let results = this.search( query );

                    // Save values
                    this.searchQuery = query;
                    this.searchResults = results;

                    // Filter
                    this.ULT_Modules.Modules.filter();
                });
            },

            // Search
            search: function ( string ){
                // Return array with results
                // This returns modules
                return this.fuseInstance.search( string );
            },

            // Get array with the ID of the results
            getIdsOfResults: function ( results ){
                // Ids
                let ids = [];

                // Iterate each result
                $.each( results, ( index, element ) => {
                    ids.push( element.id );
                });

                return ids;
            },

            // Determinates if a element match the search or not
            // Returns true or false
            matchSearch: function ( element ){
                // Get array with the ID of the results
                let results = this.getIdsOfResults( this.searchResults );

                // If the user was trying to search an empty string then return true,
                // Otherwise return true only if this element was one of the results
                return this.searchQuery.length < 3 || results.includes( parseInt( element.dataset.id ) );
            }
        },

        Filters: {
            $elements: {},

            currentFilters: {},

            init: function ( ULT_Modules ){
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Status
                this.$elements.selects = $( '.ult .ult-form-element__select' );

                // Create select2 instances
                this.createSelect2Instances();

                // Bind
                this.bindFilters();
            },

            createSelect2Instances: function(){
                this.$elements.selects.select2({
                    theme: 'default ult-select2'
                });
            },

            bindFilters: function(){
                // Reference object
                let _this = this;

                // Bind change of the selects
                this.$elements.selects.on( 'change', function(){
                    // Get changed filter
                    let $thisFilter = $( this );

                    // Get filter data
                    let filterData = {
                        name: $thisFilter.data( 'name' ),
                        value: $thisFilter.val()
                    };

                    // Add filter to filter list
                    _this.currentFilters[ filterData.name ] = filterData.value;

                    // If it's empty then delete the filter
                    if ( filterData.value == '' ){
                        delete _this.currentFilters[ filterData.name ];
                    }

                    // Filter
                    _this.ULT_Modules.Modules.filter();
                });
            },

            matchFilters: function ( element ){
                // Create variable where we're going to save the true/false
                // boolean that's going to decide if we have to show or hide
                // the module.
                // We're going to return true if the module match all the filter
                let matches = true;

                // Iterate each filter and check if the element matches it
                $.each( this.currentFilters, ( filterName, filterValue ) => {
                    // Get the element value
                    let elementValue = $( element ).data( filterName );

                    // Try to parse the value
                    // This will be useful if the element's value is an array
                    try {
                        elementValue = JSON.parse( elementValue )
                    } catch ( event ){}

                    // Check if we have to check more than one option
                    if ( Array.isArray( elementValue ) ){
                        matches = matches && elementValue.includes( filterValue );
                    }
                    else {
                        // Otherweise compare the value directly
                        matches = matches && elementValue == filterValue;
                    }
                });

                // Return result
                return matches;
            } 
         },

        StatusToggle: {
            $elements: {},

            init: function ( ULT_Modules ){
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Get elements
                this.$elements.toggles = $( '.ult .ult-directory-module__status-toggle' );

                // Bind Toggle
                this.bindToggles();
            },

            bindToggles: function () {
                // Reference
                let _this = this;

                // Bind changes
                this.$elements.toggles.on( 'change', function (){
                    // Get parent module
                    let $toggle = $( this ),
                        $module = $toggle.closest( '.ult-directory-module' );

                    // Change status
                    _this.changeStatus( $toggle, $module );
                });
            },

            changeStatus: function( $toggle, $module ){
                // Get data
                let shouldActive = $toggle.is( ':checked' ),
                    status = shouldActive ? 'active' : 'inactive';

                // Reference
                let _this = this;

                // Show loading animation
                this.ULT_Modules.Modules.changeLoadingStatus( $module, true );

                var data = {
                    'action': 'activate_deactivate_module',
                    'value':  $toggle.val(),
                    'active': status,
                };

                $.post( ajaxurl, data, function ( response ){
                    if ( 'success' === response.trim() ){
                        // If it's correct then change data attribute value
                        $module.data( 'status', status );
                    }
                    else {
                        // Revert change
                        $toggle.prop( 'checked', ! shouldActive );
                    }

                    //Stop loading animation
                    _this.ULT_Modules.Modules.changeLoadingStatus( $module, false );

                    // Filter values
                    _this.ULT_Modules.Modules.filter();
                });
            } 
         },

        Views: {
            $elements: {},

            init: function ( ULT_Modules ){
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Get elements
                this.$elements.directory = $( '.ult-directory' );
                this.$elements.container = $( '#ult-directory-layout-toggle' );
                this.$elements.toggles   = $( '.ult-directory-layout-item' );

                // Get or create localstorage variable to save the view
                if ( ! localStorage.ultView ){
                    localStorage.ultView = 'grid';
                }

                // Set view
                this.setView();

                // Bind toggles
                this.bindToggles();
            },

            setView: function(){
                // Remove classes
                this.$elements.directory.removeClass(( index, className ) => {
                    return ( className.match(/ult-directory-\S+/g ) || [] ).join( ' ' );
                });

                // Remove selected class from all toggles
                this.$elements.toggles.removeClass( 'ult-directory-layout-item--active' );

                // Add correct class
                this.$elements.directory.addClass( `ult-directory--${localStorage.ultView}` );

                // Add class to the clicked one
                this.findToggle( localStorage.ultView ).addClass( 'ult-directory-layout-item--active' );

                // Filter and refresh UI
                this.ULT_Modules.Modules.filter();
            },

            bindToggles: function(){
                // Reference
                let _this = this;

                // Bind click
                this.$elements.toggles.on( 'click', function(){
                    // This toggle
                    let $thisToggle = $( this );

                    // Save view
                    localStorage.ultView = $thisToggle.data( 'view' );

                    // Set view
                    _this.setView();
                });
            },

            findToggle: function ( viewId ){
                return $( `.ult-directory-layout-item[data-view="${viewId}"]` );
            } 
         },

        SettingsModal: {
            $elements: {},

            init: function( ULT_Modules ){
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Get elements
                this.$elements.modals           = $( '.ult-modal' );
                this.$elements.settingsButtons  = $( '.ult .ult-directory-module-settings' );
                this.$elements.bodyElement      = $( 'body' );
                this.$elements.containerElement = $( '#wpwrap' );

                // Add the type of field to TinyMCE. We can't add this with PHP,
                // so we will do it with JS
                this.addDataTypeToTinyMceFields();

                // Init Color Picker
                this.initColorPicker();

                // Move modals to another position to create blur effect on the page content
                this.moveModals();

                // Bind buttons
                this.bindButtons();
            },

            bindButtons: function(){
                // Reference
                let _this = this;

                // Bind click
                this.$elements.settingsButtons.on( 'click', function(){
                    // Save button
                    let $button = $( this );

                    // Get settings ID
                    let settingsId = ULT_Utility.removeBackslash( $button.data( 'settings' ) );
                    //settingsId = settingsId.toLowerCase();

                    // Get modal
                    let $modal = _this.getModal( settingsId );

                    // Show Modal
                    _this.showModal( $modal, settingsId );
                });
            },

            bindModalActions: function( $modal ){
                // Get modal elements
                let $elements = {
                    modalBox:     $modal.find( '.ult-modal-box' ),
                    form:         $modal.find( '.ult-modal-form-js' ),
                    cancelButton: $modal.find( '.ult-modal-action__btn-cancel-js' ),
                    submitButton: $modal.find( '.ult-modal-action__btn-submit-js' )
                }

                // Get settings ID
                let settingsId = $modal.data( 'settings' );

                // Bind form submission
                $elements.form.on( 'submit.ultModal', ( event ) => {
                    // Prevent default. We're going to save this using ajax
                    event.preventDefault();

                    // Get form data
                    let formData = this.getFormData( $elements.form );

                    // Add loading class to submit button
                    $elements.submitButton.addClass( 'ult-modal-action__btn--loading' );

                    // Save data
                    ULT_Utility.ajaxRequest({
                        action:  'settings_save',
                        class:   settingsId,
                        options: formData
                    }, ( response, data ) => {
                        // Remove loading animation from submit button
                        $elements.submitButton.removeClass( 'ult-modal-action__btn--loading' );

                        // Success
                        if ( ! response.error ){
                            // Show ok message
                            this.showNotice( $modal, 'success', response.message );
                        }
                        else {
                            // Validation error
                            this.showNotice( $modal, 'error', response.message );
                        }
                    },
                    ( response, data ) => {
                        // Remove loading animation from submit button
                        $elements.submitButton.removeClass( 'ult-modal-action__btn--loading' );
                    });

                    // Just trying to prevent the form again
                    return false;
                });

                // Bind cancel button
                $elements.cancelButton.on( 'click.ultModal', () => {
                    // Close modal
                    this.hideModal( $modal );

                    // Unbind modal
                    this.unbindModalActions( $modal );
                });

                // Bind click outside
                $( document ).on( 'mousedown.ultModal', ( event ) => {
                    // If the target of the click isn't the container nor a descendant of the container
                    let isClickingOutsideTheBox = $modal.is( event.target ) && $elements.modalBox.has( event.target ).length === 0;

                    // If is clicking outside the box
                    if ( isClickingOutsideTheBox ){
                        // Close modal
                        this.hideModal( $modal );

                        // Unbind modal
                        this.unbindModalActions( $modal );
                    }
                });
            },

            showNotice: function( $modal, type, message ){
                // Get notice element
                let $notice = $modal.find( '.ult-modal-notice' );

                // Remove all classes from the notice
                $notice.removeClass();

                // Set text
                $notice.text( message );

                // Add classes based on the message type
                $notice.addClass( `ult-modal-notice ult-modal-notice--${type}` );

                // Show notice
                $notice.slideDown( 150 );
            },

            hideNotice: function( $modal ){
                // Get notice element
                let $notice = $modal.find( '.ult-modal-notice' );

                // Hide notice
                $notice.hide();
            },

            moveModals: function(){
                this.$elements.modals.appendTo( this.$elements.bodyElement );
            },

            getModal: function( settingsId ){
                return $( `.ult-modal[data-settings="${settingsId}"]` );
            },

            showModal: function( $modal, settingsId ){
                // Add background to main element
                this.$elements.containerElement.addClass( 'ult-modal-open' );

                // Show modal
                $modal.fadeIn( 150, () => {
                    // Add class to know 
                    $modal.addClass( 'ult-modal--visible' );
                });

                // Show loading animation
                $modal.addClass( 'ult-modal--loading' );

                // Hide notice
                this.hideNotice( $modal );

                // Bind form
                this.bindModalActions( $modal );

                // Disable scrolling
                this.disableScroll();

                // Get field values
                this.getFieldsValue( settingsId, ( response, data ) => {
                    // Remove loading animation
                    $modal.removeClass( 'ult-modal--loading' );

                    // Fill fields
                    this.fillFields( $modal, response );

                    // Init Select2
                    this.initSelect2();
                }, ( response, data ) => {
                    // Remove loading animation
                    $modal.removeClass( 'ult-modal--loading' );

                    // Something went wrong. Abort and show error
                    this.hideModal( $modal );
                });
            },

            hideModal: function( $modal ){
                // Remove background to main element
                this.$elements.containerElement.removeClass( 'ult-modal-open' );

                // Enable scrolling
                this.enableScroll();

                // Hide the modal
                $modal.fadeOut( 150, () => {
                    // Remove visibility class to the modal
                    $modal.removeClass( 'ult-modal--visible' );
                });
            },

            fillFields: function( $modal, data ){
                // Iterate each option
                $.each( data, ( index, field ) => {
                    // Get field info
                    field = $.extend( true, {
                        value: field.value,
                        name:  field.name,
                    }, this.getFieldByName( $modal, field.name ));

                    // Check if we have the field type
                    if ( ULT_Utility.isDefined( field.type ) ){
                        // If not then try to get it
                        field.type = ULT_Utility.legacyGetFieldType( field.$element );
                    }

                    // Fill the fields
                    switch ( field.type ){
                        case 'text':
                        case 'textarea':
                        case 'tinymce':
                            field.$element.val( field.value );

                            if ( field.type == 'tinymce' ){
                                let editor = tinymce.get( field.name );

                                if ( ULT_Utility.isDefined( editor ) ){
                                    editor.execCommand( 'mceInsertContent', false, field.value );
                                }
                            }
                            break;

                        case 'color':
                        case 'select':
                            field.$element.val( field.value ).trigger( 'change' );
                            break;
                            
                        case 'checkbox':
                            // Check if the checkbox is selected
                            if ( field.value == 'on' ){
                                field.$element.prop( 'checked', true );
                            }
                            break;

                        case 'radio':
                            // Check the selected value
                            $.each( field.$element, function(){
                                let $radio = $(this);

                                if ( $radio.val() == field.value ){
                                    $radio.prop( 'checked', true );
                                }
                            });
                            break;
                    };
                })
            },

            getFieldByName: function( $modal, fieldName ){
                // Find field
                let $field = $modal.find( `*[name="${fieldName}"]` );

                return {
                    $element: $field,
                    type:   $field.data( 'type' )
                };
            },

            unbindModalActions: function( $modal ){
                // Get modal elements
                let $elements = {
                    form:         $modal.find( '.ult-modal-form-js' ),
                    cancelButton: $modal.find( '.ult-modal-action__btn-cancel-js' ),
                }

                $elements.form.off( 'submit.ultModal' );
                $elements.cancelButton.off( 'click.ultModal' );
                $( document ).off( 'mouseup.ultModal' );
            },

            getFieldsValue: function( settingsId, onSuccess, onFail ){
                ULT_Utility.ajaxRequest({
                    action: 'settings_load',
                    class:  settingsId
                }, onSuccess, onFail );
            },

            getFormData: function( $form ){
                // Get form data
                let formData = $form.serializeArray();

                // Check if we have TinyMCE fields, we need to get the value using
                // TinyMCE methods
                $.each( $form.find( 'ult-tinymce' ), ( $field ) => {
                    // Get field name
                    let fieldName = $field.prop( 'name' );

                    // Get TinyMCE instance
                    let tinyMceInstance = tinymce.get( fieldName );

                    // Add data to the formData
                    formData[ fieldName ] = tinyMceInstance.getContent()
                });

                // Return data
                return formData;
            },

            disableScroll: function(){
                // Add "noscroll" class to the html element
                $( 'html' ).addClass( 'noscroll' );
            },

            enableScroll: function(){
                // Remove class "noscroll"
                $( 'html' ).removeClass( 'noscroll' );
            },

            addDataTypeToTinyMceFields: function(){
                $( '.ult-tinymce' ).data( 'type', 'tinymce' );
            },

            initColorPicker: function(){
                $( '.uo-color-picker' ).wpColorPicker();
            },

            initSelect2: function(){
                $( '.ult-modal-form-row__select' ).select2({
                    theme: 'default ult-select2 ult-select2--modal'
                });
            },
        },

        isTheModulesPage: function(){
            return $( '.ult .ult-directory-modules' ).length > 0;
        }
    }

    var ULT_Utility = {
        ajaxRequest: function( data, onSuccess, onFail ){
            // Do AJAX
            $.ajax({
                method:   'POST',
                dataType: 'json',
                url:      ajaxurl,
                data:     data,

                success: function( response ){
                    // Check if onSuccess is defined
                    if ( ULT_Utility.isDefined( onSuccess ) ){
                        // Invoke callback
                        onSuccess( response, data );
                    }
                },

                statusCode: {
                    403: function(){
                        location.reload();
                    }
                },

                fail: function( response ){
                    console.error( 'fail' );

                    console.log( response );

                    if ( ULT_Utility.isDefined( onFail ) ){
                        onFail( response, data );
                    }
                },
            });
        },

        isDefined: function( variable ){
            return typeof variable !== 'undefined' && variable !== null;
        },

        removeBackslash: function( string ){
            return string.replace( /\\/g, '' );
        },

        legacyGetFieldType: function( $fieldElement ){
            let fieldType = 'text';

            if ( $fieldElement.is( 'input[type="color"]' ) || $fieldElement.hasClass( 'uo-color-picker' ) ){
                fieldType = 'color';
            }
            else if ( $fieldElement.is( 'input[type="text"]' ) ){
                fieldType = 'text';
            }
            else if ( $fieldElement.is( 'input[type="checkbox"]' ) ){
                fieldType = 'checkbox';
            }
            else if ( $fieldElement.is( 'input[type="radio"]' ) ){
                fieldType = 'radio';
            }
            else if ( $fieldElement.is( 'select' ) ){
                fieldType = 'select';
            }
            else if ( $fieldElement.is( 'textarea' ) ){
                if ( $fieldElement.hasClass( 'wp-editor-area' ) ){
                    fieldType = 'tinymce';
                }
                else {
                    fieldType = 'textarea';
                }
            }

            return fieldType;
        }
    }
});