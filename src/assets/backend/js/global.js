jQuery( function($){
    $( document ).ready( function(){
        ULT_Sidebar.init();
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
                $container.prop( 'target', '_blank' );
            });
        }
    }
});