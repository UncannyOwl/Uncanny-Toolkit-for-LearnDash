jQuery( function($){
    $( document ).ready( function(){
        ULT_Sidebar.init();
        ULT_ReviewsBanner.init();
    });

    var ULT_ReviewsBanner = {
        init: function(){
            // Check if the banner exists
            if ( this.hasBanner() ){
                // Get elements
                this.getElements();

                // Add actions
                this.addActions();
            }
        },

        getElements: function(){
            this.$elements = {
                container: $( '#ult-review-banner' ),
                actions:   $( '.ult-review-banner__action' ),
                close:     $( '#ult-review-banner__close' )
            }
        },

        addActions: function(){
            // Create reference to this object instance
            let _this = this;

            // Listen clicks to the actions
            this.$elements.actions.on( 'click', function( event ){
                // Get the clicked button
                let $button = $( event.currentTarget );

                // Get the ID of the action
                let action = $button.data( 'action' );

                // Perform action
                _this.doAction( action, $button, 'ult-review-banner__action--loading' );
            });

            // Listen click to the close button
            this.$elements.close.on( 'click', function(){
                // Perform action
                _this.doAction( 'maybe-later', _this.$elements.close, 'ult-review-banner__close--loading' );
            });
        },

        doAction: function( action, $button, loadingClass ){
            // Create reference to this object instance
            let _this = this;

            // Add loading animation to the button
            $button.addClass( loadingClass );

            // Do rest call
            ULT_Utilities.restCall( 'review-banner-visibility', 'POST', {
                action: action
            }, function( response ){
                if ( response.success ){
                    // Remove the loading animation from the button
                    $button.removeClass( loadingClass );

                    // Hide the banner
                    _this.closeBanner();
                }
                else {
                    // If it fails, add a parameter to the page and reload
                    ULT_Utilities.insertParameterToURL( 'ult_review-banner-visibility', action, true );

                    // Hide the banner
                    _this.closeBanner();
                }
            }, function(){
                // If it fails, add a parameter to the page and reload
                ULT_Utilities.insertParameterToURL( 'ult_review-banner-visibility', action, true );

                // Hide the banner
                _this.closeBanner();
            });
        },

        closeBanner: function(){
            // Reference to the object instance
            let _this = this;

            this.$elements.container.slideUp( 500, function(){
                _this.$elements.container.remove();
            });
        },

        hasBanner: function(){
            return $( '#ult-review-banner' ).length > 0;
        }
    }

    var ULT_Sidebar = {
        init: function(){
            // Get elements
            this.getElements();

            // Add class to the container of the featured item
            this.addClassToContainerOfFeaturedItems();
        },

        getElements: function(){
            this.$elements = {
                featuredItems: $( '.ult-sidebar-featured-item' )
            }
        },

        addClassToContainerOfFeaturedItems: function(){
            $.each( this.$elements.featuredItems, function( index, featuredItem ){
                // Get featured item
                let $featuredItem = $( featuredItem );

                // Get container
                let $container = $featuredItem.closest( 'a' );

                // Add class to the container
                $container.addClass( 'ult-sidebar-featured-item-container' );

                // Open that link on a new tab
                //$container.prop( 'target', '_blank' );
            });
        }
    }

    var ULT_Utilities = {
        isDefined: function( variable ){
            return typeof variable !== 'undefined' && variable !== null;
        },

        // https://stackoverflow.com/a/487049/4418559
        insertParameterToURL: function( key, value, reload ){
            key = encodeURI( key );
            value = encodeURI( value );

            var kvp = document.location.search.substr( 1 ).split( '&' );

            var i = kvp.length;
            var x;

            while( i-- ){
                x = kvp[i].split( '=' );

                if ( x[ 0 ] == key ){
                    x[ 1 ] = value;
                    kvp[i] = x.join( '=' );
                    break;
                }
            }

            if ( i < 0 ){
                kvp[ kvp.length ] = [ key, value ].join( '=' );
            }

            if ( reload ){
                // This will reload the page, it's likely better to store this until finished
                document.location.search = kvp.join( '&' );
            }
            else {
                // Get title of the current page
                let pageTitle = document.title;

                // Remove the empty ones
                kvp = kvp.filter(function( parameter ){
                    return parameter != '';
                });

                // Push history and update URL
                window.history.pushState({}, pageTitle, '?' + kvp.join( '&' ) );
            }
        },

        restCall: function( endPoint, method, data, onSuccess, onFail ){
            // Do AJAX
            $.ajax({
                method: method,
                url:    UncannyToolkitGlobal.rest.url + '/' + endPoint + '/',
                data:   $.param( data ) + '&' + $.param({ doing_rest: 1 }),

                // Attach Nonce the the header of the request
                beforeSend: function( xhr ){
                    xhr.setRequestHeader( 'X-WP-Nonce', UncannyToolkitGlobal.rest.nonce );
                },

                success: function( response ){
                    // Check if the request succeeded
                    if ( response.success ){
                        // Check if onSuccess
                        if ( ULT_Utilities.isDefined( onSuccess ) ){
                            // Invoke callback
                            onSuccess( response );
                        }
                    }
                    else {
                        // The call was successful, but there were errors
                        console.error( 'The call was successful, but there were errors.' );

                        // Check if the onFail callback is defined
                        if ( ULT_Utilities.isDefined( onFail ) ){
                            // Invoke callback
                            onFail( response );
                        }
                    }
                },

                statusCode: {
                    403: function(){
                        location.reload();
                    }
                },

                fail: function ( response ){
                    if ( ULT_Utilities.isDefined( onFail ) ){
                        onFail( response );
                    }
                },
            });
        },
    }
});
