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
            },

            // Search a module using a data attribute
            searchModule: function( name, value ){
                return this.$elements.modules.filter(() => {
                    return $( this ).data(name) == value;
                });
            },

            // Apply all filters
            filter: function(){
                const $results = this.$elements.modules
                    .filter( ( index, toolkitModule ) => {
                        // Check if we should show the element
                        const shouldShow = (
                            // Check search results
                            this.ULT_Modules.Search.matchSearch( toolkitModule )
                            // Check filters
                            && this.ULT_Modules.Filters.matchFilters( toolkitModule )
                        );

                        const $toolkitModule = $( toolkitModule );

                        if ( shouldShow && ! $toolkitModule.is( ':visible' ) ){
                            $toolkitModule
                                .fadeIn( 100 );
                        } else if ( ! shouldShow && $toolkitModule.is( ':visible' ) ) {
                            $toolkitModule
                                .fadeOut( 100 );
                        }

                        return shouldShow;
                    }
                );    
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
                    threshold: 0.2,
                    ignoreLocation: true,
                    minMatchCharLength: 1,
                    keys: [
                        'title',
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
                this.$elements.searchField.on( 'input', ULT_Utility.debounce( () => {
                    // Query
                    let query = this.$elements.searchField.val();

                    // Get results
                    let results = this.search( query );

                    // Save values
                    this.searchQuery = query;
                    this.searchResults = results;

                    // Filter
                    this.ULT_Modules.Modules.filter();
                }, 300 ) );
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
                    ids.push( element.item.id );
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
                return this.searchQuery.length < 3 || $.inArray( element.dataset.id, results ) >= 0;;
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
                    theme: 'ult-select2'
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
                    nonce: UncannyToolkitGlobal.ajax.nonce,
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
                this.$elements.settingsButtons  = $( '.ult .ult-directory-module-settings--modal' );
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

                // Set the visibility of fields depending on
                // the values of other fields
                this.conditionalVisibility();
            },

            bindButtons: function(){
                // Reference
                let _this = this;

                // Bind click
                this.$elements.settingsButtons.on( 'click', function(){
                    // Save button
                    let $button = $( this );

                    // Get the module box
                    let $moduleBox = $button.closest( '.ult-directory-module' );

                    // Get settings ID
                    let settingsId = ULT_Utility.removeBackslash( $button.data( 'settings' ) );
                    //settingsId = settingsId.toLowerCase();

                    // Get modal
                    let $modal = _this.getModal( settingsId );

                    // Set the URL of the help buttons
                    _this.setHelpButtonURL( $moduleBox, $modal );

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

                    // Hide the notices
                    this.hideNotice( $modal );

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

            setHelpButtonURL: function( $moduleBox, $modal ){
                // Get the URL of the KB article
                const KBArticleURL = $moduleBox.find( '.ult-directory-module-settings--kb-link' ).prop( 'href' );

                // Get the "Help" button in the modal
                const $helpButton = $modal.find( '.ult-modal-action__btn-help-js' );

                // Check if it's defined
                if ( ULT_Utility.isDefined( KBArticleURL ) ){
                    // Set the URL
                    $helpButton.prop( 'href', KBArticleURL );
                }
                else {
                    $helpButton.hide();
                }
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
                // Get the default values
                let $fieldsWithDefaultValues = $modal.find( '.ult-modal-form-row:not([data-default=""])[data-id][data-type]' );
                let fieldDefaultValues = {};
                $.each( $fieldsWithDefaultValues, ( index, field ) => {
                    // Get the jQuery element
                    field = $( field );
                    // Add the default value
                    fieldDefaultValues[ field.data( 'id' ) ] = field.data( 'default' );
                });

                // Convert the array with the data from an array with objects
                // to and object
                let fieldData = {};
                $.each( data, ( index, field ) => {
                    fieldData[ field.name ] = field.value;
                });

                // Merge the default values with the field data
                fieldData = $.extend( fieldDefaultValues, fieldData );

                // Convert the field data from an object to an
                // array with objects
                data = [];
                $.each( fieldData, ( key, value ) => {
                    data.push({
                        name:  key,
                        value: value
                    });
                });

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
                            field.$element.val( field.value ).trigger( 'input' );

                            if ( field.type == 'tinymce' ){
                                let editor = tinymce.get( field.name );

                                if ( ULT_Utility.isDefined( editor ) ){
                                    if( typeof wp.editor !== 'undefined' && typeof wp.editor.autop !== 'undefined'  ){
                                        editor.setContent( wp.editor.autop( field.value ) );
                                    }else{
                                        editor.setContent( field.value );
                                    }
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
                                field.$element.prop( 'checked', true ).trigger( 'change' );
                            }
                            break;

                        case 'radio':
                            // Check the selected value
                            $.each( field.$element, function(){
                                let $radio = $(this);

                                if ( $radio.val() == field.value ){
                                    $radio.prop( 'checked', true ).trigger( 'change' );
                                }
                            });
                            break;
                    };
                });
            },

            getFieldByName: function( $modal, fieldName ){
                // Find field
                let $field    = $modal.find( `*[name="${ fieldName }"]` );
                let $fieldRow = $field.closest( '.ult-modal-form-row' );

                return {
                    $row:         $fieldRow,
                    $element:     $field,
                    type:         $field.data( 'type' ),
                    showIf:       $fieldRow.data( 'show-if' ),
                    defaultValue: $fieldRow.data( 'default' ),
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

            conditionalVisibility: function(){
                // Get all the fields with conditional visibility rules
                const $fieldsWithShowIf = $( '.ult-modal-form-row:not([data-show-if=""])' );

                // Get the modals that have a field that's shown dynamically
                let $modals = $fieldsWithShowIf.closest( '.ult-modal' );

                // Iterate the modals
                $.each( $modals, ( index, $modal ) => {
                    $modal = $( $modal );

                    // Get the fields with conditional visibility in the modal
                    const $fieldsWithShowIfInModal = $modal.find( '.ult-modal-form-row:not([data-show-if=""])' );

                    // Get the visibility conditions of each field
                    let fieldsWithShowIfInModal = {};
                    $.each( $fieldsWithShowIfInModal, ( index, $field ) => {
                        $field = $( $field );

                        // Get the field row
                        const $fieldRow = $field.closest( '.ult-modal-form-row' );

                        // Try to get the show-if conditions
                        let showIf = $fieldRow.data( 'show-if' );

                        try {
                            showIf = JSON.parse( showIf );
                        } catch ( e ){}

                        fieldsWithShowIfInModal[ $fieldRow.data( 'id' ) ] = {
                            $fieldRow:  $fieldRow,
                            conditions: showIf
                        };
                    });

                    // Listen changes in the fields
                    $modal.find( 'input, textarea, select' ).on( 'change input', ULT_Utility.debounce(() => {
                        // Get the value of all the fields
                        let fieldsValuesArray = $modal.find( 'form' ).serializeArray();
                        fieldsValues = {};
                        $.each( fieldsValuesArray, ( index, $field ) => {
                            fieldsValues[ $field.name ] = $field.value;
                        });

                        // Iterate the fields with conditional visibility
                        $.each( fieldsWithShowIfInModal, ( fieldID, field ) => {
                            // Check if it matches the condition
                            let shouldShow = Object.entries( field.conditions ).reduce(( accumulator, [ conditionKey, conditionValue ]) => {
                                return accumulator && fieldsValues[ conditionKey ] == conditionValue;
                            }, true );

                            // Check if it should show the field
                            if ( shouldShow ){
                                field.$fieldRow.removeClass( 'ult-modal-form-row--hide' );
                            }
                            else {
                                // Hide it
                                field.$fieldRow.addClass( 'ult-modal-form-row--hide' );
                            }
                        });
                    }, 20 ));
                });
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
                    theme: 'ult-select2'
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
                data:     Object.assign( {
                    nonce: UncannyToolkitGlobal.ajax.nonce
                }, data ),

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
        },

        throttle( func, interval ){
            var lastCall = 0;

            return function(){
                var now = Date.now();
                if ( lastCall + interval < now ){
                    lastCall = now;
                    return func.apply( this, arguments );
                }
            };
        },

        debounce( func, interval ){
            var lastCall = -1;

            return function(){
                clearTimeout( lastCall );
                var args = arguments;
                var self = this;
                lastCall = setTimeout( function(){
                    func.apply( self, args );
                }, interval );
            };
        },
    }
});
