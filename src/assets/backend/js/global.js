jQuery( function($){
    $(function(){
        ULT_Sidebar.init();

        new ULT_ReviewsBanner();
    });

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
});

/**
 * Hides the review banner when one of the buttons is clicked
 */
class ULT_ReviewsBanner {
    constructor() {
        // Check if the banner exists
        if ( ! this.$banner ) {
            return;
        }

        // Listen clicks to the buttons
        this.listenClickToButtons();
    }

    /**
     * Listen to clicks to all the buttons that perform an action
     */
    listenClickToButtons() {
        this.$buttons.forEach( ( $button ) => {
            $button.addEventListener( 'click', ( e ) => {
                // Remove the banner
                this.removeBanner();
            } );
        } );
    }

    /**
     * Removes the banner
     */
    removeBanner() {
        this.$banner.parentNode.removeChild( this.$banner );
    }

    /**
     * Returns the banner
     * 
     * @return {Node} The banner
     */
    get $banner() {
        return document.getElementById( 'ult-review-banner' );
    }

    /**
     * Returns the action buttons
     * 
     * @return {NodeList} A NodeList with all the buttons that perform an action
     */
    get $buttons() {
        return this.$banner.querySelectorAll( '[data-action="hide-banner"]' );
    }
}