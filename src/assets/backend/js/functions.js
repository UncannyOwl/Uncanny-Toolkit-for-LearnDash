jQuery(function ($) {
    $(document).ready(function () {
        ULT_Modules.init();
        $('div[rel*=leanModal]').leanModal()
    });

    var ULT_Modules = {
        init: function () {
            this.Modules.init(this);
            this.Search.init(this);
            this.Filters.init(this);
            this.StatusToggle.init(this);
            this.Views.init(this);
            this.SettingsModal.init(this);
        },

        Modules: {
            $elements: {},

            init: function (ULT_Modules) {
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Get elements
                this.$elements.modulesContainer = $('.ult .ult-directory-modules');
                this.$elements.modules = $('.ult .ult-directory-module');

                // Create Shuffle Instance
                this.createShuffleInstance();
            },

            // Search a module using a data attribute
            searchModule: function (name, value) {
                return this.$elements.modules.filter(() => {
                    return $(this).data(name) == value;
                });
            },

            // Shuffle
            createShuffleInstance: function () {
                // Get Shuffle
                let Shuffle = window.Shuffle;

                // Create Shuffle instance
                this.ULT_Modules.shuffle = new Shuffle(this.$elements.modulesContainer, {
                    itemSelector: '.ult-directory-module',
                    sizer: '.ult-directory-module',
                    buffer: 1,
                });
            },

            // Apply all filters
            filter: function () {
                this.ULT_Modules.shuffle.filter((element) => {
                    return this.ULT_Modules.Search.matchSearch(element) && this.ULT_Modules.Filters.matchFilters(element);
                });
            },

            // Show or hide loading animation
            changeLoadingStatus($module, enable) {
                if (enable) {
                    $module.addClass('ult-directory-module--loading');
                }
                else {
                    $module.removeClass('ult-directory-module--loading');
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
            init: function (ULT_Modules) {
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Get elements
                this.$elements.searchField = $('#ult-directory-search-input');

                // Init Fuse
                this.createFuseInstance();
            },

            // Create Fuse Instance
            // Read http://fusejs.io/ for more instructions
            createFuseInstance: function () {
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
                this.fuseInstance = new Fuse(ultModules, options);

                // Bind changes
                this.bindSearch();
            },

            // Bind search
            bindSearch: function () {
                // Bind input event
                this.$elements.searchField.on('input', () => {
                    // Query
                    let query = this.$elements.searchField.val();

                    // Get results
                    let results = this.search(query);

                    console.log( results );

                    // Save values
                    this.searchQuery = query;
                    this.searchResults = results;

                    // Filter
                    this.ULT_Modules.Modules.filter();
                });
            },

            // Search
            search: function (string) {
                // Return array with results
                // This returns modules
                return this.fuseInstance.search(string);
            },

            // Get array with the ID of the results
            getIdsOfResults: function (results) {
                // Ids
                let ids = [];

                // Iterate each result
                $.each(results, (index, element) => {
                    ids.push(element.id);
                });

                return ids;
            },

            // Determinates if a element match the search or not
            // Returns true or false
            matchSearch: function (element) {
                // Get array with the ID of the results
                let results = this.getIdsOfResults(this.searchResults);

                // If the user was trying to search an empty string then return true,
                // Otherwise return true only if this element was one of the results
                return this.searchQuery.length < 3 || results.includes(parseInt(element.dataset.id));
            }
        },

        Filters: {
            $elements: {},

            currentFilters: {},

            init: function (ULT_Modules) {
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Status
                this.$elements.selects = $('.ult .ult-form-element__select');

                // Create select2 instances
                this.createSelect2Instances();

                // Bind
                this.bindFilters();
            },

            createSelect2Instances() {
                this.$elements.selects.select2();
            },

            bindFilters: function () {
                // Reference object
                let _this = this;

                // Bind change of the selects
                this.$elements.selects.on('change', function () {
                    // Get changed filter
                    let $thisFilter = $(this);

                    // Get filter data
                    let filterData = {
                        name: $thisFilter.data('name'),
                        value: $thisFilter.val()
                    };

                    // Add filter to filter list
                    _this.currentFilters[filterData.name] = filterData.value;

                    // If it's empty then delete the filter
                    if (filterData.value == '') {
                        delete _this.currentFilters[filterData.name];
                    }

                    // Filter
                    _this.ULT_Modules.Modules.filter();
                });
            },

            matchFilters: function (element) {
                // Create variable where we're going to save the true/false
                // boolean that's going to decide if we have to show or hide
                // the module.
                // We're going to return true if the module match all the filter
                let matches = true;

                // Iterate each filter and check if the element matches it
                $.each(this.currentFilters, (filterName, filterValue) => {
                    // Get the element value
                    let elementValue = $(element).data(filterName);

                    // Try to parse the value
                    // This will be useful if the element's value is an array
                    try {
                        elementValue = JSON.parse(elementValue)
                    } catch (event) {
                    }

                    // Check if we have to check more than one option
                    if (Array.isArray(elementValue)) {
                        matches = matches && elementValue.includes(filterValue);
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

            init: function (ULT_Modules) {
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Get elements
                this.$elements.toggles = $('.ult .ult-directory-module__status-toggle');

                // Bind Toggle
                this.bindToggles();
            },

            bindToggles: function () {
                // Reference
                let _this = this;

                // Bind changes
                this.$elements.toggles.on('change', function () {
                    // Get parent module
                    let $toggle = $(this),
                        $module = $toggle.closest('.ult-directory-module');

                    // Change status
                    _this.changeStatus($toggle, $module);
                });
            },

            changeStatus: function ($toggle, $module) {
                // Get data
                let shouldActive = $toggle.is(':checked'),
                    status = shouldActive ? 'active' : 'inactive';

                // Reference
                let _this = this;

                // Show loading animation
                this.ULT_Modules.Modules.changeLoadingStatus($module, true);

                var data = {
                    'action': 'activate_deactivate_module',
                    'value':  $toggle.val(),
                    'active': status,
                };

                $.post( ajaxurl, data, function (response){
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

            init: function (ULT_Modules) {
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Get elements
                this.$elements.directory = $('.ult-directory');
                this.$elements.container = $('#ult-directory-layout-toggle');
                this.$elements.toggles = $('.ult-directory-layout-item');

                // Get or create localstorage variable to save the view
                if (!localStorage.ultView) {
                    localStorage.ultView = 'grid';
                }

                // Set view
                this.setView();

                // Bind toggles
                this.bindToggles();
            },

            setView: function () {
                // Remove classes
                this.$elements.directory.removeClass((index, className) => {
                    return (className.match(/ult-directory-\S+/g) || []).join(' ');
                });

                // Remove selected class from all toggles
                this.$elements.toggles.removeClass('ult-directory-layout-item--active');

                // Add correct class
                this.$elements.directory.addClass(`ult-directory--${localStorage.ultView}`);

                // Add class to the clicked one
                this.findToggle(localStorage.ultView).addClass('ult-directory-layout-item--active');

                // Filter and refresh UI
                this.ULT_Modules.Modules.filter();
            },

            bindToggles: function () {
                // Reference
                let _this = this;

                // Bind click
                this.$elements.toggles.on('click', function () {
                    // This toggle
                    let $thisToggle = $(this);

                    // Save view
                    localStorage.ultView = $thisToggle.data('view');

                    // Set view
                    _this.setView();
                });
            },

            findToggle: function (viewId) {
                return $(`.ult-directory-layout-item[data-view="${viewId}"]`);
            }
        },

        SettingsModal: {
            $elements: {},

            init: function (ULT_Modules) {
                // Create reference of main object
                this.ULT_Modules = ULT_Modules;

                // Get elements
                this.$elements.settingsButtons = $('.ult .ult-directory-module-settings');

                // Bind buttons
                this.bindButtons();
            },

            bindButtons: function () {
                // Reference
                let _this = this;

                // Bind click
                this.$elements.settingsButtons.on('click', function () {

                    // Save button
                    let $button = $(this);

                    // Get settings ID
                    let settingsId = $button.data('settings');

                    let settings_class = $button.data('settings');

                    let settings_container = $('#' + settings_class).find('.uo_settings_options');

                    console.log(`Open Settings ID: ${settingsId}`);

                    // Show Spinner
                    $('.sk-folding-cube').show();

                    //Hide Setting UI
                    settings_container.hide();

                    let data = {
                        'action': 'settings_load',
                        'class': settings_class,
                    }

                    $.post(ajaxurl, data, function (response) {
                        console.log(response);
                        let saved_options = JSON.parse(response)

                        $.each(saved_options, function (options_index, option) {
                            var element = $('#' + settings_class).find('[name="' + option['name'] + '"]')
                            var option_value = removeSlashesFromString(option['value'])
                            //console.log(option_value);
                            if (element.is('input[type="text"]')) {
                                element.val(option_value)
                            }

                            if (element.is('textarea')) {
                                element.val(option_value)
                            }

                            if (element.is('input[type="color"]')) {
                                element.val(option_value)
                            }

                            if (element.is('input[type="checkbox"]')) {
                                if ('on' === option_value) {
                                    element.prop('checked', true)
                                } else {
                                    element.prop('checked', false)
                                }
                            }

                            if (element.is('input[type="radio"]')) {

                                $.each(element, function (radio_index, radio) {
                                    if (option_value === $(radio).val()) {
                                        $(radio).prop('checked', true)
                                    }
                                })

                            }

                            if (element.is('select')) {
                                element.val(option_value)
                            }

                        })
                        // Hide Spinner
                        $('.sk-folding-cube').hide('fast')
                        // Show Settings Hide
                        settings_container.show('fast')
                        settings_container.find('.uo-color-picker').wpColorPicker()
                    })


                });
            }
        }
    }

    function removeSlashesFromString(string) {
        return string.replace(new RegExp('\\\\', 'g'), '')
    }
});